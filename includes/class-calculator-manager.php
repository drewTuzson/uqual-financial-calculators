<?php
/**
 * Calculator Manager Class
 * 
 * Manages all calculator instances and operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Calculator_Manager {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Registered calculators
     */
    private $calculators = array();
    
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
        $this->register_calculators();
    }
    
    /**
     * Register all available calculators
     */
    private function register_calculators() {
        // Register each calculator type
        $calculator_classes = array(
            'UQUAL_Loan_Readiness_Calculator',
            'UQUAL_DTI_Calculator',
            'UQUAL_Affordability_Calculator',
            'UQUAL_Credit_Simulator',
            'UQUAL_Savings_Calculator'
        );
        
        foreach ($calculator_classes as $class) {
            if (class_exists($class)) {
                $calculator = new $class();
                $this->calculators[$calculator->get_type()] = $calculator;
            }
        }
        
        // Allow third-party calculators to be registered
        do_action('uqual_register_calculators', $this);
    }
    
    /**
     * Register a calculator
     */
    public function register_calculator($calculator) {
        if ($calculator instanceof UQUAL_Base_Calculator) {
            $this->calculators[$calculator->get_type()] = $calculator;
        }
    }
    
    /**
     * Get a specific calculator instance
     */
    public function get_calculator($type) {
        return isset($this->calculators[$type]) ? $this->calculators[$type] : null;
    }
    
    /**
     * Get all registered calculators
     */
    public function get_calculators() {
        return $this->calculators;
    }
    
    /**
     * Get calculator types
     */
    public function get_calculator_types() {
        return array_keys($this->calculators);
    }
    
    /**
     * Get calculator select options
     */
    public function get_calculator_options() {
        $options = array();
        foreach ($this->calculators as $type => $calculator) {
            $options[$type] = $calculator->get_name();
        }
        return $options;
    }
    
    /**
     * Process calculation request
     */
    public function process_calculation($type, $input_data) {
        $calculator = $this->get_calculator($type);
        
        if (!$calculator) {
            return array(
                'success' => false,
                'message' => __('Invalid calculator type', 'uqual-calculators')
            );
        }
        
        // Sanitize input
        $sanitized_input = $calculator->sanitize_input($input_data);
        
        // Validate input
        $validation = $calculator->validate_input($sanitized_input);
        if (!$validation['valid']) {
            return array(
                'success' => false,
                'message' => $validation['message'],
                'errors' => $validation['errors']
            );
        }
        
        // Perform calculation
        try {
            $results = $calculator->calculate($sanitized_input);
            
            return array(
                'success' => true,
                'results' => $results,
                'formatted_results' => $this->format_results($type, $results)
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => __('An error occurred during calculation', 'uqual-calculators'),
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Format calculation results for display
     */
    public function format_results($type, $results) {
        $calculator = $this->get_calculator($type);
        
        if (!$calculator) {
            return $results;
        }
        
        // Format based on calculator type
        switch ($type) {
            case 'loan_readiness':
                return $this->format_loan_readiness_results($results);
                
            case 'dti':
                return $this->format_dti_results($results);
                
            case 'affordability':
                return $this->format_affordability_results($results);
                
            case 'credit_simulator':
                return $this->format_credit_simulator_results($results);
                
            case 'savings':
                return $this->format_savings_results($results);
                
            default:
                return $results;
        }
    }
    
    /**
     * Format Loan Readiness results
     */
    private function format_loan_readiness_results($results) {
        ob_start();
        ?>
        <div class="uqual-results-loan-readiness">
            <div class="score-gauge-container">
                <canvas id="score-gauge" width="300" height="200"></canvas>
                <div class="score-value">
                    <span class="score-number"><?php echo $results['score']; ?></span>
                    <span class="score-label"><?php _e('Loan Readiness Score', 'uqual-calculators'); ?></span>
                </div>
            </div>
            
            <div class="score-classification <?php echo esc_attr($results['classification']['class']); ?>">
                <h3><?php echo esc_html($results['classification']['label']); ?></h3>
            </div>
            
            <div class="component-scores">
                <h4><?php _e('Score Components', 'uqual-calculators'); ?></h4>
                <?php foreach ($results['components'] as $component => $score) : ?>
                <div class="component-item">
                    <span class="component-label"><?php echo esc_html($this->get_component_label($component)); ?></span>
                    <div class="component-bar">
                        <div class="component-fill" style="width: <?php echo $score; ?>%;">
                            <span class="component-score"><?php echo $score; ?>/100</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($results['recommendations'])) : ?>
            <div class="recommendations">
                <h4><?php _e('Recommendations', 'uqual-calculators'); ?></h4>
                <?php foreach ($results['recommendations'] as $rec) : ?>
                <div class="recommendation-card <?php echo esc_attr($rec['priority']); ?>">
                    <h5><?php echo esc_html($rec['title']); ?></h5>
                    <p><?php echo esc_html($rec['description']); ?></p>
                    <?php if ($rec['type'] === 'cta' && $rec['action']) : ?>
                    <a href="<?php echo esc_url($rec['action']); ?>" 
                       class="cta-button"
                       data-event="cta_click">
                        <?php echo esc_html($rec['action_text']); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        // Render gauge chart
        (function() {
            const canvas = document.getElementById('score-gauge');
            const ctx = canvas.getContext('2d');
            const score = <?php echo $results['score']; ?>;
            const color = '<?php echo $results['classification']['color']; ?>';
            
            // Draw gauge arc
            const centerX = 150;
            const centerY = 150;
            const radius = 100;
            const startAngle = Math.PI * 0.75;
            const endAngle = Math.PI * 2.25;
            const scoreAngle = startAngle + (score / 100) * (endAngle - startAngle);
            
            // Background arc
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            ctx.strokeStyle = '#e0e0e0';
            ctx.lineWidth = 20;
            ctx.stroke();
            
            // Score arc
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, startAngle, scoreAngle);
            ctx.strokeStyle = color;
            ctx.lineWidth = 20;
            ctx.lineCap = 'round';
            ctx.stroke();
        })();
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Format DTI results
     */
    private function format_dti_results($results) {
        ob_start();
        ?>
        <div class="uqual-results-dti">
            <div class="dti-ratio-display">
                <div class="ratio-value <?php echo esc_attr(strtolower(str_replace(' ', '-', $results['classification']))); ?>">
                    <span class="ratio-number"><?php echo $results['ratio']; ?>%</span>
                    <span class="ratio-label"><?php _e('Debt-to-Income Ratio', 'uqual-calculators'); ?></span>
                </div>
                <div class="ratio-classification">
                    <?php echo esc_html($results['classification']); ?>
                </div>
            </div>
            
            <div class="dti-breakdown">
                <h4><?php _e('Monthly Breakdown', 'uqual-calculators'); ?></h4>
                <div class="breakdown-item">
                    <span><?php _e('Monthly Income', 'uqual-calculators'); ?></span>
                    <span class="value">$<?php echo number_format($results['monthlyIncome'], 0); ?></span>
                </div>
                <div class="breakdown-item">
                    <span><?php _e('Total Monthly Debt', 'uqual-calculators'); ?></span>
                    <span class="value">$<?php echo number_format($results['totalDebt'], 0); ?></span>
                </div>
            </div>
            
            <?php if (!empty($results['recommendations'])) : ?>
            <div class="dti-recommendations">
                <h4><?php _e('How to Improve Your DTI', 'uqual-calculators'); ?></h4>
                <ul>
                    <?php foreach ($results['recommendations'] as $recommendation) : ?>
                    <li><?php echo esc_html($recommendation); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($results['ratio'] > 36) : ?>
            <div class="cta-section">
                <p><?php _e('Your DTI ratio may impact your loan qualification. Let us help you improve it.', 'uqual-calculators'); ?></p>
                <a href="<?php echo esc_url(get_option('uqual_calc_cta_url')); ?>" 
                   class="cta-button"
                   data-event="cta_click">
                    <?php echo esc_html(get_option('uqual_calc_default_cta_text')); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Format Affordability results
     */
    private function format_affordability_results($results) {
        ob_start();
        ?>
        <div class="uqual-results-affordability">
            <div class="affordability-summary">
                <div class="summary-item primary">
                    <span class="label"><?php _e('Maximum Home Price', 'uqual-calculators'); ?></span>
                    <span class="value">$<?php echo number_format($results['homePrice'], 0); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label"><?php _e('Loan Amount', 'uqual-calculators'); ?></span>
                    <span class="value">$<?php echo number_format($results['loanAmount'], 0); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label"><?php _e('Monthly Payment', 'uqual-calculators'); ?></span>
                    <span class="value">$<?php echo number_format($results['monthlyPayment'], 0); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label"><?php _e('DTI Ratio', 'uqual-calculators'); ?></span>
                    <span class="value"><?php echo number_format($results['dtiRatio'], 1); ?>%</span>
                </div>
            </div>
            
            <div class="payment-breakdown">
                <h4><?php _e('Estimated Monthly Payment Breakdown', 'uqual-calculators'); ?></h4>
                <canvas id="payment-chart" width="400" height="300"></canvas>
            </div>
            
            <?php if (isset($results['scenarios'])) : ?>
            <div class="affordability-scenarios">
                <h4><?php _e('Different Down Payment Scenarios', 'uqual-calculators'); ?></h4>
                <table class="scenarios-table">
                    <thead>
                        <tr>
                            <th><?php _e('Down Payment', 'uqual-calculators'); ?></th>
                            <th><?php _e('Home Price', 'uqual-calculators'); ?></th>
                            <th><?php _e('Monthly Payment', 'uqual-calculators'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['scenarios'] as $scenario) : ?>
                        <tr>
                            <td><?php echo $scenario['downPaymentPercent']; ?>%</td>
                            <td>$<?php echo number_format($scenario['homePrice'], 0); ?></td>
                            <td>$<?php echo number_format($scenario['monthlyPayment'], 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        // Render payment breakdown chart
        (function() {
            const ctx = document.getElementById('payment-chart').getContext('2d');
            const payment = <?php echo $results['monthlyPayment']; ?>;
            const principal = payment * 0.5;
            const interest = payment * 0.25;
            const taxes = payment * 0.15;
            const insurance = payment * 0.1;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Principal', 'Interest', 'Taxes', 'Insurance'],
                    datasets: [{
                        data: [principal, interest, taxes, insurance],
                        backgroundColor: ['#2E7D32', '#66BB6A', '#A5D6A7', '#C8E6C9']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        })();
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Format Credit Simulator results
     */
    private function format_credit_simulator_results($results) {
        ob_start();
        ?>
        <div class="uqual-results-credit-simulator">
            <div class="credit-projection">
                <div class="score-comparison">
                    <div class="current-score">
                        <span class="label"><?php _e('Current Score', 'uqual-calculators'); ?></span>
                        <span class="value"><?php echo $results['currentScore']; ?></span>
                    </div>
                    <div class="arrow">→</div>
                    <div class="projected-score">
                        <span class="label"><?php _e('Projected Score', 'uqual-calculators'); ?></span>
                        <span class="value"><?php echo $results['projectedScore']; ?></span>
                    </div>
                </div>
                <div class="improvement-stats">
                    <span class="improvement">+<?php echo $results['improvement']; ?> <?php _e('points', 'uqual-calculators'); ?></span>
                    <span class="timeline"><?php echo sprintf(__('in %d months', 'uqual-calculators'), $results['timelineMonths']); ?></span>
                </div>
            </div>
            
            <div class="action-plan">
                <h4><?php _e('Your Action Plan', 'uqual-calculators'); ?></h4>
                <ol class="action-steps">
                    <?php foreach ($results['actions'] as $action) : ?>
                    <li>
                        <span class="action-title"><?php echo esc_html($action['title']); ?></span>
                        <span class="action-impact">+<?php echo $action['impact']; ?> <?php _e('points', 'uqual-calculators'); ?></span>
                        <p class="action-description"><?php echo esc_html($action['description']); ?></p>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            
            <div class="timeline-chart">
                <h4><?php _e('Credit Score Timeline', 'uqual-calculators'); ?></h4>
                <canvas id="timeline-chart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <script>
        // Render timeline chart
        (function() {
            const ctx = document.getElementById('timeline-chart').getContext('2d');
            const currentScore = <?php echo $results['currentScore']; ?>;
            const projectedScore = <?php echo $results['projectedScore']; ?>;
            const months = <?php echo $results['timelineMonths']; ?>;
            
            const labels = [];
            const data = [];
            
            for (let i = 0; i <= months; i++) {
                labels.push('Month ' + i);
                data.push(currentScore + (projectedScore - currentScore) * (i / months));
            }
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Credit Score',
                        data: data,
                        borderColor: '#2E7D32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 300,
                            max: 850
                        }
                    }
                }
            });
        })();
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Format Savings results
     */
    private function format_savings_results($results) {
        ob_start();
        ?>
        <div class="uqual-results-savings">
            <?php if ($results['canReachGoal']) : ?>
            <div class="savings-success">
                <h3><?php _e('You can reach your goal!', 'uqual-calculators'); ?></h3>
                <div class="goal-summary">
                    <div class="summary-item">
                        <span class="label"><?php _e('Time to Goal', 'uqual-calculators'); ?></span>
                        <span class="value"><?php echo $results['yearsToGoal']; ?> <?php _e('years', 'uqual-calculators'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label"><?php _e('Final Amount', 'uqual-calculators'); ?></span>
                        <span class="value">$<?php echo number_format($results['finalAmount'], 0); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label"><?php _e('Interest Earned', 'uqual-calculators'); ?></span>
                        <span class="value">$<?php echo number_format($results['totalInterestEarned'], 0); ?></span>
                    </div>
                </div>
            </div>
            <?php else : ?>
            <div class="savings-adjustment-needed">
                <h3><?php _e('Adjustment Needed', 'uqual-calculators'); ?></h3>
                <p><?php _e('To reach your goal in time, you need to save:', 'uqual-calculators'); ?></p>
                <div class="required-payment">
                    $<?php echo number_format($results['requiredMonthlyPayment'], 0); ?> <?php _e('per month', 'uqual-calculators'); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="savings-chart">
                <h4><?php _e('Savings Growth Over Time', 'uqual-calculators'); ?></h4>
                <canvas id="savings-chart" width="400" height="250"></canvas>
            </div>
        </div>
        
        <script>
        // Render savings growth chart
        (function() {
            const ctx = document.getElementById('savings-chart').getContext('2d');
            // Chart implementation would go here
        })();
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get component label
     */
    private function get_component_label($component) {
        $labels = array(
            'creditScore' => __('Credit Score', 'uqual-calculators'),
            'dtiRatio' => __('DTI Ratio', 'uqual-calculators'),
            'downPayment' => __('Down Payment', 'uqual-calculators'),
            'documentation' => __('Documentation', 'uqual-calculators')
        );
        
        return isset($labels[$component]) ? $labels[$component] : ucwords(str_replace('_', ' ', $component));
    }
}