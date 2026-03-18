# Recipient Directory for Gravity Forms

A WordPress multisite plugin that provides network-wide contact management for Gravity Forms notifications with merge tag support.

## Description

Recipient Directory for Gravity Forms allows network administrators to define contact email addresses for each site in a WordPress multisite installation. These contacts are then available as merge tags in Gravity Forms notifications, making it easy to route form submissions to the appropriate recipients.

**Perfect for:** WP Engine multisite installations running Gravity Forms where different sites need different contact routing.

## Features

- **Network-Level Administration**: Manage all site contacts from one central location
- **Site-Specific Contacts**: Define unique contact lists for each site in your network
- **Gravity Forms Integration**: Seamless merge tag integration with Gravity Forms notifications
- **Multiple Recipients**: Support for comma-separated email addresses
- **Six Contact Types**: Pre-configured contact roles for common organizational needs

## Contact Types

The plugin provides six contact types with corresponding merge tags:

| Contact Type | Merge Tag | Description |
|-------------|-----------|-------------|
| Senior Program Administrator | `{SPA}` | Program leadership contact |
| Development/Fundraising | `{DEV}` | Fundraising and development team |
| Marketing and Communications | `{MARCOMM}` | Marketing and communications team |
| General | `{GEN}` | General inquiries contact |
| Business Office | `{BIZ}` | Business and finance office |
| Executive Director | `{ED}` | Executive leadership |

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

### Setting Up Contacts

1. Navigate to **Network Admin > Recipient Directory**
2. Select a site from the dropdown menu
3. Enter email addresses for each contact type (comma-separated for multiple recipients)
4. Click **Save Contacts**
5. Repeat for each site in your network

### Using Merge Tags in Gravity Forms

1. Go to any form in Gravity Forms
2. Navigate to **Settings > Notifications**
3. Click on a notification or create a new one
4. In the "Send To" field, click the merge tags dropdown
5. Select any of the contact types (Senior Program Administrator, Development/Fundraising, etc.)
6. The merge tag (e.g., `{SPA}`) will be inserted and will resolve to the email address(es) you configured

**Example:**
```
To: {SPA}, {ED}
CC: {GEN}
```

This will send the notification to the Senior Program Administrator and Executive Director, with a copy to the General contact.

## Configuration Storage

Contact configurations are stored in WordPress network options with the following format:
- Option name: `rdgf_contacts_{blog_id}`
- Storage: Network-wide options table
- Data format: Serialized array of contact types and email addresses

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
