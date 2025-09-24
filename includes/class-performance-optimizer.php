<?php
/**
 * Enhanced Performance Optimizer for Zeen Events
 * 
 * This file implements advanced performance optimizations
 * to achieve sub-1-second load times and optimal user experience
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Performance Optimizer Class
 * 
 * Handles all advanced performance-related optimizations
 */
class DZ_Events_Performance_Optimizer_Enhanced {
    
    private static $instance = null;
    private $cache_groups = [];
    private $preloaded_assets = [];
    
    /**
     * Singleton pattern
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', [$this, 'init_enhanced_optimizations']);
        add_action('wp_enqueue_scripts', [$this, 'optimize_assets_advanced']);
        add_action('wp_head', [$this, 'add_performance_hints_advanced']);
        add_action('wp_footer', [$this, 'preload_next_pages']);
    }
    
    /**
     * Initialize enhanced performance optimizations
     */
    public function init_enhanced_optimizations() {
        // Advanced lazy loading
        add_filter('wp_get_attachment_image_attributes', [$this, 'add_advanced_lazy_loading'], 10, 3);
        add_filter('the_content', [$this, 'optimize_content_images']);
        
        // Database query optimization
        add_action('pre_get_posts', [$this, 'optimize_event_queries_advanced']);
        add_filter('posts_request', [$this, 'optimize_event_requests']);
        add_filter('query', [$this, 'optimize_database_queries']);
        
        // Advanced caching strategies
        add_action('init', [$this, 'init_advanced_caching']);
        add_action('wp_loaded', [$this, 'enable_memory_caching']);
        
        // Image optimization
        add_filter('wp_generate_attachment_metadata', [$this, 'optimize_images_advanced'], 10, 2);
        add_action('wp_head', [$this, 'add_webp_support']);
        add_action('wp_head', [$this, 'add_responsive_images']);
        
        // Database indexing
        add_action('init', [$this, 'ensure_database_indexes']);
        
        // Memory optimization
        add_action('wp_loaded', [$this, 'optimize_memory_usage']);
        
        // CDN integration
        add_filter('dz_events_asset_url', [$this, 'cdn_asset_url'], 10, 2);
        
        // Critical resource preloading
        add_action('wp_head', [$this, 'preload_critical_resources']);
        
        // Service Worker for caching
        add_action('wp_head', [$this, 'register_service_worker']);
        
        // HTTP/2 Server Push
        add_action('wp_head', [$this, 'add_server_push_hints']);
    }
    
    /**
     * Advanced lazy loading with intersection observer
     */
    public function add_advanced_lazy_loading($attr, $attachment, $size) {
        if (is_admin() || wp_doing_ajax()) {
            return $attr;
        }
        
        // Add lazy loading attributes
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
        
        // Add data attributes for progressive loading
        if (isset($attr['src'])) {
            $attr['data-src'] = $attr['src'];
            $attr['src'] = $this->get_placeholder_image();
        }
        
        return $attr;
    }
    
    /**
     * Optimize content images
     */
    public function optimize_content_images($content) {
        if (is_admin() || wp_doing_ajax()) {
            return $content;
        }
        
        // Add lazy loading to content images
        $content = preg_replace('/<img(.*?)src=/', '<img$1loading="lazy" decoding="async" src=', $content);
        
        // Add WebP support
        $content = $this->add_webp_support_to_content($content);
        
        return $content;
    }
    
