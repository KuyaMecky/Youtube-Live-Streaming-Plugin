# YouTube Live Broadcasts Plugin

## Description
The YouTube Live Broadcasts plugin automatically fetches and displays the latest live broadcast from a specified YouTube channel on your WordPress site.

## Features
- Fetch and display the latest live broadcast from a YouTube channel.
- Easy to configure via the WordPress admin dashboard.
- Shortcode support to embed live broadcasts in posts or pages.

## Installation
1. **Download**: Obtain the plugin files.
2. **Upload**: Transfer the plugin files to the `/wp-content/plugins/` directory.
3. **Activate**: Enable the plugin via the 'Plugins' menu in WordPress.

## Configuration
## Configuration

1. **API Key**: Obtain a YouTube Data API v3 key from the [Google Cloud Console](https://console.cloud.google.com/).
    - Go to the [Google Cloud Console](https://console.cloud.google.com/).
    - Create a new project or select an existing project.
    - Navigate to `APIs & Services` > `Library`.
    - Search for `YouTube Data API v3` and enable it.
    - Go to `APIs & Services` > `Credentials`.
    - Click on `Create Credentials` and select `API Key`.
    - Copy the generated API key.

2. **Channel ID**: Find the Channel ID of the YouTube channel you want to fetch live broadcasts from.
    - Go to the YouTube channel.
    - Click on the channel name to go to the channel's homepage.
    - The Channel ID is the string of characters after `/channel/` in the URL. For example, in `https://www.youtube.com/channel/UC1234567890`, `UC1234567890` is the Channel ID.

3. **Setup in Plugin**:
    - Navigate to `Settings` > `YouTube Live Broadcasts` in the WordPress admin dashboard.
    - Enter your YouTube API key and Channel ID in the provided fields.
    - Click `Save Changes` to store your settings.

## Setup
1. Navigate to `Settings` > `YouTube Live Broadcasts` in the WordPress admin dashboard.
2. Enter your YouTube API key and Channel ID in the provided fields.
3. Click `Save Changes` to store your settings.

## Usage
To display the latest live broadcast from the specified YouTube channel, use the shortcode `[youtube_live]` in your posts or pages.

## Shortcode
- `[youtube_live]`: Embeds the latest live broadcast from the configured YouTube channel.

## Example
