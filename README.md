# Call Manager Plugin

The Call Manager plugin for ITSM-NG provides telephony integration capabilities, allowing you to associate RIO numbers (telephone identifiers) with users and quickly access their information and tickets.

## Features

- **RIO Number Management**: Store and manage RIO numbers for users
- **Multiple Storage Options**: Choose where to store RIO numbers:
  - Plugin dedicated table (custom user tab)
  - User login (name)
  - Registration number
- **User Search**: Quickly find users by their RIO number
- **Ticket Integration**: Direct access to a user's tickets from the Call Manager interface
- **REST API**: Expose user data via API endpoints for integration with telephony systems
- **Access Control**: Fine-grained permissions for plugin configuration and API access
- **Web Interface**: Modern UI built with Preact for responsive user experience

## Requirements

- ITSM-NG >= 1.0
- PHP >= 7.4
- Composer for dependency management

## Configuration

1. After activation, go to `Setup > Plugins > Call Manager Plugin` to configure plugin settings
2. Choose the RIO storage method:
   - **Plugin dedicated table**: Store RIO numbers in a separate plugin table with a custom user tab
   - **User login**: Store RIO numbers in the user's login field
   - **Registration number**: Store RIO numbers in the user's registration number field
3. Configure user permissions in `Administration > Profiles`:
   - "Config update" allows users to modify plugin settings
   - "Access to CallManager API/UI" allows users to access the Call Manager interface and API

## Usage

### Adding RIO Numbers to Users

1. Go to `Administration > Users`
2. Select a user
3. If using "Plugin dedicated table" storage method, you'll see a "Call Manager" tab
4. Enter the RIO number in the provided field and save

### Using the Call Manager Interface

1. Navigate to `Helpdesk > Call Manager` in the main menu
2. Enter a RIO number in the search field
3. Click "Search" to find the associated user(s)
4. Click "View tickets" to see the user's tickets

### API Endpoints

The plugin provides a REST API for integration with telephony systems:

```
GET /plugins/callmanager/api.php/users/{rio}
```

This endpoint returns detailed user information for the specified RIO number in JSON format:

```json
{
  "users": [
    {
      "id": 1,
      "phone": "+1234567890",
      "lastname": "Doe",
      "firstname": "John",
      "rio": "1234567890",
      "email": "john.doe@example.com"
    }
  ]
}
```

API requests require a valid GLPI session. You can authenticate by including the session token in the `Session-Token` header or as a cookie.

## License

This plugin is licensed under the [GNU General Public License v3.0](LICENSE).

## Support

For issues, feature requests, or contributions, please visit the [GitHub repository](https://github.com/itsmng/callmanager).
