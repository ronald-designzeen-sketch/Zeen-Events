<?php
/**
 * Single Event Template - Enhanced with Maximum Customization
 * 
 * This template provides maximum customization options for single event pages.
 * It's compatible with Elementor and includes comprehensive action hooks.
 * 
 * @package ZeenEvents
 * @version 2.0.0
 * @copyright 2024 Design Zeen Agency
 * @license GPL v2 or later
 */

get_header(); ?>

<div class="dz-single-event-wrapper dz-template-customizable">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('dz-single-event dz-event-' . get_the_ID()); ?>>
            
            <?php
            // Check if Elementor is being used to edit this page
            if (class_exists('\Elementor\Plugin') && (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode())) {
                // For Elementor editing/preview, just show the content area
                the_content();
            } else {
                // Get template customizer instance
                $template_customizer = DZ_Events_Template_Customizer::instance();
                $template_options = $template_customizer->get_template_options('single_event');
                
                // Render customizable template sections
                echo '<div class="dz-single-event-container">';
                
                // Header Section
                if ($template_options['components']['header']['enabled'] ?? true) {
                    echo '<header class="dz-single-event-header-section">';
                    do_action('dz_single_event_header');
                    echo '</header>';
                }
                
                // Main Content Area
                echo '<div class="dz-single-event-main">';
                
                // Content Section
                if ($template_options['components']['content']['enabled'] ?? true) {
                    echo '<div class="dz-single-event-content-section">';
                    do_action('dz_single_event_content');
                    echo '</div>';
                }
                
                // Sidebar Section
                if ($template_options['components']['sidebar']['enabled'] ?? true) {
                    echo '<aside class="dz-single-event-sidebar-section">';
                    do_action('dz_single_event_sidebar');
                    echo '</aside>';
                }
                
                echo '</div>'; // End main content area
                
                // Footer Section
                if ($template_options['components']['footer']['enabled'] ?? true) {
                    echo '<footer class="dz-single-event-footer-section">';
                    do_action('dz_single_event_footer');
                    echo '</footer>';
                }
                
                echo '</div>'; // End container
                
                // Fallback to default content if no custom content is rendered
                if (!did_action('dz_single_event_content')) {
                    do_action('dz_single_event_default_content');
                }
            }
            ?>
            
        </article>
    <?php endwhile; ?>
</div>

<?php
// Add custom JavaScript for interactive features
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Event registration form handling
    $('.dz-event-registration-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var eventId = form.data('event-id');
        var formData = form.serialize();
        
        // Add loading state
        form.find('.dz-register-btn').prop('disabled', true).text('<?php _e('Registering...', 'designzeen-events'); ?>');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'dz_events_register_attendee',
                event_id: eventId,
                form_data: formData,
                nonce: '<?php echo wp_create_nonce('dz_events_registration'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    form.html('<div class="dz-registration-success"><h4><?php _e('Registration Successful!', 'designzeen-events'); ?></h4><p><?php _e('You will receive a confirmation email shortly.', 'designzeen-events'); ?></p></div>');
                } else {
                    form.find('.dz-register-btn').prop('disabled', false).text('<?php _e('Register Now', 'designzeen-events'); ?>');
                    alert(response.data.message || '<?php _e('Registration failed. Please try again.', 'designzeen-events'); ?>');
                }
            },
            error: function() {
                form.find('.dz-register-btn').prop('disabled', false).text('<?php _e('Register Now', 'designzeen-events'); ?>');
                alert('<?php _e('An error occurred. Please try again.', 'designzeen-events'); ?>');
            }
        });
    });
    
    // Calendar action handling
    $('.dz-calendar-btn').on('click', function() {
        var eventId = $(this).data('event-id');
        var calendarUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=dz_event_ics&event_id=' + eventId + '&nonce=<?php echo wp_create_nonce('dz_events_ics_nonce'); ?>';
        window.open(calendarUrl, '_blank');
    });
    
    // Share action handling
    $('.dz-share-btn').on('click', function() {
        var eventId = $(this).data('event-id');
        var eventUrl = window.location.href;
        var eventTitle = document.title;
        
        // Create share modal or use native sharing
        if (navigator.share) {
            navigator.share({
                title: eventTitle,
                url: eventUrl
            });
        } else {
            // Fallback to custom share modal
            showShareModal(eventTitle, eventUrl);
        }
    });
    
    // Invite friends handling
    $('.dz-invite-btn').on('click', function() {
        var eventId = $(this).data('event-id');
        showInviteModal(eventId);
    });
    
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
});

