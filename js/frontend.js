(function($) {
    'use strict';

    const YTLiveFrontend = {
        init: function() {
            this.containers = $('.yt-live-container');
            this.initializeAutoRefresh();
            this.setupResponsiveEmbeds();
        },

        initializeAutoRefresh: function() {
            this.containers.each(function() {
                const $container = $(this);
                const refreshInterval = $container.data('refresh-interval');
                
                if (refreshInterval > 0) {
                    setInterval(() => YTLiveFrontend.refreshBroadcast($container), refreshInterval * 1000);
                }
            });
        },

        refreshBroadcast: function($container) {
            $.ajax({
                url: ytLiveData.ajax_url,
                type: 'POST',
                data: {
                    action: 'refresh_live_broadcast',
                    nonce: ytLiveData.nonce,
                    layout: $container.data('layout')
                },
                beforeSend: function() {
                    $container.addClass('is-refreshing');
                },
                success: function(response) {
                    if (response.success && response.data) {
                        YTLiveFrontend.updateContainer($container, response.data);
                    }
                },
                complete: function() {
                    $container.removeClass('is-refreshing');
                }
            });
        },

        updateContainer: function($container, data) {
            if (!data.items || data.items.length === 0) {
                $container.html('<div class="yt-live-notice">No live broadcasts found.</div>');
                return;
            }

            const broadcast = data.items[0];
            const html = `
                <h3 class="yt-live-title">${broadcast.snippet.title}</h3>
                <div class="yt-live-embed">
                    <iframe 
                        src="https://www.youtube.com/embed/${broadcast.id.videoId}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            `;
            
            $container.html(html);
            this.setupResponsiveEmbeds();
        },

        setupResponsiveEmbeds: function() {
            // Ensure responsive behavior for embedded iframes
            $('.yt-live-embed iframe').each(function() {
                const $iframe = $(this);
                const width = $iframe.width();
                const height = Math.floor(width * (9/16)); // 16:9 aspect ratio
                $iframe.height(height);
            });

            // Update iframe sizes on window resize
            $(window).on('resize', function() {
                $('.yt-live-embed iframe').each(function() {
                    const $iframe = $(this);
                    const width = $iframe.width();
                    const height = Math.floor(width * (9/16));
                    $iframe.height(height);
                });
            });
        }
    };

    $(document).ready(function() {
        YTLiveFrontend.init();
    });

})(jQuery);