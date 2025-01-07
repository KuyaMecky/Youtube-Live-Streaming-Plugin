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
    - Note: The Channel ID is not the same as the custom URL or username (e.g., `@DEARLOTTERIESLIVE`).
    - You can use this to convert the channel tag into channel ID [UC Converter](https://www.tunepocket.com/youtube-channel-id-finder/#channle-id-finder-form).

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

## Embed Version
To use the embed version of the YouTube Live Broadcasts plugin, follow these steps:

1. **Embed Code**: Obtain the embed code for the live broadcast from YouTube.
    - Go to the live broadcast on YouTube.
    - Click on the `Share` button below the video.
    - Select `Embed` and copy the provided embed code.

2. **Embed in WordPress**:
    - Navigate to the post or page where you want to embed the live broadcast.
    - Switch to the `Text` editor in the WordPress editor.
    - Paste the embed code where you want the live broadcast to appear.
    - Switch back to the `Visual` editor to see the embedded live broadcast.

3. **Save Changes**: Update or publish the post or page to save your changes.

By following these steps, you can easily embed a YouTube live broadcast directly into your WordPress site without using the shortcode.