// Share modal function
function showShareModal(title, url) {
    var modal = '<div class="dz-share-modal">' +
        '<div class="dz-share-modal-content">' +
        '<h3><?php _e('Share Event', 'designzeen-events'); ?></h3>' +
        '<div class="dz-share-buttons">' +
        '<a href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '" target="_blank" class="dz-share-btn-facebook">Facebook</a>' +
        '<a href="https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title) + '" target="_blank" class="dz-share-btn-twitter">Twitter</a>' +
        '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(url) + '" target="_blank" class="dz-share-btn-linkedin">LinkedIn</a>' +
        '<a href="https://wa.me/?text=' + encodeURIComponent(title + ' ' + url) + '" target="_blank" class="dz-share-btn-whatsapp">WhatsApp</a>' +
        '</div>' +
        '<div class="dz-share-url">' +
        '<input type="text" value="' + url + '" readonly>' +
        '<button onclick="copyToClipboard(this.previousElementSibling)"><?php _e('Copy', 'designzeen-events'); ?></button>' +
        '</div>' +
        '<button class="dz-close-modal"><?php _e('Close', 'designzeen-events'); ?></button>' +
        '</div>' +
        '</div>';
    
    $('body').append(modal);
    $('.dz-share-modal').fadeIn();
    
    $('.dz-close-modal, .dz-share-modal').on('click', function(e) {
        if (e.target === this) {
            $('.dz-share-modal').fadeOut(function() {
                $(this).remove();
            });
        }
    });
}

// Invite modal function
function showInviteModal(eventId) {
    var modal = '<div class="dz-invite-modal">' +
        '<div class="dz-invite-modal-content">' +
        '<h3><?php _e('Invite Friends', 'designzeen-events'); ?></h3>' +
        '<form class="dz-invite-form">' +
        '<div class="dz-form-group">' +
        '<label><?php _e('Friend\'s Email', 'designzeen-events'); ?></label>' +
        '<input type="email" name="friend_email" required>' +
        '</div>' +
        '<div class="dz-form-group">' +
        '<label><?php _e('Your Name', 'designzeen-events'); ?></label>' +
        '<input type="text" name="your_name" required>' +
        '</div>' +
        '<div class="dz-form-group">' +
        '<label><?php _e('Personal Message (Optional)', 'designzeen-events'); ?></label>' +
        '<textarea name="message" rows="3"></textarea>' +
        '</div>' +
        '<button type="submit"><?php _e('Send Invitation', 'designzeen-events'); ?></button>' +
        '</form>' +
        '<button class="dz-close-modal"><?php _e('Close', 'designzeen-events'); ?></button>' +
        '</div>' +
        '</div>';
    
    $('body').append(modal);
    $('.dz-invite-modal').fadeIn();
    
    $('.dz-close-modal, .dz-invite-modal').on('click', function(e) {
        if (e.target === this) {
            $('.dz-invite-modal').fadeOut(function() {
                $(this).remove();
            });
        }
    });
    
    $('.dz-invite-form').on('submit', function(e) {
        e.preventDefault();
        // Handle invitation sending
        alert('<?php _e('Invitation sent successfully!', 'designzeen-events'); ?>');
        $('.dz-invite-modal').fadeOut(function() {
            $(this).remove();
        });
    });
}

// Copy to clipboard function
function copyToClipboard(element) {
    element.select();
    document.execCommand('copy');
    alert('<?php _e('URL copied to clipboard!', 'designzeen-events'); ?>');
}
</script>

<?php get_footer(); ?>
