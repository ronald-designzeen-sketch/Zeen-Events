/**
 * Zeen Events JavaScript Functionality
 * Handles interactive features for events display
 */

(function($) {
    'use strict';

    // Debug function to test AJAX
    function testAjaxConnection() {
        console.log('Testing AJAX connection...');
        console.log('AJAX URL:', dz_events_ajax.ajax_url);
        console.log('Nonce:', dz_events_ajax.nonce);
        
        $.ajax({
            url: dz_events_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dz_get_event_calendar_data',
                event_id: 1, // Test with event ID 1
                nonce: dz_events_ajax.nonce
            },
            success: function(response) {
                console.log('Test AJAX success:', response);
            },
            error: function(xhr, status, error) {
                console.error('Test AJAX error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Document ready - initializing event widgets...');
        console.log('AJAX URL:', typeof dz_events_ajax !== 'undefined' ? dz_events_ajax.ajax_url : 'NOT DEFINED');
        console.log('AJAX Nonce:', typeof dz_events_ajax !== 'undefined' ? dz_events_ajax.nonce : 'NOT DEFINED');
        
        initEventCarousel();
        initEventFilters();
        initEventActions();
        initSocialShare();
        initEventCards();
        initEventSearch();
        initEventDetailsWidget();
        
        // Test AJAX connection (uncomment to debug)
        // testAjaxConnection();
        
        // Add a simple test to verify buttons are clickable
        setTimeout(function() {
            console.log('Testing button functionality...');
            console.log('Save to Calendar buttons:', $('.dz-save-to-calendar').length);
            console.log('Invite Friends buttons:', $('.dz-invite-friends').length);
            
            // Test if buttons are clickable
            $('.dz-save-to-calendar').each(function(index) {
                console.log('Save to Calendar button ' + index + ':', $(this));
            });
            
            $('.dz-invite-friends').each(function(index) {
                console.log('Invite Friends button ' + index + ':', $(this));
            });
        }, 1000);
    });

    /**
     * Initialize event carousel functionality
     */
    function initEventCarousel() {
        $('.dz-events-wrapper.dz-events-carousel').each(function() {
            var $carousel = $(this);
            var $cards = $carousel.find('.dz-event-card');
            
            if ($cards.length === 0) return;

            // Add navigation buttons
            var $nav = $('<div class="dz-carousel-nav"></div>');
            var $prevBtn = $('<button class="dz-carousel-btn dz-carousel-prev" aria-label="Previous events"><i class="fas fa-chevron-left"></i></button>');
            var $nextBtn = $('<button class="dz-carousel-btn dz-carousel-next" aria-label="Next events"><i class="fas fa-chevron-right"></i></button>');
            
            $nav.append($prevBtn, $nextBtn);
            $carousel.after($nav);

            var currentIndex = 0;
            var visibleCards = getVisibleCardsCount();
            var maxIndex = Math.max(0, $cards.length - visibleCards);

            // Update carousel position
            function updateCarousel() {
                var translateX = -currentIndex * (100 / visibleCards);
                $carousel.css('transform', 'translateX(' + translateX + '%)');
                
                $prevBtn.prop('disabled', currentIndex === 0);
                $nextBtn.prop('disabled', currentIndex >= maxIndex);
            }

            // Get number of visible cards based on screen size
            function getVisibleCardsCount() {
                var width = $(window).width();
                if (width < 480) return 1;
                if (width < 768) return 1.5;
                if (width < 1024) return 2;
                return 3;
            }

            // Navigation handlers
            $prevBtn.on('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateCarousel();
                }
            });

            $nextBtn.on('click', function() {
                if (currentIndex < maxIndex) {
                    currentIndex++;
                    updateCarousel();
                }
            });

            // Handle window resize
            $(window).on('resize', function() {
                visibleCards = getVisibleCardsCount();
                maxIndex = Math.max(0, $cards.length - visibleCards);
                if (currentIndex > maxIndex) {
                    currentIndex = maxIndex;
                }
                updateCarousel();
            });

            // Initialize
            updateCarousel();

            // Add touch/swipe support for mobile
            var startX = 0;
            var startY = 0;
            var distX = 0;
            var distY = 0;

            $carousel.on('touchstart', function(e) {
                var touch = e.originalEvent.touches[0];
                startX = touch.clientX;
                startY = touch.clientY;
            });

            $carousel.on('touchmove', function(e) {
                if (!startX || !startY) return;
                
                var touch = e.originalEvent.touches[0];
                distX = touch.clientX - startX;
                distY = touch.clientY - startY;
            });

            $carousel.on('touchend', function(e) {
                if (!startX || !startY) return;

                // Check if horizontal swipe is more significant than vertical
                if (Math.abs(distX) > Math.abs(distY) && Math.abs(distX) > 50) {
                    if (distX > 0 && currentIndex > 0) {
                        // Swipe right - go to previous
                        currentIndex--;
                        updateCarousel();
                    } else if (distX < 0 && currentIndex < maxIndex) {
                        // Swipe left - go to next
                        currentIndex++;
                        updateCarousel();
                    }
                }

                startX = 0;
                startY = 0;
                distX = 0;
                distY = 0;
            });
        });
    }

    /**
     * Initialize event filters functionality
     */
    function initEventFilters() {
        $('.dz-events-filter-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $wrapper = $('.dz-events-wrapper');
            var $loading = $('<div class="dz-events-loading">Loading events...</div>');
            
            // Show loading state
            $wrapper.before($loading);
            $wrapper.hide();

            // Get form data
            var formData = $form.serialize();
            
            // Make AJAX request
            $.ajax({
                url: dz_events_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'dz_filter_events',
                    form_data: formData,
                    nonce: dz_events_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $wrapper.html(response.data.html);
                        $wrapper.show();
                        
                        // Update URL without page reload
                        var url = new URL(window.location);
                        var params = new URLSearchParams(formData);
                        url.search = params.toString();
                        window.history.pushState({}, '', url);
                    } else {
                        $wrapper.html('<div class="dz-no-events"><h3>Error loading events</h3><p>Please try again later.</p></div>');
                        $wrapper.show();
                    }
                },
                error: function() {
                    $wrapper.html('<div class="dz-no-events"><h3>Error loading events</h3><p>Please try again later.</p></div>');
                    $wrapper.show();
                },
                complete: function() {
                    $loading.remove();
                }
            });
        });

        // Auto-submit on filter change (optional)
        $('.dz-events-filter-form select').on('change', function() {
            if ($(this).closest('.dz-events-filter-form').data('auto-submit')) {
                $(this).closest('form').submit();
            }
        });
    }

    /**
     * Initialize event action buttons
     */
    function initEventActions() {
        // Add to calendar functionality
        $('.dz-btn-calendar').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            
            // Open calendar in new window
            window.open(url, '_blank', 'width=600,height=600,scrollbars=yes,resizable=yes');
        });

        // Register button tracking
        $('.dz-btn-register').on('click', function(e) {
            var eventId = $(this).data('event-id');
            var eventTitle = $(this).data('event-title');
            
            // Track registration click (you can integrate with analytics)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'event_registration_click', {
                    'event_category': 'Events',
                    'event_label': eventTitle,
                    'value': eventId
                });
            }
        });

        // Back button functionality
        $('.dz-btn-back').on('click', function(e) {
            // If there's a referrer and it's from the same domain, go back
            if (document.referrer && document.referrer.includes(window.location.hostname)) {
                e.preventDefault();
                window.history.back();
            }
        });
    }

    /**
     * Initialize social share functionality
     */
    function initSocialShare() {
        $('.dz-social-btn').on('click', function(e) {
            var $btn = $(this);
            var platform = $btn.attr('class').match(/dz-social-(\w+)/);
            
            if (platform) {
                platform = platform[1];
                
                // Track social share
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'share', {
                        'method': platform,
                        'content_type': 'event',
                        'item_id': $btn.data('event-id') || 'unknown'
                    });
                }
            }
        });

        // Copy link functionality for email sharing
        $('.dz-social-email').on('click', function(e) {
            var url = $(this).attr('href');
            if (url.startsWith('mailto:')) {
                // Extract the URL from mailto link
                var urlMatch = url.match(/body=([^&]+)/);
                if (urlMatch) {
                    var eventUrl = decodeURIComponent(urlMatch[1]).split(' ').pop();
                    
                    // Copy to clipboard
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(eventUrl).then(function() {
                            showNotification('Event link copied to clipboard!');
                        });
                    }
                }
            }
        });
    }

    /**
     * Initialize enhanced event search functionality
     */
    function initEventSearch() {
        var $searchInput = $('#dz-events-search, .dz-events-search-input');
        var $searchBtn = $('#dz-events-search-btn, .dz-events-search-btn');
        var $categoryFilter = $('#dz-events-category-filter, .dz-events-category-filter');
        var $dateFromFilter = $('#dz-events-date-from, .dz-events-date-from');
        var $dateToFilter = $('#dz-events-date-to, .dz-events-date-to');
        var $clearFiltersBtn = $('#dz-events-clear-filters, .dz-events-clear-filters');
        
        // Search input with debounce
        $searchInput.on('input', debounce(function() {
            performSearch();
        }, 300));
        
        // Search button click
        $searchBtn.on('click', function() {
            performSearch();
        });
        
        // Filter changes
        $categoryFilter.on('change', performSearch);
        $dateFromFilter.on('change', performSearch);
        $dateToFilter.on('change', performSearch);
        
        // Clear filters
        $clearFiltersBtn.on('click', function() {
            $searchInput.val('');
            $categoryFilter.val('');
            $dateFromFilter.val('');
            $dateToFilter.val('');
            performSearch();
        });
        
        // Clear search on escape key
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('').trigger('input');
            }
        });
        
        function performSearch() {
            var searchTerm = $searchInput.val().toLowerCase().trim();
            var selectedCategory = $categoryFilter.val();
            var fromDate = $dateFromFilter.val();
            var toDate = $dateToFilter.val();
            
            // Debug logging
            console.log('Search performed:', {
                searchTerm: searchTerm,
                selectedCategory: selectedCategory,
                fromDate: fromDate,
                toDate: toDate
            });
            
            var $searchContainer = $searchInput.closest('.dz-events-search-container');
            var $wrapper = $searchContainer.next('.dz-events-wrapper');
            var $cards = $wrapper.find('.dz-event-card-custom');
            var $resultsCount = $searchContainer.find('.dz-events-search-results-count');
            var visibleCount = 0;
            
            console.log('Found elements:', {
                searchContainer: $searchContainer.length,
                wrapper: $wrapper.length,
                cards: $cards.length,
                resultsCount: $resultsCount.length
            });
            
            $cards.each(function() {
                var $card = $(this);
                var isMatch = true;
                
                // Enhanced text search
                if (searchTerm !== '') {
                    var textMatch = false;
                    var searchWords = searchTerm.split(/\s+/);
                    var searchScore = 0;
                    var totalWords = searchWords.length;
                    
                    // Get all searchable text from the card
                    var cardTitle = $card.find('.dz-event-title').text().toLowerCase();
                    var cardExcerpt = $card.find('.dz-event-excerpt').text().toLowerCase();
                    var cardLocation = $card.find('.dz-event-location').text().toLowerCase();
                    var cardPrice = $card.find('.dz-event-price').text().toLowerCase();
                    var cardCategory = $card.data('search-category') || '';
                    
                    // Check for exact phrase match (highest priority)
                    if (cardTitle.includes(searchTerm) || cardExcerpt.includes(searchTerm)) {
                        searchScore = totalWords;
                    } else {
                        // Check for individual word matches with scoring
                        searchWords.forEach(function(word) {
                            if (word.length > 2) { // Only consider words longer than 2 characters
                                if (cardTitle.includes(word)) {
                                    searchScore += 1.5; // Title matches are weighted higher
                                }
                                if (cardExcerpt.includes(word)) {
                                    searchScore += 1;
                                }
                                if (cardLocation.includes(word)) {
                                    searchScore += 0.8;
                                }
                                if (cardPrice.includes(word)) {
                                    searchScore += 0.5;
                                }
                                if (cardCategory.toLowerCase().includes(word)) {
                                    searchScore += 0.7;
                                }
                            }
                        });
                    }
                    
                    // Show results if at least 30% of search words match or exact phrase found
                    textMatch = searchScore >= (totalWords * 0.3);
                    isMatch = isMatch && textMatch;
                }
                
                // Category filter
                if (selectedCategory !== '' && isMatch) {
                    var categoryMatch = false;
                    $card[0].attributes.forEach(function(attr) {
                        if (attr.name === 'data-search-category') {
                            var value = attr.value.toLowerCase();
                            if (value.includes(selectedCategory.toLowerCase())) {
                                categoryMatch = true;
                                return false;
                            }
                        }
                    });
                    isMatch = isMatch && categoryMatch;
                }
                
                // Date range filter
                if ((fromDate !== '' || toDate !== '') && isMatch) {
                    var dateMatch = false;
                    $card[0].attributes.forEach(function(attr) {
                        if (attr.name === 'data-search-date' || attr.name === 'data-search-start_date') {
                            var eventDate = attr.value;
                            if (eventDate) {
                                var eventDateObj = new Date(eventDate);
                                var matchesFrom = fromDate === '' || eventDateObj >= new Date(fromDate);
                                var matchesTo = toDate === '' || eventDateObj <= new Date(toDate);
                                if (matchesFrom && matchesTo) {
                                    dateMatch = true;
                                    return false;
                                }
                            }
                        }
                    });
                    isMatch = isMatch && dateMatch;
                }
                
                if (isMatch) {
                    $card.removeClass('search-hidden').addClass('search-visible');
                    visibleCount++;
                } else {
                    $card.removeClass('search-visible').addClass('search-hidden');
                }
            });
            
            // Update results count
            var hasFilters = searchTerm !== '' || selectedCategory !== '' || fromDate !== '' || toDate !== '';
            if (!hasFilters) {
                $resultsCount.hide();
            } else {
                if (visibleCount > 0) {
                    $resultsCount.text(visibleCount + ' event' + (visibleCount !== 1 ? 's' : '') + ' found').addClass('show');
                } else {
                    $resultsCount.text('No events found').addClass('show');
                }
            }
        }
    }

    /**
     * Initialize event cards interactions
     */
    function initEventCards() {
        // Add hover effects and animations
        $('.dz-event-card').each(function() {
            var $card = $(this);
            
            // Add click tracking
            $card.on('click', function(e) {
                if (!$(e.target).is('a, button')) {
                    var $link = $card.find('.dz-btn-primary');
                    if ($link.length) {
                        window.location.href = $link.attr('href');
                    }
                }
            });

            // Add keyboard navigation
            $card.attr('tabindex', '0');
            $card.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    $(this).click();
                }
            });
        });

        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });

            $('.dz-event-thumb img[data-src]').each(function() {
                imageObserver.observe(this);
            });
        }
    }

    /**
     * Show notification message
     */
    function showNotification(message) {
        var $notification = $('<div class="dz-notification">' + message + '</div>');
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.addClass('show');
        }, 100);
        
        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Utility function to debounce events
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

})(jQuery);

