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
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Basic shortcode for testing
        add_shortcode('uqual_calculator', array($this, 'render_calculator_shortcode'));
    }
    
    /**
     * Plugin initialization
     */
    public function init() {
        // Load plugin textdomain
        load_plugin_textdomain('uqual-calculators', false, dirname(UQUAL_CALC_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();
        
        // Set default options
        add_option('uqual_calc_version', UQUAL_CALC_VERSION);
        add_option('uqual_calc_enable_analytics', 'yes');
        add_option('uqual_calc_default_cta_text', 'Get Professional Help');
        add_option('uqual_calc_cta_url', home_url('/consultation'));
        add_option('uqual_calc_primary_color', '#2E7D32');
        add_option('uqual_calc_accent_color', '#FFA726');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Sessions table
        $sessions_table = "CREATE TABLE IF NOT EXISTS " . UQUAL_CALC_TABLE_SESSIONS . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64) NOT NULL UNIQUE,
            calculator_type VARCHAR(50) NOT NULL,
            user_ip VARCHAR(45),
            user_agent TEXT,
            page_url VARCHAR(500),
            started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME NULL,
            completion_rate DECIMAL(5,2) DEFAULT 0.00,
            INDEX idx_calculator_type (calculator_type),
            INDEX idx_started_at (started_at)
        ) $charset_collate;";
        
        // Inputs table
        $inputs_table = "CREATE TABLE IF NOT EXISTS " . UQUAL_CALC_TABLE_INPUTS . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64) NOT NULL,
            calculator_type VARCHAR(50) NOT NULL,
            input_data LONGTEXT NOT NULL,
            calculated_results LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_calculator_type (calculator_type),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";
        
        // Events table
        $events_table = "CREATE TABLE IF NOT EXISTS " . UQUAL_CALC_TABLE_EVENTS . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64) NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            event_data LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session_event (session_id, event_type),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sessions_table);
        dbDelta($inputs_table);
        dbDelta($events_table);
    }
    
    /**
     * Basic shortcode handler for testing
     */
    public function render_calculator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'loan_readiness',
            'theme' => 'light'
        ), $atts, 'uqual_calculator');
        
        ob_start();
        ?>
        <div class="uqual-calculator-wrapper" data-calculator-type="<?php echo esc_attr($atts['type']); ?>">
            <div class="uqual-calculator">
                <div class="uqual-calculator-intro">
                    <h2><?php _e('UQUAL Financial Calculator', 'uqual-calculators'); ?></h2>
                    <p><?php _e('Calculator is loading... Plugin activated successfully!', 'uqual-calculators'); ?></p>
                </div>
                
                <div class="uqual-test-info">
                    <h3><?php _e('Plugin Test Information', 'uqual-calculators'); ?></h3>
                    <ul>
                        <li><strong><?php _e('Plugin Version:', 'uqual-calculators'); ?></strong> <?php echo UQUAL_CALC_VERSION; ?></li>
                        <li><strong><?php _e('Calculator Type:', 'uqual-calculators'); ?></strong> <?php echo esc_html($atts['type']); ?></li>
                        <li><strong><?php _e('Theme:', 'uqual-calculators'); ?></strong> <?php echo esc_html($atts['theme']); ?></li>
                        <li><strong><?php _e('Database Tables:', 'uqual-calculators'); ?></strong>
                            <?php
                            global $wpdb;
                            $sessions_exists = $wpdb->get_var("SHOW TABLES LIKE '" . UQUAL_CALC_TABLE_SESSIONS . "'");
                            $inputs_exists = $wpdb->get_var("SHOW TABLES LIKE '" . UQUAL_CALC_TABLE_INPUTS . "'");
                            $events_exists = $wpdb->get_var("SHOW TABLES LIKE '" . UQUAL_CALC_TABLE_EVENTS . "'");
                            
                            if ($sessions_exists && $inputs_exists && $events_exists) {
                                echo '<span style="color: green;">✓ All tables created successfully</span>';
                            } else {
                                echo '<span style="color: red;">✗ Some tables missing</span>';
                            }
                            ?>
                        </li>
                        <li><strong><?php _e('PHP Version:', 'uqual-calculators'); ?></strong> <?php echo PHP_VERSION; ?></li>
                        <li><strong><?php _e('WordPress Version:', 'uqual-calculators'); ?></strong> <?php echo get_bloginfo('version'); ?></li>
                    </ul>
                    
                    <div class="test-form">
                        <h4><?php _e('Test Basic Functionality:', 'uqual-calculators'); ?></h4>
                        <p><?php _e('Enter some values to test:', 'uqual-calculators'); ?></p>
                        
                        <form style="max-width: 400px;">
                            <p>
                                <label><?php _e('Credit Score:', 'uqual-calculators'); ?></label><br>
                                <input type="range" min="300" max="850" value="650" style="width: 100%;">
                                <span>650</span>
                            </p>
                            
                            <p>
                                <label><?php _e('Monthly Income:', 'uqual-calculators'); ?></label><br>
                                <input type="number" placeholder="5000" style="width: 100%;">
                            </p>
                            
                            <p>
                                <button type="button" style="background: #2E7D32; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
                                    <?php _e('Test Calculate', 'uqual-calculators'); ?>
                                </button>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .uqual-calculator-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 20px auto;
        }
        .uqual-calculator {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .uqual-calculator-intro {
            background: linear-gradient(135deg, #2E7D32, #FFA726);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .uqual-calculator-intro h2 {
            margin: 0 0 10px 0;
            font-size: 1.75rem;
        }
        .uqual-calculator-intro p {
            margin: 0;
            opacity: 0.9;
        }
        .uqual-test-info {
            padding: 20px;
        }
        .uqual-test-info h3, .uqual-test-info h4 {
            color: #2E7D32;
            margin-top: 0;
        }
        .uqual-test-info ul {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .uqual-test-info li {
            margin: 8px 0;
            line-height: 1.4;
        }
        .test-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .test-form label {
            font-weight: 600;
            color: #333;
        }
        .test-form input {
            margin: 5px 0;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
}

// Initialize plugin
add_action('plugins_loaded', array('UQUAL_Financial_Calculators', 'get_instance'));