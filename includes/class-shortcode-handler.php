<?php
/**
 * Shortcode Handler Class
 * 
 * Manages all shortcode operations for the calculators
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Shortcode_Handler {
    
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
        $this->register_shortcodes();
    }
    
    /**
     * Register all shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('uqual_calculator', array($this, 'render_calculator_shortcode'));
        add_shortcode('uqual_loan_readiness', array($this, 'render_loan_readiness_shortcode'));
        add_shortcode('uqual_dti', array($this, 'render_dti_shortcode'));
        add_shortcode('uqual_affordability', array($this, 'render_affordability_shortcode'));
        add_shortcode('uqual_credit_simulator', array($this, 'render_credit_simulator_shortcode'));
        add_shortcode('uqual_savings', array($this, 'render_savings_shortcode'));
    }
    
    /**
     * Main calculator shortcode handler
     * Usage: [uqual_calculator type="loan_readiness" theme="light" show_intro="true"]
     */
    public function render_calculator_shortcode($atts) {
        $defaults = array(
            'type' => 'loan_readiness',
            'theme' => 'light',
            'show_intro' => 'true',
            'cta_text' => get_option('uqual_calc_default_cta_text'),
            'mobile_steps' => 'true',
            'locale' => '',
            'max_scenarios' => 3,
            'default_rate' => '2.5'
        );
        
        $atts = shortcode_atts($defaults, $atts, 'uqual_calculator');
        
        // Convert string booleans to actual booleans
        $atts['show_intro'] = filter_var($atts['show_intro'], FILTER_VALIDATE_BOOLEAN);
        $atts['mobile_steps'] = filter_var($atts['mobile_steps'], FILTER_VALIDATE_BOOLEAN);
        
        // Get calculator instance
        $calculator = UQUAL_Calculator_Manager::get_instance()->get_calculator($atts['type']);
        
        if (!$calculator) {
            return '<div class="uqual-calculator-error">' . 
                   __('Invalid calculator type specified.', 'uqual-calculators') . 
                   '</div>';
        }
        
        // Start session for tracking
        $session_id = $this->start_calculator_session($atts['type']);
        
        // Build calculator HTML
        ob_start();
        ?>
        <div class="uqual-calculator-wrapper" 
             data-session-id="<?php echo esc_attr($session_id); ?>"
             data-calculator-type="<?php echo esc_attr($atts['type']); ?>">
            <?php echo $calculator->render($atts); ?>
        </div>
        <?php
        
        $html = ob_get_clean();
        
        // Add inline styles for theme
        if ($atts['theme'] === 'dark') {
            $html = $this->add_dark_theme_styles() . $html;
        }
        
        // Add schema markup
        if (get_option('uqual_calc_enable_schema', 'yes') === 'yes') {
            $html .= $this->generate_schema_markup($atts['type']);
        }
        
        return $html;
    }
    
    /**
     * Loan Readiness specific shortcode
     */
    public function render_loan_readiness_shortcode($atts) {
        $atts = shortcode_atts(
            array('theme' => 'light', 'show_intro' => 'true'),
            $atts,
            'uqual_loan_readiness'
        );
        $atts['type'] = 'loan_readiness';
        return $this->render_calculator_shortcode($atts);
    }
    
    /**
     * DTI Calculator specific shortcode
     */
    public function render_dti_shortcode($atts) {
        $atts = shortcode_atts(
            array('theme' => 'light', 'show_intro' => 'true'),
            $atts,
            'uqual_dti'
        );
        $atts['type'] = 'dti';
        return $this->render_calculator_shortcode($atts);
    }
    
    /**
     * Affordability Calculator specific shortcode
     */
    public function render_affordability_shortcode($atts) {
        $atts = shortcode_atts(
            array('theme' => 'light', 'show_intro' => 'true', 'locale' => ''),
            $atts,
            'uqual_affordability'
        );
        $atts['type'] = 'affordability';
        return $this->render_calculator_shortcode($atts);
    }
    
    /**
     * Credit Simulator specific shortcode
     */
    public function render_credit_simulator_shortcode($atts) {
        $atts = shortcode_atts(
            array('theme' => 'light', 'show_intro' => 'true', 'max_scenarios' => 5),
            $atts,
            'uqual_credit_simulator'
        );
        $atts['type'] = 'credit_simulator';
        return $this->render_calculator_shortcode($atts);
    }
    
    /**
     * Savings Calculator specific shortcode
     */
    public function render_savings_shortcode($atts) {
        $atts = shortcode_atts(
            array('theme' => 'light', 'show_intro' => 'true', 'default_rate' => '2.5'),
            $atts,
            'uqual_savings'
        );
        $atts['type'] = 'savings';
        return $this->render_calculator_shortcode($atts);
    }
    
    /**
     * Start calculator session for tracking
     */
    private function start_calculator_session($calculator_type) {
        $session_id = wp_hash(uniqid('calc_', true));
        
        // Create session in database
        UQUAL_Database_Handler::create_session($calculator_type, $session_id);
        
        return $session_id;
    }
    
    /**
     * Add dark theme styles
     */
    private function add_dark_theme_styles() {
        return '<style>
            .uqual-calculator-wrapper[data-theme="dark"] {
                --uqual-bg-color: #1a1a1a;
                --uqual-text-color: #ffffff;
                --uqual-border-color: #333333;
                --uqual-input-bg: #2a2a2a;
                --uqual-input-text: #ffffff;
            }
        </style>';
    }
    
    /**
     * Generate schema markup for SEO
     */
    private function generate_schema_markup($calculator_type) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FinancialService',
            'name' => $this->get_calculator_schema_name($calculator_type),
            'description' => $this->get_calculator_schema_description($calculator_type),
            'provider' => array(
                '@type' => 'Organization',
                'name' => 'UQUAL LLC',
                'url' => home_url()
            ),
            'areaServed' => 'United States',
            'serviceType' => 'Loan Readiness Assessment'
        );
        
        return '<script type="application/ld+json">' . 
               wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . 
               '</script>';
    }
    
    /**
     * Get calculator schema name
     */
    private function get_calculator_schema_name($type) {
        $names = array(
            'loan_readiness' => 'Loan Readiness Score Calculator',
            'dti' => 'Debt-to-Income Ratio Calculator',
            'affordability' => 'Mortgage Affordability Calculator',
            'credit_simulator' => 'Credit Score Improvement Simulator',
            'savings' => 'Down Payment Savings Calculator'
        );
        
        return isset($names[$type]) ? $names[$type] : 'Financial Calculator';
    }
    
    /**
     * Get calculator schema description
     */
    private function get_calculator_schema_description($type) {
        $descriptions = array(
            'loan_readiness' => 'Comprehensive loan readiness assessment tool that evaluates credit score, income, debt, and down payment to determine mortgage qualification readiness.',
            'dti' => 'Calculate your debt-to-income ratio to understand your borrowing capacity and loan qualification status.',
            'affordability' => 'Determine how much house you can afford based on your income, debts, and down payment.',
            'credit_simulator' => 'Simulate credit score improvements and see how different actions can impact your creditworthiness.',
            'savings' => 'Plan your down payment savings strategy with our interactive savings calculator.'
        );
        
        return isset($descriptions[$type]) ? $descriptions[$type] : 'Interactive financial calculator for loan readiness assessment.';
    }
}