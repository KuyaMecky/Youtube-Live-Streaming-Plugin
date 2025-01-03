<?php
/*
Plugin Name: YouTube Live Broadcasts
Description: Automatically fetch and display live broadcasts from a YouTube channel.
Version: 1.2
Author: Michael Tallada
*/

function yt_live_broadcasts_register_settings() {
    add_option('yt_live_broadcasts_api_key', '');
    add_option('yt_live_broadcasts_channel_id', '');
    register_setting('yt_live_broadcasts_options_group', 'yt_live_broadcasts_api_key');
    register_setting('yt_live_broadcasts_options_group', 'yt_live_broadcasts_channel_id');
}
add_action('admin_init', 'yt_live_broadcasts_register_settings');

function yt_live_broadcasts_register_options_page() {
    add_options_page('YouTube Live Broadcasts', 'YouTube Live Broadcasts', 'manage_options', 'yt_live_broadcasts', 'yt_live_broadcasts_options_page');
}
add_action('admin_menu', 'yt_live_broadcasts_register_options_page');

function yt_live_broadcasts_options_page() {
?>
    <div>
    <h2>YouTube Live Broadcasts Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('yt_live_broadcasts_options_group'); ?>
        <table>
            <tr valign="top">
                <th scope="row"><label for="yt_live_broadcasts_api_key">API Key</label></th>
                <td><input type="text" id="yt_live_broadcasts_api_key" name="yt_live_broadcasts_api_key" value="<?php echo get_option('yt_live_broadcasts_api_key'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="yt_live_broadcasts_channel_id">Channel ID</label></th>
                <td><input type="text" id="yt_live_broadcasts_channel_id" name="yt_live_broadcasts_channel_id" value="<?php echo get_option('yt_live_broadcasts_channel_id'); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    </div>
<?php
}

function fetch_youtube_live_broadcasts() {
    $api_key = get_option('yt_live_broadcasts_api_key');
    $channel_id = get_option('yt_live_broadcasts_channel_id');
    $api_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channel_id}&type=video&eventType=live&key={$api_key}";

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Error fetching broadcasts.';
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['items'])) {
        return 'No live broadcasts found.';
    }

    $output = array_map(function($item) {
        $video_id = $item['id']['videoId'];
        $title = $item['snippet']['title'];
        return "<h3>{$title}</h3><iframe width='560' height='315' src='https://www.youtube.com/embed/{$video_id}' frameborder='0' allowfullscreen></iframe>";
    }, $data['items']);

    return implode('', $output);
}

function display_youtube_live_broadcasts($atts) {
    return fetch_youtube_live_broadcasts();
}
// Shortcode: [youtube_live]
add_shortcode('youtube_live', 'display_youtube_live_broadcasts');

function yt_live_broadcasts_admin_menu() {
    add_menu_page('YouTube Live Broadcasts', 'YouTube Live Broadcasts', 'manage_options', 'yt_live_broadcasts_dashboard', 'yt_live_broadcasts_dashboard_page', 'dashicons-video-alt3', 6);
}
add_action('admin_menu', 'yt_live_broadcasts_admin_menu');

function yt_live_broadcasts_dashboard_page() {
?>
    <div class="wrap">
        <h1>YouTube Live Broadcasts Dashboard</h1>
        <div>
            <?php echo fetch_youtube_live_broadcasts(); ?>
        </div>
    </div>
<?php
}
?>
