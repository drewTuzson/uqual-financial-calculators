<?php
/**
 * Plugin Name: UQUAL Financial Calculators
 * Plugin URI: https://uqual.com/calculators
 * Description: Comprehensive financial calculators for loan readiness assessment, DTI calculation, mortgage affordability, and more.
 * Version: 1.0.0
 * Author: UQUAL LLC
 * Author URI: https://uqual.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: uqual-calculators
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * 
 * GitHub Plugin URI: drewTuzson/uqual-financial-calculators
 * GitHub Branch: main
 * Primary Branch: main
 * Release Asset: true
 * 
 * Network: false
 * Update Server: https://api.github.com
 * Tested up to: 6.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UQUAL_CALC_VERSION', '1.0.0');
define('UQUAL_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UQUAL_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UQUAL_CALC_PLUGIN_FILE', __FILE__);
define('UQUAL_CALC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Database table names
global $wpdb;
define('UQUAL_CALC_TABLE_SESSIONS', $wpdb->prefix . 'uqual_calculator_sessions');
define('UQUAL_CALC_TABLE_INPUTS', $wpdb->prefix . 'uqual_calculator_inputs');
define('UQUAL_CALC_TABLE_EVENTS', $wpdb->prefix . 'uqual_calculator_events');

/**
 * Main plugin class
 */
class UQUAL_Financial_Calculators {
    
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core classes (load in correct order)
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/class-database-handler.php';
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/class-analytics-tracker.php';
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/class-shortcode-handler.php';
        
        // Base calculator class must be loaded before calculator manager and individual calculators
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/class-base-calculator.php';
        
        // Individual calculator classes
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/calculators/class-loan-readiness-calculator.php';
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/calculators/class-dti-calculator.php';
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/calculators/class-affordability-calculator.php';
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/calculators/class-credit-simulator.php';
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/calculators/class-savings-calculator.php';
        
        // Calculator manager (needs base calculator and individual calculators loaded first)
        require_once UQUAL_CALC_PLUGIN_DIR . 'includes/class-calculator-manager.php';
        
