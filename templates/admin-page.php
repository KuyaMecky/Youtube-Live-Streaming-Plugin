<?php
// templates/admin-page.php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap yt-live-broadcasts-wrap">
    <div class="yt-live-broadcasts-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <span class="yt-live-status offline">Offline</span>
    </div>

    <?php settings_errors(); ?>

    <form method="post" action="options.php" class="yt-live-settings-form">
        <?php
        settings_fields('yt_live_broadcasts_options');
        ?>
        
        <label for="yt_live_broadcasts_api_key">YouTube API Key</label>
        <input 
            type="text" 
            id="yt_live_broadcasts_api_key" 
            name="yt_live_broadcasts_api_key" 
            value="<?php echo esc_attr(get_option('yt_live_broadcasts_api_key')); ?>"
            class="regular-text"
            required
        />

        <label for="yt_live_broadcasts_channel_id">Channel ID</label>
        <input 
            type="text" 
            id="yt_live_broadcasts_channel_id" 
            name="yt_live_broadcasts_channel_id" 
            value="<?php echo esc_attr(get_option('yt_live_broadcasts_channel_id')); ?>"
            class="regular-text"
            required
        />

        <label for="yt_live_broadcasts_layout">Layout</label>
        <select 
            id="yt_live_broadcasts_layout" 
            name="yt_live_broadcasts_layout"
        >
            <option value="default" <?php selected(get_option('yt_live_broadcasts_layout'), 'default'); ?>>Default</option>
            <option value="compact" <?php selected(get_option('yt_live_broadcasts_layout'), 'compact'); ?>>Compact</option>
            <option value="full" <?php selected(get_option('yt_live_broadcasts_layout'), 'full'); ?>>Full Width</option>
        </select>

        <label for="yt_live_broadcasts_autorefresh">Auto-refresh Interval (seconds)</label>
        <input 
            type="number" 
            id="yt_live_broadcasts_autorefresh" 
            name="yt_live_broadcasts_autorefresh" 
            value="<?php echo esc_attr(get_option('yt_live_broadcasts_autorefresh', 300)); ?>"
            min="0"
            step="1"
            class="small-text"
        />

        <?php submit_button(); ?>
        <button type="button" id="test-api-credentials" class="button button-secondary">Test API Credentials</button>
    </form>

    <div class="yt-live-preview">
        <h2>Live Preview</h2>
        <p>Your live broadcast will appear here when you go live.</p>
    </div>

    <div class="yt-live-shortcode-info">
        <h2>Shortcode Usage</h2>
        <p>Use the following shortcode to display the live broadcast on any page or post:</p>
        <code>[youtube_live]</code>
        
        <h3>Optional Parameters:</h3>
        <ul>
            <li><code>layout</code> - Specify the layout (default, compact, full)</li>
            <li><code>autorefresh</code> - Set custom refresh interval in seconds</li>
        </ul>
        <p>Example: <code>[youtube_live layout="compact" autorefresh="60"]</code></p>
    </div>
</div>

<?php
// templates/layout-default.php
if (!defined('ABSPATH')) exit;
?>
<div class="yt-live-container" data-autorefresh="<?php echo esc_attr($atts['autorefresh']); ?>">
    <h2 class="yt-live-title"><?php echo esc_html($title); ?></h2>
    <div class="yt-live-embed">
        <iframe 
            data-video-id="<?php echo esc_attr($video_id); ?>"
            src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>"
            frameborder="0" 
            allowfullscreen>
        </iframe>
    </div>
</div>

<?php
// templates/layout-compact.php
if (!defined('ABSPATH')) exit;
?>
<div class="yt-live-container compact" data-autorefresh="<?php echo esc_attr($atts['autorefresh']); ?>">
    <div class="yt-live-embed">
        <iframe 
            data-video-id="<?php echo esc_attr($video_id); ?>"
            src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>"
            frameborder="0" 
            allowfullscreen>
        </iframe>
    </div>
    <div class="yt-live-title"><?php echo esc_html($title); ?></div>
</div>

<?php
// templates/layout-full.php
if (!defined('ABSPATH')) exit;
?>
<div class="yt-live-container full-width" data-autorefresh="<?php echo esc_attr($atts['autorefresh']); ?>">
    <div class="yt-live-header">
        <h2 class="yt-live-title"><?php echo esc_html($title); ?></h2>
        <div class="yt-live-meta">
            <span class="yt-live-indicator">LIVE</span>
            <span class="yt-live-viewers">
                <?php echo esc_html(isset($broadcast['liveStreamingDetails']['concurrentViewers']) ? number_format($broadcast['liveStreamingDetails']['concurrentViewers']) . ' watching' : ''); ?>
            </span>
        </div>
    </div>
    <div class="yt-live-embed">
        <iframe 
            data-video-id="<?php echo esc_attr($video_id); ?>"
            src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>?autoplay=1"
            frameborder="0" 
            allowfullscreen>
        </iframe>
    </div>
</div>