<?php
/*
Plugin Name: YouTube Live Broadcasts
Description: Automatically fetch and display live broadcasts from a YouTube channel.
Version: 1.0
Author: Michael Tallada
*/

function fetch_youtube_live_broadcasts() {
    $api_key = 'YOUR_API_KEY';
    $channel_id = 'YOUR_CHANNEL_ID';
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
add_shortcode('youtube_live', 'display_youtube_live_broadcasts');
?>
