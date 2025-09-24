/**
 * Advanced Widgets JavaScript for Zeen Events
 * 
 * This file contains JavaScript functionality for advanced Elementor widgets
 * 
 * @package ZeenEvents
 * @version 2.0.0
 * @copyright 2024 Design Zeen Agency
 * @license GPL v2 or later
 */

(function($) {
    'use strict';

    /**
     * Countdown Timer Widget
     */
    window.DZ_Events_Countdown_Timer = {
        init: function() {
            $('.dz-countdown-timer').each(function() {
                var $timer = $(this);
                var timestamp = parseInt($timer.data('timestamp'));
                var format = $timer.data('format');
                var customFormat = $timer.data('custom-format');
                var style = $timer.data('style');
                var primaryColor = $timer.data('primary-color');
                var textColor = $timer.data('text-color');

                // Apply styling
                $timer.css({
                    '--primary-color': primaryColor,
                    '--text-color': textColor
                });

                // Start countdown
                DZ_Events_Countdown_Timer.startCountdown($timer, timestamp, format, customFormat, style);
            });
        },

        startCountdown: function($timer, timestamp, format, customFormat, style) {
            var countdown = setInterval(function() {
                var now = new Date().getTime();
                var distance = timestamp * 1000 - now;

                if (distance < 0) {
                    clearInterval(countdown);
                    $timer.find('.dz-timer-display').hide();
                    $timer.find('.dz-timer-message').show();
                    return;
                }

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update display based on format
                switch (format) {
                    case 'full':
                        $timer.find('.dz-timer-days .dz-timer-number').text(days.toString().padStart(2, '0'));
                        $timer.find('.dz-timer-hours .dz-timer-number').text(hours.toString().padStart(2, '0'));
                        $timer.find('.dz-timer-minutes .dz-timer-number').text(minutes.toString().padStart(2, '0'));
                        $timer.find('.dz-timer-seconds .dz-timer-number').text(seconds.toString().padStart(2, '0'));
                        break;
                    case 'compact':
                        var compactTime = days.toString().padStart(2, '0') + ':' + 
                                        hours.toString().padStart(2, '0') + ':' + 
                                        minutes.toString().padStart(2, '0') + ':' + 
                                        seconds.toString().padStart(2, '0');
                        $timer.find('.dz-timer-display').html('<div class="dz-compact-timer">' + compactTime + '</div>');
                        break;
                    case 'minimal':
                        $timer.find('.dz-timer-display').html('<div class="dz-minimal-timer">' + days + ' days remaining</div>');
                        break;
                    case 'custom':
                        var customTime = customFormat
                            .replace('{days}', days)
                            .replace('{hours}', hours)
                            .replace('{minutes}', minutes)
                            .replace('{seconds}', seconds);
                        $timer.find('.dz-timer-display').html('<div class="dz-custom-timer">' + customTime + '</div>');
                        break;
                }
            }, 1000);
        }
    };

    /**
     * Progress Bar Widget
     */
    window.DZ_Events_Progress_Bar = {
        init: function() {
            $('.dz-progress-bar-widget').each(function() {
                var $widget = $(this);
                var progress = parseInt($widget.find('.dz-progress-bar').data('progress'));
                
                // Animate progress bar
                setTimeout(function() {
                    $widget.find('.dz-progress-bar').css('width', progress + '%');
                }, 500);
            });
        }
    };

    /**
     * Weather Widget
     */
    window.DZ_Events_Weather_Widget = {
        init: function() {
            $('.dz-weather-widget').each(function() {
                var $widget = $(this);
                var location = $widget.data('location');
                var eventDate = $widget.data('event-date');
                var days = $widget.data('days');
                var showDetails = $widget.data('show-details');

                DZ_Events_Weather_Widget.loadWeather($widget, location, eventDate, days, showDetails);
            });
        },

        loadWeather: function($widget, location, eventDate, days, showDetails) {
            // This would integrate with a weather API
            // For now, we'll show a placeholder
            $widget.find('.dz-weather-forecast').html(
                '<div class="dz-weather-placeholder">' +
                '<p>Weather forecast for ' + location + '</p>' +
                '<p>Event date: ' + eventDate + '</p>' +
                '<p>Loading weather data...</p>' +
                '</div>'
            );

            // Simulate API call
            setTimeout(function() {
                $widget.find('.dz-weather-forecast').html(
                    '<div class="dz-weather-day">' +
                    '<div class="dz-weather-icon">☀️</div>' +
                    '<div class="dz-weather-temp">22°C</div>' +
                    '<div class="dz-weather-desc">Sunny</div>' +
                    '</div>'
                );
            }, 2000);
        }
    };

    /**
     * Social Proof Widget
     */
    window.DZ_Events_Social_Proof_Widget = {
        init: function() {
            $('.dz-social-proof-widget').each(function() {
                var $widget = $(this);
                var autoRefresh = $widget.data('auto-refresh');

                if (autoRefresh === 'yes') {
                    DZ_Events_Social_Proof_Widget.startAutoRefresh($widget);
                }
            });
        },

        startAutoRefresh: function($widget) {
            setInterval(function() {
                // Refresh social proof data
                DZ_Events_Social_Proof_Widget.refreshData($widget);
            }, 30000); // Refresh every 30 seconds
        },

        refreshData: function($widget) {
            var eventId = $widget.data('event-id');
            var proofType = $widget.data('proof-type');

            // AJAX call to refresh data
            $.ajax({
                url: dz_events_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'dz_events_refresh_social_proof',
                    event_id: eventId,
                    proof_type: proofType,
                    nonce: dz_events_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.dz-social-proof-content').html(response.data.html);
                    }
                }
            });
        }
    };

    /**
     * Interactive Map Widget
     */
    window.DZ_Events_Interactive_Map_Widget = {
        init: function() {
            $('.dz-interactive-map-widget').each(function() {
                var $widget = $(this);
                var location = $widget.data('location');
                var mapType = $widget.data('map-type');
                var showNearby = $widget.data('show-nearby');

                DZ_Events_Interactive_Map_Widget.initMap($widget, location, mapType, showNearby);
            });
        },

        initMap: function($widget, location, mapType, showNearby) {
            // Initialize Google Maps
            var mapElement = $widget.find('.dz-event-map')[0];
            
            if (typeof google !== 'undefined' && google.maps) {
                var map = new google.maps.Map(mapElement, {
                    zoom: 15,
                    center: { lat: 0, lng: 0 }, // Will be updated with geocoded location
                    mapTypeId: mapType
                });

                // Geocode location
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: location }, function(results, status) {
                    if (status === 'OK') {
                        map.setCenter(results[0].geometry.location);
                        
                        // Add marker
                        var marker = new google.maps.Marker({
                            position: results[0].geometry.location,
                            map: map,
                            title: location
                        });

                        // Add info window
                        var infoWindow = new google.maps.InfoWindow({
                            content: '<div><h3>' + location + '</h3><p>Event Location</p></div>'
                        });

                        marker.addListener('click', function() {
                            infoWindow.open(map, marker);
                        });
                    }
                });
            } else {
                // Fallback if Google Maps is not loaded
                $widget.find('.dz-event-map').html(
                    '<div class="dz-map-fallback">' +
                    '<p>Map not available. Location: ' + location + '</p>' +
                    '</div>'
                );
            }

            // Map controls
            $widget.find('.dz-map-control-btn').on('click', function() {
                var action = $(this).data('action');
                DZ_Events_Interactive_Map_Widget.handleMapAction(action, location);
            });
        },

        handleMapAction: function(action, location) {
            switch (action) {
                case 'center':
                    // Center map on location
                    break;
                case 'directions':
                    // Open directions
                    window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(location));
                    break;
                case 'streetview':
                    // Open street view
                    window.open('https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' + encodeURIComponent(location));
                    break;
            }
        }
    };

    /**
     * Live Chat Widget
     */
    window.DZ_Events_Live_Chat_Widget = {
        init: function() {
            $('.dz-live-chat-widget').each(function() {
                var $widget = $(this);
                var eventId = $widget.data('event-id');
                var chatType = $widget.data('chat-type');
                var requireRegistration = $widget.data('require-registration');
                var maxMessages = $widget.data('max-messages');

                DZ_Events_Live_Chat_Widget.initChat($widget, eventId, chatType, requireRegistration, maxMessages);
            });
        },

        initChat: function($widget, eventId, chatType, requireRegistration, maxMessages) {
            // Load initial messages
            DZ_Events_Live_Chat_Widget.loadMessages($widget, eventId, maxMessages);

            // Set up message sending
            $widget.find('.dz-chat-send-btn').on('click', function() {
                DZ_Events_Live_Chat_Widget.sendMessage($widget, eventId);
            });

            $widget.find('.dz-chat-message-input').on('keypress', function(e) {
                if (e.which === 13) {
                    DZ_Events_Live_Chat_Widget.sendMessage($widget, eventId);
                }
            });

            // Auto-refresh messages
            setInterval(function() {
                DZ_Events_Live_Chat_Widget.loadMessages($widget, eventId, maxMessages);
            }, 5000);
        },

        loadMessages: function($widget, eventId, maxMessages) {
            $.ajax({
                url: dz_events_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'dz_events_load_chat_messages',
                    event_id: eventId,
                    max_messages: maxMessages,
                    nonce: dz_events_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.dz-chat-messages').html(response.data.html);
                        $widget.find('.dz-online-count').text(response.data.online_count + ' online');
                    }
                }
            });
        },

        sendMessage: function($widget, eventId) {
            var message = $widget.find('.dz-chat-message-input').val().trim();
            
            if (!message) return;

            $.ajax({
                url: dz_events_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'dz_events_send_chat_message',
                    event_id: eventId,
                    message: message,
                    nonce: dz_events_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.dz-chat-message-input').val('');
                        DZ_Events_Live_Chat_Widget.loadMessages($widget, eventId, 50);
                    }
                }
            });
        }
    };

    // Initialize all widgets when document is ready
    $(document).ready(function() {
        DZ_Events_Countdown_Timer.init();
        DZ_Events_Progress_Bar.init();
        DZ_Events_Weather_Widget.init();
        DZ_Events_Social_Proof_Widget.init();
        DZ_Events_Interactive_Map_Widget.init();
        DZ_Events_Live_Chat_Widget.init();
    });

})(jQuery);