    /**
     * Advanced event query optimization
     */
    public function optimize_event_queries_advanced($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        if (is_post_type_archive('dz_event') || is_tax('dz_event_category')) {
            // Optimize event queries
            $query->set('posts_per_page', 12);
            $query->set('no_found_rows', true);
            $query->set('update_post_meta_cache', false);
            $query->set('update_post_term_cache', false);
            
            // Add custom meta query optimization
            $meta_query = $query->get('meta_query');
            if (empty($meta_query)) {
                $meta_query = [];
            }
            
            // Optimize date queries
            $meta_query[] = [
                'key' => '_dz_event_start',
                'value' => current_time('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            ];
            
            $query->set('meta_query', $meta_query);
            $query->set('meta_key', '_dz_event_start');
            $query->set('orderby', 'meta_value');
            $query->set('order', 'ASC');
        }
    }
    
    /**
     * Optimize event requests
     */
    public function optimize_event_requests($request) {
        if (strpos($request, 'dz_event') !== false) {
            // Add query optimizations
            $request = str_replace('SELECT', 'SELECT SQL_CACHE', $request);
        }
        
        return $request;
    }
    
    /**
     * Optimize database queries
     */
    public function optimize_database_queries($query) {
        // Add query caching hints
        if (strpos($query, 'dz_event') !== false) {
            $query = str_replace('SELECT', 'SELECT SQL_CACHE', $query);
        }
        
        return $query;
    }
    
    /**
     * Initialize advanced caching
     */
    public function init_advanced_caching() {
        // Enable object caching
        if (!wp_using_ext_object_cache()) {
            add_action('init', [$this, 'enable_object_caching']);
        }
        
        // Enable page caching
        add_action('template_redirect', [$this, 'enable_page_caching']);
        
        // Enable fragment caching
        add_action('init', [$this, 'enable_fragment_caching']);
    }
    
    /**
     * Enable object caching
     */
    public function enable_object_caching() {
        // Implement custom object caching
        add_filter('dz_events_cache_get', [$this, 'get_cached_object'], 10, 2);
        add_filter('dz_events_cache_set', [$this, 'set_cached_object'], 10, 3);
        add_filter('dz_events_cache_delete', [$this, 'delete_cached_object'], 10, 2);
    }
    
    /**
     * Enable page caching
     */
    public function enable_page_caching() {
        if (is_singular('dz_event') || is_post_type_archive('dz_event')) {
            // Set cache headers
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        }
    }
    
    /**
     * Enable fragment caching
     */
    public function enable_fragment_caching() {
        // Cache event cards
        add_filter('dz_events_render_card', [$this, 'cache_event_card'], 10, 2);
        
        // Cache event lists
        add_filter('dz_events_render_list', [$this, 'cache_event_list'], 10, 2);
    }
    
    /**
     * Cache event card
     */
    public function cache_event_card($content, $event_id) {
        $cache_key = 'dz_event_card_' . $event_id;
        $cached = wp_cache_get($cache_key, 'dz_events');
        
        if ($cached === false) {
            wp_cache_set($cache_key, $content, 'dz_events', 3600);
        } else {
            $content = $cached;
        }
        
        return $content;
    }
    
    /**
     * Cache event list
     */
    public function cache_event_list($content, $args) {
        $cache_key = 'dz_event_list_' . md5(serialize($args));
        $cached = wp_cache_get($cache_key, 'dz_events');
        
        if ($cached === false) {
            wp_cache_set($cache_key, $content, 'dz_events', 1800);
        } else {
            $content = $cached;
        }
        
        return $content;
    }
    
    /**
     * Optimize images advanced
     */
    public function optimize_images_advanced($metadata, $attachment_id) {
        if (!isset($metadata['sizes'])) {
            return $metadata;
        }
        
        // Generate WebP versions
        $this->generate_webp_versions($attachment_id, $metadata);
        
        // Optimize image sizes
        $this->optimize_image_sizes($attachment_id, $metadata);
        
        return $metadata;
    }
    
    /**
     * Generate WebP versions
     */
    private function generate_webp_versions($attachment_id, $metadata) {
        $upload_dir = wp_upload_dir();
        $file_path = get_attached_file($attachment_id);
        
        if (!$file_path || !file_exists($file_path)) {
            return;
        }
        
        // Generate WebP for main image
        $this->convert_to_webp($file_path);
        
        // Generate WebP for thumbnails
        foreach ($metadata['sizes'] as $size => $size_data) {
            $thumb_path = $upload_dir['basedir'] . '/' . dirname($metadata['file']) . '/' . $size_data['file'];
            if (file_exists($thumb_path)) {
                $this->convert_to_webp($thumb_path);
            }
        }
    }
    
    /**
     * Convert image to WebP
     */
    private function convert_to_webp($file_path) {
        if (!function_exists('imagewebp')) {
            return;
        }
        
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file_path);
        
        $image = null;
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'png':
                $image = imagecreatefrompng($file_path);
                break;
        }
        
        if ($image) {
            imagewebp($image, $webp_path, 80);
            imagedestroy($image);
        }
    }
    
