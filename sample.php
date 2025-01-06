<?php
/*
Plugin Name: YouTube Live Broadcasts
Description: Automatically fetch and display the latest live broadcast from a YouTube channel.
Version: 1.3
Author: Michael Tallada
*/

function yt_live_broadcasts_register_settings() {
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
                <th scope="row"><label for="yt_live_broadcasts_api_key">API Key (YouTube Data API v3)</label></th>
                <td><input type="text" id="yt_live_broadcasts_api_key" name="yt_live_broadcasts_api_key" value="<?php echo esc_attr(get_option('yt_live_broadcasts_api_key')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="yt_live_broadcasts_channel_id">Channel ID</label></th>
                <td><input type="text" id="yt_live_broadcasts_channel_id" name="yt_live_broadcasts_channel_id" value="<?php echo esc_attr(get_option('yt_live_broadcasts_channel_id')); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    </div>
<?php
}

function fetch_youtube_live_broadcasts() {
    // Check if we're in quota exceeded state
    $quota_exceeded = get_transient('yt_quota_exceeded');
    
    if ($quota_exceeded) {
        return '<div class="notice-message">YouTube streaming will resume when API quota resets. Please check back later.</div>';
    }
    
    $api_key = get_option('yt_live_broadcasts_api_key');
    $channel_id = get_option('yt_live_broadcasts_channel_id');
    
    if (empty($api_key) || empty($channel_id)) {
        return '<div class="notice-message">Please configure YouTube API key and Channel ID in the plugin settings.</div>';
    }

    // Return a simple message without making API call
    return '<div class="notice-message">Draw results will be shown here during live broadcast.</div>';
}

function display_youtube_live_broadcasts($atts = []) {
    ob_start();
    ?>
    <style>
        .clock {
            position: relative;
            width: 300px;
            margin: 5px auto;
            background-color: #fff;
            padding: 20px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-family: 'Arial', sans-serif;
        }
        
        #date {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        #countdown {
            font-size: 20px;
            font-weight: normal;
            color: #666;
        }

        .countdown-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .countdown-item span {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .countdown-item small {
            font-size: 12px;
            color: #999;
        }

        .header-draw {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .notice-message {
            color: #004085;
            background-color: #cce5ff;
            border: 1px solid #b8daff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }

        .draw-time {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            color: #666;
        }
        .countdown-container {
            flex-wrap: wrap;
            justify-content: space-evenly;
            }
            .countdown-item {
                flex: 1 1 auto;
                margin: 5px;
            }

        
    </style>
    
    <div class="clock">
        <p id="date"></p>
        <div class="header-draw">Next Live Draw Will Start:</div>
        <div id="countdown" class="countdown-container"></div>
    </div>
    
    <script>
    const dateElement = document.getElementById('date');
    const countdownElement = document.getElementById('countdown');
    const indiaTimezone = 'Asia/Kolkata';
    const drawTimes = [
        { hour: 13, minute: 0 },
        { hour: 18, minute: 0 },
        { hour: 20, minute: 0 }
    ];

    function updateDate() {
        const now = new Date();
        const indiaDate = new Intl.DateTimeFormat('en-US', {
            timeZone: indiaTimezone,
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }).format(now);
        dateElement.innerText = indiaDate;
    }

    function getNextDrawTime() {
        const now = new Date();
        const indiaNow = new Date(now.toLocaleString('en-US', { timeZone: indiaTimezone }));
        for (let drawTime of drawTimes) {
            const nextDraw = new Date(indiaNow);
            nextDraw.setHours(drawTime.hour, drawTime.minute, 0, 0);
            if (nextDraw > indiaNow) {
                return nextDraw;
            }
        }
        const nextDay = new Date(indiaNow);
        nextDay.setDate(nextDay.getDate() + 1);
        nextDay.setHours(drawTimes[0].hour, drawTimes[0].minute, 0, 0);
        return nextDay;
    }

    function updateCountdown() {
        const now = new Date();
        const nextDraw = getNextDrawTime();
        const timeDifference = nextDraw - now;
        if (timeDifference <= 0) {
            countdownElement.textContent = "Draw Live Now!";
            return;
        }
        const hours = Math.floor((timeDifference / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((timeDifference / (1000 * 60)) % 60);
        const seconds = Math.floor((timeDifference / 1000) % 60);
        countdownElement.innerHTML = `
            <div class="countdown-container">
                <div class="countdown-item"><span>${hours}</span><small>Hours</small></div>
                <div class="countdown-item"><span>${minutes}</span><small>Minutes</small></div>
                <div class="countdown-item"><span>${seconds}</span><small>Seconds</small></div>
            </div>
        `;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
    updateDate();
</script>

    
    <div id="yt-live-broadcast">
        <?php echo fetch_youtube_live_broadcasts(); ?>
    </div>

    <div class="draw-time">
        Daily Draw Times (IST):<br>
        1:00 PM | 6:00 PM | 8:00 PM
    </div>
    <?php
    return ob_get_clean();
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
            <h2>Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('yt_live_broadcasts_options_group'); ?>
                <table>
                    <tr valign="top">
                        <th scope="row"><label for="yt_live_broadcasts_api_key">API Key</label></th>
                        <td><input type="text" id="yt_live_broadcasts_api_key" name="yt_live_broadcasts_api_key" value="<?php echo esc_attr(get_option('yt_live_broadcasts_api_key')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="yt_live_broadcasts_channel_id">Channel ID</label></th>
                        <td><input type="text" id="yt_live_broadcasts_channel_id" name="yt_live_broadcasts_channel_id" value="<?php echo esc_attr(get_option('yt_live_broadcasts_channel_id')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <div>
            <h2>Live Broadcasts</h2>
            <?php echo display_youtube_live_broadcasts(); ?>
        </div>
    </div>
<?php
}

function yt_live_broadcasts_widget() {
    register_widget('YT_Live_Broadcasts_Widget');
}
add_action('widgets_init', 'yt_live_broadcasts_widget');

class YT_Live_Broadcasts_Widget extends WP_Widget {
    function __construct() {
        parent::__construct(
            'yt_live_broadcasts_widget',
            __('YouTube Live Broadcasts', 'text_domain'),
            array('description' => __('Displays the current live broadcast from a specified YouTube channel.', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', esc_html($instance['title'])) . $args['after_title'];
        }
        echo display_youtube_live_broadcasts();
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Live Broadcast', 'text_domain');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}
?>
