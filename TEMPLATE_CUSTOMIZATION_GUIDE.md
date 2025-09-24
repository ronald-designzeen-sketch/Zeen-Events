# Template Customization Guide for Zeen Events

## 🎯 **Maximum Template Customization**

This guide covers the **complete template customization system** for Zeen Events, providing **unlimited flexibility** for both single event and archive templates.

## 🏗️ **Template Customization System**

### **✅ Dynamic Template Builder**
- **Visual Layout Builder** - Drag and drop template components
- **Real-time Preview** - See changes instantly
- **Component Library** - 20+ customizable components
- **Responsive Design** - Mobile-first approach
- **Custom Styling** - Complete CSS control

### **✅ Template Types**
- **Single Event Template** - Customize individual event pages
- **Archive Template** - Customize events listing pages
- **Custom Layouts** - Create your own layout structures
- **Component Templates** - Reusable component designs

## 🎨 **Single Event Template Components**

### **1. Header Section**
- **Featured Image** - Event hero image with overlay options
- **Event Title** - Customizable title with breadcrumbs
- **Event Badges** - Status, featured, and category badges
- **Quick Meta Info** - Date, time, location, price display

### **2. Content Section**
- **Event Description** - Full content with excerpt options
- **Event Details Table** - Comprehensive event information
- **Custom Fields** - Display custom event fields
- **Event Gallery** - Image gallery with lightbox
- **Event Testimonials** - Attendee testimonials
- **Event Sponsors** - Sponsor logos and information

### **3. Sidebar Section**
- **Registration Form** - Event registration with payment
- **Event Actions** - Calendar, share, invite buttons
- **Related Events** - Similar events suggestions
- **Event Map** - Interactive location map
- **Event Countdown** - Real-time countdown timer
- **Event Weather** - Weather forecast for event date

### **4. Footer Section**
- **Event Gallery** - Additional image gallery
- **Event Testimonials** - More testimonials
- **Event Sponsors** - Sponsor information
- **Social Sharing** - Social media sharing buttons
- **Event Tags** - Event tags and categories

## 🏛️ **Archive Template Components**

### **1. Header Section**
- **Page Title** - Archive page title with breadcrumbs
- **Page Description** - Archive description text
- **Search & Filter** - Advanced search and filtering
- **View Toggle** - Grid/list view switcher

### **2. Content Section**
- **Events Grid** - Customizable events display
- **Pagination** - Advanced pagination options
- **Sort Options** - Multiple sorting criteria
- **Loading States** - Custom loading indicators

### **3. Sidebar Section**
- **Category Filter** - Event category filtering
- **Date Filter** - Date range filtering
- **Featured Events** - Highlighted events
- **Recent Events** - Latest events widget
- **Event Calendar** - Calendar widget

## 🎛️ **Layout Options**

### **✅ Layout Types**
- **Single Column** - Full-width single column
- **Two Column** - Content and sidebar layout
- **Three Column** - Content with two sidebars
- **Grid Layout** - CSS Grid system
- **Custom Layout** - Your own layout structure

### **✅ Responsive Breakpoints**
- **Mobile** - Up to 768px
- **Tablet** - 769px to 1024px
- **Desktop** - 1025px and above
- **Custom Breakpoints** - Define your own

## 🔧 **Component Customization**

### **✅ Component Properties**
- **Enable/Disable** - Turn components on/off
- **Styling Options** - Colors, fonts, spacing
- **Layout Settings** - Position, size, alignment
- **Content Settings** - What to display
- **Behavior Settings** - How components behave

### **✅ Component Types**

#### **Basic Components**
- **Text** - Custom text content
- **Image** - Images with overlay options
- **Button** - Customizable buttons
- **Link** - Styled links
- **Divider** - Visual separators

#### **Event Components**
- **Event Title** - Event name display
- **Event Meta** - Event information
- **Event Description** - Event content
- **Event Gallery** - Image galleries
- **Event Map** - Location maps

#### **Interactive Components**
- **Registration Form** - Event registration
- **Search Form** - Event search
- **Filter Form** - Event filtering
- **Share Buttons** - Social sharing
- **Calendar Button** - Add to calendar

#### **Advanced Components**
- **Countdown Timer** - Event countdown
- **Weather Widget** - Weather forecast
- **Testimonials** - Attendee reviews
- **Related Events** - Event suggestions
- **Event Statistics** - Event analytics

