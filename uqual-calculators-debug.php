<?php
/**
 * Plugin Name: UQUAL Financial Calculators (Debug Version)
 * Plugin URI: https://uqual.com/calculators
 * Description: DEBUG VERSION - Minimal WordPress plugin to test activation and identify issues safely.
 * Version: 1.0.0-debug
 * Author: UQUAL LLC
 * Text Domain: uqual-calculators
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed.');
}

// Define plugin constants with error handling
if (!defined('UQUAL_CALC_VERSION')) {
    define('UQUAL_CALC_VERSION', '1.0.0-debug');
}

if (!defined('UQUAL_CALC_PLUGIN_DIR')) {
    define('UQUAL_CALC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('UQUAL_CALC_PLUGIN_URL')) {
    define('UQUAL_CALC_PLUGIN_URL', plugin_dir_url(__FILE__));
}

/**
 * Debug logging function
 */
function uqual_debug_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('UQUAL DEBUG: ' . $message);
    }
}

/**
 * Minimal Safe Plugin Class
 */
class UQUAL_Financial_Calculators_Debug {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        uqual_debug_log('Plugin constructor called');
        
        // Use try-catch to prevent fatal errors
        try {
            $this->init_hooks();
            uqual_debug_log('Hooks initialized successfully');
        } catch (Exception $e) {
            uqual_debug_log('Error in constructor: ' . $e->getMessage());
            add_action('admin_notices', array($this, 'show_error_notice'));
        }
    }
    
    private function init_hooks() {
        // Basic WordPress hooks
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_debug_menu'));
        
        // Activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Simple shortcode for testing
        add_shortcode('uqual_debug', array($this, 'debug_shortcode'));
        
        uqual_debug_log('All hooks registered');
    }
    
    public function init() {
        uqual_debug_log('Plugin init called');
        
        // Load text domain
        load_plugin_textdomain('uqual-calculators', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        uqual_debug_log('Plugin init completed');
    }
    
    public function admin_init() {
        uqual_debug_log('Admin init called');
    }
    
    public function add_debug_menu() {
        add_menu_page(
            'UQUAL Debug',
            'UQUAL Debug',
            'manage_options',
            'uqual-debug',
            array($this, 'debug_page'),
            'dashicons-bug',
            99
        );
        
        uqual_debug_log('Debug menu added');
    }
    
    public function debug_page() {
        ?>
        <div class="wrap">
            <h1>UQUAL Financial Calculators - Debug Information</h1>
            
            <div style="background: #f0f8ff; border: 1px solid #0073aa; padding: 20px; margin: 20px 0; border-radius: 5px;">
                <h2>✅ Plugin Activated Successfully!</h2>
                <p>If you can see this page, the basic plugin structure is working correctly.</p>
            </div>
            
            <h2>Environment Information</h2>
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
                        <td><strong>PHP Memory Limit</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Theme</strong></td>
                        <td><?php echo wp_get_theme()->get('Name') . ' v' . wp_get_theme()->get('Version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Active Plugins</strong></td>
                        <td><?php echo count(get_option('active_plugins', array())); ?> active plugins</td>
                    </tr>
                    <tr>
                        <td><strong>WP_DEBUG</strong></td>
                        <td><?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled'; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Database Test</h2>
            <?php
            global $wpdb;
            
            // Test database connection
            try {
                $test_query = $wpdb->get_var("SELECT 1");
                if ($test_query === '1') {
                    echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px;">✅ Database connection working</div>';
                } else {
                    echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px;">❌ Database connection issue</div>';
                }
            } catch (Exception $e) {
                echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px;">❌ Database error: ' . esc_html($e->getMessage()) . '</div>';
            }
            ?>
            
            <h2>Plugin Files Check</h2>
            <?php
            $required_files = array(
                'includes/class-database-handler.php',
                'includes/class-base-calculator.php',
                'includes/class-calculator-manager.php',
                'includes/class-analytics-tracker.php',
                'includes/class-shortcode-handler.php',
                'admin/class-admin-interface.php'
            );
            
            echo '<ul>';
            foreach ($required_files as $file) {
                $file_path = UQUAL_CALC_PLUGIN_DIR . $file;
                $exists = file_exists($file_path);
                $icon = $exists ? '✅' : '❌';
                $status = $exists ? 'EXISTS' : 'MISSING';
                echo '<li>' . $icon . ' ' . esc_html($file) . ' - ' . $status . '</li>';
            }
            echo '</ul>';
            ?>
            
            <h2>Shortcode Test</h2>
            <p>Test shortcode: <code>[uqual_debug]</code></p>
            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
                <?php echo do_shortcode('[uqual_debug]'); ?>
            </div>
            
            <h2>Next Steps</h2>
            <ol>
                <li>✅ Plugin activated without breaking the site</li>
                <li>✅ Basic WordPress integration working</li>
                <li>🔄 Check if all required files exist (see list above)</li>
                <li>🔄 Test database table creation</li>
                <li>🔄 Add calculator functionality incrementally</li>
            </ol>
            
            <h2>Error Log</h2>
            <p>Check your WordPress debug log for any UQUAL DEBUG entries.</p>
            <?php
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                $log_file = WP_CONTENT_DIR . '/debug.log';
                if (file_exists($log_file)) {
                    echo '<p><strong>Debug log location:</strong> ' . esc_html($log_file) . '</p>';
                } else {
                    echo '<p>Debug log file not found at expected location.</p>';
                }
            }
            ?>
        </div>
        <?php
    }
    
    public function debug_shortcode($atts) {
        $atts = shortcode_atts(array(
            'message' => 'Plugin is working!'
        ), $atts, 'uqual_debug');
        
        ob_start();
        ?>
        <div style="background: #e8f5e8; border: 2px solid #4caf50; padding: 15px; border-radius: 5px; text-align: center; margin: 10px 0;">
            <h3>🎉 UQUAL Debug Shortcode Working!</h3>
            <p><strong>Message:</strong> <?php echo esc_html($atts['message']); ?></p>
            <p><strong>Time:</strong> <?php echo current_time('mysql'); ?></p>
            <p><strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></p>
            <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function activate() {
        uqual_debug_log('Plugin activation started');
        
        try {
            // Test database connection
            global $wpdb;
            $wpdb->query("SELECT 1");
            uqual_debug_log('Database connection test passed');
            
            // Set plugin version
            update_option('uqual_calc_debug_version', UQUAL_CALC_VERSION);
            update_option('uqual_calc_activation_time', current_time('mysql'));
            
            uqual_debug_log('Plugin activated successfully');
            
        } catch (Exception $e) {
            uqual_debug_log('Activation error: ' . $e->getMessage());
            throw $e; // Re-throw to prevent activation
        }
    }
    
    public function deactivate() {
        uqual_debug_log('Plugin deactivated');
    }
    
    public function show_error_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>UQUAL Calculators Debug:</strong> An error occurred during plugin initialization. Check your error logs for details.</p>
        </div>
        <?php
    }
}

// Initialize plugin with error handling
try {
    add_action('plugins_loaded', function() {
        uqual_debug_log('plugins_loaded action fired');
        UQUAL_Financial_Calculators_Debug::get_instance();
    });
    
    uqual_debug_log('Plugin file loaded successfully');
    
} catch (Exception $e) {
    uqual_debug_log('Critical error in main plugin file: ' . $e->getMessage());
    
    // Show admin notice about the error
    add_action('admin_notices', function() use ($e) {
        echo '<div class="notice notice-error"><p><strong>UQUAL Calculators Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
    });
}