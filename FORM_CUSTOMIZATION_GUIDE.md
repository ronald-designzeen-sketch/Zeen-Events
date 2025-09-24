# Form Customization Guide for Zeen Events

## 🎯 **Maximum Customization Approach**

This guide focuses on **maximum customization** rather than rigid pre-built templates. The Zeen Events plugin provides a **comprehensive form builder** that allows you to create **completely custom forms** tailored to your specific needs.

## 🏗️ **Dynamic Form Builder Features**

### **✅ Drag & Drop Interface**
- **Visual Form Builder** - Drag fields from library to form
- **Real-time Preview** - See changes instantly
- **Responsive Design** - Forms work on all devices
- **Custom Styling** - Complete control over appearance

### **✅ Comprehensive Field Library**
- **25+ Field Types** - From basic text to advanced fields
- **Custom Field Types** - Create your own field types
- **Field Validation** - Advanced validation rules
- **Conditional Logic** - Dynamic form behavior

### **✅ Advanced Conditional Logic**
- **Show/Hide Fields** - Based on user input
- **Dynamic Options** - Change dropdown options
- **Multi-condition Logic** - Complex conditional rules
- **Real-time Updates** - Instant form changes

## 🎨 **Field Library Categories**

### **1. Basic Fields**
- **Text Input** - Single line text with validation
- **Textarea** - Multi-line text input
- **Email** - Email with format validation
- **Phone** - Phone number with format validation
- **Number** - Numeric input with min/max
- **URL** - URL input with validation
- **Password** - Password with strength validation

### **2. Choice Fields**
- **Dropdown** - Single selection dropdown
- **Radio Buttons** - Single selection radio buttons
- **Checkboxes** - Multiple selection checkboxes
- **Multi-Select** - Multiple selection dropdown

### **3. Date & Time Fields**
- **Date Picker** - Calendar date selection
- **Time Picker** - Time selection
- **Date & Time** - Combined date and time

### **4. File Fields**
- **File Upload** - File upload with validation
- **Image Upload** - Image upload with preview

### **5. Advanced Fields**
- **Hidden Field** - Store hidden data
- **HTML Content** - Custom HTML content
- **Divider** - Visual section dividers
- **Rating** - Star rating input
- **Range Slider** - Numeric range slider
- **Color Picker** - Color selection

### **6. Conditional Fields**
- **Conditional Text** - Text that shows based on conditions
- **Conditional Section** - Section that shows based on conditions

## 🔧 **Conditional Logic System**

### **✅ Conditional Operators**
- **Equals** - Field value equals specified value
- **Not Equals** - Field value does not equal specified value
- **Contains** - Field value contains specified text
- **Not Contains** - Field value does not contain specified text
- **Greater Than** - Field value is greater than specified value
- **Less Than** - Field value is less than specified value
- **Is Empty** - Field value is empty
- **Not Empty** - Field value is not empty
- **Is Checked** - Checkbox/radio is checked
- **Not Checked** - Checkbox/radio is not checked

### **✅ Conditional Examples**

#### **Example 1: Corporate Registration**
```javascript
// Show company field only if registration type is "Corporate"
{
  "field": "company",
  "condition": {
    "show_if": "registration_type",
    "operator": "equals",
    "value": "Corporate"
  }
}
```

#### **Example 2: Dietary Requirements**
```javascript
// Show dietary requirements only if catering is included
{
  "field": "dietary_requirements",
  "condition": {
    "show_if": "catering_included",
    "operator": "equals",
    "value": "yes"
  }
}
```

#### **Example 3: Emergency Contact**
```javascript
// Show emergency contact for events longer than 4 hours
{
  "field": "emergency_contact",
  "condition": {
    "show_if": "event_duration",
    "operator": "greater_than",
    "value": "4_hours"
  }
}
```

## 🎨 **Customization Options**

### **✅ Field Customization**
- **Labels** - Custom field labels
- **Placeholders** - Custom placeholder text
- **Help Text** - Additional field instructions
- **Validation** - Custom validation rules
- **Styling** - Custom CSS classes
- **Icons** - Font Awesome icons

### **✅ Form Styling**
- **Color Scheme** - Custom colors
- **Typography** - Custom fonts
- **Layout** - Custom layouts
- **Spacing** - Custom margins and padding
- **Borders** - Custom borders and shadows
- **Animations** - Custom animations

### **✅ Advanced Customization**
- **Custom CSS** - Complete styling control
- **JavaScript** - Custom functionality
- **Hooks & Filters** - WordPress integration
- **API Integration** - External service integration
- **Custom Validation** - Custom validation rules
- **Custom Fields** - Create your own field types

## 🚀 **Form Builder Interface**

### **✅ Visual Builder**
- **Drag & Drop** - Drag fields from library
- **Real-time Preview** - See changes instantly
- **Responsive Preview** - Test on different devices
- **Live Validation** - Test validation rules

### **✅ Field Properties Panel**
- **Basic Settings** - Label, placeholder, required
- **Validation Rules** - Min/max length, format validation
- **Conditional Logic** - Show/hide conditions
- **Styling Options** - Colors, fonts, spacing
- **Advanced Settings** - Custom attributes, CSS classes

### **✅ Form Settings**
- **Form Title** - Custom form title
- **Form Description** - Form instructions
- **Success Message** - Custom success message
- **Error Messages** - Custom error messages
- **Email Settings** - Notification settings
- **Payment Settings** - Payment gateway configuration

## 📱 **Responsive Design**

### **✅ Mobile-First Approach**
- **Touch-Friendly** - Large touch targets
- **Mobile Layouts** - Optimized for mobile
- **Progressive Enhancement** - Works on all devices
- **Accessibility** - WCAG compliant