// Add CSS for carousel navigation and notifications
var carouselCSS = `
<style>
.dz-carousel-nav {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.dz-carousel-btn {
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 16px;
}

.dz-carousel-btn:hover:not(:disabled) {
    background: #005f8d;
    transform: scale(1.1);
}

.dz-carousel-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    opacity: 0.5;
}

.dz-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    z-index: 9999;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.dz-notification.show {
    transform: translateX(0);
}

.dz-events-wrapper.dz-events-carousel {
    display: flex;
    transition: transform 0.3s ease;
}

.dz-events-wrapper.dz-events-carousel .dz-event-card {
    flex: 0 0 auto;
    margin-right: 20px;
}

@media (max-width: 768px) {
    .dz-carousel-nav {
        margin-top: 15px;
    }
    
    .dz-carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
    
    .dz-notification {
        top: 10px;
        right: 10px;
        left: 10px;
        text-align: center;
    }
}
</style>
`;

// Inject CSS
document.head.insertAdjacentHTML('beforeend', carouselCSS);

    /**
     * Initialize event details widget functionality
     */
    function initEventDetailsWidget() {
        console.log('Initializing Event Details Widget...');
        
        // Debug: Check if buttons exist
        console.log('Save to Calendar buttons found:', $('.dz-save-to-calendar').length);
        console.log('Invite Friends buttons found:', $('.dz-invite-friends').length);
        console.log('Calendar dropdown buttons found:', $('.dz-calendar-dropdown').length);
        
        // Calendar dropdown functionality
        $(document).on('click', '.dz-calendar-option', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $option = $(this);
            var calendarType = $option.data('calendar-type');
            var customUrl = $option.data('custom-url');
            var eventId = $option.data('event-id');
            
            // Get event details from data attributes
            var eventDetails = {
                title: $option.data('event-title'),
                description: $option.data('event-description'),
                location: $option.data('event-location'),
                organizer: $option.data('event-organizer'),
                organizerEmail: $option.data('event-organizer-email'),
                website: $option.data('event-website'),
                reminder: $option.data('event-reminder'),
                privacy: $option.data('event-privacy'),
                showUrl: $option.data('event-show-url'),
                showPrice: $option.data('event-show-price'),
                showCapacity: $option.data('event-show-capacity'),
                price: $option.data('event-price'),
                capacity: $option.data('event-capacity'),
                url: $option.data('event-url')
            };
            
            console.log('Calendar option clicked:', { calendarType, customUrl, eventId, eventDetails });
            
            // Close the dropdown
            $option.closest('.dz-calendar-dropdown').removeClass('active');
            
            // Handle the calendar action with event details
            handleCalendarAction(calendarType, customUrl, eventId, eventDetails);
        });
        
        // Calendar dropdown click functionality
        $(document).on('click', '.dz-calendar-dropdown .dz-event-action-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $dropdown = $(this).closest('.dz-calendar-dropdown');
            
            console.log('Calendar button clicked, dropdown:', $dropdown);
            console.log('Button element:', $(this));
            
            // Toggle active state
            $dropdown.toggleClass('active');
            
            // Close other dropdowns
            $('.dz-calendar-dropdown').not($dropdown).removeClass('active');
            
            console.log('Dropdown active state:', $dropdown.hasClass('active'));
        });
        
        // Alternative click handler for save to calendar button
        $(document).on('click', '.dz-save-to-calendar', function(e) {
            console.log('Save to Calendar button clicked directly!');
            console.log('Button element:', $(this));
            console.log('Button classes:', $(this).attr('class'));
            
            // Check if it's inside a dropdown
            var $dropdown = $(this).closest('.dz-calendar-dropdown');
            if ($dropdown.length > 0) {
                console.log('Button is inside dropdown, toggling...');
                e.preventDefault();
                e.stopPropagation();
                $dropdown.toggleClass('active');
                $('.dz-calendar-dropdown').not($dropdown).removeClass('active');
            } else {
                console.log('Button is not inside dropdown, this might be a legacy button');
            }
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dz-calendar-dropdown').length) {
                $('.dz-calendar-dropdown').removeClass('active');
            }
        });
        
        // Legacy save to calendar functionality (fallback)
        $(document).on('click', '.dz-save-to-calendar', function(e) {
            // Only prevent default if it's not a dropdown button
            if (!$(this).closest('.dz-calendar-dropdown').length) {
                e.preventDefault();
                var eventId = $(this).data('event-id');
                saveToCalendar(eventId);
            }
        });
        
        // Invite Friends functionality
        $(document).on('click', '.dz-invite-friends', function(e) {
            e.preventDefault();
            console.log('Invite Friends button clicked!');
            var eventId = $(this).data('event-id');
            console.log('Event ID:', eventId);
            showInviteFriendsModal(eventId);
        });
        
        // Back button functionality
        $(document).on('click', '.dz-back-button', function(e) {
            e.preventDefault();
            history.back();
        });
    }
    
    /**
     * Handle calendar action based on type
     */
    function handleCalendarAction(calendarType, customUrl, eventId, eventDetails) {
        console.log('handleCalendarAction called:', { calendarType, customUrl, eventId, eventDetails });
        
        switch (calendarType) {
            case 'google':
                console.log('Adding to Google Calendar');
                addToGoogleCalendar(eventId, eventDetails);
                break;
            case 'outlook':
                console.log('Adding to Outlook Calendar');
                addToOutlookCalendar(eventId, eventDetails);
                break;
            case 'yahoo':
                console.log('Adding to Yahoo Calendar');
                addToYahooCalendar(eventId, eventDetails);
                break;
            case 'apple':
                console.log('Adding to Apple Calendar');
                addToAppleCalendar(eventId, eventDetails);
                break;
            case 'ical':
                console.log('Downloading iCal file');
                saveToCalendar(eventId, eventDetails);
                break;
            case 'custom':
                console.log('Opening custom URL');
                if (customUrl) {
                    window.open(customUrl, '_blank');
                } else {
                    alert('Custom URL not configured');
                }
                break;
            default:
                console.log('Default case - saving to calendar');
                saveToCalendar(eventId, eventDetails);
        }
    }
    
    /**
     * Save event to calendar (iCal download)
     */
    function saveToCalendar(eventId, eventDetails) {
        // Get event data via AJAX
        $.ajax({
            url: dz_events_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dz_get_event_calendar_data',
                event_id: eventId,
                nonce: dz_events_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var eventData = response.data;
                    generateCalendarFile(eventData);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error loading event data');
            }
        });
    }
    
    /**
     * Add to Google Calendar
     */
    function addToGoogleCalendar(eventId, eventDetails) {
        console.log('Adding to Google Calendar for event ID:', eventId, 'with details:', eventDetails);
        
        // Use provided event details or fetch via AJAX
        if (eventDetails && eventDetails.title) {
            console.log('Using provided event details for Google Calendar');
            var googleUrl = generateGoogleCalendarUrl(eventDetails);
            console.log('Generated Google Calendar URL:', googleUrl);
            window.open(googleUrl, '_blank');
        } else {
            console.log('Fetching event data via AJAX for Google Calendar');
            $.ajax({
                url: dz_events_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'dz_get_event_calendar_data',
                    event_id: eventId,
                    nonce: dz_events_ajax.nonce
                },
                success: function(response) {
                    console.log('Google Calendar data response:', response);
                    if (response.success) {
                        var eventData = response.data;
                        console.log('Event data for Google Calendar:', eventData);
                        var googleUrl = generateGoogleCalendarUrl(eventData);
                        console.log('Generated Google Calendar URL:', googleUrl);
                        window.open(googleUrl, '_blank');
                    } else {
                        console.error('Google Calendar data error:', response.data);
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Google Calendar AJAX error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading event data');
                }
            });
        }
    }
    
    /**
     * Add to Outlook Calendar
     */
    function addToOutlookCalendar(eventId) {
        $.ajax({
            url: dz_events_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dz_get_event_calendar_data',
                event_id: eventId,
                nonce: dz_events_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var eventData = response.data;
                    var outlookUrl = generateOutlookCalendarUrl(eventData);
                    window.open(outlookUrl, '_blank');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error loading event data');
            }
        });
    }
    
    /**
     * Add to Yahoo Calendar
     */
    function addToYahooCalendar(eventId) {
        $.ajax({
            url: dz_events_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dz_get_event_calendar_data',
                event_id: eventId,
                nonce: dz_events_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var eventData = response.data;
                    var yahooUrl = generateYahooCalendarUrl(eventData);
                    window.open(yahooUrl, '_blank');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error loading event data');
            }
        });
    }
    
    /**
     * Add to Apple Calendar
     */
    function addToAppleCalendar(eventId) {
        // For Apple Calendar, we'll generate an ICS file
        saveToCalendar(eventId);
    }
    
    /**
     * Generate and download calendar file
     */
    function generateCalendarFile(eventData) {
        var icsContent = generateICSContent(eventData);
        var blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = eventData.title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.ics';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    /**
     * Generate ICS content
     */
    function generateICSContent(eventData) {
        var startDate = formatDateForICS(eventData.start_date, eventData.start_time);
        var endDate = formatDateForICS(eventData.end_date || eventData.start_date, eventData.end_time || eventData.start_time);
        
        // Build comprehensive description
        var description = eventData.description || '';
        if (eventData.price) {
            description += '\n\nPrice: ' + eventData.price;
        }
        if (eventData.capacity) {
            description += '\nCapacity: ' + eventData.capacity;
        }
        if (eventData.contact) {
            description += '\nContact: ' + eventData.contact;
        }
        description += '\n\nView Event: ' + eventData.url;
        
        // Escape special characters for ICS format
        description = description.replace(/[,;\\]/g, '\\$&');
        var title = eventData.title.replace(/[,;\\]/g, '\\$&');
        var location = (eventData.location || '').replace(/[,;\\]/g, '\\$&');
        
        return [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Zeen Events//Event Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' + eventData.id + '@' + window.location.hostname,
            'DTSTART:' + startDate,
            'DTEND:' + endDate,
            'DTSTAMP:' + formatDateForICS(new Date().toISOString().split('T')[0], '00:00'),
            'SUMMARY:' + title,
            'DESCRIPTION:' + description,
            'LOCATION:' + location,
            'URL:' + eventData.url,
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
            'END:VEVENT',
            'END:VCALENDAR'
        ].join('\r\n');
    }
    
    /**
     * Format date for ICS
     */
    function formatDateForICS(date, time) {
        var dateTime = new Date(date + ' ' + time);
        return dateTime.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    }
    
    /**
     * Generate Google Calendar URL
     */
    function generateGoogleCalendarUrl(eventData) {
        var startDate = formatDateForGoogle(eventData.start_date, eventData.start_time);
        var endDate = formatDateForGoogle(eventData.end_date || eventData.start_date, eventData.end_time || eventData.start_time);
        
        // Build enhanced description with custom details
        var description = eventData.description || '';
        
        // Add organizer info if available
        if (eventData.organizer) {
            description += '\n\nOrganizer: ' + eventData.organizer;
            if (eventData.organizerEmail) {
                description += ' (' + eventData.organizerEmail + ')';
            }
        }
        
        // Add website if available and enabled
        if (eventData.website && eventData.showUrl === 'yes') {
            description += '\n\nWebsite: ' + eventData.website;
        } else if (eventData.url && eventData.showUrl === 'yes') {
            description += '\n\nEvent URL: ' + eventData.url;
        }
        
        // Add price if available and enabled
        if (eventData.price && eventData.showPrice === 'yes') {
            description += '\n\nPrice: ' + eventData.price;
        }
        
        // Add capacity if available and enabled
        if (eventData.capacity && eventData.showCapacity === 'yes') {
            description += '\n\nCapacity: ' + eventData.capacity;
        }
        
        var params = {
            action: 'TEMPLATE',
            text: eventData.title,
            dates: startDate + '/' + endDate,
            details: description,
            location: eventData.location || '',
            trp: false
        };
        
        // Add reminder if specified
        if (eventData.reminder && eventData.reminder !== '0') {
            params.remind = eventData.reminder;
        }
        
        var queryString = Object.keys(params).map(function(key) {
            return key + '=' + encodeURIComponent(params[key]);
        }).join('&');
        
        return 'https://calendar.google.com/calendar/render?' + queryString;
    }
    
    /**
     * Generate Outlook Calendar URL
     */
    function generateOutlookCalendarUrl(eventData) {
        var startDate = formatDateForOutlook(eventData.start_date, eventData.start_time);
        var endDate = formatDateForOutlook(eventData.end_date || eventData.start_date, eventData.end_time || eventData.start_time);
        
        var params = {
            path: '/calendar/action/compose',
            rru: 'addevent',
            subject: eventData.title,
            startdt: startDate,
            enddt: endDate,
            body: eventData.description + '\n\n' + eventData.url,
            location: eventData.location || ''
        };
        
        var queryString = Object.keys(params).map(function(key) {
            return key + '=' + encodeURIComponent(params[key]);
        }).join('&');
        
        return 'https://outlook.live.com/calendar/0/deeplink/compose?' + queryString;
    }
    
    /**
     * Generate Yahoo Calendar URL
     */
    function generateYahooCalendarUrl(eventData) {
        var startDate = formatDateForYahoo(eventData.start_date, eventData.start_time);
        var endDate = formatDateForYahoo(eventData.end_date || eventData.start_date, eventData.end_time || eventData.start_time);
        
        var params = {
            v: '60',
            view: 'd',
            type: '20',
            title: eventData.title,
            st: startDate,
            et: endDate,
            desc: eventData.description + '\n\n' + eventData.url,
            in_loc: eventData.location || ''
        };
        
        var queryString = Object.keys(params).map(function(key) {
            return key + '=' + encodeURIComponent(params[key]);
        }).join('&');
        
        return 'https://calendar.yahoo.com/?' + queryString;
    }
    
    /**
     * Format date for Google Calendar
     */
    function formatDateForGoogle(date, time) {
        var dateTime = new Date(date + ' ' + time);
        return dateTime.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    }
    
    /**
     * Format date for Outlook Calendar
     */
    function formatDateForOutlook(date, time) {
        var dateTime = new Date(date + ' ' + time);
        return dateTime.toISOString();
    }
    
    /**
     * Format date for Yahoo Calendar
     */
    function formatDateForYahoo(date, time) {
        var dateTime = new Date(date + ' ' + time);
        return Math.floor(dateTime.getTime() / 1000);
    }
    
    /**
     * Show invite friends modal
     */
    function showInviteFriendsModal(eventId) {
        console.log('showInviteFriendsModal called with eventId:', eventId);
        // Get event data first
        $.ajax({
            url: dz_events_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dz_get_event_calendar_data',
                event_id: eventId,
                nonce: dz_events_ajax.nonce
            },
            success: function(response) {
                console.log('Calendar data response:', response);
                if (response.success) {
                    var eventData = response.data;
                    console.log('Event data received:', eventData);
                    showInviteModalWithData(eventId, eventData);
                } else {
                    console.error('Calendar data error:', response.data);
                    showInviteModalWithData(eventId, null);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Response:', xhr.responseText);
                showInviteModalWithData(eventId, null);
            }
        });
    }
    
    /**
     * Show invite modal with event data
     */
    function showInviteModalWithData(eventId, eventData) {
        var eventInfo = '';
        if (eventData) {
            // Use formatted dates if available, fallback to raw dates
            var displayDate = eventData.formatted_start_date || eventData.start_date || 'TBA';
            var displayTime = eventData.start_time || 'TBA';
            var displayLocation = eventData.location || 'TBA';
            var displayPrice = eventData.price || 'TBA';
            var displayCapacity = eventData.capacity || 'TBA';
            
            eventInfo = `
                <div class="dz-event-preview-card">
                    <div class="dz-event-preview-header">
                        <h4><i class="bi bi-calendar-event"></i> Event Details</h4>
                    </div>
                    <div class="dz-event-preview-content">
                        <div class="dz-event-preview-item">
                            <i class="bi bi-calendar3"></i>
                            <span class="dz-event-preview-label">Event:</span>
                            <span class="dz-event-preview-value">${eventData.title || 'Untitled Event'}</span>
                        </div>
                        <div class="dz-event-preview-item">
                            <i class="bi bi-calendar-date"></i>
                            <span class="dz-event-preview-label">Date:</span>
                            <span class="dz-event-preview-value">${displayDate}</span>
                        </div>
                        <div class="dz-event-preview-item">
                            <i class="bi bi-clock"></i>
                            <span class="dz-event-preview-label">Time:</span>
                            <span class="dz-event-preview-value">${displayTime}</span>
                        </div>
                        <div class="dz-event-preview-item">
                            <i class="bi bi-geo-alt"></i>
                            <span class="dz-event-preview-label">Location:</span>
                            <span class="dz-event-preview-value">${displayLocation}</span>
                        </div>
                        <div class="dz-event-preview-item">
                            <i class="bi bi-currency-dollar"></i>
                            <span class="dz-event-preview-label">Price:</span>
                            <span class="dz-event-preview-value">${displayPrice}</span>
                        </div>
                        <div class="dz-event-preview-item">
                            <i class="bi bi-people"></i>
                            <span class="dz-event-preview-label">Capacity:</span>
                            <span class="dz-event-preview-value">${displayCapacity}</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            eventInfo = `
                <div class="dz-event-preview-card">
                    <div class="dz-event-preview-header">
                        <h4><i class="bi bi-calendar-event"></i> Event Details</h4>
                    </div>
                    <div class="dz-event-preview-content">
                        <div class="dz-event-preview-loading">
                            <i class="bi bi-hourglass-split"></i>
                            <span>Loading event information...</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        var modalHtml = `
            <div id="dz-invite-modal" class="dz-modal-overlay">
                <div class="dz-modal-content dz-modal-invite">
                    <div class="dz-modal-header">
                        <div class="dz-modal-header-content">
                            <h3><i class="bi bi-envelope-heart"></i> Invite Friends to Event</h3>
                            <p>Share this amazing event with your friends!</p>
                        </div>
                        <button class="dz-modal-close" aria-label="Close modal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="dz-modal-body">
                        ${eventInfo}
                        
                        <div class="dz-invite-form-section">
                            <h4><i class="bi bi-person-plus"></i> Invitation Details</h4>
                            <form id="dz-invite-form" class="dz-invite-form">
                                <div class="dz-form-row">
                                    <div class="dz-form-group">
                                        <label for="invite-name">
                                            <i class="bi bi-person"></i> Your Name *
                                        </label>
                                        <input type="text" id="invite-name" name="name" required 
                                               placeholder="Enter your full name">
                                        <div class="dz-form-error" id="name-error"></div>
                                    </div>
                                </div>
                                
                                <div class="dz-form-row">
                                    <div class="dz-form-group">
                                        <label for="invite-friend-names">
                                            <i class="bi bi-people"></i> Friend Names *
                                        </label>
                                        <textarea id="invite-friend-names" name="friend_names" 
                                                  placeholder="Enter each friend's name on a separate line" required></textarea>
                                        <small><i class="bi bi-info-circle"></i> Example: John Smith, Jane Doe, Mike Johnson</small>
                                        <div class="dz-form-error" id="names-error"></div>
                                    </div>
                                </div>
                                
                                <div class="dz-form-row">
                                    <div class="dz-form-group">
                                        <label for="invite-friend-emails">
                                            <i class="bi bi-envelope"></i> Friend Emails *
                                        </label>
                                        <textarea id="invite-friend-emails" name="friend_emails" 
                                                  placeholder="Enter each friend's email on a separate line" required></textarea>
                                        <small><i class="bi bi-info-circle"></i> Example: john@example.com, jane@example.com, mike@example.com</small>
                                        <div class="dz-form-error" id="emails-error"></div>
                                    </div>
                                </div>
                                
                                <div class="dz-form-row">
                                    <div class="dz-form-group">
                                        <label for="invite-subject">
                                            <i class="bi bi-chat-text"></i> Email Subject (Optional)
                                        </label>
                                        <input type="text" id="invite-subject" name="subject" 
                                               placeholder="Custom subject line for the invitation">
                                    </div>
                                </div>
                                
                                <div class="dz-form-row">
                                    <div class="dz-form-group">
                                        <label for="invite-message">
                                            <i class="bi bi-chat-heart"></i> Personal Message (Optional)
                                        </label>
                                        <textarea id="invite-message" name="message" 
                                                  placeholder="Add a personal message to make your invitation special..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="dz-form-actions">
                                    <button type="button" class="dz-btn dz-btn-secondary dz-modal-close">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                    <button type="submit" class="dz-btn dz-btn-primary dz-btn-send">
                                        <i class="bi bi-send"></i> Send Invitations
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        
        // Add form validation
        addInviteFormValidation();
        
        // Handle form submission
        $('#dz-invite-form').on('submit', function(e) {
            e.preventDefault();
            if (validateInviteForm()) {
                sendInvitations(eventId, $(this).serialize());
            }
        });
        
        // Handle modal close
        $('.dz-modal-close').on('click', function() {
            closeInviteModal();
        });
        
        // Close on overlay click
        $('.dz-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                closeInviteModal();
            }
        });
        
        // Close on Escape key
        $(document).on('keydown.inviteModal', function(e) {
            if (e.key === 'Escape') {
                closeInviteModal();
            }
        });
        
        // Focus on first input
        setTimeout(function() {
            $('#invite-name').focus();
        }, 100);
    }
    
    /**
     * Add form validation
     */
    function addInviteFormValidation() {
        // Real-time validation
        $('#invite-name').on('blur', function() {
            validateName($(this).val());
        });
        
        $('#invite-friend-names').on('blur', function() {
            validateFriendNames($(this).val());
        });
        
        $('#invite-friend-emails').on('blur', function() {
            validateFriendEmails($(this).val());
        });
    }
    
    /**
     * Validate name field
     */
    function validateName(name) {
        var error = $('#name-error');
        if (!name || name.trim().length < 2) {
            error.text('Please enter your full name (at least 2 characters)').show();
            return false;
        }
        error.hide();
        return true;
    }
    
    /**
     * Validate friend names
     */
    function validateFriendNames(names) {
        var error = $('#names-error');
        if (!names || names.trim().length === 0) {
            error.text('Please enter at least one friend\'s name').show();
            return false;
        }
        
        var nameList = names.split('\n').filter(name => name.trim().length > 0);
        if (nameList.length === 0) {
            error.text('Please enter at least one friend\'s name').show();
            return false;
        }
        
        if (nameList.length > 10) {
            error.text('Maximum 10 friends allowed per invitation').show();
            return false;
        }
        
        error.hide();
        return true;
    }
    
    /**
     * Validate friend emails
     */
    function validateFriendEmails(emails) {
        var error = $('#emails-error');
        if (!emails || emails.trim().length === 0) {
            error.text('Please enter at least one friend\'s email').show();
            return false;
        }
        
        var emailList = emails.split('\n').filter(email => email.trim().length > 0);
        if (emailList.length === 0) {
            error.text('Please enter at least one friend\'s email').show();
            return false;
        }
        
        if (emailList.length > 10) {
            error.text('Maximum 10 emails allowed per invitation').show();
            return false;
        }
        
        // Validate email format
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        for (var i = 0; i < emailList.length; i++) {
            if (!emailRegex.test(emailList[i].trim())) {
                error.text('Please enter valid email addresses').show();
                return false;
            }
        }
        
        error.hide();
        return true;
    }
    
    /**
     * Validate entire form
     */
    function validateInviteForm() {
        var name = $('#invite-name').val();
        var names = $('#invite-friend-names').val();
        var emails = $('#invite-friend-emails').val();
        
        var nameValid = validateName(name);
        var namesValid = validateFriendNames(names);
        var emailsValid = validateFriendEmails(emails);
        
        // Check if number of names matches number of emails
        if (namesValid && emailsValid) {
            var nameList = names.split('\n').filter(n => n.trim().length > 0);
            var emailList = emails.split('\n').filter(e => e.trim().length > 0);
            
            if (nameList.length !== emailList.length) {
                $('#emails-error').text('Number of names must match number of emails').show();
                return false;
            }
        }
        
        return nameValid && namesValid && emailsValid;
    }
    
    /**
     * Close invite modal
     */
    function closeInviteModal() {
        $('#dz-invite-modal').fadeOut(300, function() {
            $(this).remove();
        });
        $(document).off('keydown.inviteModal');
    }
    
    /**
     * Send invitations
     */
    function sendInvitations(eventId, formData) {
        var $submitBtn = $('.dz-btn-send');
        var originalText = $submitBtn.html();
        
        $.ajax({
            url: dz_events_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dz_send_event_invitations',
                event_id: eventId,
                form_data: formData,
                nonce: dz_events_ajax.nonce
            },
            beforeSend: function() {
                $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');
                $('.dz-form-actions button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    showSuccessMessage('Invitations sent successfully! Your friends will receive the event details via email.');
                    closeInviteModal();
                } else {
                    showErrorMessage('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showErrorMessage('Error sending invitations. Please try again.');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalText);
                $('.dz-form-actions button').prop('disabled', false);
            }
        });
    }
    
    /**
     * Show success message
     */
    function showSuccessMessage(message) {
        var successHtml = `
            <div id="dz-success-message" class="dz-message-overlay">
                <div class="dz-message-content dz-message-success">
                    <div class="dz-message-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h3>Success!</h3>
                    <p>${message}</p>
                    <button class="dz-btn dz-btn-primary" onclick="closeSuccessMessage()">OK</button>
                </div>
            </div>
        `;
        $('body').append(successHtml);
        
        // Auto close after 5 seconds
        setTimeout(function() {
            closeSuccessMessage();
        }, 5000);
    }
    
    /**
     * Show error message
     */
    function showErrorMessage(message) {
        var errorHtml = `
            <div id="dz-error-message" class="dz-message-overlay">
                <div class="dz-message-content dz-message-error">
                    <div class="dz-message-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <h3>Error</h3>
                    <p>${message}</p>
                    <button class="dz-btn dz-btn-primary" onclick="closeErrorMessage()">OK</button>
                </div>
            </div>
        `;
        $('body').append(errorHtml);
    }
    
    /**
     * Close success message
     */
    function closeSuccessMessage() {
        $('#dz-success-message').fadeOut(300, function() {
            $(this).remove();
        });
    }
    
    /**
     * Close error message
     */
    function closeErrorMessage() {
        $('#dz-error-message').fadeOut(300, function() {
            $(this).remove();
        });
    }
