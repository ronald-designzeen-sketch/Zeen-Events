# Zeen Events WordPress Plugin

**Version 2.0.0** - Professional event management plugin with multiple layouts, advanced filtering, and Elementor integration.

A comprehensive WordPress plugin for managing and displaying events with multiple layout options, advanced filtering, modern design, and powerful Elementor integration.

## 🚀 **Repository Status: LIVE**

**🔗 GitHub Repository:** https://github.com/ronald-designzeen-sketch/Zeen-Events  
**📄 License:** GPL v2 or later  
**© Copyright:** 2024 Design Zeen Agency

## ✨ Features

### 🎯 Core Features
- **Custom Post Type**: Dedicated event management system with comprehensive admin interface
- **Event Categories**: Hierarchical taxonomy for organizing events
- **Multiple Layouts**: Grid, List, and Carousel display options
- **Gutenberg Block**: Easy-to-use block editor integration
- **Shortcode Support**: Flexible shortcode for any page or post
- **Elementor Integration**: Full Elementor compatibility with 4 widgets
- **Single Event Templates**: Professional single event page layouts
- **Social Media Sharing**: Built-in social sharing functionality
- **Action Buttons**: Add to calendar, invite friends, register, and navigation buttons
- **Responsive Design**: Mobile-first, fully responsive layouts
- **Calendar Integration**: Save to Google, Outlook, Yahoo, Apple Calendar, and iCal download
- **Invite Friends**: Email invitation system with event details
- **Advanced Admin UI**: Professional admin interface with custom columns and quick edit

### 🎨 Event Management
- **Event Details**: Start/end dates, times, price, location, capacity, contact info
- **Event Status**: Automatic status updates (upcoming, ongoing, completed, cancelled, sold out)
- **Featured Events**: Mark important events as featured
- **Contact Information**: Add contact details for each event
- **External Links**: Link to ticket sales or event websites
- **Rich Media**: Featured images with hover effects
- **Custom Fields**: Add unlimited custom fields with icons
- **Event Categories**: Hierarchical categorization system
- **Quick Edit**: Inline editing of event details in admin

### 🔧 Advanced Features
- **Smart Filtering**: Filter by category, status, date range, price, location
- **Flexible Ordering**: Sort by date, title, price, capacity, or custom criteria
- **Admin Dashboard**: Comprehensive settings and statistics
- **SEO Ready**: Meta tags and structured data
- **Export/Import**: Backup and restore event data
- **Debug Tools**: Built-in debugging and troubleshooting

### 📅 Calendar & Sharing Features
- **Multi-Platform Calendar**: Google, Outlook, Yahoo, Apple Calendar support
- **iCal Download**: Download events as .ics files
- **Bulk Calendar Export**: Export multiple events at once
- **Social Sharing**: Facebook, Twitter, LinkedIn, WhatsApp, Email, Telegram, Reddit, Pinterest
- **Invite Friends**: Email invitation system with event preview
- **Custom Calendar URLs**: Support for custom calendar services

### 🚀 Elementor Widgets
- **Events List**: Display events in various layouts
- **Event Details**: Show individual event information
- **Event Actions**: Calendar, share, and registration buttons
- **Social Share**: Share events on social media platforms

## 🚀 Installation

1. Download the plugin from the GitHub repository
2. Upload the plugin files to `/wp-content/plugins/zeen-events/`
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Go to **Events** in the admin menu to start creating events
5. Configure settings at **Settings > Zeen Events**

## 📖 Usage

### 📝 Basic Usage
1. **Create Events**: Add events with comprehensive details
2. **Configure Settings**: Set up display options and layouts
3. **Use Shortcodes**: Display events anywhere with `[zeen_events]`
4. **Elementor Integration**: Use widgets for advanced layouts

### 🎛️ Elementor Integration

The plugin includes **4 powerful Elementor widgets**:

#### 1. 📋 Events List Widget
- Display events in any Elementor page
- **Layout Options**: Grid, List, Carousel
- **Filtering**: By category, status, date range
- **Sorting**: By date, title, price, capacity
- **Responsive Controls**: Different settings for desktop, tablet, mobile
- **Custom Styling**: Colors, typography, spacing, borders, shadows

#### 2. 📱 Event Social Share Widget
- Share events on 8 social media platforms
- **Platforms**: Facebook, Twitter, LinkedIn, WhatsApp, Email, Telegram, Reddit, Pinterest
- **Complete Styling Control**: Button width, height, padding, margin, icon size, spacing, alignment
- **Individual Platform Colors**: Customize each platform's appearance
- **Typography Controls**: Font family, size, weight, style

#### 3. ⚡ Event Actions Widget
- **Calendar Actions**: Add to Google, Outlook, Yahoo, Apple Calendar, iCal download
- **Invite Friends**: Email invitation system with event preview
- **Registration**: Direct registration links and buttons
- **Navigation**: Previous/Next event navigation
- **Custom Styling**: Complete control over button appearance

#### 4. 📊 Event Details Table Widget
- Display event information in organized table format
- **Customizable Fields**: Show/hide any event field
- **Icon Support**: Font Awesome and Bootstrap Icons
- **Responsive Design**: Mobile-optimized table layouts
- **Custom Styling**: Colors, typography, spacing, borders

### 📝 Shortcodes

#### Basic Shortcode
```
[zeen_events]
```

#### Advanced Shortcode with Options
```
[zeen_events layout="grid" posts_per_page="6" category="conferences" orderby="date" order="ASC"]
```

**Available Parameters:**
- `layout`: grid, list, carousel
- `posts_per_page`: Number of events to display (default: 6)
- `category`: Event category slug
- `orderby`: date, title, price, capacity
- `order`: ASC, DESC
- `show_filters`: true, false
- `show_pagination`: true, false

### 🎨 Styling

The plugin includes comprehensive CSS classes for easy customization:

```css
.dz-events-wrapper          /* Main container */
.dz-events-grid             /* Grid layout */
.dz-events-list             /* List layout */
.dz-events-carousel         /* Carousel layout */
.dz-event-card              /* Individual event card */
.dz-event-thumb             /* Event thumbnail */
.dz-event-content           /* Event content area */
.dz-event-meta              /* Event metadata */
.dz-event-actions           /* Action buttons */
.dz-event-badge             /* Status/featured badges */
```

## 📋 Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## 🌐 Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Design Zeen Agency

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

---

**Made with ❤️ by Design Zeen Agency**  
© 2024 Design Zeen Agency. All rights reserved.

*Transform your events with the most powerful and flexible WordPress events plugin available.*