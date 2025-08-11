<?php
/**
 * Admin Interface Class
 * 
 * Manages the WordPress admin interface for UQUAL Financial Calculators
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Admin_Interface {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('UQUAL Calculators', 'uqual-calculators'),
            __('UQUAL Calculators', 'uqual-calculators'),
            'manage_options',
            'uqual-calculators',
            array($this, 'dashboard_page'),
            'dashicons-calculator',
            30
        );
        
        // Dashboard (same as main page)
        add_submenu_page(
            'uqual-calculators',
            __('Dashboard', 'uqual-calculators'),
            __('Dashboard', 'uqual-calculators'),
            'manage_options',
            'uqual-calculators',
            array($this, 'dashboard_page')
        );
        
        // Settings page
        add_submenu_page(
            'uqual-calculators',
            __('Settings', 'uqual-calculators'),
            __('Settings', 'uqual-calculators'),
            'manage_options',
            'uqual-calculators-settings',
            array($this, 'settings_page')
        );
        
        // Help page
        add_submenu_page(
            'uqual-calculators',
            __('Help & Documentation', 'uqual-calculators'),
            __('Help', 'uqual-calculators'),
            'manage_options',
            'uqual-calculators-help',
            array($this, 'help_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General settings section
        add_settings_section(
            'uqual_calc_general',
            __('General Settings', 'uqual-calculators'),
            array($this, 'general_section_callback'),
            'uqual-calculators-settings'
        );
        
        // Register settings
        $settings = array(
            'uqual_calc_enable_analytics' => 'boolean',
            'uqual_calc_default_cta_text' => 'string',
            'uqual_calc_cta_url' => 'url',
            'uqual_calc_primary_color' => 'string',
            'uqual_calc_accent_color' => 'string'
        );
        
        foreach ($settings as $setting => $type) {
            register_setting('uqual_calculators_settings', $setting, array(
                'sanitize_callback' => array($this, 'sanitize_setting'),
                'default' => $this->get_default_value($setting)
            ));
            
            $callback = array($this, $this->get_field_callback($type));
            $label = $this->get_field_label($setting);
            $section = 'uqual_calc_general';
            
            add_settings_field(
                $setting,
                $label,
                $callback,
                'uqual-calculators-settings',
                $section,
                array('setting' => $setting, 'type' => $type)
            );
        }
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        // Simple analytics data for basic functionality
        $analytics = array(
            'summary' => array(
                'total_sessions' => 0,
                'total_completions' => 0,
                'completion_rate' => 0,
                'most_popular_calculator' => 'loan_readiness'
            ),
            'calculator_performance' => array(),
            'daily_data' => array()
        );
        
        if (file_exists(UQUAL_CALC_PLUGIN_DIR . 'admin/views/dashboard.php')) {
            include UQUAL_CALC_PLUGIN_DIR . 'admin/views/dashboard.php';
        } else {
            echo '<div class="wrap"><h1>UQUAL Calculators Dashboard</h1><p>Dashboard view file missing.</p></div>';
        }
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (file_exists(UQUAL_CALC_PLUGIN_DIR . 'admin/views/settings.php')) {
            include UQUAL_CALC_PLUGIN_DIR . 'admin/views/settings.php';
        } else {
            echo '<div class="wrap"><h1>UQUAL Calculators Settings</h1><p>Settings view file missing.</p></div>';
        }
    }
    
    /**
     * Help page
     */
    public function help_page() {
        if (file_exists(UQUAL_CALC_PLUGIN_DIR . 'admin/views/help.php')) {
            include UQUAL_CALC_PLUGIN_DIR . 'admin/views/help.php';
        } else {
            echo '<div class="wrap"><h1>UQUAL Calculators Help</h1><p>Help view file missing.</p></div>';
        }
    }
    
    /**
     * Settings section callbacks
     */
    public function general_section_callback() {
        echo '<p>' . __('Configure general plugin settings.', 'uqual-calculators') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function text_field_callback($args) {
        $setting = $args['setting'];
        $value = get_option($setting, '');
        
        // Add color picker class for color fields
        $class = 'regular-text';
        if (strpos($setting, 'color') !== false) {
            $class .= ' color-picker';
            $value = $value ?: '#2E7D32'; // Default color if empty
        }
        
        echo '<input type="text" id="' . esc_attr($setting) . '" name="' . esc_attr($setting) . '" value="' . esc_attr($value) . '" class="' . esc_attr($class) . '" />';
        echo '<p class="description">' . $this->get_field_description($setting) . '</p>';
    }
    
    public function url_field_callback($args) {
        $setting = $args['setting'];
        $value = get_option($setting, '');
        echo '<input type="url" id="' . esc_attr($setting) . '" name="' . esc_attr($setting) . '" value="' . esc_url($value) . '" class="regular-text" />';
        echo '<p class="description">' . $this->get_field_description($setting) . '</p>';
    }
    
    public function checkbox_field_callback($args) {
        $setting = $args['setting'];
        $value = get_option($setting, 'yes');
        echo '<input type="checkbox" id="' . esc_attr($setting) . '" name="' . esc_attr($setting) . '" value="yes" ' . checked('yes', $value, false) . ' />';
        echo '<label for="' . esc_attr($setting) . '">' . $this->get_field_description($setting) . '</label>';
    }
    
    /**
     * Helper methods
     */
    private function get_field_callback($type) {
        $callbacks = array(
            'string' => 'text_field_callback',
            'url' => 'url_field_callback',
            'boolean' => 'checkbox_field_callback'
        );
        
        return isset($callbacks[$type]) ? $callbacks[$type] : 'text_field_callback';
    }
    
    private function get_field_label($setting) {
        $labels = array(
            'uqual_calc_enable_analytics' => __('Enable Analytics', 'uqual-calculators'),
            'uqual_calc_default_cta_text' => __('Default CTA Text', 'uqual-calculators'),
            'uqual_calc_cta_url' => __('CTA URL', 'uqual-calculators'),
            'uqual_calc_primary_color' => __('Primary Color', 'uqual-calculators'),
            'uqual_calc_accent_color' => __('Accent Color', 'uqual-calculators')
        );
        
        return isset($labels[$setting]) ? $labels[$setting] : ucwords(str_replace('_', ' ', $setting));
    }
    
    private function get_field_description($setting) {
        $descriptions = array(
            'uqual_calc_enable_analytics' => __('Track calculator usage and user interactions', 'uqual-calculators'),
            'uqual_calc_default_cta_text' => __('Default text for call-to-action buttons', 'uqual-calculators'),
            'uqual_calc_cta_url' => __('URL where CTA buttons should link to', 'uqual-calculators'),
            'uqual_calc_primary_color' => __('Primary color used throughout the calculators (e.g., #2E7D32). Click the color box to open the color picker.', 'uqual-calculators'),
            'uqual_calc_accent_color' => __('Accent color for highlights and secondary elements (e.g., #FFA726). Click the color box to open the color picker.', 'uqual-calculators')
        );
        
        return isset($descriptions[$setting]) ? $descriptions[$setting] : '';
    }
    
    /**
     * Sanitize setting values
     */
    public function sanitize_setting($value) {
        $setting = sanitize_key($_REQUEST['option_page'] ?? '');
        
        // Get the actual setting name from POST data
        foreach ($_POST as $key => $val) {
            if (strpos($key, 'uqual_calc_') === 0) {
                $setting = $key;
                $value = $val;
                break;
            }
        }
        
        // Color field validation
        if (strpos($setting, 'color') !== false) {
            return sanitize_hex_color($value);
        }
        
        // URL validation
        if (strpos($setting, 'url') !== false) {
            return esc_url_raw($value);
        }
        
        // Boolean validation
        if (strpos($setting, 'enable_') !== false) {
            return $value === 'yes' ? 'yes' : 'no';
        }
        
        // Default text sanitization
        return sanitize_text_field($value);
    }
    
    /**
     * Get default values for settings
     */
    private function get_default_value($setting) {
        $defaults = array(
            'uqual_calc_enable_analytics' => 'yes',
            'uqual_calc_default_cta_text' => 'Get Professional Help',
            'uqual_calc_cta_url' => home_url('/consultation'),
            'uqual_calc_primary_color' => '#2E7D32',
            'uqual_calc_accent_color' => '#FFA726'
        );
        
        return isset($defaults[$setting]) ? $defaults[$setting] : '';
    }
}