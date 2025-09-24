<?php
/**
 * Form Integration System for Zeen Events
 * 
 * This file implements a comprehensive form integration system
 * that works with all major free WordPress form plugins
 * 
 * @package ZeenEvents
 * @version 2.0.0
 * @copyright 2024 Design Zeen Agency
 * @license GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Form Integration Manager Class
 * 
 * Handles integration with various form plugins and payment processing
 */
class DZ_Events_Form_Integration {
    
    private static $instance = null;
    private $supported_forms = [];
    private $form_templates = [];
    
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
        add_action('init', [$this, 'init_form_integration']);
        add_action('wp_ajax_dz_events_process_form_payment', [$this, 'ajax_process_form_payment']);
        add_action('wp_ajax_nopriv_dz_events_process_form_payment', [$this, 'ajax_process_form_payment']);
        add_action('wp_ajax_dz_events_get_form_template', [$this, 'ajax_get_form_template']);
        add_action('wp_ajax_nopriv_dz_events_get_form_template', [$this, 'ajax_get_form_template']);
    }
    
    /**
     * Initialize form integration
     */
    public function init_form_integration() {
        $this->detect_supported_forms();
        $this->load_form_templates();
        $this->register_form_hooks();
    }
    
    /**
     * Detect supported form plugins
     */
    private function detect_supported_forms() {
        $this->supported_forms = [
            'forminator' => [
                'name' => 'Forminator',
                'active' => class_exists('Forminator'),
                'version' => defined('FORMINATOR_VERSION') ? FORMINATOR_VERSION : null,
                'payment_support' => true,
                'free' => true
            ],
            'contact_form_7' => [
                'name' => 'Contact Form 7',
                'active' => class_exists('WPCF7'),
                'version' => defined('WPCF7_VERSION') ? WPCF7_VERSION : null,
                'payment_support' => false, // Requires extensions
                'free' => true
            ],
            'wpforms' => [
                'name' => 'WPForms',
                'active' => class_exists('WPForms'),
                'version' => defined('WPFORMS_VERSION') ? WPFORMS_VERSION : null,
                'payment_support' => false, // Limited in free version
                'free' => true
            ],
            'gravity_forms' => [
                'name' => 'Gravity Forms',
                'active' => class_exists('GFForms'),
                'version' => defined('GF_MIN_WP_VERSION') ? GF_MIN_WP_VERSION : null,
                'payment_support' => true,
                'free' => false
            ],
            'ninja_forms' => [
                'name' => 'Ninja Forms',
                'active' => class_exists('Ninja_Forms'),
                'version' => defined('NF_PLUGIN_VERSION') ? NF_PLUGIN_VERSION : null,
                'payment_support' => false, // Requires extensions
                'free' => true
            ]
        ];
    }
    
    /**
     * Load form templates
     */
    private function load_form_templates() {
        $this->form_templates = [
            'custom_event_form' => [
                'name' => 'Custom Event Form Builder',
                'description' => 'Fully customizable event form with drag-and-drop builder',
                'type' => 'builder',
                'customizable' => true,
                'sections' => [
                    'personal_info' => [
                        'name' => 'Personal Information',
                        'description' => 'Collect attendee personal details',
                        'fields' => [
                            'first_name' => [
                                'type' => 'text', 
                                'required' => true, 
                                'label' => 'First Name',
                                'placeholder' => 'Enter your first name',
                                'validation' => ['min_length' => 2, 'max_length' => 50],
                                'conditional' => false
                            ],
                            'last_name' => [
                                'type' => 'text', 
                                'required' => true, 
                                'label' => 'Last Name',
                                'placeholder' => 'Enter your last name',
                                'validation' => ['min_length' => 2, 'max_length' => 50],
                                'conditional' => false
                            ],
                            'email' => [
                                'type' => 'email', 
                                'required' => true, 
                                'label' => 'Email Address',
                                'placeholder' => 'your.email@example.com',
                                'validation' => ['email_format' => true],
                                'conditional' => false
                            ],
                            'phone' => [
                                'type' => 'tel', 
                                'required' => true, 
                                'label' => 'Phone Number',
                                'placeholder' => '+27 12 345 6789',
                                'validation' => ['phone_format' => true],
                                'conditional' => false
                            ],
                            'company' => [
                                'type' => 'text', 
                                'required' => false, 
                                'label' => 'Company/Organization',
                                'placeholder' => 'Your company name',
                                'validation' => ['max_length' => 100],
                                'conditional' => ['show_if' => 'registration_type', 'equals' => 'corporate']
                            ],
                            'job_title' => [
                                'type' => 'text', 
                                'required' => false, 
                                'label' => 'Job Title',
                                'placeholder' => 'Your job title',
                                'validation' => ['max_length' => 100],
                                'conditional' => ['show_if' => 'company', 'not_empty' => true]
                            ],
                            'age' => [
                                'type' => 'number', 
                                'required' => false, 
                                'label' => 'Age',
                                'placeholder' => '25',
                                'validation' => ['min' => 18, 'max' => 100],
                                'conditional' => ['show_if' => 'event_type', 'equals' => 'age_restricted']
                            ],
                            'gender' => [
                                'type' => 'select', 
                                'required' => false, 
                                'label' => 'Gender',
                                'options' => ['Male', 'Female', 'Other', 'Prefer not to say'],
                                'conditional' => ['show_if' => 'collect_demographics', 'equals' => 'yes']
                            ],
                            'profile_picture' => [
                                'type' => 'file', 
                                'required' => false, 
                                'label' => 'Profile Picture',
                                'accept' => 'image/*',
                                'max_size' => '2MB',
                                'conditional' => ['show_if' => 'networking_event', 'equals' => 'yes']
                            ]
                        ]
                    ],
                    'event_details' => [
                        'name' => 'Event Details',
                        'description' => 'Event-specific information and preferences',
                        'fields' => [
                            'event_id' => [
                                'type' => 'hidden', 
                                'required' => true, 
                                'label' => 'Event ID',
                                'value' => 'auto_detect'
                            ],
                            'registration_type' => [
                                'type' => 'select', 
                                'required' => true, 
                                'label' => 'Registration Type',
                                'options' => ['Individual', 'Corporate', 'Student', 'Group', 'VIP'],
                                'conditional' => false,
                                'pricing' => true
                            ],
                            'ticket_quantity' => [
                                'type' => 'number', 
                                'required' => true, 
                                'label' => 'Number of Tickets',
                                'min' => 1, 
                                'max' => 20,
                                'conditional' => false,
                                'pricing' => true
                            ],
                            'dietary_requirements' => [
                                'type' => 'checkbox', 
                                'required' => false, 
                                'label' => 'Dietary Requirements',
                                'options' => ['Vegetarian', 'Vegan', 'Gluten-Free', 'Halal', 'Kosher', 'No Nuts', 'Other'],
                                'conditional' => ['show_if' => 'catering_included', 'equals' => 'yes']
                            ],
                            'dietary_other' => [
                                'type' => 'textarea', 
                                'required' => false, 
                                'label' => 'Other Dietary Requirements',
                                'placeholder' => 'Please specify any other dietary requirements',
                                'conditional' => ['show_if' => 'dietary_requirements', 'contains' => 'Other']
                            ],
                            'accessibility_needs' => [
                                'type' => 'textarea', 
                                'required' => false, 
                                'label' => 'Accessibility Needs',
                                'placeholder' => 'Please let us know about any accessibility needs',
                                'conditional' => ['show_if' => 'accessibility_support', 'equals' => 'yes']
                            ],
                            'emergency_contact' => [
                                'type' => 'text', 
                                'required' => false, 
                                'label' => 'Emergency Contact Name',
                                'placeholder' => 'Emergency contact full name',
                                'conditional' => ['show_if' => 'event_duration', 'greater_than' => '4_hours']
                            ],
                            'emergency_phone' => [
                                'type' => 'tel', 
                                'required' => false, 
                                'label' => 'Emergency Contact Phone',
                                'placeholder' => '+27 12 345 6789',
                                'conditional' => ['show_if' => 'emergency_contact', 'not_empty' => true]
                            ],
                            'tshirt_size' => [
                                'type' => 'select', 
                                'required' => false, 
                                'label' => 'T-Shirt Size',
                                'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
                                'conditional' => ['show_if' => 'free_tshirt', 'equals' => 'yes']
                            ],
                            'workshop_preferences' => [
                                'type' => 'checkbox', 
                                'required' => false, 
                                'label' => 'Workshop Preferences',
                                'options' => ['Technology', 'Marketing', 'Finance', 'Leadership', 'Networking', 'Other'],
                                'conditional' => ['show_if' => 'event_type', 'equals' => 'conference']
                            ],
                            'networking_goals' => [
                                'type' => 'textarea', 
                                'required' => false, 
                                'label' => 'Networking Goals',
                                'placeholder' => 'What are your networking goals for this event?',
                                'conditional' => ['show_if' => 'networking_focused', 'equals' => 'yes']
                            ]
                        ]
                    ],
                    'payment_info' => [
                        'name' => 'Payment Information',
                        'description' => 'Payment method and billing details',
                        'fields' => [
                            'payment_method' => [
                                'type' => 'select', 
                                'required' => true, 
                                'label' => 'Payment Method',
                                'options' => $this->get_payment_methods(),
                                'conditional' => false
                            ],
                            'total_amount' => [
                                'type' => 'hidden', 
                                'required' => true, 
                                'label' => 'Total Amount',
                                'value' => 'calculated'
                            ],
                            'currency' => [
                                'type' => 'hidden', 
                                'required' => true, 
                                'label' => 'Currency',
                                'value' => 'ZAR'
                            ],
                            'billing_address' => [
                                'type' => 'textarea', 
                                'required' => false, 
                                'label' => 'Billing Address',
                                'placeholder' => 'Enter your billing address',
                                'conditional' => ['show_if' => 'payment_method', 'not_equals' => 'free']
                            ],
                            'vat_number' => [
                                'type' => 'text', 
                                'required' => false, 
                                'label' => 'VAT Number',
                                'placeholder' => 'VAT number for business registration',
                                'conditional' => ['show_if' => 'registration_type', 'equals' => 'corporate']
                            ],
                            'invoice_email' => [
                                'type' => 'email', 
                                'required' => false, 
                                'label' => 'Invoice Email',
                                'placeholder' => 'invoice@company.com',
                                'conditional' => ['show_if' => 'registration_type', 'equals' => 'corporate']
                            ]
                        ]
                    ],
                    'additional_info' => [
                        'name' => 'Additional Information',
                        'description' => 'Custom fields and additional details',
                        'fields' => [
                            'how_did_you_hear' => [
                                'type' => 'select', 
                                'required' => false, 
                                'label' => 'How did you hear about this event?',
                                'options' => ['Social Media', 'Email', 'Website', 'Friend/Colleague', 'Advertisement', 'Other'],
                                'conditional' => false
                            ],
                            'marketing_consent' => [
                                'type' => 'checkbox', 
                                'required' => false, 
                                'label' => 'Marketing Consent',
                                'options' => ['I agree to receive marketing emails', 'I agree to receive SMS updates'],
                                'conditional' => false
                            ],
                            'photo_consent' => [
                                'type' => 'radio', 
                                'required' => false, 
                                'label' => 'Photo/Video Consent',
                                'options' => ['Yes, I consent', 'No, I do not consent'],
                                'conditional' => ['show_if' => 'photography_allowed', 'equals' => 'yes']
                            ],
                            'custom_field_1' => [
                                'type' => 'text', 
                                'required' => false, 
                                'label' => 'Custom Field 1',
                                'placeholder' => 'Enter custom information',
                                'conditional' => false
                            ],
                            'custom_field_2' => [
                                'type' => 'textarea', 
                                'required' => false, 
                                'label' => 'Custom Field 2',
                                'placeholder' => 'Enter additional custom information',
                                'conditional' => false
                            ]
                        ]
                    ]
                ]
            ],
            'tour_booking' => [
                'name' => 'Tour Package Booking Form',
                'description' => 'Tour package booking with payment',
                'fields' => [
                    'personal_info' => [
                        'first_name' => ['type' => 'text', 'required' => true, 'label' => 'First Name'],
                        'last_name' => ['type' => 'text', 'required' => true, 'label' => 'Last Name'],
                        'email' => ['type' => 'email', 'required' => true, 'label' => 'Email Address'],
                        'phone' => ['type' => 'tel', 'required' => true, 'label' => 'Phone Number'],
                        'nationality' => ['type' => 'text', 'required' => true, 'label' => 'Nationality'],
                        'passport_number' => ['type' => 'text', 'required' => false, 'label' => 'Passport Number']
                    ],
                    'tour_details' => [
                        'tour_id' => ['type' => 'hidden', 'required' => true, 'label' => 'Tour ID'],
                        'tour_date' => ['type' => 'date', 'required' => true, 'label' => 'Preferred Tour Date'],
                        'participants' => ['type' => 'number', 'required' => true, 'label' => 'Number of Participants', 'min' => 1, 'max' => 20],
                        'accommodation' => ['type' => 'select', 'required' => true, 'label' => 'Accommodation Type', 'options' => ['Standard', 'Deluxe', 'Luxury']],
                        'transportation' => ['type' => 'select', 'required' => true, 'label' => 'Transportation', 'options' => ['Included', 'Self-arranged']],
                        'special_requests' => ['type' => 'textarea', 'required' => false, 'label' => 'Special Requests']
                    ],
                    'payment_info' => [
                        'payment_method' => ['type' => 'select', 'required' => true, 'label' => 'Payment Method', 'options' => $this->get_payment_methods()],
                        'total_amount' => ['type' => 'hidden', 'required' => true, 'label' => 'Total Amount'],
                        'currency' => ['type' => 'hidden', 'required' => true, 'label' => 'Currency', 'default' => 'ZAR']
                    ]
                ]
            ],
            'workshop_registration' => [
                'name' => 'Workshop Registration Form',
                'description' => 'Workshop registration with payment',
                'fields' => [
                    'personal_info' => [
                        'first_name' => ['type' => 'text', 'required' => true, 'label' => 'First Name'],
                        'last_name' => ['type' => 'text', 'required' => true, 'label' => 'Last Name'],
                        'email' => ['type' => 'email', 'required' => true, 'label' => 'Email Address'],
                        'phone' => ['type' => 'tel', 'required' => true, 'label' => 'Phone Number'],
                        'profession' => ['type' => 'text', 'required' => false, 'label' => 'Profession'],
                        'experience_level' => ['type' => 'select', 'required' => true, 'label' => 'Experience Level', 'options' => ['Beginner', 'Intermediate', 'Advanced']]
                    ],
                    'workshop_details' => [
                        'workshop_id' => ['type' => 'hidden', 'required' => true, 'label' => 'Workshop ID'],
                        'workshop_date' => ['type' => 'date', 'required' => true, 'label' => 'Workshop Date'],
                        'dietary_requirements' => ['type' => 'textarea', 'required' => false, 'label' => 'Dietary Requirements'],
                        'learning_objectives' => ['type' => 'textarea', 'required' => false, 'label' => 'Learning Objectives'],
                        'previous_workshops' => ['type' => 'textarea', 'required' => false, 'label' => 'Previous Workshop Experience']
                    ],
                    'payment_info' => [
                        'payment_method' => ['type' => 'select', 'required' => true, 'label' => 'Payment Method', 'options' => $this->get_payment_methods()],
                        'total_amount' => ['type' => 'hidden', 'required' => true, 'label' => 'Total Amount'],
                        'currency' => ['type' => 'hidden', 'required' => true, 'label' => 'Currency', 'default' => 'ZAR']
                    ]
                ]
            ],
            'conference_registration' => [
                'name' => 'Conference Registration Form',
                'description' => 'Conference registration with payment',
                'fields' => [
                    'personal_info' => [
                        'first_name' => ['type' => 'text', 'required' => true, 'label' => 'First Name'],
                        'last_name' => ['type' => 'text', 'required' => true, 'label' => 'Last Name'],
                        'email' => ['type' => 'email', 'required' => true, 'label' => 'Email Address'],
                        'phone' => ['type' => 'tel', 'required' => true, 'label' => 'Phone Number'],
                        'company' => ['type' => 'text', 'required' => true, 'label' => 'Company/Organization'],
                        'job_title' => ['type' => 'text', 'required' => false, 'label' => 'Job Title'],
                        'industry' => ['type' => 'select', 'required' => false, 'label' => 'Industry', 'options' => ['Technology', 'Healthcare', 'Finance', 'Education', 'Other']]
                    ],
                    'conference_details' => [
                        'conference_id' => ['type' => 'hidden', 'required' => true, 'label' => 'Conference ID'],
                        'registration_type' => ['type' => 'select', 'required' => true, 'label' => 'Registration Type', 'options' => ['Full Conference', 'Day Pass', 'Student', 'Speaker']],
                        'sessions' => ['type' => 'checkbox', 'required' => false, 'label' => 'Sessions of Interest', 'options' => ['Keynote', 'Workshops', 'Networking', 'Panel Discussions']],
                        'dietary_requirements' => ['type' => 'textarea', 'required' => false, 'label' => 'Dietary Requirements'],
                        'accessibility_needs' => ['type' => 'textarea', 'required' => false, 'label' => 'Accessibility Needs'],
                        'networking_goals' => ['type' => 'textarea', 'required' => false, 'label' => 'Networking Goals']
                    ],
                    'payment_info' => [
                        'payment_method' => ['type' => 'select', 'required' => true, 'label' => 'Payment Method', 'options' => $this->get_payment_methods()],
                        'total_amount' => ['type' => 'hidden', 'required' => true, 'label' => 'Total Amount'],
                        'currency' => ['type' => 'hidden', 'required' => true, 'label' => 'Currency', 'default' => 'ZAR']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get available payment methods
     */
    private function get_payment_methods() {
        $payment_gateways = DZ_Events_Payment_Gateways::instance()->get_available_gateways();
        $methods = [];
        
        foreach ($payment_gateways as $gateway) {
            $methods[$gateway['id']] = $gateway['name'];
        }
        
        return $methods;
    }
    
    /**
     * Get comprehensive field library
     */
    public function get_field_library() {
        return [
            'basic_fields' => [
                'text' => [
                    'name' => 'Text Input',
                    'description' => 'Single line text input',
                    'icon' => 'fa-font',
                    'properties' => [
                        'label', 'placeholder', 'required', 'validation', 'conditional', 'help_text'
                    ]
                ],
                'textarea' => [
                    'name' => 'Textarea',
                    'description' => 'Multi-line text input',
                    'icon' => 'fa-align-left',
                    'properties' => [
                        'label', 'placeholder', 'required', 'rows', 'validation', 'conditional', 'help_text'
                    ]
                ],
                'email' => [
                    'name' => 'Email',
                    'description' => 'Email address input with validation',
                    'icon' => 'fa-envelope',
                    'properties' => [
                        'label', 'placeholder', 'required', 'validation', 'conditional', 'help_text'
                    ]
                ],
                'tel' => [
                    'name' => 'Phone',
                    'description' => 'Phone number input with validation',
                    'icon' => 'fa-phone',
                    'properties' => [
                        'label', 'placeholder', 'required', 'validation', 'conditional', 'help_text'
                    ]
                ],
                'number' => [
                    'name' => 'Number',
                    'description' => 'Numeric input with min/max validation',
                    'icon' => 'fa-hashtag',
                    'properties' => [
                        'label', 'placeholder', 'required', 'min', 'max', 'step', 'validation', 'conditional', 'help_text'
                    ]
                ],
                'url' => [
                    'name' => 'URL',
                    'description' => 'URL input with validation',
                    'icon' => 'fa-link',
                    'properties' => [
                        'label', 'placeholder', 'required', 'validation', 'conditional', 'help_text'
                    ]
                ],
                'password' => [
                    'name' => 'Password',
                    'description' => 'Password input with strength validation',
                    'icon' => 'fa-lock',
                    'properties' => [
                        'label', 'placeholder', 'required', 'validation', 'conditional', 'help_text'
                    ]
                ]
            ],
            'choice_fields' => [
                'select' => [
                    'name' => 'Dropdown',
                    'description' => 'Single selection dropdown',
                    'icon' => 'fa-list',
                    'properties' => [
                        'label', 'options', 'required', 'conditional', 'help_text', 'searchable'
                    ]
                ],
                'radio' => [
                    'name' => 'Radio Buttons',
                    'description' => 'Single selection radio buttons',
                    'icon' => 'fa-dot-circle-o',
                    'properties' => [
                        'label', 'options', 'required', 'conditional', 'help_text', 'layout'
                    ]
                ],
                'checkbox' => [
                    'name' => 'Checkboxes',
                    'description' => 'Multiple selection checkboxes',
                    'icon' => 'fa-check-square-o',
                    'properties' => [
                        'label', 'options', 'required', 'conditional', 'help_text', 'layout'
                    ]
                ],
                'multiselect' => [
                    'name' => 'Multi-Select',
                    'description' => 'Multiple selection dropdown',
                    'icon' => 'fa-list-alt',
                    'properties' => [
                        'label', 'options', 'required', 'conditional', 'help_text', 'searchable'
                    ]
                ]
            ],
            'date_time_fields' => [
                'date' => [
                    'name' => 'Date Picker',
                    'description' => 'Date selection with calendar',
                    'icon' => 'fa-calendar',
                    'properties' => [
                        'label', 'required', 'min_date', 'max_date', 'conditional', 'help_text'
                    ]
                ],
                'time' => [
                    'name' => 'Time Picker',
                    'description' => 'Time selection',
                    'icon' => 'fa-clock-o',
                    'properties' => [
                        'label', 'required', 'format', 'conditional', 'help_text'
                    ]
                ],
                'datetime' => [
                    'name' => 'Date & Time',
                    'description' => 'Date and time selection',
                    'icon' => 'fa-calendar-plus-o',
                    'properties' => [
                        'label', 'required', 'min_date', 'max_date', 'format', 'conditional', 'help_text'
                    ]
                ]
            ],
            'file_fields' => [
                'file' => [
                    'name' => 'File Upload',
                    'description' => 'File upload with validation',
                    'icon' => 'fa-upload',
                    'properties' => [
                        'label', 'required', 'accept', 'max_size', 'multiple', 'conditional', 'help_text'
                    ]
                ],
                'image' => [
                    'name' => 'Image Upload',
                    'description' => 'Image upload with preview',
                    'icon' => 'fa-image',
                    'properties' => [
                        'label', 'required', 'max_size', 'dimensions', 'conditional', 'help_text'
                    ]
                ]
            ],
            'advanced_fields' => [
                'hidden' => [
                    'name' => 'Hidden Field',
                    'description' => 'Hidden field for storing data',
                    'icon' => 'fa-eye-slash',
                    'properties' => [
                        'value', 'conditional'
                    ]
                ],
                'html' => [
                    'name' => 'HTML Content',
                    'description' => 'Custom HTML content',
                    'icon' => 'fa-code',
                    'properties' => [
                        'content', 'conditional'
                    ]
                ],
                'divider' => [
                    'name' => 'Divider',
                    'description' => 'Visual divider between sections',
                    'icon' => 'fa-minus',
                    'properties' => [
                        'style', 'conditional'
                    ]
                ],
                'rating' => [
                    'name' => 'Rating',
                    'description' => 'Star rating input',
                    'icon' => 'fa-star',
                    'properties' => [
                        'label', 'required', 'max_rating', 'conditional', 'help_text'
                    ]
                ],
                'slider' => [
                    'name' => 'Range Slider',
                    'description' => 'Numeric range slider',
                    'icon' => 'fa-sliders',
                    'properties' => [
                        'label', 'required', 'min', 'max', 'step', 'conditional', 'help_text'
                    ]
                ],
                'color' => [
                    'name' => 'Color Picker',
                    'description' => 'Color selection input',
                    'icon' => 'fa-paint-brush',
                    'properties' => [
                        'label', 'required', 'default_color', 'conditional', 'help_text'
                    ]
                ]
            ],
            'conditional_fields' => [
                'conditional_text' => [
                    'name' => 'Conditional Text',
                    'description' => 'Text that shows based on conditions',
                    'icon' => 'fa-eye',
                    'properties' => [
                        'content', 'conditions', 'conditional'
                    ]
                ],
                'conditional_section' => [
                    'name' => 'Conditional Section',
                    'description' => 'Section that shows based on conditions',
                    'icon' => 'fa-folder',
                    'properties' => [
                        'title', 'fields', 'conditions', 'conditional'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get conditional logic operators
     */
    public function get_conditional_operators() {
        return [
            'equals' => [
                'name' => 'Equals',
                'description' => 'Field value equals specified value',
                'icon' => 'fa-equals'
            ],
            'not_equals' => [
                'name' => 'Not Equals',
                'description' => 'Field value does not equal specified value',
                'icon' => 'fa-not-equal'
            ],
            'contains' => [
                'name' => 'Contains',
                'description' => 'Field value contains specified text',
                'icon' => 'fa-search'
            ],
            'not_contains' => [
                'name' => 'Not Contains',
                'description' => 'Field value does not contain specified text',
                'icon' => 'fa-search-minus'
            ],
            'greater_than' => [
                'name' => 'Greater Than',
                'description' => 'Field value is greater than specified value',
                'icon' => 'fa-greater-than'
            ],
            'less_than' => [
                'name' => 'Less Than',
                'description' => 'Field value is less than specified value',
                'icon' => 'fa-less-than'
            ],
            'is_empty' => [
                'name' => 'Is Empty',
                'description' => 'Field value is empty',
                'icon' => 'fa-ban'
            ],
            'not_empty' => [
                'name' => 'Not Empty',
                'description' => 'Field value is not empty',
                'icon' => 'fa-check'
            ],
            'is_checked' => [
                'name' => 'Is Checked',
                'description' => 'Checkbox/radio is checked',
                'icon' => 'fa-check-square'
            ],
            'not_checked' => [
                'name' => 'Not Checked',
                'description' => 'Checkbox/radio is not checked',
                'icon' => 'fa-square-o'
            ]
        ];
    }
    
    /**
     * Get validation rules
     */
    public function get_validation_rules() {
        return [
            'required' => [
                'name' => 'Required',
                'description' => 'Field must be filled',
                'type' => 'boolean'
            ],
            'min_length' => [
                'name' => 'Minimum Length',
                'description' => 'Minimum character length',
                'type' => 'number'
            ],
            'max_length' => [
                'name' => 'Maximum Length',
                'description' => 'Maximum character length',
                'type' => 'number'
            ],
            'email_format' => [
                'name' => 'Email Format',
                'description' => 'Valid email format',
                'type' => 'boolean'
            ],
            'phone_format' => [
                'name' => 'Phone Format',
                'description' => 'Valid phone number format',
                'type' => 'boolean'
            ],
            'url_format' => [
                'name' => 'URL Format',
                'description' => 'Valid URL format',
                'type' => 'boolean'
            ],
            'numeric' => [
                'name' => 'Numeric',
                'description' => 'Must be a number',
                'type' => 'boolean'
            ],
            'min' => [
                'name' => 'Minimum Value',
                'description' => 'Minimum numeric value',
                'type' => 'number'
            ],
            'max' => [
                'name' => 'Maximum Value',
                'description' => 'Maximum numeric value',
                'type' => 'number'
            ],
            'pattern' => [
                'name' => 'Custom Pattern',
                'description' => 'Custom regex pattern',
                'type' => 'text'
            ],
            'file_size' => [
                'name' => 'File Size',
                'description' => 'Maximum file size in MB',
                'type' => 'number'
            ],
            'file_type' => [
                'name' => 'File Type',
                'description' => 'Allowed file types',
                'type' => 'text'
            ]
        ];
    }
    
    /**
     * Register form hooks
     */
    private function register_form_hooks() {
        // Forminator hooks
        if (class_exists('Forminator')) {
            add_action('forminator_custom_form_submit_before_set_fields', [$this, 'handle_forminator_submission'], 10, 3);
        }
        
        // Contact Form 7 hooks
        if (class_exists('WPCF7')) {
            add_action('wpcf7_before_send_mail', [$this, 'handle_cf7_submission']);
        }
        
        // WPForms hooks
        if (class_exists('WPForms')) {
            add_action('wpforms_process_complete', [$this, 'handle_wpforms_submission'], 10, 4);
        }
        
        // Gravity Forms hooks
        if (class_exists('GFForms')) {
            add_action('gform_after_submission', [$this, 'handle_gravity_forms_submission'], 10, 2);
        }
    }
    
    /**
     * Handle Forminator form submission
     */
    public function handle_forminator_submission($entry, $form_id, $form_data) {
        if ($this->is_event_form($form_id)) {
            $this->process_form_submission('forminator', $entry, $form_data);
        }
    }
    
    /**
     * Handle Contact Form 7 submission
     */
    public function handle_cf7_submission($contact_form) {
        $submission = WPCF7_Submission::get_instance();
        if ($submission) {
            $form_id = $contact_form->id();
            if ($this->is_event_form($form_id)) {
                $this->process_form_submission('contact_form_7', $submission->get_posted_data(), $form_id);
            }
        }
    }
    
    /**
     * Handle WPForms submission
     */
    public function handle_wpforms_submission($fields, $entry, $form_data, $entry_id) {
        $form_id = $form_data['id'];
        if ($this->is_event_form($form_id)) {
            $this->process_form_submission('wpforms', $fields, $form_id);
        }
    }
    
    /**
     * Handle Gravity Forms submission
     */
    public function handle_gravity_forms_submission($entry, $form) {
        $form_id = $form['id'];
        if ($this->is_event_form($form_id)) {
            $this->process_form_submission('gravity_forms', $entry, $form_id);
        }
    }
    
    /**
     * Check if form is an event form
     */
    private function is_event_form($form_id) {
        $event_forms = get_option('dz_events_forms', []);
        return in_array($form_id, $event_forms);
    }
    
    /**
     * Process form submission
     */
    private function process_form_submission($form_type, $data, $form_id) {
        // Extract event data
        $event_data = $this->extract_event_data($data);
        
        if (!$event_data) {
            return;
        }
        
        // Create registration
        $registration_id = $this->create_registration($event_data);
        
        if (!$registration_id) {
            return;
        }
        
        // Process payment if required
        if ($event_data['total_amount'] > 0) {
            $this->process_payment($registration_id, $event_data);
        } else {
            // Free event - confirm registration
            $this->confirm_registration($registration_id);
        }
        
        // Send confirmation email
        $this->send_confirmation_email($registration_id, $event_data);
    }
    
    /**
     * Extract event data from form submission
     */
    private function extract_event_data($data) {
        $event_data = [
            'event_id' => $data['event_id'] ?? null,
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'total_amount' => floatval($data['total_amount'] ?? 0),
            'currency' => $data['currency'] ?? 'ZAR',
            'payment_method' => $data['payment_method'] ?? '',
            'form_data' => $data
        ];
        
        return $event_data;
    }
    
    /**
     * Create registration record
     */
    private function create_registration($event_data) {
        global $wpdb;
        
        $registration_data = [
            'event_id' => $event_data['event_id'],
            'first_name' => $event_data['first_name'],
            'last_name' => $event_data['last_name'],
            'email' => $event_data['email'],
            'phone' => $event_data['phone'],
            'status' => 'pending',
            'payment_status' => $event_data['total_amount'] > 0 ? 'pending' : 'free',
            'total_amount' => $event_data['total_amount'],
            'currency' => $event_data['currency'],
            'form_data' => json_encode($event_data['form_data']),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'dz_event_registrations',
            $registration_data,
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s']
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Process payment
     */
    private function process_payment($registration_id, $event_data) {
        $payment_gateways = DZ_Events_Payment_Gateways::instance();
        
        try {
            $payment_result = $payment_gateways->process_payment(
                $event_data['payment_method'],
                $event_data['total_amount'],
                $event_data['currency'],
                [
                    'registration_id' => $registration_id,
                    'customer_email' => $event_data['email'],
                    'customer_name' => $event_data['first_name'] . ' ' . $event_data['last_name'],
                    'return_url' => home_url('/event-registration-success/'),
                    'cancel_url' => home_url('/event-registration-cancel/')
                ]
            );
            
            // Update registration with payment info
            $this->update_registration_payment($registration_id, $payment_result);
            
            return $payment_result;
            
        } catch (Exception $e) {
            error_log('DZ Events Payment Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update registration with payment info
     */
    private function update_registration_payment($registration_id, $payment_result) {
        global $wpdb;
        
        $update_data = [
            'payment_id' => $payment_result['payment_id'],
            'payment_status' => $payment_result['status'],
            'payment_method' => $payment_result['gateway']
        ];
        
        if ($payment_result['status'] === 'paid') {
            $update_data['status'] = 'confirmed';
        }
        
        $wpdb->update(
            $wpdb->prefix . 'dz_event_registrations',
            $update_data,
            ['id' => $registration_id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Confirm free registration
     */
    private function confirm_registration($registration_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'dz_event_registrations',
            ['status' => 'confirmed'],
            ['id' => $registration_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Send confirmation email
     */
    private function send_confirmation_email($registration_id, $event_data) {
        $to = $event_data['email'];
        $subject = 'Event Registration Confirmation';
        $message = $this->get_confirmation_email_template($registration_id, $event_data);
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get confirmation email template
     */
    private function get_confirmation_email_template($registration_id, $event_data) {
        $event = get_post($event_data['event_id']);
        $event_title = $event ? $event->post_title : 'Event';
        
        $template = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Event Registration Confirmed</h1>
                </div>
                <div class='content'>
                    <p>Dear {$event_data['first_name']} {$event_data['last_name']},</p>
                    <p>Thank you for registering for <strong>{$event_title}</strong>!</p>
                    <p><strong>Registration Details:</strong></p>
                    <ul>
                        <li>Registration ID: #{$registration_id}</li>
                        <li>Event: {$event_title}</li>
                        <li>Email: {$event_data['email']}</li>
                        <li>Phone: {$event_data['phone']}</li>
                        <li>Total Amount: {$event_data['currency']} {$event_data['total_amount']}</li>
                    </ul>
                    <p>We look forward to seeing you at the event!</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $template;
    }
    
    /**
     * AJAX get form template
     */
    public function ajax_get_form_template() {
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        
        if (!isset($this->form_templates[$template_id])) {
            wp_send_json_error('Template not found');
        }
        
        $template = $this->form_templates[$template_id];
        
        wp_send_json_success([
            'template' => $template,
            'form_code' => $this->generate_form_code($template_id, $template)
        ]);
    }
    
    /**
     * Generate form code for different form plugins
     */
    private function generate_form_code($template_id, $template) {
        $form_codes = [];
        
        // Forminator code
        if (class_exists('Forminator')) {
            $form_codes['forminator'] = $this->generate_forminator_code($template_id, $template);
        }
        
        // Contact Form 7 code
        if (class_exists('WPCF7')) {
            $form_codes['contact_form_7'] = $this->generate_cf7_code($template_id, $template);
        }
        
        // WPForms code
        if (class_exists('WPForms')) {
            $form_codes['wpforms'] = $this->generate_wpforms_code($template_id, $template);
        }
        
        return $form_codes;
    }
    
    /**
     * Generate Forminator form code
     */
    private function generate_forminator_code($template_id, $template) {
        $code = "<!-- Forminator Form Code for {$template['name']} -->\n";
        $code .= "[forminator_form id=\"\"]\n\n";
        $code .= "<!-- Form Fields:\n";
        
        foreach ($template['fields'] as $section => $fields) {
            $code .= "Section: {$section}\n";
            foreach ($fields as $field_id => $field_config) {
                $code .= "- {$field_config['label']} ({$field_config['type']})";
                if ($field_config['required']) {
                    $code .= " [Required]";
                }
                $code .= "\n";
            }
        }
        
        $code .= "-->\n";
        
        return $code;
    }
    
    /**
     * Generate Contact Form 7 code
     */
    private function generate_cf7_code($template_id, $template) {
        $code = "<!-- Contact Form 7 Code for {$template['name']} -->\n";
        $code .= "[contact-form-7 id=\"\" title=\"\"]\n\n";
        $code .= "<!-- Form Fields:\n";
        
        foreach ($template['fields'] as $section => $fields) {
            $code .= "Section: {$section}\n";
            foreach ($fields as $field_id => $field_config) {
                $code .= "- {$field_config['label']} ({$field_config['type']})";
                if ($field_config['required']) {
                    $code .= " [Required]";
                }
                $code .= "\n";
            }
        }
        
        $code .= "-->\n";
        
        return $code;
    }
    
    /**
     * Generate WPForms code
     */
    private function generate_wpforms_code($template_id, $template) {
        $code = "<!-- WPForms Code for {$template['name']} -->\n";
        $code .= "[wpforms id=\"\"]\n\n";
        $code .= "<!-- Form Fields:\n";
        
        foreach ($template['fields'] as $section => $fields) {
            $code .= "Section: {$section}\n";
            foreach ($fields as $field_id => $field_config) {
                $code .= "- {$field_config['label']} ({$field_config['type']})";
                if ($field_config['required']) {
                    $code .= " [Required]";
                }
                $code .= "\n";
            }
        }
        
        $code .= "-->\n";
        
        return $code;
    }
    
    /**
     * Get supported forms
     */
    public function get_supported_forms() {
        return $this->supported_forms;
    }
    
    /**
     * Get form templates
     */
    public function get_form_templates() {
        return $this->form_templates;
    }
    
    /**
     * Get recommended form plugin
     */
    public function get_recommended_form_plugin() {
        // Check for Forminator first (best free option)
        if (class_exists('Forminator')) {
            return 'forminator';
        }
        
        // Check for Contact Form 7
        if (class_exists('WPCF7')) {
            return 'contact_form_7';
        }
        
        // Check for WPForms
        if (class_exists('WPForms')) {
            return 'wpforms';
        }
        
        return 'forminator'; // Recommend Forminator
    }
}

/**
 * Initialize form integration
 */
function dz_events_init_form_integration() {
    return DZ_Events_Form_Integration::instance();
}
add_action('init', 'dz_events_init_form_integration');
