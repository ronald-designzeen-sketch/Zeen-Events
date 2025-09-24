<?php
/**
 * Events Archive Template - Enhanced with Maximum Customization
 * 
 * This template provides maximum customization options for events archive pages.
 * It's compatible with Elementor and includes advanced filtering and search.
 * 
 * @package ZeenEvents
 * @version 2.0.0
 * @copyright 2024 Design Zeen Agency
 * @license GPL v2 or later
 */

get_header(); ?>

<div class="dz-events-archive-wrapper dz-template-customizable">
    <?php
    // Get template customizer instance
    $template_customizer = DZ_Events_Template_Customizer::instance();
    $template_options = $template_customizer->get_template_options('archive');
    ?>
    
    <div class="dz-events-archive-container">
        
        <?php
        // Header Section
        if ($template_options['components']['header']['enabled'] ?? true) {
            echo '<header class="dz-events-archive-header-section">';
            do_action('dz_events_archive_header');
            echo '</header>';
        }
        
        // Main Content Area
        echo '<div class="dz-events-archive-main">';
        
        // Content Section
        if ($template_options['components']['content']['enabled'] ?? true) {
            echo '<div class="dz-events-archive-content-section">';
            do_action('dz_events_archive_content');
            echo '</div>';
        }
        
        // Sidebar Section
        if ($template_options['components']['sidebar']['enabled'] ?? true) {
            echo '<aside class="dz-events-archive-sidebar-section">';
            do_action('dz_events_archive_sidebar');
            echo '</aside>';
        }
        
        echo '</div>'; // End main content area
        
    ?>
    
    </div>
</div>

<?php
// Add custom JavaScript for archive functionality
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Advanced search and filtering
    var $searchForm = $('.dz-events-search-form');
    var $filterForm = $('.dz-events-filter-form');
    var $eventsContainer = $('.dz-events-container');
    var $loadingIndicator = $('.dz-events-loading');
    
    // Search functionality
    $searchForm.on('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    // Real-time search
    $searchForm.find('input[type="search"]').on('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(performSearch, 500);
    });
    
    // Filter functionality
    $filterForm.on('change', 'select, input', function() {
        performSearch();
    });
    
    // View toggle (grid/list)
    $('.dz-view-toggle').on('click', function(e) {
        e.preventDefault();
        var view = $(this).data('view');
        $('.dz-view-toggle').removeClass('active');
        $(this).addClass('active');
        $eventsContainer.attr('data-view', view);
        
        // Save preference
        localStorage.setItem('dz_events_view_preference', view);
    });
    
    // Load saved view preference
    var savedView = localStorage.getItem('dz_events_view_preference');
    if (savedView) {
        $('.dz-view-toggle[data-view="' + savedView + '"]').click();
    }
    
    // Sort functionality
    $('.dz-sort-select').on('change', function() {
        performSearch();
    });
    
    // Pagination
    $(document).on('click', '.dz-pagination a', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        performSearch(page);
    });
    
    // Perform search function
    function performSearch(page = 1) {
        var searchData = {
            action: 'dz_events_archive_search',
            search: $searchForm.find('input[type="search"]').val(),
            category: $filterForm.find('select[name="category"]').val(),
            date_from: $filterForm.find('input[name="date_from"]').val(),
            date_to: $filterForm.find('input[name="date_to"]').val(),
            price_min: $filterForm.find('input[name="price_min"]').val(),
            price_max: $filterForm.find('input[name="price_max"]').val(),
            status: $filterForm.find('select[name="status"]').val(),
            sort: $('.dz-sort-select').val(),
            view: $eventsContainer.attr('data-view') || 'grid',
            page: page,
            nonce: '<?php echo wp_create_nonce('dz_events_archive_search'); ?>'
        };
        
        // Show loading
        $loadingIndicator.show();
        $eventsContainer.addClass('loading');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: searchData,
            success: function(response) {
                if (response.success) {
                    $eventsContainer.html(response.data.html);
                    updateURL(searchData);
                } else {
                    $eventsContainer.html('<div class="dz-no-events"><?php _e('No events found matching your criteria.', 'designzeen-events'); ?></div>');
                }
            },
            error: function() {
                $eventsContainer.html('<div class="dz-error"><?php _e('An error occurred while searching. Please try again.', 'designzeen-events'); ?></div>');
            },
            complete: function() {
                $loadingIndicator.hide();
                $eventsContainer.removeClass('loading');
            }
        });
    }
    
    // Update URL without page reload
    function updateURL(data) {
        var url = new URL(window.location);
        Object.keys(data).forEach(key => {
            if (data[key] && key !== 'action' && key !== 'nonce' && key !== 'page') {
                url.searchParams.set(key, data[key]);
            }
        });
        if (data.page > 1) {
            url.searchParams.set('page', data.page);
        } else {
            url.searchParams.delete('page');
        }
        window.history.pushState({}, '', url);
    }
    
    // Load initial data from URL parameters
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search') || urlParams.has('category') || urlParams.has('date_from') || urlParams.has('date_to')) {
        // Populate form fields from URL
        if (urlParams.has('search')) {
            $searchForm.find('input[type="search"]').val(urlParams.get('search'));
        }
        if (urlParams.has('category')) {
            $filterForm.find('select[name="category"]').val(urlParams.get('category'));
        }
        if (urlParams.has('date_from')) {
            $filterForm.find('input[name="date_from"]').val(urlParams.get('date_from'));
        }
        if (urlParams.has('date_to')) {
            $filterForm.find('input[name="date_to"]').val(urlParams.get('date_to'));
        }
        if (urlParams.has('sort')) {
            $('.dz-sort-select').val(urlParams.get('sort'));
        }
        if (urlParams.has('view')) {
            $('.dz-view-toggle[data-view="' + urlParams.get('view') + '"]').click();
        }
        
        // Perform search
        performSearch();
    }
    
    // Clear filters
    $('.dz-clear-filters').on('click', function(e) {
        e.preventDefault();
        $searchForm[0].reset();
        $filterForm[0].reset();
        $('.dz-sort-select').val('date_asc');
        $('.dz-view-toggle[data-view="grid"]').click();
        
        // Clear URL parameters
        window.history.pushState({}, '', window.location.pathname);
        
        // Reload page to show all events
        location.reload();
    });
    
    // Event card interactions
    $(document).on('click', '.dz-event-card', function(e) {
        if (!$(e.target).closest('a, button').length) {
            var eventUrl = $(this).find('.dz-event-title a').attr('href');
            if (eventUrl) {
                window.location.href = eventUrl;
            }
        }
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        var imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        $('.dz-event-card img[data-src]').each(function() {
            imageObserver.observe(this);
        });
    }
    
    // Infinite scroll (optional)
    if ($('.dz-infinite-scroll').length) {
        var loading = false;
        var page = 1;
        
        $(window).on('scroll', function() {
            if (loading) return;
            
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 1000) {
                loading = true;
                page++;
                
                var searchData = {
                    action: 'dz_events_archive_search',
                    page: page,
                    nonce: '<?php echo wp_create_nonce('dz_events_archive_search'); ?>'
                };
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: searchData,
                    success: function(response) {
                        if (response.success && response.data.html) {
                            $eventsContainer.append(response.data.html);
                        }
                    },
                    complete: function() {
                        loading = false;
                    }
                });
            }
        });
    }
});
</script>

<?php get_footer(); ?>
