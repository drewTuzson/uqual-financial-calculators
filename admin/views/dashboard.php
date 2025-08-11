<?php
/**
 * Admin Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('UQUAL Financial Calculators - Dashboard', 'uqual-calculators'); ?></h1>
    
    <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully!', 'uqual-calculators'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="uqual-admin-dashboard">
        <!-- Summary Cards -->
        <div class="uqual-summary-cards">
            <div class="summary-card">
                <div class="summary-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="summary-content">
                    <h3><?php echo number_format($analytics['summary']['total_sessions']); ?></h3>
                    <p><?php _e('Total Sessions (30 days)', 'uqual-calculators'); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="summary-content">
                    <h3><?php echo number_format($analytics['summary']['total_completions']); ?></h3>
                    <p><?php _e('Completed Calculations', 'uqual-calculators'); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <span class="dashicons dashicons-chart-pie"></span>
                </div>
                <div class="summary-content">
                    <h3><?php echo $analytics['summary']['completion_rate']; ?>%</h3>
                    <p><?php _e('Completion Rate', 'uqual-calculators'); ?></p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="summary-content">
                    <h3><?php echo ucwords(str_replace('_', ' ', $analytics['summary']['most_popular_calculator'] ?? 'N/A')); ?></h3>
                    <p><?php _e('Most Popular Calculator', 'uqual-calculators'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="uqual-quick-actions">
            <h2><?php _e('Quick Actions', 'uqual-calculators'); ?></h2>
            <div class="action-buttons">
                <a href="<?php echo admin_url('admin.php?page=uqual-calculators-settings'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Plugin Settings', 'uqual-calculators'); ?>
                </a>
                <a href="#" class="button" onclick="alert('Analytics feature coming soon! Monitor your calculator usage in the summary above.'); return false;">
                    <span class="dashicons dashicons-chart-area"></span>
                    <?php _e('View Analytics', 'uqual-calculators'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=uqual-calculators-help'); ?>" class="button">
                    <span class="dashicons dashicons-editor-help"></span>
                    <?php _e('Documentation', 'uqual-calculators'); ?>
                </a>
            </div>
        </div>
        
        <!-- Calculator Performance -->
        <div class="uqual-calculator-performance">
            <h2><?php _e('Calculator Performance', 'uqual-calculators'); ?></h2>
            <div class="performance-grid">
                <?php foreach ($analytics['calculator_performance'] as $type => $metrics) : ?>
                    <div class="performance-card">
                        <h3><?php echo esc_html(ucwords(str_replace('_', ' ', $type))); ?></h3>
                        <div class="metric">
                            <span class="metric-value"><?php echo number_format($metrics['sessions']); ?></span>
                            <span class="metric-label"><?php _e('Sessions', 'uqual-calculators'); ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo number_format($metrics['completions']); ?></span>
                            <span class="metric-label"><?php _e('Completions', 'uqual-calculators'); ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-value"><?php echo $metrics['completion_rate']; ?>%</span>
                            <span class="metric-label"><?php _e('Rate', 'uqual-calculators'); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Shortcode Reference -->
        <div class="uqual-shortcodes">
            <h2><?php _e('Shortcode Reference', 'uqual-calculators'); ?></h2>
            <p><?php _e('Use these shortcodes to embed calculators in your posts and pages:', 'uqual-calculators'); ?></p>
            
            <div class="shortcode-examples">
                <div class="shortcode-item">
                    <h4><?php _e('Loan Readiness Calculator', 'uqual-calculators'); ?></h4>
                    <code>[uqual_calculator type="loan_readiness"]</code>
                    <p><?php _e('UQUAL\'s proprietary loan readiness assessment tool', 'uqual-calculators'); ?></p>
                </div>
                
                <div class="shortcode-item">
                    <h4><?php _e('DTI Calculator', 'uqual-calculators'); ?></h4>
                    <code>[uqual_calculator type="dti"]</code>
                    <p><?php _e('Advanced debt-to-income ratio calculator', 'uqual-calculators'); ?></p>
                </div>
                
                <div class="shortcode-item">
                    <h4><?php _e('Affordability Calculator', 'uqual-calculators'); ?></h4>
                    <code>[uqual_calculator type="affordability"]</code>
                    <p><?php _e('Mortgage affordability calculator with scenarios', 'uqual-calculators'); ?></p>
                </div>
                
                <div class="shortcode-item">
                    <h4><?php _e('Credit Simulator', 'uqual-calculators'); ?></h4>
                    <code>[uqual_calculator type="credit_simulator"]</code>
                    <p><?php _e('Credit score improvement simulator', 'uqual-calculators'); ?></p>
                </div>
                
                <div class="shortcode-item">
                    <h4><?php _e('Savings Calculator', 'uqual-calculators'); ?></h4>
                    <code>[uqual_calculator type="savings"]</code>
                    <p><?php _e('Down payment savings calculator', 'uqual-calculators'); ?></p>
                </div>
            </div>
            
            <h3><?php _e('Shortcode Parameters', 'uqual-calculators'); ?></h3>
            <div class="parameters-table">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Parameter', 'uqual-calculators'); ?></th>
                            <th><?php _e('Default', 'uqual-calculators'); ?></th>
                            <th><?php _e('Description', 'uqual-calculators'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>type</code></td>
                            <td>loan_readiness</td>
                            <td><?php _e('Calculator type (required)', 'uqual-calculators'); ?></td>
                        </tr>
                        <tr>
                            <td><code>theme</code></td>
                            <td>light</td>
                            <td><?php _e('Theme (light or dark)', 'uqual-calculators'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_intro</code></td>
                            <td>true</td>
                            <td><?php _e('Show calculator introduction', 'uqual-calculators'); ?></td>
                        </tr>
                        <tr>
                            <td><code>mobile_steps</code></td>
                            <td>true</td>
                            <td><?php _e('Use step wizard on mobile', 'uqual-calculators'); ?></td>
                        </tr>
                        <tr>
                            <td><code>cta_text</code></td>
                            <td>Get Help</td>
                            <td><?php _e('Custom CTA button text', 'uqual-calculators'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <?php if (!empty($analytics['daily_data'])) : ?>
        <div class="uqual-recent-activity">
            <h2><?php _e('Recent Activity', 'uqual-calculators'); ?></h2>
            <div class="activity-chart">
                <canvas id="activity-chart" width="800" height="300"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.uqual-admin-dashboard {
    max-width: 1200px;
}

.uqual-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.summary-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.summary-icon {
    width: 50px;
    height: 50px;
    background: #2271b1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.summary-content h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    color: #2271b1;
}

.summary-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.uqual-quick-actions {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-buttons .button {
    display: flex;
    align-items: center;
    gap: 8px;
}

.uqual-calculator-performance {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.performance-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    text-align: center;
}

.performance-card h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}

.metric {
    margin: 8px 0;
}

.metric-value {
    display: block;
    font-size: 20px;
    font-weight: 600;
    color: #2271b1;
}

.metric-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.uqual-shortcodes {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.shortcode-examples {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.shortcode-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
}

.shortcode-item h4 {
    margin: 0 0 8px 0;
    color: #333;
}

.shortcode-item code {
    background: #2271b1;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 13px;
    display: block;
    margin: 8px 0;
}

.shortcode-item p {
    margin: 8px 0 0 0;
    color: #666;
    font-size: 14px;
}

.parameters-table {
    margin-top: 15px;
}

.parameters-table code {
    background: #f1f1f1;
    padding: 2px 6px;
    border-radius: 3px;
}

.uqual-recent-activity {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.activity-chart {
    margin-top: 15px;
    text-align: center;
}

@media (max-width: 768px) {
    .uqual-summary-cards {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .shortcode-examples {
        grid-template-columns: 1fr;
    }
}
</style>

<?php if (!empty($analytics['daily_data'])) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx = document.getElementById('activity-chart').getContext('2d');
        const data = <?php echo json_encode($analytics['daily_data']); ?>;
        
        const labels = data.map(item => item.date);
        const sessions = data.map(item => parseInt(item.total_sessions));
        const completions = data.map(item => parseInt(item.completed_sessions));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php _e('Sessions', 'uqual-calculators'); ?>',
                    data: sessions,
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                    tension: 0.4
                }, {
                    label: '<?php _e('Completions', 'uqual-calculators'); ?>',
                    data: completions,
                    borderColor: '#00a32a',
                    backgroundColor: 'rgba(0, 163, 42, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>