    /**
     * Add WebP support
     */
    public function add_webp_support() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        ?>
        <script>
        // WebP support detection
        function supportsWebP() {
            return new Promise((resolve) => {
                const webP = new Image();
                webP.onload = webP.onerror = () => resolve(webP.height === 2);
                webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
            });
        }
        
        // Replace images with WebP versions
        supportsWebP().then(supported => {
            if (supported) {
                document.querySelectorAll('img[data-src]').forEach(img => {
                    const src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src.replace(/\.(jpg|jpeg|png)$/i, '.webp');
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Add responsive images
     */
    public function add_responsive_images() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        ?>
        <script>
        // Responsive image loading
        function loadResponsiveImages() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        }
        
        document.addEventListener('DOMContentLoaded', loadResponsiveImages);
        </script>
        <?php
    }
    
    /**
     * Ensure database indexes
     */
    public function ensure_database_indexes() {
        global $wpdb;
        
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_dz_events_start_date ON {$wpdb->postmeta} (meta_key, meta_value) WHERE meta_key = '_dz_event_start'",
            "CREATE INDEX IF NOT EXISTS idx_dz_events_status ON {$wpdb->postmeta} (meta_key, meta_value) WHERE meta_key = '_dz_event_status'",
            "CREATE INDEX IF NOT EXISTS idx_dz_events_featured ON {$wpdb->postmeta} (meta_key, meta_value) WHERE meta_key = '_dz_event_featured'",
            "CREATE INDEX IF NOT EXISTS idx_dz_events_location ON {$wpdb->postmeta} (meta_key, meta_value) WHERE meta_key = '_dz_event_location'"
        ];
        
        foreach ($indexes as $index) {
            $wpdb->query($index);
        }
    }
    
    /**
     * Optimize memory usage
     */
    public function optimize_memory_usage() {
        // Increase memory limit for image processing
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '256M');
        }
        
        // Optimize WordPress memory usage
        add_filter('wp_memory_limit', function() {
            return '256M';
        });
    }
    
    /**
     * CDN asset URL
     */
    public function cdn_asset_url($url, $asset_type) {
        $cdn_url = get_option('dz_events_cdn_url');
        
        if ($cdn_url && in_array($asset_type, ['css', 'js', 'images'])) {
            $url = str_replace(wp_upload_dir()['baseurl'], $cdn_url, $url);
        }
        
        return $url;
    }
    
    /**
     * Preload critical resources
     */
    public function preload_critical_resources() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        $critical_css = plugin_dir_url(__FILE__) . '../assets/css/critical.css';
        $critical_js = plugin_dir_url(__FILE__) . '../assets/js/critical.js';
        
