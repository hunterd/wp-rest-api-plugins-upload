# WP REST API Plugin Uploader

## Description
The WP REST API Plugin Uploader adds a dedicated REST API endpoint to your WordPress site, allowing you to upload plugins via a `.zip` file remotely. This is particularly beneficial for environments where direct server access via SSH is restricted or unavailable.

## Features
- **REST API Endpoint**: Provides a dedicated endpoint (`/wp-json/api-rest-plugin-upload/v1/upload/`) for uploading `.zip` files of plugins.
- **Security**: The endpoint enforces that only users with the `install_plugins` capability (typically administrators) can upload plugins, ensuring secure operations.
- **Ease of Use**: Facilitates the management of plugins on environments without direct server access, simplifying remote operations.

## Installation
1. Download the `.zip` file of this plugin.
2. Navigate to the WordPress admin dashboard.
3. Go to Plugins > Add New > Upload Plugin.
4. Choose the downloaded `.zip` file and click on 'Install Now'.
5. After the installation is complete, activate the plugin through the 'Plugins' menu in WordPress.

## Usage
To use this plugin, you can send a POST request to the endpoint with the `.zip` file of the plugin you wish to install. Here's a sample code snippet using JavaScript to perform this operation:

```javascript
import FormData from 'form-data';
import fs from 'fs';
import fetch from 'node-fetch';

const websiteURL = 'https://yourwebsite.com'; // Replace with your website URL
const username = 'your_username'; // Replace with your admin username
const appPassword = 'your_app_password'; // Replace with your application password

const pluginUploadApiURL = `${websiteURL}/wp-json/api-rest-plugin-upload/v1/upload`;

async function uploadAndActivatePlugin() {
    const formData = new FormData();
    formData.append('pluginfile', fs.createReadStream('path/to/your-plugin.zip'));

    try {
        const response = await fetch(pluginUploadApiURL, {
            method: 'POST',
            headers: {
                'Authorization': `Basic ${Buffer.from(`${username}:${appPassword}`).toString('base64')}`,
                ...formData.getHeaders()
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Plugin uploaded and activated successfully:', result);
    } catch (error) {
        console.error('Failed to upload and activate plugin:', error);
    }
}

uploadAndActivatePlugin();
```

## License
GPLv2 or later

## Author
This plugin was developed by David Mussard. For more information, visit [David Mussard's Website](https://davidmussard.com).

## Support
If you find this plugin useful and would like to support the developer, consider making a donation via [PayPal](https://paypal.me/davidmussard).