## 🎨 **Styling Options**

### **✅ Global Styling**
- **Color Scheme** - Primary, secondary, accent colors
- **Typography** - Font families, sizes, weights
- **Spacing** - Margins, padding, gaps
- **Borders** - Border styles, colors, radius
- **Shadows** - Box shadows and effects

### **✅ Component Styling**
- **Individual Colors** - Per-component colors
- **Custom CSS** - Complete CSS control
- **Responsive Styles** - Device-specific styling
- **Animation Effects** - Hover and transition effects
- **Custom Classes** - Add your own CSS classes

### **✅ Layout Styling**
- **Container Widths** - Content area widths
- **Column Gaps** - Spacing between columns
- **Section Spacing** - Spacing between sections
- **Background Options** - Colors, images, gradients
- **Overlay Effects** - Image overlays and effects

## 🚀 **Advanced Features**

### **✅ Conditional Display**
- **Show/Hide Rules** - Display components based on conditions
- **User Role Display** - Show different content to different users
- **Event Type Display** - Different layouts for different event types
- **Date-based Display** - Show content based on event dates

### **✅ Dynamic Content**
- **Custom Fields** - Display custom event fields
- **Related Content** - Show related events or content
- **User-specific Content** - Personalized content
- **Real-time Updates** - Live content updates

### **✅ Performance Optimization**
- **Lazy Loading** - Load content as needed
- **Image Optimization** - Optimized image delivery
- **Caching** - Template caching
- **Minification** - CSS/JS minification

## 📱 **Mobile Optimization**

### **✅ Responsive Design**
- **Mobile-first** - Designed for mobile first
- **Touch-friendly** - Large touch targets
- **Fast Loading** - Optimized for mobile
- **Offline Support** - Works offline

### **✅ Mobile-specific Features**
- **Swipe Gestures** - Touch interactions
- **Mobile Navigation** - Mobile-friendly navigation
- **Progressive Web App** - PWA features
- **App-like Experience** - Native app feel

## 🔧 **Customization Examples**

### **Example 1: Conference Event Template**
```php
// Custom conference template
$conference_template = [
    'header' => [
        'featured_image' => ['overlay' => true, 'overlay_opacity' => 0.4],
        'event_title' => ['show_breadcrumbs' => true],
        'event_badges' => ['show_status' => true, 'show_categories' => true],
        'event_meta' => ['layout' => 'horizontal', 'show_all' => true]
    ],
    'content' => [
        'event_description' => ['show_excerpt' => true, 'excerpt_length' => 300],
        'event_details' => ['table_style' => 'modern', 'show_icons' => true],
        'event_schedule' => ['show_sessions' => true, 'show_speakers' => true],
        'event_sponsors' => ['show_logos' => true, 'logo_size' => 'large']
    ],
    'sidebar' => [
        'registration_form' => ['form_style' => 'modern', 'show_pricing' => true],
        'event_actions' => ['show_calendar' => true, 'show_share' => true],
        'speaker_profiles' => ['show_photos' => true, 'show_bios' => true],
        'event_map' => ['map_style' => 'satellite', 'show_markers' => true]
    ]
];
```

### **Example 2: Workshop Event Template**
```php
// Custom workshop template
$workshop_template = [
    'header' => [
        'featured_image' => ['overlay' => false],
        'event_title' => ['show_breadcrumbs' => false],
        'event_badges' => ['show_status' => true, 'show_featured' => false],
        'event_meta' => ['layout' => 'vertical', 'show_date_time' => true]
    ],
    'content' => [
        'event_description' => ['show_full_content' => true],
        'workshop_details' => ['show_materials' => true, 'show_prerequisites' => true],
        'instructor_info' => ['show_photo' => true, 'show_bio' => true],
        'workshop_schedule' => ['show_breaks' => true, 'show_duration' => true]
    ],
    'sidebar' => [
        'registration_form' => ['form_style' => 'simple', 'show_capacity' => true],
        'event_actions' => ['show_calendar' => true, 'show_invite' => false],
        'related_workshops' => ['count' => 3, 'show_images' => true],
        'workshop_materials' => ['show_downloads' => true, 'show_requirements' => true]
    ]
];
```

