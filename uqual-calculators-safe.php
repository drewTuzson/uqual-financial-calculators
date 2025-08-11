<?php
/**
 * Plugin Name: UQUAL Financial Calculators (Safe Version)
 * Plugin URI: https://uqual.com/calculators
 * Description: Safe version with error handling - Comprehensive financial calculators for loan readiness assessment.
 * Version: 1.0.0-safe
 * Author: UQUAL LLC
 * Author URI: https://uqual.com
 * License: GPL v2 or later
 * Text Domain: uqual-calculators
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed.');
}

// Define plugin constants with safety checks
if (!defined('UQUAL_CALC_VERSION')) {
    define('UQUAL_CALC_VERSION', '1.0.0-safe');
}

if (!defined('UQUAL_CALC_PLUGIN_DIR')) {
    define('UQUAL_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('UQUAL_CALC_PLUGIN_URL')) {
    define('UQUAL_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('UQUAL_CALC_PLUGIN_FILE')) {
    define('UQUAL_CALC_PLUGIN_FILE', __FILE__);
}

if (!defined('UQUAL_CALC_PLUGIN_BASENAME')) {
    define('UQUAL_CALC_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Database table names with safety checks
global $wpdb;
if (isset($wpdb) && is_object($wpdb)) {
    define('UQUAL_CALC_TABLE_SESSIONS', $wpdb->prefix . 'uqual_calculator_sessions');
    define('UQUAL_CALC_TABLE_INPUTS', $wpdb->prefix . 'uqual_calculator_inputs');
    define('UQUAL_CALC_TABLE_EVENTS', $wpdb->prefix . 'uqual_calculator_events');
}

/**
 * Error logging function
 */
function uqual_log_error($message, $context = '') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = 'UQUAL ERROR: ' . $message;
        if ($context) {
            $log_message .= ' Context: ' . $context;
        }
        error_log($log_message);
    }
}

/**
 * Main plugin class with comprehensive error handling
 */
class UQUAL_Financial_Calculators {
    
