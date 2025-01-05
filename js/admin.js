(function($) {
    'use strict';

    const YTLiveAdmin = {
        init: function() {
            this.bindEvents();
            this.initializeAutoRefresh();
        },

        bindEvents: function() {
            $('#yt-live-refresh-button').on('click', this.refreshBroadcast);
            $('#yt-live-test-connection').on('click', this.testAPIConnection);
            $('#yt-live-broadcasts-form').on('submit', this.validateForm);
        },

        initializeAutoRefresh: function() {
            const autoRefreshInterval = parseInt($('#yt_live_broadcasts_autorefresh').val(), 10);
            if (autoRefreshInterval > 0) {
                setInterval(() => this.refreshBroadcast(true), autoRefreshInterval * 1000);
            }
        },

        refreshBroadcast: function(silent = false) {
            const $preview = $('#yt-live-preview-content');
            const $spinner = $('.spinner');

            if (!silent) {
                $spinner.addClass('is-active');
            }

            $.ajax({
                url: ytLiveData.ajax_url,
                type: 'POST',
                data: {
                    action: 'refresh_live_broadcast',
                    nonce: ytLiveData.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        YTLiveAdmin.updatePreview(response.data);
                    } else {
                        YTLiveAdmin.showError('Failed to fetch broadcast data');
                    }
                },
                error: function() {
                    YTLiveAdmin.showError('Failed to connect to server');
                },
                complete: function() {
                    if (!silent) {
                        $spinner.removeClass('is-active');
                    }
                }
            });
        },

        updatePreview: function(data) {
            const $preview = $('#yt-live-preview-content');
            
            if (!data.items || data.items.length === 0) {
                $preview.html('<div class="yt-live-notice">No live broadcasts found.</div>');
                return;
            }

            const broadcast = data.items[0];
            const html = `
                <h3 class="yt-live-title">${broadcast.snippet.title}</h3>
                <div class="yt-live-embed">
                    <iframe 
                        src="https://www.youtube.com/embed/${broadcast.id.videoId}"
                        frameborder="0"
                        allowfullscreen>
                    </iframe>
                </div>
            `;
            
            $preview.html(html);
        },

        testAPIConnection: function(e) {
            e.preventDefault();
            
            const apiKey = $('#yt_live_broadcasts_api_key').val();
            const channelId = $('#yt_live_broadcasts_channel_id').val();

            if (!apiKey || !channelId) {
                YTLiveAdmin.showError('Please enter both API Key and Channel ID');
                return;
            }

            const $button = $(this);
            const originalText = $button.text();
            $button.text('Testing...').prop('disabled', true);

            $.ajax({
                url: ytLiveData.ajax_url,
                type: 'POST',
                data: {
                    action: 'test_api_connection',
                    nonce: ytLiveData.nonce,
                    api_key: apiKey,
                    channel_id: channelId
                },
                success: function(response) {
                    if (response.success) {
                        YTLiveAdmin.showSuccess('Connection successful!');
                    } else {
                        YTLiveAdmin.showError(response.data || 'Connection failed');
                    }
                },
                error: function() {
                    YTLiveAdmin.showError('Failed to test connection');
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                }
            });
        },

        validateForm: function(e) {
            const apiKey = $('#yt_live_broadcasts_api_key').val();
            const channelId = $('#yt_live_broadcasts_channel_id').val();

            if (!apiKey || !channelId) {
                e.preventDefault();
                YTLiveAdmin.showError('Please enter both API Key and Channel ID');
                return false;
            }
        },

        showError: function(message) {
            const $notice = $('<div class="notice notice-error"><p></p></div>');
            $notice.find('p').text(message);
            $('.yt-live-notices').html($notice);
        },

        showSuccess: function(message) {
            const $notice = $('<div class="notice notice-success"><p></p></div>');
            $notice.find('p').text(message);
            $('.yt-live-notices').html($notice);
        }
    };

    $(document).ready(function() {
        YTLiveAdmin.init();
    });

})(jQuery);