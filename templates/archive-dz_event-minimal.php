<?php
/**
 * Minimal Events Archive Template
 */

get_header(); ?>

<div class="dz-events-archive-wrapper">
    <header class="dz-archive-header">
        <h1 class="dz-archive-title">Events</h1>
    </header>

    <div class="dz-events-container">
        <?php if (have_posts()) : ?>
            <div class="dz-events-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <div class="dz-event-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="dz-event-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="dz-event-content">
                            <h3 class="dz-event-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <div class="dz-event-meta">
                                <?php
                                $start_date = get_post_meta(get_the_ID(), '_dz_event_start', true);
                                $location = get_post_meta(get_the_ID(), '_dz_event_location', true);
                                $price = get_post_meta(get_the_ID(), '_dz_event_price', true);
                                
                                if ($start_date) {
                                    echo '<div class="dz-meta-item">' . date('M j, Y', strtotime($start_date)) . '</div>';
                                }
                                if ($location) {
                                    echo '<div class="dz-meta-item">' . esc_html($location) . '</div>';
                                }
                                if ($price) {
                                    echo '<div class="dz-meta-item">' . esc_html($price) . '</div>';
                                }
                                ?>
                            </div>
                            
                            <?php if (has_excerpt()) : ?>
                                <div class="dz-event-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="dz-event-actions">
                                <a href="<?php the_permalink(); ?>" class="dz-btn dz-btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php
            // Pagination
            the_posts_pagination(array(
                'prev_text' => __('Previous', 'designzeen-events'),
                'next_text' => __('Next', 'designzeen-events'),
            ));
            ?>
            
        <?php else : ?>
            <div class="dz-no-events">
                <p>No events found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
