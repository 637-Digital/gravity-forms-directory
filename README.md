# Recipient Directory for Gravity Forms

A WordPress multisite plugin that provides network-wide contact management for Gravity Forms notifications with merge tag support.

## Description

Recipient Directory for Gravity Forms allows network administrators to define contact email addresses for each site in a WordPress multisite installation. These contacts are then available as merge tags in Gravity Forms notifications, making it easy to route form submissions to the appropriate recipients.

**Perfect for:** WP Engine multisite installations running Gravity Forms where different sites need different contact routing.

## Features

- **Network-Level Administration**: Manage all site contacts from one central location
- **Customizable Contact Roles**: Define up to 7 custom contact roles for your organization
- **Site-Specific Contacts**: Define unique contact lists for each site in your network
- **Gravity Forms Integration**: Seamless merge tag integration with Gravity Forms notifications
- **Multiple Recipients**: Support for comma-separated email addresses

## Custom Contact Roles

The plugin allows network administrators to define up to 7 custom contact roles that fit your organization's structure. Each role consists of:

- **Role Key**: A unique identifier (alphanumeric, uppercase) used in merge tags
- **Role Label**: A descriptive name for the role
- **Merge Tag**: Automatically generated as `{ROLE_KEY}`

**Example Custom Roles:**
- `HR` - Human Resources
- `IT` - Information Technology  
- `LEGAL` - Legal Department

By default, the plugin includes six common roles, but these can be completely customized or replaced.

## Requirements

- WordPress 5.0 or higher
- WordPress Multisite installation
- PHP 7.4 or higher
- Gravity Forms plugin (any recent version)

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `recipient-directory-gravity-forms` folder to `/wp-content/plugins/`
3. Network activate the plugin through the 'Network Admin > Plugins' menu in WordPress

### WP Engine Specific

1. Upload via SFTP to `/wp-content/plugins/` on your WP Engine installation
2. Navigate to Network Admin > Plugins
3. Network Activate "Recipient Directory for Gravity Forms"

## Usage

### Defining Custom Roles

1. Navigate to **Network Admin > Recipient Directory > Define Roles**
2. Enter a unique **Role Key** (e.g., "HR") and **Role Label** (e.g., "Human Resources") for each role
3. Add up to 7 roles as needed for your organization
4. Click **Save Roles**

### Setting Up Contacts

1. Navigate to **Network Admin > Recipient Directory**
2. Select a site from the dropdown menu
3. Enter email addresses for each custom role (comma-separated for multiple recipients)
4. Click **Save Contacts**
5. Repeat for each site in your network

### Using Merge Tags in Gravity Forms

1. Go to any form in Gravity Forms
2. Navigate to **Settings > Notifications**
3. Click on a notification or create a new one
4. In the "Send To" field, click the merge tags dropdown
5. Select any of your custom contact roles
6. The merge tag (e.g., `{HR}`) will be inserted and will resolve to the email address(es) you configured

**Example:**
```
To: {HR}, {IT}
CC: {GEN}
```

This will send the notification to the Human Resources and IT contacts, with a copy to the General contact.

## Configuration Storage

Contact configurations are stored in WordPress network options:

- **Custom Roles**: `rdgf_custom_roles` - Serialized array of role keys and labels
- **Site Contacts**: `rdgf_contacts_{blog_id}` - Serialized array of contact emails for each site
- Storage: Network-wide options table

## Development

### File Structure

```
recipient-directory-gravity-forms/
├── recipient-directory-gravity-forms.php  # Main plugin file
├── README.md                              # Documentation
└── LICENSE                                # MIT License
```

### Hooks and Filters

The plugin uses the following Gravity Forms hooks:

- `gform_custom_merge_tags` - Adds custom merge tags to the merge tag dropdown
- `gform_replace_merge_tags` - Replaces merge tags with actual email addresses

### AJAX Endpoints

- `rdgf_save_contacts` - Saves contact configuration for a site
- `rdgf_load_contacts` - Loads contact configuration for a site

## Security

- AJAX requests are protected with WordPress nonces
- Email addresses are sanitized using WordPress's `is_email()` function
- Network admin capabilities are required for all operations
- No default values to prevent accidental email sends

## Troubleshooting

### Merge tags not appearing in Gravity Forms

- Ensure Gravity Forms is active on the site
- Check that the plugin is network activated
- Try deactivating and reactivating the plugin

### Emails not being sent to contacts

- Verify contacts are saved for the specific site (not just selected but not saved)
- Check that email addresses are valid
- Review Gravity Forms notification logs
- Test with a simple single email address first

### Site not appearing in dropdown

- Ensure the site exists in the network
- Try refreshing the Network Admin page
- Check that you have network admin permissions

## Support

For issues, questions, or contributions, please contact 637 Digital Solutions.

## License

This plugin is licensed under the MIT License. See LICENSE file for details.

## Author

**637 Digital Solutions**

## Changelog

### 1.0.0
- Initial release
- Network admin settings page
- Six pre-configured contact types
- Gravity Forms merge tag integration
- Support for multiple email addresses per contact type
