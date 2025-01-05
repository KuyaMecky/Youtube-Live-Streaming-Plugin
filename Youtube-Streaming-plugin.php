<?php
/*
Plugin Name: YouTube Live Broadcasts
Description: Automatically fetch and display the latest live broadcast from a YouTube channel with enhanced features and caching.
Version: 2.0
Author: Michael Tallada
*/

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class YT_Live_Broadcasts {
    private static $instance = null;
    private $cache_duration = 300; // 5 minutes cache

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize hooks
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Register shortcode
        add_shortcode('youtube_live', array($this, 'display_broadcasts'));
        
        // Add AJAX handlers
        add_action('wp_ajax_refresh_live_broadcast', array($this, 'ajax_refresh_broadcast'));
    }

    public function activate() {
        // Set default options
        add_option('yt_live_broadcasts_api_key', '');
        add_option('yt_live_broadcasts_channel_id', '');
        add_option('yt_live_broadcasts_layout', 'default');
        add_option('yt_live_broadcasts_autorefresh', '300');
        
        // Create cache table
        $this->create_cache_table();
    }

    public function deactivate() {
        // Cleanup tasks
        wp_clear_scheduled_hook('yt_live_broadcasts_cache_cleanup');
    }

    private function create_cache_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_live_broadcasts_cache';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cache_key varchar(32) NOT NULL,
            cache_value longtext NOT NULL,
            expiration datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY cache_key (cache_key),
            KEY expiration (expiration)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function register_settings() {
        register_setting(
            'yt_live_broadcasts_options',
            'yt_live_broadcasts_api_key',
            array('sanitize_callback' => 'sanitize_text_field')
        );
        register_setting(
            'yt_live_broadcasts_options',
            'yt_live_broadcasts_channel_id',
            array('sanitize_callback' => 'sanitize_text_field')
        );
        register_setting(
            'yt_live_broadcasts_options',
            'yt_live_broadcasts_layout',
            array('sanitize_callback' => 'sanitize_text_field')
        );
        register_setting(
            'yt_live_broadcasts_options',
            'yt_live_broadcasts_autorefresh',
            array('sanitize_callback' => 'absint')
        );
    }

    public function register_admin_menu() {
        add_menu_page(
            'YouTube Live Broadcasts',
            'YouTube Live',
            'manage_options',
            'yt_live_broadcasts',
            array($this, 'render_admin_page'),
            'dashicons-video-alt3'
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_yt_live_broadcasts' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'yt-live-broadcasts-admin',
            plugins_url('css/admin.css', __FILE__),
            array(),
            '2.0'
        );

        wp_enqueue_script(
            'yt-live-broadcasts-admin',
            plugins_url('js/admin.js', __FILE__),
            array('jquery'),
            '2.0',
            true
        );

        wp_localize_script('yt-live-broadcasts-admin', 'ytLiveData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yt_live_broadcasts_nonce')
        ));
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'yt-live-broadcasts',
            plugins_url('css/frontend.css', __FILE__),
            array(),
            '2.0'
        );

        wp_enqueue_script(
            'yt-live-broadcasts',
            plugins_url('js/frontend.js', __FILE__),
            array('jquery'),
            '2.0',
            true
        );
    }

    public function fetch_broadcasts() {
        $cache_key = 'yt_live_' . get_option('yt_live_broadcasts_channel_id');
        $cached_data = $this->get_cache($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }

        $api_key = get_option('yt_live_broadcasts_api_key');
        $channel_id = get_option('yt_live_broadcasts_channel_id');
        
        if (empty($api_key) || empty($channel_id)) {
            return new WP_Error('missing_credentials', 'API key or Channel ID is missing');
        }

        $api_url = add_query_arg(array(
            'part' => 'snippet',
            'channelId' => $channel_id,
            'type' => 'video',
            'eventType' => 'live',
            'key' => $api_key
        ), 'https://www.googleapis.com/youtube/v3/search');

        $response = wp_remote_get($api_url, array(
            'timeout' => 15,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Failed to parse API response');
        }

        $this->set_cache($cache_key, $data, $this->cache_duration);
        return $data;
    }

    public function display_broadcasts($atts) {
        $atts = shortcode_atts(array(
            'layout' => get_option('yt_live_broadcasts_layout', 'default'),
            'autorefresh' => get_option('yt_live_broadcasts_autorefresh', 300)
        ), $atts, 'youtube_live');

        $data = $this->fetch_broadcasts();
        
        if (is_wp_error($data)) {
            return sprintf(
                '<div class="yt-live-error">%s</div>',
                esc_html($data->get_error_message())
            );
        }

        if (empty($data['items'])) {
            return '<div class="yt-live-notice">No live broadcasts found.</div>';
        }

        $broadcast = $data['items'][0];
        $video_id = esc_attr($broadcast['id']['videoId']);
        $title = esc_html($broadcast['snippet']['title']);
        
        ob_start();
        include plugin_dir_path(__FILE__) . "templates/layout-{$atts['layout']}.php";
        return ob_get_clean();
    }

    public function ajax_refresh_broadcast() {
        check_ajax_referer('yt_live_broadcasts_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }

        $data = $this->fetch_broadcasts();
        wp_send_json_success($data);
    }

    private function get_cache($key) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_live_broadcasts_cache';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT cache_value FROM $table_name 
                WHERE cache_key = %s AND expiration > NOW()",
                $key
            )
        );

        return $result ? unserialize($result->cache_value) : false;
    }

    private function set_cache($key, $value, $expiration) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_live_broadcasts_cache';
        
        $wpdb->replace(
            $table_name,
            array(
                'cache_key' => $key,
                'cache_value' => serialize($value),
                'expiration' => date('Y-m-d H:i:s', time() + $expiration)
            ),
            array('%s', '%s', '%s')
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        include plugin_dir_path(__FILE__) . 'templates/admin-page.php';
    }
}

// Initialize the plugin
function yt_live_broadcasts_init() {
    YT_Live_Broadcasts::get_instance();
}
add_action('plugins_loaded', 'yt_live_broadcasts_init');