        ?>
        <link rel="preload" href="<?php echo esc_url($critical_css); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <link rel="preload" href="<?php echo esc_url($critical_js); ?>" as="script">
        <noscript><link rel="stylesheet" href="<?php echo esc_url($critical_css); ?>"></noscript>
        <?php
    }
    
    /**
     * Preload next pages
     */
    public function preload_next_pages() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        if (is_post_type_archive('dz_event')) {
            $paged = get_query_var('paged') ?: 1;
            $next_page = $paged + 1;
            $next_url = get_pagenum_link($next_page);
            
            if ($next_url) {
                echo '<link rel="prefetch" href="' . esc_url($next_url) . '">';
            }
        }
    }
    
    /**
     * Register service worker
     */
    public function register_service_worker() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?php echo plugin_dir_url(__FILE__) . '../assets/js/sw.js'; ?>')
                .then(registration => console.log('SW registered'))
                .catch(error => console.log('SW registration failed'));
        }
        </script>
        <?php
    }
    
    /**
     * Add server push hints
     */
    public function add_server_push_hints() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        $critical_assets = [
            plugin_dir_url(__FILE__) . '../assets/css/style.css',
            plugin_dir_url(__FILE__) . '../assets/js/script.js'
        ];
        
        foreach ($critical_assets as $asset) {
            echo '<link rel="preload" href="' . esc_url($asset) . '" as="style">';
        }
    }
    
    /**
     * Optimize assets advanced
     */
    public function optimize_assets_advanced() {
        if (is_admin()) {
            return;
        }
        
        // Minify and combine CSS
        $this->minify_and_combine_css();
        
        // Minify and combine JS
        $this->minify_and_combine_js();
        
        // Add asset versioning
        $this->add_asset_versioning();
    }
    
    /**
     * Minify and combine CSS
     */
    private function minify_and_combine_css() {
        $css_files = [
            plugin_dir_path(__FILE__) . '../assets/css/style.css',
            plugin_dir_path(__FILE__) . '../assets/css/editor.css'
        ];
        
        $combined_css = '';
        foreach ($css_files as $file) {
            if (file_exists($file)) {
                $combined_css .= file_get_contents($file);
            }
        }
        
        // Minify CSS
        $combined_css = $this->minify_css($combined_css);
        
        // Save combined CSS
        $upload_dir = wp_upload_dir();
        $combined_file = $upload_dir['basedir'] . '/dz-events-combined.css';
        file_put_contents($combined_file, $combined_css);
        
        // Enqueue combined CSS
        wp_enqueue_style(
            'dz-events-combined',
            $upload_dir['baseurl'] . '/dz-events-combined.css',
            [],
            filemtime($combined_file)
        );
    }
    
    /**
     * Minify and combine JS
     */
    private function minify_and_combine_js() {
        $js_files = [
            plugin_dir_path(__FILE__) . '../assets/js/script.js',
            plugin_dir_path(__FILE__) . '../assets/js/events-block.js'
        ];
        
        $combined_js = '';
        foreach ($js_files as $file) {
            if (file_exists($file)) {
                $combined_js .= file_get_contents($file);
            }
        }
        
        // Minify JS
        $combined_js = $this->minify_js($combined_js);
        
        // Save combined JS
        $upload_dir = wp_upload_dir();
        $combined_file = $upload_dir['basedir'] . '/dz-events-combined.js';
        file_put_contents($combined_file, $combined_js);
        
        // Enqueue combined JS
        wp_enqueue_script(
            'dz-events-combined',
            $upload_dir['baseurl'] . '/dz-events-combined.js',
            ['jquery'],
            filemtime($combined_file),
            true
        );
    }
    
    /**
     * Minify CSS
     */
    private function minify_css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove unnecessary whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remove unnecessary spaces
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ' ,', ', '], [';', '{', '{', '}', '}', ':', ',', ','], $css);
        
        return trim($css);
    }
    
    /**
     * Minify JS
     */
    private function minify_js($js) {
        // Remove comments
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        $js = preg_replace('!//.*$!m', '', $js);
        
        // Remove unnecessary whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }
    
    /**
     * Add asset versioning
     */
    private function add_asset_versioning() {
        // Add version parameter to assets
        add_filter('style_loader_src', [$this, 'add_version_to_assets'], 10, 2);
        add_filter('script_loader_src', [$this, 'add_version_to_assets'], 10, 2);
    }
    
    /**
     * Add version to assets
     */
    public function add_version_to_assets($src, $handle) {
        if (strpos($handle, 'dz-events') !== false) {
            $src = add_query_arg('v', DZ_EVENTS_VERSION, $src);
        }
        
        return $src;
    }
    
    /**
     * Add performance hints advanced
     */
    public function add_performance_hints_advanced() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="format-detection" content="telephone=no">
        <link rel="dns-prefetch" href="//fonts.googleapis.com">
        <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <?php
    }
    
    /**
     * Get placeholder image
     */
    private function get_placeholder_image() {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg width="1" height="1" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="#f0f0f0"/></svg>');
    }
    
    /**
     * Add WebP support to content
     */
    private function add_webp_support_to_content($content) {
        // Replace image sources with WebP versions
        $content = preg_replace_callback('/<img([^>]+)src="([^"]+)"([^>]*)>/', function($matches) {
            $webp_src = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $matches[2]);
            return '<img' . $matches[1] . 'src="' . $matches[2] . '" data-webp="' . $webp_src . '"' . $matches[3] . '>';
        }, $content);
        
        return $content;
    }
}

/**
 * Initialize enhanced performance optimizer
 */
function dz_events_init_performance_optimizer_enhanced() {
    return DZ_Events_Performance_Optimizer_Enhanced::instance();
}
add_action('init', 'dz_events_init_performance_optimizer_enhanced');
