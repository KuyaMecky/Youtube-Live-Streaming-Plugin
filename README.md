# YouTube Streaming Plugin

## Introduction
The YouTube Streaming Plugin enables seamless integration of YouTube videos and live streams into your WordPress site. With an intuitive interface, you can effortlessly embed and manage video content.

## Features
- Embed YouTube live streams and videos
- Customizable player settings
- Responsive design
- User-friendly shortcode

## Installation
1. **Download**: Obtain the plugin from the [GitHub repository](#).
2. **Upload**: Transfer the plugin files to the `/wp-content/plugins/` directory.
3. **Activate**: Enable the plugin via the 'Plugins' menu in WordPress.

## Usage
1. **Settings**: Access the plugin settings page in the WordPress admin dashboard.
2. **API Key**: Input your YouTube API key.
3. **Embed**: Use the shortcode `[youtube_stream id="VIDEO_ID"]` to embed a YouTube video or live stream in your posts or pages.

## How to Create and Insert API Key
1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project or select an existing project.
3. Navigate to `APIs & Services` > `Library`.
4. Search for `YouTube Data API v3` and enable it.
5. Go to `APIs & Services` > `Credentials`.
6. Click `Create Credentials` and select `API Key`.
7. Copy the generated API key.
8. Go to the WordPress admin dashboard.
9. Navigate to `Settings` > `YouTube Streaming Plugin`.
10. Enter your YouTube API key in the provided field.
11. Click `Save Changes` to store your API key.

## Shortcode Parameters
- `id` (required): The ID of the YouTube video or live stream.
- `width` (optional): The width of the video player (default: 100%).
- `height` (optional): The height of the video player (default: 480px).

## Example
```markdown
[youtube_stream id="dQw4w9WgXcQ" width="800" height="450"]
```

## Support
For assistance and troubleshooting, please visit the [support page](#) or open an issue on the [GitHub repository](#).

## License
This plugin is licensed under the MIT License. Refer to the [LICENSE](#) file for detailed information.

## Contributing
Contributions are welcome! Please review the [CONTRIBUTING](#) file for guidelines on how to contribute to this project.

## Changelog
- **v1.0.0** - Initial release

Thank you for choosing the YouTube Streaming Plugin!



