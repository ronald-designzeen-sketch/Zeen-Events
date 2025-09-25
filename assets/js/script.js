/**
 * Zeen Events - Basic JavaScript
 * Version: 2.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Basic event card interactions
    $('.dz-event-card').on('click', function(e) {
        if ($(e.target).is('a, button') || $(e.target).closest('a, button').length) {
            return;
        }
        
        var eventLink = $(this).find('.dz-event-title a');
        if (eventLink.length) {
            window.location.href = eventLink.attr('href');
        }
    });
    
    // Add hover effects
    $('.dz-event-card').hover(
        function() {
            $(this).addClass('dz-hover');
        },
        function() {
            $(this).removeClass('dz-hover');
        }
    );
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Basic AJAX functionality
    if (typeof dz_events_ajax !== 'undefined') {
        window.dzEvents = {
            ajax: function(action, data, callback) {
                data = data || {};
                data.action = action;
                data.nonce = dz_events_ajax.nonce;
                
                $.ajax({
                    url: dz_events_ajax.ajax_url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (callback) {
                            callback(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        if (callback) {
                            callback({success: false, data: 'An error occurred'});
                        }
                    }
                });
            }
        };
    }
    
    console.log('Zeen Events JavaScript loaded successfully');
});
