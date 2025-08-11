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
        add_action('wp_ajax_uqual_export_analytics', array($this, 'export_analytics'));
        add_action('wp_ajax_uqual_clear_data', array($this, 'clear_data'));
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
        
        // Analytics page
        add_submenu_page(
            'uqual-calculators',
            __('Analytics', 'uqual-calculators'),
            __('Analytics', 'uqual-calculators'),
            'manage_options',
            'uqual-calculators-analytics',
            array($this, 'analytics_page')
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
        
        // Analytics settings section
        add_settings_section(
            'uqual_calc_analytics',
            __('Analytics Settings', 'uqual-calculators'),
            array($this, 'analytics_section_callback'),
            'uqual-calculators-settings'
        );
        
        // Appearance settings section
        add_settings_section(
            'uqual_calc_appearance',
            __('Appearance Settings', 'uqual-calculators'),
            array($this, 'appearance_section_callback'),
            'uqual-calculators-settings'
        );
        
        // Register settings
        $settings = array(
            'uqual_calc_enable_analytics' => 'boolean',
            'uqual_calc_ga_tracking_id' => 'string',
            'uqual_calc_default_cta_text' => 'string',
            'uqual_calc_cta_url' => 'url',
            'uqual_calc_primary_color' => 'string',
            'uqual_calc_accent_color' => 'string',
            'uqual_calc_enable_schema' => 'boolean',
            'uqual_calc_cache_duration' => 'integer'
        );
        
        foreach ($settings as $setting => $type) {
            register_setting('uqual_calculators_settings', $setting);
            
            $callback = array($this, $this->get_field_callback($type));
            $label = $this->get_field_label($setting);
            $section = $this->get_field_section($setting);
            
            add_settings_field(
                $setting,
                $label,
                $callback,
                'uqual-calculators-settings',
                $section,
                array('setting' => $setting, 'type' => $type)
            );
        }\n    }\n    \n    /**\n     * Dashboard page\n     */\n    public function dashboard_page() {\n        // Get analytics data for the last 30 days\n        $analytics = UQUAL_Analytics_Tracker::get_instance()->get_analytics_data();\n        \n        include UQUAL_CALC_PLUGIN_DIR . 'admin/views/dashboard.php';\n    }\n    \n    /**\n     * Settings page\n     */\n    public function settings_page() {\n        include UQUAL_CALC_PLUGIN_DIR . 'admin/views/settings.php';\n    }\n    \n    /**\n     * Analytics page\n     */\n    public function analytics_page() {\n        // Get detailed analytics\n        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));\n        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');\n        $calculator_type = isset($_GET['calculator_type']) ? sanitize_text_field($_GET['calculator_type']) : null;\n        \n        $analytics = UQUAL_Analytics_Tracker::get_instance()->get_analytics_data($date_from, $date_to);\n        \n        include UQUAL_CALC_PLUGIN_DIR . 'admin/views/analytics.php';\n    }\n    \n    /**\n     * Help page\n     */\n    public function help_page() {\n        include UQUAL_CALC_PLUGIN_DIR . 'admin/views/help.php';\n    }\n    \n    /**\n     * Settings section callbacks\n     */\n    public function general_section_callback() {\n        echo '<p>' . __('Configure general plugin settings.', 'uqual-calculators') . '</p>';\n    }\n    \n    public function analytics_section_callback() {\n        echo '<p>' . __('Configure analytics and tracking settings.', 'uqual-calculators') . '</p>';\n    }\n    \n    public function appearance_section_callback() {\n        echo '<p>' . __('Customize the appearance of your calculators.', 'uqual-calculators') . '</p>';\n    }\n    \n    /**\n     * Field callbacks\n     */\n    public function text_field_callback($args) {\n        $setting = $args['setting'];\n        $value = get_option($setting, '');\n        echo '<input type=\"text\" id=\"' . esc_attr($setting) . '\" name=\"' . esc_attr($setting) . '\" value=\"' . esc_attr($value) . '\" class=\"regular-text\" />';\n        echo '<p class=\"description\">' . $this->get_field_description($setting) . '</p>';\n    }\n    \n    public function url_field_callback($args) {\n        $setting = $args['setting'];\n        $value = get_option($setting, '');\n        echo '<input type=\"url\" id=\"' . esc_attr($setting) . '\" name=\"' . esc_attr($setting) . '\" value=\"' . esc_url($value) . '\" class=\"regular-text\" />';\n        echo '<p class=\"description\">' . $this->get_field_description($setting) . '</p>';\n    }\n    \n    public function number_field_callback($args) {\n        $setting = $args['setting'];\n        $value = get_option($setting, 0);\n        echo '<input type=\"number\" id=\"' . esc_attr($setting) . '\" name=\"' . esc_attr($setting) . '\" value=\"' . esc_attr($value) . '\" class=\"small-text\" />';\n        echo '<p class=\"description\">' . $this->get_field_description($setting) . '</p>';\n    }\n    \n    public function checkbox_field_callback($args) {\n        $setting = $args['setting'];\n        $value = get_option($setting, 'yes');\n        echo '<input type=\"checkbox\" id=\"' . esc_attr($setting) . '\" name=\"' . esc_attr($setting) . '\" value=\"yes\" ' . checked('yes', $value, false) . ' />';\n        echo '<label for=\"' . esc_attr($setting) . '\">' . $this->get_field_description($setting) . '</label>';\n    }\n    \n    public function color_field_callback($args) {\n        $setting = $args['setting'];\n        $value = get_option($setting, '#2E7D32');\n        echo '<input type=\"text\" id=\"' . esc_attr($setting) . '\" name=\"' . esc_attr($setting) . '\" value=\"' . esc_attr($value) . '\" class=\"color-picker\" />';\n        echo '<p class=\"description\">' . $this->get_field_description($setting) . '</p>';\n    }\n    \n    /**\n     * Export analytics data\n     */\n    public function export_analytics() {\n        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'uqual_admin_nonce')) {\n            wp_die(__('Permission denied', 'uqual-calculators'));\n        }\n        \n        $format = sanitize_text_field($_POST['format'] ?? 'csv');\n        $date_from = sanitize_text_field($_POST['date_from'] ?? null);\n        $date_to = sanitize_text_field($_POST['date_to'] ?? null);\n        \n        $data = UQUAL_Analytics_Tracker::get_instance()->export_analytics($format, $date_from, $date_to);\n        \n        // Set headers for download\n        if ($format === 'csv') {\n            header('Content-Type: text/csv');\n            header('Content-Disposition: attachment; filename=\"uqual-analytics-' . date('Y-m-d') . '.csv\"');\n            echo $data;\n        } else {\n            header('Content-Type: application/json');\n            header('Content-Disposition: attachment; filename=\"uqual-analytics-' . date('Y-m-d') . '.json\"');\n            echo $data;\n        }\n        \n        exit;\n    }\n    \n    /**\n     * Clear analytics data\n     */\n    public function clear_data() {\n        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'], 'uqual_admin_nonce')) {\n            wp_send_json_error(__('Permission denied', 'uqual-calculators'));\n        }\n        \n        $days_to_keep = intval($_POST['days_to_keep'] ?? 0);\n        \n        $deleted = UQUAL_Database_Handler::cleanup_old_sessions($days_to_keep);\n        \n        wp_send_json_success(array(\n            'message' => sprintf(__('%d sessions cleared successfully', 'uqual-calculators'), $deleted),\n            'deleted_count' => $deleted\n        ));\n    }\n    \n    /**\n     * Helper methods\n     */\n    private function get_field_callback($type) {\n        $callbacks = array(\n            'string' => 'text_field_callback',\n            'url' => 'url_field_callback',\n            'integer' => 'number_field_callback',\n            'boolean' => 'checkbox_field_callback',\n            'color' => 'color_field_callback'\n        );\n        \n        return $callbacks[$type] ?? 'text_field_callback';\n    }\n    \n    private function get_field_label($setting) {\n        $labels = array(\n            'uqual_calc_enable_analytics' => __('Enable Analytics', 'uqual-calculators'),\n            'uqual_calc_ga_tracking_id' => __('Google Analytics Tracking ID', 'uqual-calculators'),\n            'uqual_calc_default_cta_text' => __('Default CTA Text', 'uqual-calculators'),\n            'uqual_calc_cta_url' => __('CTA URL', 'uqual-calculators'),\n            'uqual_calc_primary_color' => __('Primary Color', 'uqual-calculators'),\n            'uqual_calc_accent_color' => __('Accent Color', 'uqual-calculators'),\n            'uqual_calc_enable_schema' => __('Enable Schema Markup', 'uqual-calculators'),\n            'uqual_calc_cache_duration' => __('Cache Duration (seconds)', 'uqual-calculators')\n        );\n        \n        return $labels[$setting] ?? ucwords(str_replace('_', ' ', $setting));\n    }\n    \n    private function get_field_section($setting) {\n        if (strpos($setting, 'analytics') !== false || strpos($setting, 'ga_') !== false) {\n            return 'uqual_calc_analytics';\n        }\n        \n        if (strpos($setting, 'color') !== false || strpos($setting, 'schema') !== false) {\n            return 'uqual_calc_appearance';\n        }\n        \n        return 'uqual_calc_general';\n    }\n    \n    private function get_field_description($setting) {\n        $descriptions = array(\n            'uqual_calc_enable_analytics' => __('Track calculator usage and user interactions', 'uqual-calculators'),\n            'uqual_calc_ga_tracking_id' => __('Your Google Analytics 4 Measurement ID (e.g., G-XXXXXXXXXX)', 'uqual-calculators'),\n            'uqual_calc_default_cta_text' => __('Default text for call-to-action buttons', 'uqual-calculators'),\n            'uqual_calc_cta_url' => __('URL where CTA buttons should link to', 'uqual-calculators'),\n            'uqual_calc_primary_color' => __('Primary color used throughout the calculators', 'uqual-calculators'),\n            'uqual_calc_accent_color' => __('Accent color for highlights and secondary elements', 'uqual-calculators'),\n            'uqual_calc_enable_schema' => __('Add structured data markup for better SEO', 'uqual-calculators'),\n            'uqual_calc_cache_duration' => __('How long to cache calculation results (in seconds)', 'uqual-calculators')\n        );\n        \n        return $descriptions[$setting] ?? '';\n    }\n}"
}]