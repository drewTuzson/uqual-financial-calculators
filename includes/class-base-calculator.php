<?php
/**
 * Base Calculator Class
 * 
 * Abstract base class for all calculator types
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class UQUAL_Base_Calculator {
    
    /**
     * Calculator type identifier
     */
    protected $type = '';
    
    /**
     * Calculator name
     */
    protected $name = '';
    
    /**
     * Calculator description
     */
    protected $description = '';
    
    /**
     * Input fields configuration
     */
    protected $input_fields = array();
    
    /**
     * Validation rules
     */
    protected $validation_rules = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }
    
    /**
     * Initialize calculator - must be implemented by child classes
     */
    abstract protected function init();
    
    /**
     * Perform calculation - must be implemented by child classes
     */
    abstract public function calculate($input_data);
    
    /**
     * Get calculator type
     */
    public function get_type() {
        return $this->type;
    }
    
    /**
     * Get calculator name
     */
    public function get_name() {
        return $this->name;
    }
    
    /**
     * Get calculator description
     */
    public function get_description() {
        return $this->description;
    }
    
    /**
     * Get input fields configuration
     */
    public function get_input_fields() {
        return $this->input_fields;
    }
    
    /**
     * Sanitize input data
     */
    public function sanitize_input($input_data) {
        $sanitized = array();
        
        foreach ($this->input_fields as $field) {
            $field_name = $field['name'];
            
            if (!isset($input_data[$field_name])) {
                if (isset($field['default'])) {
                    $sanitized[$field_name] = $field['default'];
                }
                continue;
            }
            
            $value = $input_data[$field_name];
            
            switch ($field['type']) {
                case 'number':
                case 'range':
                case 'currency':
                    $sanitized[$field_name] = floatval($value);
                    break;
                    
                case 'integer':
                    $sanitized[$field_name] = intval($value);
                    break;
                    
                case 'checkbox':
                    $sanitized[$field_name] = (bool) $value;
                    break;
                    
                case 'select':
                case 'radio':
                    if (isset($field['options']) && in_array($value, array_keys($field['options']))) {
                        $sanitized[$field_name] = sanitize_text_field($value);
                    }
                    break;
                    
                case 'checkboxes':
                    if (is_array($value)) {
                        $sanitized[$field_name] = array_map('sanitize_text_field', $value);
                    }
                    break;
                    
                default:
                    $sanitized[$field_name] = sanitize_text_field($value);
                    break;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate input data
     */
    public function validate_input($input_data) {
        $errors = array();
        
        foreach ($this->input_fields as $field) {
            $field_name = $field['name'];
            $value = $input_data[$field_name] ?? null;
            
            // Check required fields
            if (isset($field['required']) && $field['required'] && empty($value)) {
                $errors[] = sprintf(
                    __('%s is required', 'uqual-calculators'),
                    $field['label']
                );
                continue;
            }
            
            // Skip validation if field is not set and not required
            if (!isset($input_data[$field_name])) {
                continue;
            }
            
            // Validate based on field type
            switch ($field['type']) {
                case 'number':
                case 'range':
                case 'currency':
                    if (isset($field['min']) && $value < $field['min']) {
                        $errors[] = sprintf(
                            __('%s must be at least %s', 'uqual-calculators'),
                            $field['label'],
                            $field['min']
                        );
                    }
                    if (isset($field['max']) && $value > $field['max']) {
                        $errors[] = sprintf(
                            __('%s must be no more than %s', 'uqual-calculators'),
                            $field['label'],
                            $field['max']
                        );
                    }
                    break;
                    
                case 'integer':
                    if (!is_int($value)) {
                        $errors[] = sprintf(
                            __('%s must be a whole number', 'uqual-calculators'),
                            $field['label']
                        );
                    }
                    break;
                    
                case 'select':
                case 'radio':
                    if (isset($field['options']) && !in_array($value, array_keys($field['options']))) {
                        $errors[] = sprintf(
                            __('Invalid value for %s', 'uqual-calculators'),
                            $field['label']
                        );
                    }
                    break;
            }
        }
        
        // Run custom validation rules
        if (!empty($this->validation_rules)) {
            foreach ($this->validation_rules as $rule) {
                $result = call_user_func($rule, $input_data);
                if ($result !== true) {
                    $errors[] = $result;
                }
            }
        }
        
        return array(
            'valid' => empty($errors),
            'message' => empty($errors) ? '' : implode(', ', $errors),
            'errors' => $errors
        );
    }
    
    /**
     * Format currency value
     */
    protected function format_currency($value, $decimals = 0) {
        return '$' . number_format($value, $decimals);
    }
    
    /**
     * Format percentage value
     */
    protected function format_percentage($value, $decimals = 2) {
        return number_format($value, $decimals) . '%';
    }
    
    /**
     * Calculate monthly payment using amortization formula
     */
    protected function calculate_monthly_payment($principal, $annual_rate, $years) {
        if ($annual_rate == 0) {
            return $principal / ($years * 12);
        }
        
        $monthly_rate = $annual_rate / 100 / 12;
        $num_payments = $years * 12;
        
        $monthly_payment = $principal * (
            ($monthly_rate * pow(1 + $monthly_rate, $num_payments)) /
            (pow(1 + $monthly_rate, $num_payments) - 1)
        );
        
        return $monthly_payment;
    }
    
    /**
     * Calculate loan amount from monthly payment
     */
    protected function calculate_loan_amount($monthly_payment, $annual_rate, $years) {
        if ($annual_rate == 0) {
            return $monthly_payment * $years * 12;
        }
        
        $monthly_rate = $annual_rate / 100 / 12;
        $num_payments = $years * 12;
        
        $loan_amount = $monthly_payment * (
            (pow(1 + $monthly_rate, $num_payments) - 1) /
            ($monthly_rate * pow(1 + $monthly_rate, $num_payments))
        );
        
        return $loan_amount;
    }
    
    /**
     * Generate recommendations based on results
     */
    protected function generate_recommendations($score, $threshold = 80) {
        $recommendations = array();
        
        if ($score < $threshold) {
            $recommendations[] = array(
                'type' => 'cta',
                'title' => __('Get Professional Help', 'uqual-calculators'),
                'description' => __('Our loan readiness experts can help you improve your score and qualify for better rates.', 'uqual-calculators'),
                'action' => get_option('uqual_calc_cta_url', home_url('/consultation')),
                'action_text' => get_option('uqual_calc_default_cta_text', __('Schedule Consultation', 'uqual-calculators')),
                'priority' => 'high'
            );
        }
        
        return $recommendations;
    }
    
    /**
     * Get score classification
     */
    protected function get_score_classification($score) {
        if ($score >= 90) return array('label' => __('Excellent', 'uqual-calculators'), 'class' => 'excellent', 'color' => '#2E7D32');
        if ($score >= 80) return array('label' => __('Very Good', 'uqual-calculators'), 'class' => 'very-good', 'color' => '#388E3C');
        if ($score >= 70) return array('label' => __('Good', 'uqual-calculators'), 'class' => 'good', 'color' => '#FFA726');
        if ($score >= 60) return array('label' => __('Fair', 'uqual-calculators'), 'class' => 'fair', 'color' => '#FF9800');
        if ($score >= 50) return array('label' => __('Poor', 'uqual-calculators'), 'class' => 'poor', 'color' => '#F57C00');
        return array('label' => __('Needs Improvement', 'uqual-calculators'), 'class' => 'needs-improvement', 'color' => '#D32F2F');
    }
    
    /**
     * Render calculator HTML
     */
    public function render($atts = array()) {
        $defaults = array(
            'theme' => 'light',
            'show_intro' => true,
            'cta_text' => get_option('uqual_calc_default_cta_text'),
            'mobile_steps' => true
        );
        
        $atts = wp_parse_args($atts, $defaults);
        
        ob_start();
        ?>
        <div class="uqual-calculator" 
             data-calculator-type="<?php echo esc_attr($this->type); ?>"
             data-theme="<?php echo esc_attr($atts['theme']); ?>">
            
            <?php if ($atts['show_intro']) : ?>
            <div class="uqual-calculator-intro">
                <h2><?php echo esc_html($this->name); ?></h2>
                <p><?php echo esc_html($this->description); ?></p>
            </div>
            <?php endif; ?>
            
            <form class="uqual-calculator-form" id="uqual-calc-<?php echo esc_attr($this->type); ?>">
                <?php $this->render_input_fields($atts); ?>
                
                <div class="uqual-calculator-actions">
                    <button type="submit" class="uqual-calculate-btn">
                        <?php _e('Calculate', 'uqual-calculators'); ?>
                    </button>
                    <button type="reset" class="uqual-reset-btn">
                        <?php _e('Reset', 'uqual-calculators'); ?>
                    </button>
                </div>
            </form>
            
            <div class="uqual-calculator-results" style="display:none;">
                <div class="uqual-results-loading">
                    <span class="spinner"></span>
                    <?php _e('Calculating...', 'uqual-calculators'); ?>
                </div>
                <div class="uqual-results-content"></div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render input fields
     */
    protected function render_input_fields($atts) {
        $is_mobile = wp_is_mobile();
        $use_steps = $is_mobile && $atts['mobile_steps'];
        
        if ($use_steps) {
            $this->render_step_wizard();
        } else {
            $this->render_standard_fields();
        }
    }
    
    /**
     * Render standard field layout
     */
    protected function render_standard_fields() {
        foreach ($this->input_fields as $field) {
            $this->render_field($field);
        }
    }
    
    /**
     * Render step wizard layout
     */
    protected function render_step_wizard() {
        $steps = $this->group_fields_into_steps();
        ?>
        <div class="uqual-step-wizard">
            <div class="uqual-step-indicators">
                <?php foreach ($steps as $index => $step) : ?>
                <div class="step-indicator <?php echo $index === 0 ? 'active' : ''; ?>" 
                     data-step="<?php echo $index; ?>">
                    <span class="step-number"><?php echo $index + 1; ?></span>
                    <span class="step-label"><?php echo esc_html($step['label']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="uqual-step-content">
                <?php foreach ($steps as $index => $step) : ?>
                <div class="step-panel <?php echo $index === 0 ? 'active' : ''; ?>" 
                     data-step="<?php echo $index; ?>">
                    <?php foreach ($step['fields'] as $field) : ?>
                        <?php $this->render_field($field); ?>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="uqual-step-navigation">
                <button type="button" class="step-prev" style="display:none;">
                    <?php _e('Previous', 'uqual-calculators'); ?>
                </button>
                <button type="button" class="step-next">
                    <?php _e('Next', 'uqual-calculators'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Group fields into steps for wizard
     */
    protected function group_fields_into_steps() {
        // Default grouping - can be overridden by child classes
        $steps = array(
            array(
                'label' => __('Personal Information', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, 0, ceil(count($this->input_fields) / 3))
            ),
            array(
                'label' => __('Financial Details', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, ceil(count($this->input_fields) / 3), ceil(count($this->input_fields) / 3))
            ),
            array(
                'label' => __('Additional Information', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, 2 * ceil(count($this->input_fields) / 3))
            )
        );
        
        return array_filter($steps, function($step) {
            return !empty($step['fields']);
        });
    }
    
    /**
     * Render individual field
     */
    protected function render_field($field) {
        $field_id = 'uqual-field-' . $field['name'];
        $field_classes = array('uqual-field', 'uqual-field-' . $field['type']);
        
        if (isset($field['class'])) {
            $field_classes[] = $field['class'];
        }
        ?>
        <div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>">
            <label for="<?php echo esc_attr($field_id); ?>">
                <?php echo esc_html($field['label']); ?>
                <?php if (isset($field['required']) && $field['required']) : ?>
                <span class="required">*</span>
                <?php endif; ?>
            </label>
            
            <?php
            switch ($field['type']) {
                case 'range':
                    $this->render_range_field($field, $field_id);
                    break;
                case 'currency':
                    $this->render_currency_field($field, $field_id);
                    break;
                case 'select':
                    $this->render_select_field($field, $field_id);
                    break;
                case 'checkbox':
                    $this->render_checkbox_field($field, $field_id);
                    break;
                case 'checkboxes':
                    $this->render_checkboxes_field($field, $field_id);
                    break;
                case 'radio':
                    $this->render_radio_field($field, $field_id);
                    break;
                default:
                    $this->render_input_field($field, $field_id);
                    break;
            }
            ?>
            
            <?php if (isset($field['help'])) : ?>
            <span class="uqual-field-help"><?php echo esc_html($field['help']); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render range/slider field
     */
    protected function render_range_field($field, $field_id) {
        $value = $field['default'] ?? $field['min'] ?? 0;
        ?>
        <div class="uqual-range-wrapper">
            <input type="range" 
                   id="<?php echo esc_attr($field_id); ?>"
                   name="<?php echo esc_attr($field['name']); ?>"
                   min="<?php echo esc_attr($field['min'] ?? 0); ?>"
                   max="<?php echo esc_attr($field['max'] ?? 100); ?>"
                   step="<?php echo esc_attr($field['step'] ?? 1); ?>"
                   value="<?php echo esc_attr($value); ?>"
                   class="uqual-range-input">
            <output class="uqual-range-value" for="<?php echo esc_attr($field_id); ?>">
                <?php echo esc_html($value); ?>
            </output>
        </div>
        <?php
    }
    
    /**
     * Render currency field
     */
    protected function render_currency_field($field, $field_id) {
        ?>
        <div class="uqual-currency-wrapper">
            <span class="currency-symbol">$</span>
            <input type="number" 
                   id="<?php echo esc_attr($field_id); ?>"
                   name="<?php echo esc_attr($field['name']); ?>"
                   min="<?php echo esc_attr($field['min'] ?? 0); ?>"
                   max="<?php echo esc_attr($field['max'] ?? ''); ?>"
                   step="<?php echo esc_attr($field['step'] ?? 0.01); ?>"
                   value="<?php echo esc_attr($field['default'] ?? ''); ?>"
                   placeholder="<?php echo esc_attr($field['placeholder'] ?? '0.00'); ?>"
                   class="uqual-currency-input">
        </div>
        <?php
    }
    
    /**
     * Render select field
     */
    protected function render_select_field($field, $field_id) {
        ?>
        <select id="<?php echo esc_attr($field_id); ?>"
                name="<?php echo esc_attr($field['name']); ?>"
                class="uqual-select-input">
            <?php foreach ($field['options'] as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>"
                    <?php selected($field['default'] ?? '', $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    /**
     * Render checkbox field
     */
    protected function render_checkbox_field($field, $field_id) {
        ?>
        <input type="checkbox" 
               id="<?php echo esc_attr($field_id); ?>"
               name="<?php echo esc_attr($field['name']); ?>"
               value="1"
               <?php checked($field['default'] ?? false, true); ?>
               class="uqual-checkbox-input">
        <?php
    }
    
    /**
     * Render checkboxes field
     */
    protected function render_checkboxes_field($field, $field_id) {
        ?>
        <div class="uqual-checkboxes-group">
            <?php foreach ($field['options'] as $value => $label) : ?>
            <label class="uqual-checkbox-label">
                <input type="checkbox" 
                       name="<?php echo esc_attr($field['name']); ?>[]"
                       value="<?php echo esc_attr($value); ?>"
                       <?php echo in_array($value, $field['default'] ?? array()) ? 'checked' : ''; ?>
                       class="uqual-checkbox-input">
                <span><?php echo esc_html($label); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Render radio field
     */
    protected function render_radio_field($field, $field_id) {
        ?>
        <div class="uqual-radio-group">
            <?php foreach ($field['options'] as $value => $label) : ?>
            <label class="uqual-radio-label">
                <input type="radio" 
                       name="<?php echo esc_attr($field['name']); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       <?php checked($field['default'] ?? '', $value); ?>
                       class="uqual-radio-input">
                <span><?php echo esc_html($label); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Render standard input field
     */
    protected function render_input_field($field, $field_id) {
        $type = $field['type'] === 'integer' ? 'number' : $field['type'];
        ?>
        <input type="<?php echo esc_attr($type); ?>" 
               id="<?php echo esc_attr($field_id); ?>"
               name="<?php echo esc_attr($field['name']); ?>"
               value="<?php echo esc_attr($field['default'] ?? ''); ?>"
               placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
               <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>
               class="uqual-input">
        <?php
    }
}