<?php
/*
Plugin Name: YouTube Live Broadcasts
Description: Display lottery live broadcasts from a YouTube channel with time-based filtering.
Version: 1.5
Author: Michael Tallada (Embed Version)
*/

function yt_live_broadcasts_register_settings() {
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
                <th scope="row"><label for="yt_live_broadcasts_channel_id">Channel ID</label></th>
                <td>
                    <input type="text" id="yt_live_broadcasts_channel_id" 
                           name="yt_live_broadcasts_channel_id" 
                           value="<?php echo esc_attr(get_option('yt_live_broadcasts_channel_id')); ?>" />
                    <p class="description">Enter your YouTube channel ID</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    </div>
<?php
}

function fetch_youtube_live_broadcasts() {
    $channel_id = get_option('yt_live_broadcasts_channel_id');
    
    if (empty($channel_id)) {
        return '<div class="notice-message">Please configure YouTube Channel ID in the plugin settings.</div>';
    }

    $embed_url = sprintf(
        'https://www.youtube.com/embed/live_stream?channel=%s&enablejsapi=1&autoplay=1&mute=1',
        esc_attr($channel_id)
    );

    return sprintf(
        '<div class="youtube-live-container">
            <iframe id="youtube-live-frame" 
                    src="%s" 
                    frameborder="0" 
                    allowfullscreen 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
            </iframe>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                var player;
                
                var tag = document.createElement("script");
                tag.src = "https://www.youtube.com/iframe_api";
                var firstScriptTag = document.getElementsByTagName("script")[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                window.onYouTubeIframeAPIReady = function() {
                    player = new YT.Player("youtube-live-frame", {
                        events: {
                            "onStateChange": onPlayerStateChange,
                            "onReady": checkCurrentStream
                        }
                    });
                };

                function getCurrentTimeSlot() {
                    const now = new Date();
                    const indiaTime = new Date(now.toLocaleString("en-US", { timeZone: "Asia/Kolkata" }));
                    const hours = indiaTime.getHours();
                    const minutes = indiaTime.getMinutes();
                    
                    // Define time slots with 30-minute buffer before and after
                    if (hours === 12 && minutes >= 30 || hours === 13 && minutes <= 30) return "1 PM";
                    if (hours === 17 && minutes >= 30 || hours === 18 && minutes <= 30) return "6 PM";
                    if (hours === 19 && minutes >= 30 || hours === 20 && minutes <= 30) return "8 PM";
                    return null;
                }

                function checkCurrentStream() {
                    const timeSlot = getCurrentTimeSlot();
                    if (!timeSlot) {
                        hideStream("Waiting for next lottery draw...");
                        return;
                    }
                    
                    if (player && player.getVideoData) {
                        const videoData = player.getVideoData();
                        if (videoData && videoData.title) {
                            validateStream(videoData.title, timeSlot);
                        }
                    }
                }

                function validateStream(title, timeSlot) {
                    // Match pattern: LOTTERY LIVE DEAR [TIME] ...
                    const expectedPattern = new RegExp(`LOTTERY LIVE DEAR ${timeSlot}`, "i");
                    if (!expectedPattern.test(title)) {
                        hideStream(`Waiting for ${timeSlot} lottery draw to start...`);
                    }
                }

                function hideStream(message) {
                    const container = document.querySelector(".youtube-live-container");
                    if (container) {
                        container.innerHTML = `<div class="notice-message">${message}</div>`;
                    }
                }

                function onPlayerStateChange(event) {
                    if (event.data === YT.PlayerState.PLAYING) {
                        const timeSlot = getCurrentTimeSlot();
                        if (timeSlot) {
                            const title = player.getVideoData().title;
                            validateStream(title, timeSlot);
                        } else {
                            hideStream("No lottery draw scheduled for current time");
                        }
                    }
                }

                // Check stream every minute
                setInterval(checkCurrentStream, 60000);
            });
            </script>
        </div>',
        esc_url($embed_url)
    );
}

function display_youtube_live_broadcasts($atts = []) {
    ob_start();
    ?>
    <style>
        .youtube-live-container {
            margin: 20px auto;
            position: relative;
            width: 100%;
            max-width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .youtube-live-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

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
        
        .countdown-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex: 1 1 auto;
            margin: 5px;
            min-width: 80px;
        }

        .countdown-item span {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .countdown-item small {
            font-size: 14px;
            color: #666;
            margin-top: 4px;
        }

        .header-draw {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .notice-message {
            color: #004085;
            background-color: #cce5ff;
            border: 1px solid #b8daff;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
            min-height: 315px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .draw-time {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
            text-align: center;
            border: 1px solid #dee2e6;
        }
    </style>
    
    <div class="clock">
        <p id="date"></p>
        <div class="header-draw">Next Lottery Draw Will Start:</div>
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
            countdownElement.innerHTML = '<div class="countdown-item"><span>LIVE NOW!</span></div>';
            return;
        }
        
        const hours = Math.floor((timeDifference / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((timeDifference / (1000 * 60)) % 60);
        const seconds = Math.floor((timeDifference / 1000) % 60);
        
        countdownElement.innerHTML = `
            <div class="countdown-container">
                <div class="countdown-item">
                    <span>${String(hours).padStart(2, '0')}</span>
                    <small>Hours</small>
                </div>
                <div class="countdown-item">
                    <span>${String(minutes).padStart(2, '0')}</span>
                    <small>Minutes</small>
                </div>
                <div class="countdown-item">
                    <span>${String(seconds).padStart(2, '0')}</span>
                    <small>Seconds</small>
                </div>
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
        Daily Lottery Draw Times (IST):<br>
        1:00 PM | 6:00 PM | 8:00 PM
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('youtube_live', 'display_youtube_live_broadcasts');
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
                        <th scope="row"><label for="yt_live_broadcasts_channel_id">Channel ID</label></th>
                        <td><input type="text" id="yt_live_broadcasts_channel_id" name="yt_live_broadcasts_channel_id" value="<?php echo esc_attr(get_option('yt_live_broadcasts_channel_id')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="yt_live_broadcasts_title_filter">Title Filter</label></th>
                        <td><input type="text" id="yt_live_broadcasts_title_filter" name="yt_live_broadcasts_title_filter" value="<?php echo esc_attr(get_option('yt_live_broadcasts_title_filter')); ?>" /></td>
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
