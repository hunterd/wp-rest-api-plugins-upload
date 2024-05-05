# WP REST API Plugin Uploader

## Description
The WP REST API Plugin Uploader adds a dedicated REST API endpoint to your WordPress site, allowing you to upload plugins via a `.zip` file remotely. This is particularly beneficial for environments where direct server access via SSH is restricted or unavailable. Or to automatize the plugin installation process after a deployment.

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

// Configuration
const websiteURL = 'https://your-wordpress-site.com'; // Replace with your website URL
const username = 'your_admin_username'; // Replace with your admin username
const appPassword = 'your_app_password'; // Replace with your application password

const pluginsApiURL = `${websiteURL}/wp-json/wp/v2`;
const pluginUploadApiURL = `${websiteURL}/wp-json/api-rest-plugin-upload/v1/upload`;

const headers = {
    'Authorization': `Basic ${Buffer.from(`${username}:${appPassword}`).toString('base64')}`,
    'Content-Type': 'application/json'
};

const pluginSlug = 'your-plugin-slug'; // Replace with your plugin slug
const filePath = './path/to/your-plugin.zip'; // Path to the ZIP file of your plugin

async function getPluginsList() {
    try {
        const response = await fetch(`${pluginsApiURL}/plugins`, {
            method: 'GET',
            headers,
            timeout: 30000
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Failed to get plugins list:', error);
    }
}

async function deactivatePlugin() {
    const url = `${pluginsApiURL}/plugins/${pluginSlug}/${pluginSlug}`;
    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers,
            body: JSON.stringify({ status: 'inactive' }),
            timeout: 30000
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return true;
    } catch (error) {
        console.error('Failed to deactivate plugin:', error);
        return false;
    }
}

async function deletePlugin() {
    const url = `${pluginsApiURL}/plugins/${pluginSlug}/${pluginSlug}`;
    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers,
            timeout: 30000
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return true;
    } catch (error) {
        console.error('Failed to delete plugin:', error);
        return false;
    }
}

async function uploadAndActivatePlugin() {
    const formData = new FormData();
    formData.append('pluginfile', fs.createReadStream(filePath));

    try {
        const response = await fetch(pluginUploadApiURL, {
            method: 'POST',
            headers: {
                ...formData.getHeaders(),
                'Authorization': headers['Authorization']
            },
            body: formData,
            timeout: 60000
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}, message: ${await response.json().message}`);
        }

        console.log('Plugin uploaded:', await response.json());
        activatePlugin();
    } catch (error) {
        console.error('Failed to upload plugin:', error);
    }
}

async function activatePlugin() {
    const url = `${pluginsApiURL}/plugins/${pluginSlug}/${pluginSlug}`;
    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers,
            body: JSON.stringify({ status: 'active' }),
            timeout: 30000
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        console.log('Plugin activated:', await response.json());
    } catch (error) {
        console.error('Failed to activate plugin:', error);
    }
}

async function managePlugin() {
    const pluginsList = await getPluginsList();
    const pluginExists = pluginsList.some(plugin => plugin.plugin === pluginSlug);

    if (pluginExists) {
        const pluginIsActive = pluginsList.find(plugin => plugin.plugin === pluginSlug).status === 'active';
        if (pluginIsActive) {
            await deactivatePlugin();
        }
        await deletePlugin();
    }

    await uploadAndActivatePlugin();
}

managePlugin();
```

## License
GPLv2 or later

## Author
This plugin was developed by David Mussard. For more information, visit [David Mussard's Website](https://davidmussard.com).

## Support
If you find this plugin useful and would like to support the developer, consider making a donation via [PayPal](https://paypal.me/davidmussard).
