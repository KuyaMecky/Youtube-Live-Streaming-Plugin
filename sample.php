<?php
/*
Plugin Name: YouTube Live Broadcasts
Description: Automatically fetch and display the latest live broadcast from a YouTube channel.
Version: 1.3
Author: Michael Tallad
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

    $item = $data['items'][0];
    $video_id = $item['id']['videoId'];
    $title = esc_html($item['snippet']['title']);
    $description = esc_html($item['snippet']['description']);
    $thumbnail = esc_url($item['snippet']['thumbnails']['medium']['url']);
    $published_at = esc_html($item['snippet']['publishedAt']);

    return "
        <div class='yt-live-broadcast'>
            <h3>{$title}</h3>
            <img src='{$thumbnail}' alt='{$title}' />
            <p>{$description}</p>
            <p><strong>Live since:</strong> {$published_at}</p>
            <iframe width='560' height='315' src='https://www.youtube.com/embed/{$video_id}' frameborder='0' allowfullscreen></iframe>
        </div>
    ";
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

    </style>
    
    <div class="clock">
        <p id="date"></p>
        <div class="header-draw">Next Live Draw Will Start:</div>
        <div id="countdown" class="countdown-container"></div>
    </div>
    
    <script>
    // Get elements
    const dateElement = document.getElementById('date');
    const countdownElement = document.getElementById('countdown');
    
    // Set India's timezone
    const indiaTimezone = 'Asia/Kolkata';
    
    // Lottery draw times
    const drawTimes = [
        { hour: 13, minute: 0 }, // 1 PM
        { hour: 18, minute: 0 }, // 6 PM
        { hour: 20, minute: 0 }  // 8 PM
    ];
    
    // Update date
    function updateDate() {
        const now = new Date();
        const indiaDate = new Intl.DateTimeFormat('en-US', {
            timeZone: indiaTimezone,
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }).format(now);
    
        // Update element
        dateElement.innerText = indiaDate;
    }
    
    // Get next draw time
    function getNextDrawTime() {
        const now = new Date();
        const indiaNow = new Date(
            now.toLocaleString('en-US', { timeZone: indiaTimezone })
        );
    
        for (let drawTime of drawTimes) {
            const nextDraw = new Date(indiaNow);
            nextDraw.setHours(drawTime.hour, drawTime.minute, 0, 0);
    
            if (nextDraw > indiaNow) {
                return nextDraw;
            }
        }
    
        // If no future draws today, return the first draw time tomorrow
        const nextDay = new Date(indiaNow);
        nextDay.setDate(nextDay.getDate() + 1);
        nextDay.setHours(drawTimes[0].hour, drawTimes[0].minute, 0, 0);
        return nextDay;
    }
    
    // Update countdown
    function updateCountdown() {
        const now = new Date();
        const nextDraw = getNextDrawTime();
        const timeDifference = nextDraw - now;
    
        if (timeDifference <= 0) {
            countdownElement.textContent = "Draw Live Now!";
            document.getElementById('yt-live-broadcast').style.display = 'block';
            return;
        }
    
        const hours = Math.floor((timeDifference / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((timeDifference / (1000 * 60)) % 60);
        const seconds = Math.floor((timeDifference / 1000) % 60);
    
        countdownElement.innerHTML = `
            <div class="countdown-container">
            <div class="countdown-item">
                <span>${hours}</span>
                <small>Hours</small>
            </div>
            <div class="countdown-item">
                <span>${minutes}</span>
                <small>Minutes</small>
            </div>
            <div class="countdown-item">
                <span>${seconds}</span>
                <small>Seconds</small>
            </div>
            </div>
        `;
        document.getElementById('yt-live-broadcast').style.display = 'none';
    }
    
    // Initialize updates
    setInterval(updateCountdown, 1000); // Update the countdown every second
    updateCountdown(); // Initial countdown update
    updateDate(); // Initial date update
    </script>
    
    <div id="yt-live-broadcast" style="display: none;">
        <?php echo fetch_youtube_live_broadcasts(); ?>
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
