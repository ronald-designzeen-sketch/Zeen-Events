<?php
/**
 * Minimal Single Event Template
 */

get_header(); ?>

<div class="dz-single-event-wrapper">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('dz-single-event'); ?>>
            
            <header class="dz-event-header">
                <h1 class="dz-event-title"><?php the_title(); ?></h1>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="dz-event-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="dz-event-content">
                <div class="dz-event-meta">
                    <?php
                    $start_date = get_post_meta(get_the_ID(), '_dz_event_start', true);
                    $end_date = get_post_meta(get_the_ID(), '_dz_event_end', true);
                    $location = get_post_meta(get_the_ID(), '_dz_event_location', true);
                    $price = get_post_meta(get_the_ID(), '_dz_event_price', true);
                    
                    if ($start_date) {
                        echo '<div class="dz-meta-item"><strong>Date:</strong> ' . date('F j, Y', strtotime($start_date)) . '</div>';
                    }
                    if ($end_date && $end_date !== $start_date) {
                        echo '<div class="dz-meta-item"><strong>End Date:</strong> ' . date('F j, Y', strtotime($end_date)) . '</div>';
                    }
                    if ($location) {
                        echo '<div class="dz-meta-item"><strong>Location:</strong> ' . esc_html($location) . '</div>';
                    }
                    if ($price) {
                        echo '<div class="dz-meta-item"><strong>Price:</strong> ' . esc_html($price) . '</div>';
                    }
                    ?>
                </div>

                <div class="dz-event-description">
                    <?php the_content(); ?>
                </div>

                <div class="dz-event-actions">
                    <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=dz_event_ics&event_id=<?php echo get_the_ID(); ?>&nonce=<?php echo wp_create_nonce('dz_events_ics_nonce'); ?>" class="dz-btn dz-btn-primary">
                        Add to Calendar
                    </a>
                </div>
            </div>

        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
