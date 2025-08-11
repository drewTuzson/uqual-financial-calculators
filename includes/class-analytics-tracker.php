<?php
/**
 * Analytics Tracker Class
 * 
 * Handles all analytics and tracking operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Analytics_Tracker {
    
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
     * Initialize hooks
     */
    private function init_hooks() {
        // Track page views with calculators
        add_action('wp_footer', array($this, 'track_page_view'));
        
        // Add Google Analytics integration
        add_action('wp_head', array($this, 'add_ga_tracking_code'));
    }
    
    /**
     * Track calculator calculation
     */
    public function track_calculation($calculator_type, $input_data, $results) {
        if (get_option('uqual_calc_enable_analytics', 'yes') !== 'yes') {
            return;
        }
        
        $session_id = $this->get_current_session_id();
        
        // Save calculation data
        UQUAL_Database_Handler::save_calculation(
            $session_id,
            $calculator_type,
            $input_data,
            $results
        );
        
        // Track calculation event
        $this->track_event($session_id, 'calculation_complete', array(
            'calculator_type' => $calculator_type,
            'score' => isset($results['score']) ? $results['score'] : null,
            'result_classification' => isset($results['classification']) ? $results['classification']['label'] : null
        ));
        
        // Send to Google Analytics if configured
        $this->send_ga_event('Calculator', 'Calculate', $calculator_type);
    }
    
    /**
     * Track event
     */
    public function track_event($session_id, $event_type, $event_data = array()) {
        if (get_option('uqual_calc_enable_analytics', 'yes') !== 'yes') {
            return;
        }
        
        UQUAL_Database_Handler::track_event($session_id, $event_type, $event_data);
        
        // Track specific events in GA
        switch ($event_type) {
            case 'cta_click':
                $this->send_ga_event('Calculator', 'CTA Click', $event_data['calculator_type'] ?? '');
                break;
                
            case 'form_start':
                $this->send_ga_event('Calculator', 'Form Start', $event_data['calculator_type'] ?? '');
                break;
                
            case 'form_abandon':
                $this->send_ga_event('Calculator', 'Form Abandon', $event_data['calculator_type'] ?? '');
                break;
                
            case 'input_change':
                // Don't track every input change in GA to avoid hitting limits
                break;
        }
    }
    
    /**
     * Track conversion (CTA click)
     */
    public function track_conversion($calculator_type, $cta_type = 'default') {
        $session_id = $this->get_current_session_id();
        
        $this->track_event($session_id, 'cta_click', array(
            'calculator_type' => $calculator_type,
            'cta_type' => $cta_type,
            'timestamp' => current_time('mysql')
        ));
        
        // Mark session as converted
        UQUAL_Database_Handler::complete_session($session_id, 100);
        
        // Send conversion to GA
        $this->send_ga_event('Calculator', 'Conversion', $calculator_type, 1);
    }
    
    /**
     * Get analytics data for admin dashboard
     */
    public function get_analytics_data($date_from = null, $date_to = null) {
        if (!$date_from) {
            $date_from = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$date_to) {
            $date_to = date('Y-m-d');
        }
        
        // Get basic analytics
        $analytics = UQUAL_Database_Handler::get_analytics(null, $date_from, $date_to);
        
        // Get conversion metrics
        $conversions = UQUAL_Database_Handler::get_conversion_metrics($date_from, $date_to);
        
        // Get popular input ranges for each calculator
        $popular_inputs = array();
        $calculator_types = UQUAL_Calculator_Manager::get_instance()->get_calculator_types();
        
        foreach ($calculator_types as $type) {
            $popular_inputs[$type] = UQUAL_Database_Handler::get_popular_inputs($type, 100);
        }
        
        return array(
            'summary' => $this->calculate_summary_metrics($analytics),
            'daily_data' => $analytics,
            'conversions' => $conversions,
            'popular_inputs' => $popular_inputs,
            'calculator_performance' => $this->calculate_calculator_performance($analytics)
        );
    }
    
    /**
     * Calculate summary metrics
     */
    private function calculate_summary_metrics($analytics) {
        $total_sessions = 0;
        $total_completions = 0;
        $calculator_counts = array();
        
        foreach ($analytics as $row) {
            $total_sessions += $row->total_sessions;
            $total_completions += $row->completed_sessions;
            
            if (!isset($calculator_counts[$row->calculator_type])) {
                $calculator_counts[$row->calculator_type] = 0;
            }
            $calculator_counts[$row->calculator_type] += $row->total_sessions;
        }
        
        $completion_rate = $total_sessions > 0 ? 
            ($total_completions / $total_sessions) * 100 : 0;
        
        // Find most popular calculator
        arsort($calculator_counts);
        $most_popular = key($calculator_counts);
        
        return array(
            'total_sessions' => $total_sessions,
            'total_completions' => $total_completions,
            'completion_rate' => round($completion_rate, 2),
            'most_popular_calculator' => $most_popular,
            'calculator_usage' => $calculator_counts
        );
    }
    
    /**
     * Calculate calculator performance metrics
     */
    private function calculate_calculator_performance($analytics) {
        $performance = array();
        
        foreach ($analytics as $row) {
            if (!isset($performance[$row->calculator_type])) {
                $performance[$row->calculator_type] = array(
                    'sessions' => 0,
                    'completions' => 0,
                    'completion_rate' => 0
                );
            }
            
            $performance[$row->calculator_type]['sessions'] += $row->total_sessions;
            $performance[$row->calculator_type]['completions'] += $row->completed_sessions;
        }
        
        // Calculate completion rates
        foreach ($performance as $type => &$metrics) {
            if ($metrics['sessions'] > 0) {
                $metrics['completion_rate'] = round(
                    ($metrics['completions'] / $metrics['sessions']) * 100,
                    2
                );
            }
        }
        
        return $performance;
    }
    
    /**
     * Track page view
     */
    public function track_page_view() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        // Check if page contains calculator shortcode
        if (has_shortcode($post->post_content, 'uqual_calculator') ||
            has_shortcode($post->post_content, 'uqual_loan_readiness') ||
            has_shortcode($post->post_content, 'uqual_dti') ||
            has_shortcode($post->post_content, 'uqual_affordability') ||
            has_shortcode($post->post_content, 'uqual_credit_simulator') ||
            has_shortcode($post->post_content, 'uqual_savings')) {
            
            // Track page view with calculator
            $this->send_ga_event('Calculator', 'Page View', get_the_title());
        }
    }
    
    /**
     * Add Google Analytics tracking code
     */
    public function add_ga_tracking_code() {
        $ga_id = get_option('uqual_calc_ga_tracking_id', '');
        
        if (empty($ga_id)) {
            return;
        }
        ?>
        <!-- UQUAL Calculator Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($ga_id); ?>');
            
            // Custom calculator tracking functions
            window.uqualTrackEvent = function(category, action, label, value) {
                if (typeof gtag !== 'undefined') {
                    gtag('event', action, {
                        'event_category': category,
                        'event_label': label,
                        'value': value
                    });
                }
            };
        </script>
        <?php
    }
    
    /**
     * Send event to Google Analytics
     */
    private function send_ga_event($category, $action, $label = '', $value = null) {
        ?>
        <script>
            if (typeof uqualTrackEvent !== 'undefined') {
                uqualTrackEvent('<?php echo esc_js($category); ?>', 
                               '<?php echo esc_js($action); ?>', 
                               '<?php echo esc_js($label); ?>', 
                               <?php echo $value ? intval($value) : 'null'; ?>);
            }
        </script>
        <?php
    }
    
    /**
     * Get current session ID
     */
    private function get_current_session_id() {
        // This would typically be retrieved from JavaScript or cookies
        // For now, generate a new one
        return wp_hash(uniqid('session_', true));
    }
    
    /**
     * Export analytics data
     */
    public function export_analytics($format = 'csv', $date_from = null, $date_to = null) {
        $data = $this->get_analytics_data($date_from, $date_to);
        
        switch ($format) {
            case 'csv':
                return $this->export_to_csv($data);
            case 'json':
                return wp_json_encode($data, JSON_PRETTY_PRINT);
            default:
                return $data;
        }
    }
    
    /**
     * Export data to CSV
     */
    private function export_to_csv($data) {
        $csv = array();
        
        // Headers
        $csv[] = array(
            'Date',
            'Calculator Type',
            'Total Sessions',
            'Completed Sessions',
            'Completion Rate'
        );
        
        // Data rows
        foreach ($data['daily_data'] as $row) {
            $csv[] = array(
                $row->date,
                $row->calculator_type,
                $row->total_sessions,
                $row->completed_sessions,
                $row->avg_completion_rate . '%'
            );
        }
        
        // Convert to CSV string
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(',', array_map('esc_attr', $row)) . "\n";
        }
        
        return $output;
    }
    
    /**
     * Get user insights
     */
    public function get_user_insights($session_id) {
        $session = UQUAL_Database_Handler::get_session($session_id);
        
        if (!$session) {
            return null;
        }
        
        global $wpdb;
        
        // Get all events for this session
        $events = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . UQUAL_CALC_TABLE_EVENTS . " 
                WHERE session_id = %s 
                ORDER BY created_at ASC",
                $session_id
            )
        );
        
        // Get calculation results
        $calculations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . UQUAL_CALC_TABLE_INPUTS . " 
                WHERE session_id = %s 
                ORDER BY created_at DESC 
                LIMIT 1",
                $session_id
            )
        );
        
        return array(
            'session' => $session,
            'events' => $events,
            'calculation' => $calculations ? $calculations[0] : null,
            'engagement_score' => $this->calculate_engagement_score($events),
            'intent_level' => $this->determine_intent_level($events, $calculations)
        );
    }
    
    /**
     * Calculate engagement score
     */
    private function calculate_engagement_score($events) {
        $score = 0;
        $event_weights = array(
            'form_start' => 10,
            'input_change' => 2,
            'calculation_complete' => 30,
            'cta_click' => 50,
            'result_view' => 20
        );
        
        foreach ($events as $event) {
            if (isset($event_weights[$event->event_type])) {
                $score += $event_weights[$event->event_type];
            }
        }
        
        return min(100, $score);
    }
    
    /**
     * Determine user intent level
     */
    private function determine_intent_level($events, $calculations) {
        $has_calculation = !empty($calculations);
        $has_cta_click = false;
        $interaction_count = count($events);
        
        foreach ($events as $event) {
            if ($event->event_type === 'cta_click') {
                $has_cta_click = true;
                break;
            }
        }
        
        if ($has_cta_click) {
            return 'high';
        } elseif ($has_calculation && $interaction_count > 5) {
            return 'medium';
        } elseif ($interaction_count > 0) {
            return 'low';
        } else {
            return 'none';
        }
    }
}