### **Example 3: Custom Archive Layout**
```php
// Custom archive layout
$archive_layout = [
    'header' => [
        'page_title' => ['show_breadcrumbs' => true],
        'search_filter' => [
            'show_search' => true,
            'show_category_filter' => true,
            'show_date_filter' => true,
            'filter_style' => 'modern'
        ]
    ],
    'content' => [
        'events_grid' => [
            'layout' => 'grid',
            'columns' => 3,
            'show_images' => true,
            'show_meta' => true,
            'card_style' => 'modern'
        ],
        'pagination' => [
            'pagination_style' => 'modern',
            'show_page_numbers' => true
        ]
    ],
    'sidebar' => [
        'category_filter' => ['show_counts' => true],
        'featured_events' => ['count' => 5, 'show_images' => true],
        'event_calendar' => ['show_navigation' => true]
    ]
];
```

## 🎨 **Custom CSS Examples**

### **Modern Event Card Styling**
```css
.dz-event-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.dz-event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.dz-event-card .dz-event-image {
    height: 200px;
    background-size: cover;
    background-position: center;
    position: relative;
}

.dz-event-card .dz-event-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #0073aa;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.dz-event-card .dz-event-content {
    padding: 20px;
}

.dz-event-card .dz-event-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.dz-event-card .dz-event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.dz-event-card .dz-meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #666;
}

.dz-event-card .dz-meta-item i {
    color: #0073aa;
}
```

### **Modern Archive Layout**
```css
.dz-events-archive-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dz-events-archive-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 30px;
    text-align: center;
}

.dz-events-archive-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.dz-events-search-form {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    justify-content: center;
}

.dz-events-search-form input {
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    min-width: 300px;
}

.dz-events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.dz-events-archive-sidebar {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin-top: 30px;
}

.dz-filter-section {
    margin-bottom: 25px;
}

.dz-filter-section h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.dz-filter-section select,
.dz-filter-section input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}
```

## 🔧 **Template Hooks & Filters**

### **✅ Action Hooks**
```php
// Single event hooks
do_action('dz_single_event_header');
do_action('dz_single_event_content');
do_action('dz_single_event_sidebar');
do_action('dz_single_event_footer');

// Archive hooks
do_action('dz_events_archive_header');
do_action('dz_events_archive_content');
do_action('dz_events_archive_sidebar');

// Component hooks
do_action('dz_event_registration_form');
do_action('dz_event_actions');
do_action('dz_event_meta');
```

### **✅ Filter Hooks**
```php
// Template filters
apply_filters('dz_single_event_template', $template);
apply_filters('dz_archive_template', $template);

// Component filters
apply_filters('dz_event_meta_fields', $fields);
apply_filters('dz_event_registration_fields', $fields);
apply_filters('dz_event_actions', $actions);

// Styling filters
apply_filters('dz_event_card_classes', $classes);
apply_filters('dz_event_meta_classes', $classes);
```

## 📊 **Performance Optimization**

### **✅ Template Performance**
- **Lazy Loading** - Load components as needed
- **Caching** - Template caching system
- **Minification** - CSS/JS minification
- **CDN Support** - CDN integration

### **✅ Database Optimization**
- **Query Optimization** - Optimized database queries
- **Transient Caching** - WordPress transient caching
- **Object Caching** - Object caching support
- **Database Indexing** - Custom database indexes

## 🎯 **Best Practices**

### **✅ Template Design**
- **Mobile-first** - Design for mobile first
- **Performance** - Optimize for speed
- **Accessibility** - Ensure accessibility compliance
- **SEO** - Optimize for search engines

### **✅ Component Design**
- **Reusability** - Create reusable components
- **Flexibility** - Make components flexible
- **Consistency** - Maintain design consistency
- **Documentation** - Document your customizations

---

**🎨 Maximum Template Customization, Zero Limitations**

The Zeen Events template system provides **unlimited customization** options:

- **✅ 20+ Components** - Comprehensive component library
- **✅ 4 Layout Types** - Multiple layout options
- **✅ Responsive Design** - Mobile-first approach
- **✅ Custom Styling** - Complete CSS control
- **✅ Conditional Display** - Smart content display
- **✅ Performance Optimized** - Fast loading templates
- **✅ SEO Ready** - Search engine optimized
- **✅ Accessibility Compliant** - WCAG compliant

**© 2024 Design Zeen Agency. All rights reserved.**