### **✅ Device-Specific Settings**
- **Desktop** - Full feature set
- **Tablet** - Optimized layout
- **Mobile** - Simplified interface
- **Custom Breakpoints** - Define your own breakpoints

## 🔒 **Security & Validation**

### **✅ Client-Side Validation**
- **Real-time Validation** - Instant feedback
- **Custom Validation** - Custom validation rules
- **Error Messages** - Custom error messages
- **Visual Feedback** - Clear error indicators

### **✅ Server-Side Validation**
- **Security Validation** - Prevent malicious input
- **Data Sanitization** - Clean user input
- **CSRF Protection** - Cross-site request forgery protection
- **Rate Limiting** - Prevent spam submissions

## 🎯 **Customization Examples**

### **Example 1: Conference Registration Form**
```javascript
{
  "form_title": "Tech Conference 2024 Registration",
  "sections": [
    {
      "name": "Personal Information",
      "fields": [
        {
          "type": "text",
          "name": "first_name",
          "label": "First Name",
          "required": true,
          "validation": {
            "min_length": 2,
            "max_length": 50
          }
        },
        {
          "type": "select",
          "name": "registration_type",
          "label": "Registration Type",
          "options": ["Individual", "Corporate", "Student", "Speaker"],
          "required": true,
          "pricing": true
        }
      ]
    },
    {
      "name": "Event Preferences",
      "fields": [
        {
          "type": "checkbox",
          "name": "workshop_preferences",
          "label": "Workshop Preferences",
          "options": ["AI/ML", "Web Development", "Mobile Apps", "DevOps"],
          "conditional": {
            "show_if": "registration_type",
            "operator": "not_equals",
            "value": "Speaker"
          }
        }
      ]
    }
  ]
}
```

### **Example 2: Tour Booking Form**
```javascript
{
  "form_title": "Safari Tour Booking",
  "sections": [
    {
      "name": "Tour Details",
      "fields": [
        {
          "type": "select",
          "name": "tour_package",
          "label": "Tour Package",
          "options": ["3-Day Safari", "5-Day Safari", "7-Day Safari", "Custom Tour"],
          "required": true,
          "pricing": true
        },
        {
          "type": "date",
          "name": "preferred_date",
          "label": "Preferred Start Date",
          "required": true,
          "validation": {
            "min_date": "today",
            "max_date": "+1_year"
          }
        },
        {
          "type": "number",
          "name": "participants",
          "label": "Number of Participants",
          "required": true,
          "validation": {
            "min": 1,
            "max": 20
          },
          "pricing": true
        }
      ]
    }
  ]
}
```

## 🛠️ **Advanced Customization**

### **✅ Custom Field Types**
```php
// Create custom field type
add_filter('dz_events_custom_field_types', function($field_types) {
    $field_types['custom_rating'] = [
        'name' => 'Custom Rating',
        'description' => 'Custom rating field',
        'render' => 'render_custom_rating_field',
        'validate' => 'validate_custom_rating_field'
    ];
    return $field_types;
});
```

### **✅ Custom Validation Rules**
```php
// Add custom validation rule
add_filter('dz_events_validation_rules', function($rules) {
    $rules['south_african_id'] = [
        'name' => 'South African ID',
        'validate' => 'validate_sa_id',
        'message' => 'Please enter a valid South African ID number'
    ];
    return $rules;
});
```

### **✅ Custom Styling**
```css
/* Custom form styling */
.dz-events-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.dz-events-form .form-field {
    margin-bottom: 25px;
}

.dz-events-form .form-field label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.dz-events-form .form-field input,
.dz-events-form .form-field select,
.dz-events-form .form-field textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.dz-events-form .form-field input:focus,
.dz-events-form .form-field select:focus,
.dz-events-form .form-field textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}
```

## 📊 **Form Analytics**

### **✅ Submission Analytics**
- **Form Views** - Track form impressions
- **Conversion Rates** - Track completion rates
- **Field Analytics** - Track field interactions
- **Error Analytics** - Track validation errors

### **✅ Performance Metrics**
- **Load Times** - Form loading performance
- **User Experience** - User interaction metrics
- **Mobile Performance** - Mobile-specific metrics
- **A/B Testing** - Test different form versions

## 🎯 **Best Practices**

### **✅ Form Design**
- **Keep it Simple** - Don't overwhelm users
- **Logical Flow** - Group related fields
- **Clear Labels** - Use descriptive labels
- **Help Text** - Provide helpful instructions
- **Visual Hierarchy** - Use proper spacing and typography

### **✅ User Experience**
- **Progress Indicators** - Show form progress
- **Auto-save** - Save progress automatically
- **Error Prevention** - Prevent common errors
- **Mobile Optimization** - Optimize for mobile
- **Accessibility** - Ensure accessibility compliance

### **✅ Performance**
- **Lazy Loading** - Load fields as needed
- **Minimal JavaScript** - Keep JS minimal
- **Optimized Images** - Optimize file uploads
- **Caching** - Cache form configurations
- **CDN** - Use CDN for assets

---

**🎨 Maximum Customization, Zero Limitations**

The Zeen Events form builder provides **unlimited customization** options:

- **✅ 25+ Field Types** - From basic to advanced
- **✅ Advanced Conditional Logic** - Dynamic form behavior
- **✅ Custom Styling** - Complete design control
- **✅ Custom Validation** - Custom validation rules
- **✅ Responsive Design** - Works on all devices
- **✅ Security Features** - Enterprise-grade security
- **✅ Analytics Integration** - Track form performance
- **✅ API Integration** - Connect to external services

**© 2024 Design Zeen Agency. All rights reserved.**