        // Admin classes
        if (is_admin()) {
            require_once UQUAL_CALC_PLUGIN_DIR . 'admin/class-admin-interface.php';
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(UQUAL_CALC_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(UQUAL_CALC_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Init action
        add_action('init', array($this, 'init'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_uqual_calculate', array($this, 'handle_ajax_calculation'));
        add_action('wp_ajax_nopriv_uqual_calculate', array($this, 'handle_ajax_calculation'));
        add_action('wp_ajax_uqual_track_event', array($this, 'handle_ajax_tracking'));
        add_action('wp_ajax_nopriv_uqual_track_event', array($this, 'handle_ajax_tracking'));
        
        // Initialize components
        $this->init_components();
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize calculator manager
        UQUAL_Calculator_Manager::get_instance();
        
        // Initialize shortcode handler
        UQUAL_Shortcode_Handler::get_instance();
        
        // Initialize admin interface
        if (is_admin()) {
            UQUAL_Admin_Interface::get_instance();
        }
        
        // Initialize analytics tracker
        UQUAL_Analytics_Tracker::get_instance();
    }
    
    /**
     * Plugin initialization
     */
    public function init() {
        // Load plugin textdomain
        load_plugin_textdomain('uqual-calculators', false, dirname(UQUAL_CALC_PLUGIN_BASENAME) . '/languages');
        
        // Register custom post types if needed
        $this->register_post_types();
        
        // Add rewrite rules if needed
        $this->add_rewrite_rules();
    }
    
    /**
     * Register custom post types
     */
    private function register_post_types() {
        // Could be used for calculator results storage or saved calculations
    }
    
    /**
     * Add custom rewrite rules
     */
    private function add_rewrite_rules() {
        // Could be used for custom calculator URLs
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_assets() {
        // Core styles
        wp_enqueue_style(
            'uqual-calculators',
            UQUAL_CALC_PLUGIN_URL . 'assets/css/uqual-calculators.css',
            array(),
            UQUAL_CALC_VERSION
        );
        
        // Divi compatibility styles
        if (function_exists('et_get_option')) {
            wp_enqueue_style(
                'uqual-calculators-divi',
                UQUAL_CALC_PLUGIN_URL . 'assets/css/divi-compatibility.css',
                array('uqual-calculators'),
                UQUAL_CALC_VERSION
            );
        }
        
        // Core scripts
        wp_enqueue_script(
            'uqual-calculators',
            UQUAL_CALC_PLUGIN_URL . 'assets/js/uqual-calculators.js',
            array('jquery'),
            UQUAL_CALC_VERSION,
            true
        );
        
        // Chart.js for visual displays
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            array(),
            '4.4.0',
            true
        );
        
        // Localize script with AJAX URL and other data
        wp_localize_script('uqual-calculators', 'uqual_calc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uqual_calc_nonce'),
            'currency_symbol' => '$',
            'number_format' => array(
                'decimals' => 2,
                'decimal_separator' => '.',
                'thousands_separator' => ','
            ),
            'messages' => array(
                'error' => __('An error occurred. Please try again.', 'uqual-calculators'),
                'invalid_input' => __('Please check your input values.', 'uqual-calculators'),
                'calculating' => __('Calculating...', 'uqual-calculators')
            )
        ));
        
        // Add custom CSS with user colors
        $this->add_custom_css();
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'uqual-calculators') === false) {
            return;
        }
        
        wp_enqueue_style(
            'uqual-calculators-admin',
            UQUAL_CALC_PLUGIN_URL . 'admin/assets/css/admin.css',
            array('wp-color-picker'),
            UQUAL_CALC_VERSION
        );
        
        // Enqueue color picker style
        wp_enqueue_style('wp-color-picker');
        
        wp_enqueue_script(
            'uqual-calculators-admin',
            UQUAL_CALC_PLUGIN_URL . 'admin/assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            UQUAL_CALC_VERSION,
            true
        );
        
        // Add Chart.js for analytics dashboard
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            array(),
            '4.4.0',
            true
        );
    }
    
    /**
     * Handle AJAX calculation requests
     */
    public function handle_ajax_calculation() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'uqual_calc_nonce')) {
            wp_die('Security check failed');
        }
        
        $calculator_type = sanitize_text_field($_POST['calculator_type'] ?? '');
        $input_data = $_POST['input_data'] ?? array();
        
        // Get calculator instance
        $calculator = UQUAL_Calculator_Manager::get_instance()->get_calculator($calculator_type);
        
        if (!$calculator) {
            wp_send_json_error(array('message' => 'Invalid calculator type'));
        }
        
        // Sanitize input data
        $sanitized_input = $calculator->sanitize_input($input_data);
        
        // Validate input
        $validation = $calculator->validate_input($sanitized_input);
        if (!$validation['valid']) {
            wp_send_json_error(array('message' => $validation['message']));
        }
        
        // Perform calculation
        $result = $calculator->calculate($sanitized_input);
        
        // Track calculation
        UQUAL_Analytics_Tracker::get_instance()->track_calculation(
            $calculator_type,
            $sanitized_input,
            $result
        );
        
        wp_send_json_success($result);
    }
    
    /**
     * Handle AJAX event tracking
     */
    public function handle_ajax_tracking() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'uqual_calc_nonce')) {
            wp_die('Security check failed');
        }
        
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');
        $event_data = $_POST['event_data'] ?? array();
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        UQUAL_Analytics_Tracker::get_instance()->track_event(
            $session_id,
            $event_type,
            $event_data
        );
        
        wp_send_json_success();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        UQUAL_Database_Handler::create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule cron jobs if needed
        $this->schedule_cron_jobs();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled cron jobs
        $this->clear_cron_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'uqual_calc_version' => UQUAL_CALC_VERSION,
            'uqual_calc_enable_analytics' => 'yes',
            'uqual_calc_default_cta_text' => 'Get Professional Help',
            'uqual_calc_cta_url' => home_url('/consultation'),
            'uqual_calc_primary_color' => '#2E7D32',
            'uqual_calc_accent_color' => '#FFA726',
            'uqual_calc_enable_schema' => 'yes',
            'uqual_calc_cache_duration' => 3600
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Schedule cron jobs
     */
    private function schedule_cron_jobs() {
        if (!wp_next_scheduled('uqual_calc_cleanup_sessions')) {
            wp_schedule_event(time(), 'daily', 'uqual_calc_cleanup_sessions');
        }
    }
    
    /**
     * Clear cron jobs
     */
    private function clear_cron_jobs() {
        wp_clear_scheduled_hook('uqual_calc_cleanup_sessions');
    }
    
    /**
     * Add custom CSS with user-defined colors
     */
    private function add_custom_css() {
        $primary_color = get_option('uqual_calc_primary_color', '#2E7D32');
        $accent_color = get_option('uqual_calc_accent_color', '#FFA726');
        
        // Ensure we have valid colors
        $primary_color = sanitize_hex_color($primary_color) ?: '#2E7D32';
        $accent_color = sanitize_hex_color($accent_color) ?: '#FFA726';
        
        $custom_css = "
            :root {
                --uqual-primary-color: {$primary_color};
                --uqual-accent-color: {$accent_color};
                --uqual-primary-rgb: " . $this->hex_to_rgb($primary_color) . ";
                --uqual-accent-rgb: " . $this->hex_to_rgb($accent_color) . ";
            }
            
            .uqual-calculator-wrapper {
                --uqual-primary: var(--uqual-primary-color);
                --uqual-accent: var(--uqual-accent-color);
            }
            
            /* Apply colors to calculator elements */
            .uqual-calculator .uqual-button-primary,
            .uqual-calculator .uqual-calculate-btn {
                background-color: var(--uqual-primary-color) !important;
                border-color: var(--uqual-primary-color) !important;
            }
            
            .uqual-calculator .uqual-button-primary:hover,
            .uqual-calculator .uqual-calculate-btn:hover {
                background-color: rgba(var(--uqual-primary-rgb), 0.9) !important;
            }
            
            .uqual-calculator .uqual-accent,
            .uqual-calculator .uqual-cta-button {
                background-color: var(--uqual-accent-color) !important;
                border-color: var(--uqual-accent-color) !important;
            }
            
            .uqual-calculator .uqual-accent:hover,
            .uqual-calculator .uqual-cta-button:hover {
                background-color: rgba(var(--uqual-accent-rgb), 0.9) !important;
            }
            
            .uqual-calculator .uqual-progress-bar {
                background-color: var(--uqual-primary-color);
            }
            
            .uqual-calculator .uqual-input:focus {
                border-color: var(--uqual-primary-color);
                box-shadow: 0 0 0 1px rgba(var(--uqual-primary-rgb), 0.3);
            }
            
            .uqual-calculator .uqual-result-highlight {
                color: var(--uqual-primary-color);
            }
        ";
        
        wp_add_inline_style('uqual-calculators', $custom_css);
    }
    
    /**
     * Convert hex color to RGB values
     */
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "{$r}, {$g}, {$b}";
    }
}

// Initialize plugin
add_action('plugins_loaded', array('UQUAL_Financial_Calculators', 'get_instance'));

// Cleanup old sessions cron job
add_action('uqual_calc_cleanup_sessions', function() {
    UQUAL_Database_Handler::cleanup_old_sessions(30); // Keep 30 days of data
});
