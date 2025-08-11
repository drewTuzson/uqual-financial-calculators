<?php
/**
 * Database Handler Class
 * 
 * Handles all database operations for the UQUAL Financial Calculators plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Database_Handler {
    
    /**
     * Create database tables
     */
    public static function create_tables() {
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
        
        // Update database version
        update_option('uqual_calc_db_version', UQUAL_CALC_VERSION);
    }
    
    /**
     * Drop database tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $wpdb->query("DROP TABLE IF EXISTS " . UQUAL_CALC_TABLE_EVENTS);
        $wpdb->query("DROP TABLE IF EXISTS " . UQUAL_CALC_TABLE_INPUTS);
        $wpdb->query("DROP TABLE IF EXISTS " . UQUAL_CALC_TABLE_SESSIONS);
    }
    
    /**
     * Create or get session
     */
    public static function create_session($calculator_type, $session_id = null) {
        global $wpdb;
        
        if (!$session_id) {
            $session_id = self::generate_session_id();
        }
        
        $user_ip = self::get_user_ip();
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $page_url = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500);
        
        $wpdb->insert(
            UQUAL_CALC_TABLE_SESSIONS,
            array(
                'session_id' => $session_id,
                'calculator_type' => $calculator_type,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'page_url' => $page_url,
                'started_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $session_id;
    }
    
    /**
     * Update session completion
     */
    public static function complete_session($session_id, $completion_rate = 100.00) {
        global $wpdb;
        
        $wpdb->update(
            UQUAL_CALC_TABLE_SESSIONS,
            array(
                'completed_at' => current_time('mysql'),
                'completion_rate' => $completion_rate
            ),
            array('session_id' => $session_id),
            array('%s', '%f'),
            array('%s')
        );
    }
    
    /**
     * Save calculator input and results
     */
    public static function save_calculation($session_id, $calculator_type, $input_data, $results = null) {
        global $wpdb;
        
        // Anonymize sensitive data
        $anonymized_input = self::anonymize_data($input_data);
        
        $wpdb->insert(
            UQUAL_CALC_TABLE_INPUTS,
            array(
                'session_id' => $session_id,
                'calculator_type' => $calculator_type,
                'input_data' => wp_json_encode($anonymized_input),
                'calculated_results' => $results ? wp_json_encode($results) : null,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Track event
     */
    public static function track_event($session_id, $event_type, $event_data = array()) {
        global $wpdb;
        
        $wpdb->insert(
            UQUAL_CALC_TABLE_EVENTS,
            array(
                'session_id' => $session_id,
                'event_type' => $event_type,
                'event_data' => wp_json_encode($event_data),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get session data
     */
    public static function get_session($session_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . UQUAL_CALC_TABLE_SESSIONS . " WHERE session_id = %s",
                $session_id
            )
        );
    }
    
    /**
     * Get calculator analytics
     */
    public static function get_analytics($calculator_type = null, $date_from = null, $date_to = null) {
        global $wpdb;
        
        $where_clauses = array();
        $where_values = array();
        
        if ($calculator_type) {
            $where_clauses[] = "calculator_type = %s";
            $where_values[] = $calculator_type;
        }
        
        if ($date_from) {
            $where_clauses[] = "started_at >= %s";
            $where_values[] = $date_from;
        }
        
        if ($date_to) {
            $where_clauses[] = "started_at <= %s";
            $where_values[] = $date_to;
        }
        
        $where = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";
        
        $query = "SELECT 
            calculator_type,
            COUNT(*) as total_sessions,
            COUNT(completed_at) as completed_sessions,
            AVG(completion_rate) as avg_completion_rate,
            DATE(started_at) as date
        FROM " . UQUAL_CALC_TABLE_SESSIONS . " 
        $where
        GROUP BY calculator_type, DATE(started_at)
        ORDER BY date DESC";
        
        if ($where_values) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get popular input ranges
     */
    public static function get_popular_inputs($calculator_type, $limit = 100) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT input_data 
            FROM " . UQUAL_CALC_TABLE_INPUTS . " 
            WHERE calculator_type = %s 
            ORDER BY created_at DESC 
            LIMIT %d",
            $calculator_type,
            $limit
        );
        
        $results = $wpdb->get_results($query);
        
        $aggregated = array();
        foreach ($results as $row) {
            $data = json_decode($row->input_data, true);
            foreach ($data as $key => $value) {
                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = array();
                }
                $aggregated[$key][] = $value;
            }
        }
        
        // Calculate ranges and averages
        $stats = array();
        foreach ($aggregated as $key => $values) {
            if (is_numeric($values[0])) {
                $stats[$key] = array(
                    'min' => min($values),
                    'max' => max($values),
                    'avg' => array_sum($values) / count($values),
                    'median' => self::calculate_median($values)
                );
            }
        }
        
        return $stats;
    }
    
    /**
     * Get conversion metrics
     */
    public static function get_conversion_metrics($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where = "";
        $where_values = array();
        
        if ($date_from && $date_to) {
            $where = "WHERE created_at BETWEEN %s AND %s";
            $where_values = array($date_from, $date_to);
        }
        
        $query = "SELECT 
            COUNT(DISTINCT e.session_id) as cta_clicks,
            COUNT(DISTINCT s.session_id) as total_sessions,
            (COUNT(DISTINCT e.session_id) / COUNT(DISTINCT s.session_id)) * 100 as conversion_rate
        FROM " . UQUAL_CALC_TABLE_SESSIONS . " s
        LEFT JOIN " . UQUAL_CALC_TABLE_EVENTS . " e 
            ON s.session_id = e.session_id 
            AND e.event_type = 'cta_click'
        $where";
        
        if ($where_values) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_row($query);
    }
    
    /**
     * Cleanup old sessions
     */
    public static function cleanup_old_sessions($days_to_keep = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days_to_keep days"));
        
        // Get session IDs to delete
        $sessions_to_delete = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT session_id FROM " . UQUAL_CALC_TABLE_SESSIONS . " WHERE started_at < %s",
                $cutoff_date
            )
        );
        
        if (!empty($sessions_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($sessions_to_delete), '%s'));
            
            // Delete events
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM " . UQUAL_CALC_TABLE_EVENTS . " WHERE session_id IN ($placeholders)",
                    $sessions_to_delete
                )
            );
            
            // Delete inputs
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM " . UQUAL_CALC_TABLE_INPUTS . " WHERE session_id IN ($placeholders)",
                    $sessions_to_delete
                )
            );
            
            // Delete sessions
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM " . UQUAL_CALC_TABLE_SESSIONS . " WHERE session_id IN ($placeholders)",
                    $sessions_to_delete
                )
            );
        }
        
        return count($sessions_to_delete);
    }
    
    /**
     * Generate unique session ID
     */
    private static function generate_session_id() {
        return wp_hash(uniqid('uqual_', true) . wp_rand());
    }
    
    /**
     * Get user IP address
     */
    private static function get_user_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    // Anonymize IP (remove last octet for IPv4, last 80 bits for IPv6)
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $parts = explode('.', $ip);
                        $parts[3] = '0';
                        return implode('.', $parts);
                    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        $parts = explode(':', $ip);
                        for ($i = 4; $i < 8; $i++) {
                            $parts[$i] = '0';
                        }
                        return implode(':', $parts);
                    }
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Anonymize sensitive data
     */
    private static function anonymize_data($data) {
        $anonymized = array();
        
        foreach ($data as $key => $value) {
            // Round financial values to ranges
            if (in_array($key, array('income', 'monthlyIncome', 'grossIncome', 'downPayment', 'homePrice'))) {
                $anonymized[$key . '_range'] = self::get_value_range($value);
            }
            // Keep credit score ranges
            elseif ($key === 'creditScore') {
                $anonymized['creditScore_range'] = self::get_credit_score_range($value);
            }
            // Keep ratios and percentages as-is
            elseif (in_array($key, array('dtiRatio', 'interestRate', 'downPaymentPercent'))) {
                $anonymized[$key] = round($value, 2);
            }
            // Skip personal identifiable information
            elseif (!in_array($key, array('name', 'email', 'phone', 'address'))) {
                $anonymized[$key] = $value;
            }
        }
        
        return $anonymized;
    }
    
    /**
     * Get value range for anonymization
     */
    private static function get_value_range($value) {
        $value = floatval($value);
        
        if ($value < 1000) return '0-1k';
        elseif ($value < 5000) return '1k-5k';
        elseif ($value < 10000) return '5k-10k';
        elseif ($value < 25000) return '10k-25k';
        elseif ($value < 50000) return '25k-50k';
        elseif ($value < 100000) return '50k-100k';
        elseif ($value < 250000) return '100k-250k';
        elseif ($value < 500000) return '250k-500k';
        elseif ($value < 1000000) return '500k-1M';
        else return '1M+';
    }
    
    /**
     * Get credit score range
     */
    private static function get_credit_score_range($score) {
        $score = intval($score);
        
        if ($score < 580) return 'Poor (300-579)';
        elseif ($score < 670) return 'Fair (580-669)';
        elseif ($score < 740) return 'Good (670-739)';
        elseif ($score < 800) return 'Very Good (740-799)';
        else return 'Excellent (800-850)';
    }
    
    /**
     * Calculate median value
     */
    private static function calculate_median($values) {
        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);
        
        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle] + $values[$middle + 1]) / 2;
        }
    }
}