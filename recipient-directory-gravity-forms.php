<?php
/**
 * Plugin Name: Recipient Directory for Gravity Forms
 * Plugin URI: https://github.com/637digital/recipient-directory-gravity-forms
 * Description: Network-wide contact management for Gravity Forms notifications with merge tag support
 * Version: 1.0.0
 * Author: 637 Digital Solutions
 * Author URI: https://637digital.com
 * License: MIT
 * Network: true
 * Text Domain: recipient-directory-gf
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RDGF_VERSION', '1.0.0');
define('RDGF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RDGF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RDGF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Recipient_Director_GF {
    
    /**
     * Contact types with their merge tag variables
     */
    private $contact_types = array(
        'SPA' => 'Senior Program Administrator',
        'DEV' => 'Development/Fundraising',
        'MARCOMM' => 'Marketing and Communications',
        'GEN' => 'General',
        'BIZ' => 'Business Office',
        'ED' => 'Executive Director'
    );
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Network admin menu
        add_action('network_admin_menu', array($this, 'add_network_admin_menu'));
        
        // Admin init for settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Gravity Forms merge tags integration
        add_filter('gform_custom_merge_tags', array($this, 'add_custom_merge_tags'), 10, 4);
        add_filter('gform_replace_merge_tags', array($this, 'replace_merge_tags'), 10, 7);
        
        // AJAX handlers for settings page
        add_action('wp_ajax_rdgf_save_contacts', array($this, 'ajax_save_contacts'));
        add_action('wp_ajax_rdgf_load_contacts', array($this, 'ajax_load_contacts'));
    }
    
    /**
     * Add network admin menu
     */
    public function add_network_admin_menu() {
        add_menu_page(
            __('Recipient Directory', 'recipient-directory-gf'),
            __('Recipient Directory', 'recipient-directory-gf'),
            'manage_network_options',
            'recipient-directory-gf',
            array($this, 'render_admin_page'),
            'dashicons-email',
            80
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Settings are stored per-site in network options
        // Format: rdgf_contacts_{blog_id}
    }
    
    /**
     * Render network admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_network_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Get all sites in the network
        $sites = get_sites(array('number' => 10000));
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('Configure contact email addresses for each site in your network. These contacts will be available as merge tags in Gravity Forms notifications.', 'recipient-directory-gf'); ?></p>
            
            <div class="rdgf-admin-container">
                <div class="rdgf-site-selector">
                    <h2><?php _e('Select Site', 'recipient-directory-gf'); ?></h2>
                    <select id="rdgf-site-select" class="regular-text">
                        <option value=""><?php _e('-- Select a site --', 'recipient-directory-gf'); ?></option>
                        <?php foreach ($sites as $site): ?>
                            <option value="<?php echo esc_attr($site->blog_id); ?>">
                                <?php echo esc_html($site->blogname . ' (' . $site->domain . $site->path . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="rdgf-contacts-form" style="display: none; margin-top: 30px;">
                    <h2><?php _e('Contact Email Addresses', 'recipient-directory-gf'); ?></h2>
                    <p class="description"><?php _e('Enter email addresses for each contact type. Multiple emails can be separated by commas.', 'recipient-directory-gf'); ?></p>
                    
                    <table class="form-table" role="presentation">
                        <tbody>
                            <?php foreach ($this->contact_types as $key => $label): ?>
                                <tr>
                                    <th scope="row">
                                        <label for="rdgf-contact-<?php echo esc_attr(strtolower($key)); ?>">
                                            <?php echo esc_html($label); ?>
                                            <br><code>{<?php echo esc_html($key); ?>}</code>
                                        </label>
                                    </th>
                                    <td>
                                        <input 
                                            type="text" 
                                            id="rdgf-contact-<?php echo esc_attr(strtolower($key)); ?>" 
                                            name="<?php echo esc_attr($key); ?>"
                                            class="large-text rdgf-contact-input" 
                                            placeholder="email@example.com, another@example.com"
                                        >
                                        <p class="description"><?php _e('Comma-separated email addresses', 'recipient-directory-gf'); ?></p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <p class="submit">
                        <button type="button" id="rdgf-save-contacts" class="button button-primary">
                            <?php _e('Save Contacts', 'recipient-directory-gf'); ?>
                        </button>
                        <span class="rdgf-spinner" style="display: none; margin-left: 10px;">
                            <span class="spinner is-active" style="float: none; margin: 0;"></span>
                        </span>
                        <span class="rdgf-message" style="margin-left: 15px; font-weight: bold;"></span>
                    </p>
                </div>
            </div>
        </div>
        
        <style>
            .rdgf-admin-container {
                background: #fff;
                padding: 20px;
                margin-top: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .rdgf-site-selector select {
                max-width: 600px;
            }
            #rdgf-contacts-form {
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .rdgf-message.success {
                color: #46b450;
            }
            .rdgf-message.error {
                color: #dc3232;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Site selection
            $('#rdgf-site-select').on('change', function() {
                var siteId = $(this).val();
                if (!siteId) {
                    $('#rdgf-contacts-form').hide();
                    return;
                }
                
                // Load contacts for selected site
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rdgf_load_contacts',
                        site_id: siteId,
                        nonce: '<?php echo wp_create_nonce('rdgf_load_contacts'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate form fields
                            $('.rdgf-contact-input').val('');
                            if (response.data.contacts) {
                                $.each(response.data.contacts, function(key, value) {
                                    $('input[name="' + key + '"]').val(value);
                                });
                            }
                            $('#rdgf-contacts-form').slideDown();
                        }
                    }
                });
            });
            
            // Save contacts
            $('#rdgf-save-contacts').on('click', function() {
                var siteId = $('#rdgf-site-select').val();
                if (!siteId) return;
                
                var contacts = {};
                $('.rdgf-contact-input').each(function() {
                    var key = $(this).attr('name');
                    var value = $(this).val().trim();
                    if (value) {
                        contacts[key] = value;
                    }
                });
                
                $('.rdgf-spinner').show();
                $('.rdgf-message').text('').removeClass('success error');
                $('#rdgf-save-contacts').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rdgf_save_contacts',
                        site_id: siteId,
                        contacts: contacts,
                        nonce: '<?php echo wp_create_nonce('rdgf_save_contacts'); ?>'
                    },
                    success: function(response) {
                        $('.rdgf-spinner').hide();
                        $('#rdgf-save-contacts').prop('disabled', false);
                        
                        if (response.success) {
                            $('.rdgf-message')
                                .text('<?php _e('Contacts saved successfully!', 'recipient-directory-gf'); ?>')
                                .addClass('success');
                        } else {
                            $('.rdgf-message')
                                .text(response.data.message || '<?php _e('Error saving contacts', 'recipient-directory-gf'); ?>')
                                .addClass('error');
                        }
                        
                        setTimeout(function() {
                            $('.rdgf-message').fadeOut();
                        }, 3000);
                    },
                    error: function() {
                        $('.rdgf-spinner').hide();
                        $('#rdgf-save-contacts').prop('disabled', false);
                        $('.rdgf-message')
                            .text('<?php _e('Error saving contacts', 'recipient-directory-gf'); ?>')
                            .addClass('error');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler to save contacts
     */
    public function ajax_save_contacts() {
        check_ajax_referer('rdgf_save_contacts', 'nonce');
        
        if (!current_user_can('manage_network_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'recipient-directory-gf')));
        }
        
        $site_id = intval($_POST['site_id']);
        $contacts = isset($_POST['contacts']) ? $_POST['contacts'] : array();
        
        // Sanitize email addresses
        $sanitized_contacts = array();
        foreach ($contacts as $key => $value) {
            if (in_array($key, array_keys($this->contact_types))) {
                // Split by comma and sanitize each email
                $emails = array_map('trim', explode(',', $value));
                $emails = array_filter($emails, 'is_email');
                if (!empty($emails)) {
                    $sanitized_contacts[$key] = implode(', ', $emails);
                }
            }
        }
        
        // Save to network options
        $option_name = 'rdgf_contacts_' . $site_id;
        update_network_option(null, $option_name, $sanitized_contacts);
        
        wp_send_json_success(array('message' => __('Contacts saved successfully', 'recipient-directory-gf')));
    }
    
    /**
     * AJAX handler to load contacts
     */
    public function ajax_load_contacts() {
        check_ajax_referer('rdgf_load_contacts', 'nonce');
        
        if (!current_user_can('manage_network_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'recipient-directory-gf')));
        }
        
        $site_id = intval($_POST['site_id']);
        $option_name = 'rdgf_contacts_' . $site_id;
        $contacts = get_network_option(null, $option_name, array());
        
        wp_send_json_success(array('contacts' => $contacts));
    }
    
    /**
     * Add custom merge tags to Gravity Forms
     */
    public function add_custom_merge_tags($merge_tags, $form_id, $fields, $element_id) {
        foreach ($this->contact_types as $key => $label) {
            $merge_tags[] = array(
                'label' => $label,
                'tag' => '{' . $key . '}'
            );
        }
        return $merge_tags;
    }
    
    /**
     * Replace merge tags with actual email addresses
     */
    public function replace_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
        // Get current site ID
        $site_id = get_current_blog_id();
        
        // Get contacts for this site
        $option_name = 'rdgf_contacts_' . $site_id;
        $contacts = get_network_option(null, $option_name, array());
        
        // Replace each merge tag
        foreach ($this->contact_types as $key => $label) {
            $merge_tag = '{' . $key . '}';
            if (strpos($text, $merge_tag) !== false) {
                $replacement = isset($contacts[$key]) ? $contacts[$key] : '';
                $text = str_replace($merge_tag, $replacement, $text);
            }
        }
        
        return $text;
    }
}

/**
 * Initialize the plugin
 */
function rdgf_init() {
    return Recipient_Director_GF::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'rdgf_init');