    private static $instance = null;
    private $dependencies_loaded = false;
    private $init_errors = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Use try-catch to prevent fatal errors during construction
        try {
            $this->init_basic_hooks();
            uqual_log_error('Plugin constructed successfully', 'constructor');
        } catch (Exception $e) {
            $this->init_errors[] = $e->getMessage();
            uqual_log_error('Constructor error: ' . $e->getMessage(), 'constructor');
            add_action('admin_notices', array($this, 'show_construction_error'));
        } catch (Error $e) {
            $this->init_errors[] = $e->getMessage();
            uqual_log_error('Fatal constructor error: ' . $e->getMessage(), 'constructor');
            add_action('admin_notices', array($this, 'show_construction_error'));
        }
    }
    
    /**
     * Initialize basic hooks safely
     */
    private function init_basic_hooks() {
        // Basic WordPress hooks
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        
        // Activation/deactivation hooks
        register_activation_hook(UQUAL_CALC_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(UQUAL_CALC_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Load dependencies on init, not immediately
        add_action('plugins_loaded', array($this, 'load_dependencies'), 5);
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Basic shortcode for testing
        add_shortcode('uqual_calculator', array($this, 'safe_shortcode_handler'));
        
        uqual_log_error('Basic hooks initialized', 'init_basic_hooks');
    }
    
    /**
     * Plugin initialization
     */
    public function init() {
        try {
            // Load text domain
            load_plugin_textdomain('uqual-calculators', false, dirname(UQUAL_CALC_PLUGIN_BASENAME) . '/languages');
            
            // Initialize components only if dependencies are loaded
            if ($this->dependencies_loaded) {
                $this->init_components();
            }
            
            uqual_log_error('Plugin init completed', 'init');
        } catch (Exception $e) {
            uqual_log_error('Init error: ' . $e->getMessage(), 'init');
            $this->init_errors[] = $e->getMessage();
        }
    }
    
    public function admin_init() {
        // Admin initialization
        uqual_log_error('Admin init called', 'admin_init');
    }
    
    /**
     * Load dependencies with error handling
     */
    public function load_dependencies() {
        try {
            $this->check_file_dependencies();
            $this->load_core_files();
            $this->dependencies_loaded = true;
            
            uqual_log_error('Dependencies loaded successfully', 'load_dependencies');
            
        } catch (Exception $e) {
            uqual_log_error('Dependency loading error: ' . $e->getMessage(), 'load_dependencies');
            $this->init_errors[] = 'Failed to load dependencies: ' . $e->getMessage();
            add_action('admin_notices', array($this, 'show_dependency_error'));
        }
    }
    
    /**
     * Check if required files exist before loading
     */
    private function check_file_dependencies() {
        $required_files = array(
            'includes/class-database-handler.php',
            'admin/class-admin-interface.php'
        );
        
        $missing_files = array();
        
        foreach ($required_files as $file) {
            $file_path = UQUAL_CALC_PLUGIN_DIR . $file;
            if (!file_exists($file_path)) {
                $missing_files[] = $file;
            }
        }
        
        if (!empty($missing_files)) {
            throw new Exception('Missing required files: ' . implode(', ', $missing_files));
        }
        
        uqual_log_error('All required files found', 'check_file_dependencies');
    }
    
    /**
     * Load core files with individual error handling
     */
    private function load_core_files() {
        $files_to_load = array(
            'includes/class-database-handler.php' => 'Database Handler',
            'admin/class-admin-interface.php' => 'Admin Interface'
        );
        
        foreach ($files_to_load as $file => $name) {
            try {
                require_once UQUAL_CALC_PLUGIN_DIR . $file;
                uqual_log_error($name . ' loaded successfully', 'load_core_files');
            } catch (Exception $e) {
                uqual_log_error('Failed to load ' . $name . ': ' . $e->getMessage(), 'load_core_files');
                throw new Exception('Failed to load ' . $name . ': ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Initialize components safely
     */
    private function init_components() {
        try {
            // Only initialize if classes exist
            if (class_exists('UQUAL_Admin_Interface')) {
                UQUAL_Admin_Interface::get_instance();
                uqual_log_error('Admin interface initialized', 'init_components');
            }
            
        } catch (Exception $e) {
            uqual_log_error('Component initialization error: ' . $e->getMessage(), 'init_components');
            $this->init_errors[] = 'Component initialization failed: ' . $e->getMessage();
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'UQUAL Calculators',
            'UQUAL Calculators',
            'manage_options',
            'uqual-calculators-safe',
            array($this, 'admin_page'),
            'dashicons-calculator',
            30
        );
        
        uqual_log_error('Admin menu added', 'add_admin_menu');
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>UQUAL Financial Calculators - Safe Version</h1>
            
            <?php if (!empty($this->init_errors)) : ?>
                <div class="notice notice-error">
                    <p><strong>Plugin Errors:</strong></p>
                    <ul>
                        <?php foreach ($this->init_errors as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else : ?>
                <div class="notice notice-success">
                    <p><strong>Plugin Status:</strong> ✅ Running successfully!</p>
                </div>
            <?php endif; ?>
            
            <h2>System Information</h2>
            <table class="widefat striped">
                <tbody>
                    <tr>
                        <td><strong>Plugin Version</strong></td>
                        <td><?php echo UQUAL_CALC_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>WordPress Version</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Theme</strong></td>
                        <td><?php echo wp_get_theme()->get('Name') . ' v' . wp_get_theme()->get('Version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Dependencies Loaded</strong></td>
                        <td><?php echo $this->dependencies_loaded ? '✅ Yes' : '❌ No'; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Database Test</h2>
            <?php $this->test_database(); ?>
            
            <h2>Shortcode Test</h2>
            <p>Test the basic shortcode: <code>[uqual_calculator type="loan_readiness"]</code></p>
            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px;">
                <?php echo do_shortcode('[uqual_calculator type="loan_readiness"]'); ?>
            </div>
            
            <?php if ($this->dependencies_loaded && empty($this->init_errors)) : ?>
                <h2>✅ Ready for Next Steps</h2>
                <p>The plugin is running safely. You can now proceed to add more features incrementally.</p>
            <?php else : ?>
                <h2>🔧 Issues Need Resolution</h2>
                <p>Please resolve the errors above before proceeding with additional features.</p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Test database connection
     */
    private function test_database() {
        global $wpdb;
        
        try {
            $test_query = $wpdb->get_var("SELECT 1");
            if ($test_query === '1') {
                echo '<div class="notice notice-success inline"><p>✅ Database connection working</p></div>';
            } else {
                echo '<div class="notice notice-error inline"><p>❌ Database connection issue</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="notice notice-error inline"><p>❌ Database error: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
    
    /**
     * Safe shortcode handler
     */
    public function safe_shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'type' => 'loan_readiness',
            'theme' => 'light'
        ), $atts, 'uqual_calculator');
        
        if (!$this->dependencies_loaded) {
            return '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; color: #856404;">⚠️ Calculator temporarily unavailable - plugin dependencies not loaded.</div>';
        }
        
        ob_start();
        ?>
        <div class="uqual-calculator-wrapper" style="max-width: 600px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
            <div style="background: linear-gradient(135deg, #2E7D32, #FFA726); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                <h3 style="margin: 0;">🧮 UQUAL Financial Calculator</h3>
                <p style="margin: 10px 0 0 0; opacity: 0.9;">Calculator Type: <?php echo esc_html($atts['type']); ?></p>
            </div>
            
            <div style="background: white; border: 1px solid #ddd; border-top: none; padding: 20px; border-radius: 0 0 8px 8px;">
                <div style="background: #e8f5e8; border: 1px solid #4caf50; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p style="margin: 0;"><strong>✅ Plugin Status:</strong> Safe version running successfully!</p>
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">Dependencies loaded: <?php echo $this->dependencies_loaded ? 'Yes' : 'No'; ?></p>
                </div>
                
                <p>This is a safe version of the UQUAL Financial Calculators plugin. The calculator interface will be implemented once the core functionality is confirmed working.</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px;">
                    <h4 style="margin: 0 0 10px 0;">Plugin Information:</h4>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Version: <?php echo UQUAL_CALC_VERSION; ?></li>
                        <li>WordPress: <?php echo get_bloginfo('version'); ?></li>
                        <li>PHP: <?php echo PHP_VERSION; ?></li>
                        <li>Theme: <?php echo wp_get_theme()->get('Name'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        try {
            uqual_log_error('Plugin activation started', 'activate');
            
            // Basic database test
            global $wpdb;
            $wpdb->query("SELECT 1");
            
            // Create basic tables if database handler is available
            if ($this->dependencies_loaded && class_exists('UQUAL_Database_Handler')) {
                UQUAL_Database_Handler::create_tables();
                uqual_log_error('Database tables created', 'activate');
            }
            
            // Set basic options
            update_option('uqual_calc_version', UQUAL_CALC_VERSION);
            update_option('uqual_calc_activation_time', current_time('mysql'));
            
            uqual_log_error('Plugin activated successfully', 'activate');
            
        } catch (Exception $e) {
            uqual_log_error('Activation error: ' . $e->getMessage(), 'activate');
            throw new Exception('Plugin activation failed: ' . $e->getMessage());
        }
    }
    
    public function deactivate() {
        uqual_log_error('Plugin deactivated', 'deactivate');
    }
    
    /**
     * Show construction error notice
     */
    public function show_construction_error() {
        ?>
        <div class="notice notice-error">
            <p><strong>UQUAL Calculators Error:</strong> Plugin failed to initialize properly. Check error logs for details.</p>
        </div>
        <?php
    }
    
    /**
     * Show dependency error notice
     */
    public function show_dependency_error() {
        ?>
        <div class="notice notice-warning">
            <p><strong>UQUAL Calculators:</strong> Some plugin dependencies failed to load. Basic functionality may be limited.</p>
        </div>
        <?php
    }
}

// Initialize plugin with comprehensive error handling
try {
    // Initialize on plugins_loaded to ensure WordPress is fully loaded
    add_action('plugins_loaded', function() {
        try {
            UQUAL_Financial_Calculators::get_instance();
            uqual_log_error('Plugin instance created successfully', 'main');
        } catch (Exception $e) {
            uqual_log_error('Failed to create plugin instance: ' . $e->getMessage(), 'main');
            
            // Show admin error notice
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p><strong>UQUAL Calculators:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }, 10);
    
    uqual_log_error('Plugin file loaded successfully', 'main_file');
    
} catch (Exception $e) {
    uqual_log_error('Critical error in main plugin file: ' . $e->getMessage(), 'main_file');
    
    // Last resort error handling
    if (function_exists('add_action')) {
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p><strong>UQUAL Calculators Critical Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        });
    }
} catch (Error $e) {
    uqual_log_error('Fatal error in main plugin file: ' . $e->getMessage(), 'main_file');
}