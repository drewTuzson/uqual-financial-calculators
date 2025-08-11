<?php
/**
 * Admin Help View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('UQUAL Calculators - Help & Documentation', 'uqual-calculators'); ?></h1>
    
    <div class="uqual-help-content">
        <div class="help-section">
            <h2><?php _e('Getting Started', 'uqual-calculators'); ?></h2>
            <p><?php _e('Follow these steps to set up your UQUAL Financial Calculators:', 'uqual-calculators'); ?></p>
            <ol>
                <li><?php _e('Configure your settings in the Settings page', 'uqual-calculators'); ?></li>
                <li><?php _e('Add calculators to your pages using shortcodes', 'uqual-calculators'); ?></li>
                <li><?php _e('Monitor performance in the Analytics page', 'uqual-calculators'); ?></li>
            </ol>
        </div>
        
        <div class="help-section">
            <h2><?php _e('Available Calculators', 'uqual-calculators'); ?></h2>
            
            <div class="calculator-help">
                <h3><?php _e('Loan Readiness Score Calculator', 'uqual-calculators'); ?></h3>
                <p><strong><?php _e('Shortcode:', 'uqual-calculators'); ?></strong> <code>[uqual_calculator type="loan_readiness"]</code></p>
                <p><?php _e('UQUAL\'s proprietary assessment tool that evaluates creditworthiness based on multiple factors including credit score, debt-to-income ratio, down payment, and documentation readiness.', 'uqual-calculators'); ?></p>
            </div>
            
            <div class="calculator-help">
                <h3><?php _e('Advanced DTI Calculator', 'uqual-calculators'); ?></h3>
                <p><strong><?php _e('Shortcode:', 'uqual-calculators'); ?></strong> <code>[uqual_calculator type="dti"]</code></p>
                <p><?php _e('Comprehensive debt-to-income ratio calculator with detailed breakdown and improvement recommendations.', 'uqual-calculators'); ?></p>
            </div>
            
            <div class="calculator-help">
                <h3><?php _e('Mortgage Affordability Calculator', 'uqual-calculators'); ?></h3>
                <p><strong><?php _e('Shortcode:', 'uqual-calculators'); ?></strong> <code>[uqual_calculator type="affordability"]</code></p>
                <p><?php _e('Calculate maximum affordable home price with different down payment scenarios and local market considerations.', 'uqual-calculators'); ?></p>
            </div>
            
            <div class="calculator-help">
                <h3><?php _e('Credit Score Improvement Simulator', 'uqual-calculators'); ?></h3>
                <p><strong><?php _e('Shortcode:', 'uqual-calculators'); ?></strong> <code>[uqual_calculator type="credit_simulator"]</code></p>
                <p><?php _e('Interactive tool showing how different actions can improve credit scores over time.', 'uqual-calculators'); ?></p>
            </div>
            
            <div class="calculator-help">
                <h3><?php _e('Down Payment Savings Calculator', 'uqual-calculators'); ?></h3>
                <p><strong><?php _e('Shortcode:', 'uqual-calculators'); ?></strong> <code>[uqual_calculator type="savings"]</code></p>
                <p><?php _e('Plan down payment savings strategy with compound interest calculations and timeline projections.', 'uqual-calculators'); ?></p>
            </div>
        </div>
        
        <div class="help-section">
            <h2><?php _e('Shortcode Parameters', 'uqual-calculators'); ?></h2>
            <p><?php _e('All calculators support these optional parameters:', 'uqual-calculators'); ?></p>
            <ul>
                <li><code>theme="light|dark"</code> - <?php _e('Color theme (default: light)', 'uqual-calculators'); ?></li>
                <li><code>show_intro="true|false"</code> - <?php _e('Show calculator introduction (default: true)', 'uqual-calculators'); ?></li>
                <li><code>mobile_steps="true|false"</code> - <?php _e('Use step wizard on mobile (default: true)', 'uqual-calculators'); ?></li>
                <li><code>cta_text="Custom Text"</code> - <?php _e('Custom CTA button text', 'uqual-calculators'); ?></li>
            </ul>
        </div>
        
        <div class="help-section">
            <h2><?php _e('Divi Integration', 'uqual-calculators'); ?></h2>
            <p><?php _e('The plugin automatically integrates with Divi theme:', 'uqual-calculators'); ?></p>
            <ul>
                <li><?php _e('Inherits Divi\'s color scheme and typography', 'uqual-calculators'); ?></li>
                <li><?php _e('Works with Divi Builder modules', 'uqual-calculators'); ?></li>
                <li><?php _e('Responsive design matches Divi\'s breakpoints', 'uqual-calculators'); ?></li>
                <li><?php _e('Compatible with Divi\'s visual builder', 'uqual-calculators'); ?></li>
            </ul>
        </div>
        
        <div class="help-section">
            <h2><?php _e('Analytics & Tracking', 'uqual-calculators'); ?></h2>
            <p><?php _e('The plugin tracks comprehensive usage analytics:', 'uqual-calculators'); ?></p>
            <ul>
                <li><?php _e('Calculator usage sessions and completion rates', 'uqual-calculators'); ?></li>
                <li><?php _e('User interaction patterns and form abandonment', 'uqual-calculators'); ?></li>
                <li><?php _e('CTA click-through rates and conversions', 'uqual-calculators'); ?></li>
                <li><?php _e('Popular input ranges for business insights', 'uqual-calculators'); ?></li>
            </ul>
            <p><?php _e('All data is anonymized and stored securely in your WordPress database.', 'uqual-calculators'); ?></p>
        </div>
        
        <div class="help-section">
            <h2><?php _e('Troubleshooting', 'uqual-calculators'); ?></h2>
            
            <h3><?php _e('Calculator Not Displaying', 'uqual-calculators'); ?></h3>
            <ul>
                <li><?php _e('Check that the shortcode syntax is correct', 'uqual-calculators'); ?></li>
                <li><?php _e('Verify the calculator type is valid', 'uqual-calculators'); ?></li>
                <li><?php _e('Check for JavaScript errors in browser console', 'uqual-calculators'); ?></li>
            </ul>
            
            <h3><?php _e('Styling Issues', 'uqual-calculators'); ?></h3>
            <ul>
                <li><?php _e('Check if your theme is overriding plugin styles', 'uqual-calculators'); ?></li>
                <li><?php _e('Try adjusting the primary and accent colors in settings', 'uqual-calculators'); ?></li>
                <li><?php _e('Test with a default WordPress theme to isolate conflicts', 'uqual-calculators'); ?></li>
            </ul>
            
            <h3><?php _e('Performance Issues', 'uqual-calculators'); ?></h3>
            <ul>
                <li><?php _e('Adjust cache duration in plugin settings', 'uqual-calculators'); ?></li>
                <li><?php _e('Clear any caching plugins after making changes', 'uqual-calculators'); ?></li>
                <li><?php _e('Check database table sizes if using for high traffic', 'uqual-calculators'); ?></li>
            </ul>
        </div>
        
        <div class="help-section">
            <h2><?php _e('Support', 'uqual-calculators'); ?></h2>
            <p><?php _e('For technical support and questions:', 'uqual-calculators'); ?></p>
            <ul>
                <li><?php _e('Plugin Version:', 'uqual-calculators'); ?> <strong><?php echo UQUAL_CALC_VERSION; ?></strong></li>
                <li><?php _e('WordPress Version:', 'uqual-calculators'); ?> <strong><?php echo get_bloginfo('version'); ?></strong></li>
                <li><?php _e('PHP Version:', 'uqual-calculators'); ?> <strong><?php echo PHP_VERSION; ?></strong></li>
            </ul>
        </div>
    </div>
</div>

<style>
.uqual-help-content {
    max-width: 800px;
}

.help-section {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 25px;
    margin: 20px 0;
}

.help-section h2 {
    margin-top: 0;
    color: #2271b1;
    border-bottom: 2px solid #f1f1f1;
    padding-bottom: 10px;
}

.help-section h3 {
    color: #333;
    margin: 20px 0 10px 0;
}

.calculator-help {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    margin: 15px 0;
}

.calculator-help h3 {
    margin-top: 0;
}

.calculator-help code {
    background: #2271b1;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 13px;
}

.help-section ul {
    margin-left: 20px;
}

.help-section li {
    margin: 8px 0;
    line-height: 1.5;
}

.help-section code {
    background: #f1f1f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}
</style>