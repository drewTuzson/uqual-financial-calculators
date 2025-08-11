<?php
/**
 * Installation Check Script
 * Upload this file to your server to verify file structure before installing the main plugin
 */

// Prevent direct access
if (!defined('ABSPATH') && !isset($_GET['standalone'])) {
    exit('Access denied. Add ?standalone=1 to run outside WordPress.');
}

$plugin_dir = __DIR__;
$required_files = array(
    'uqual-calculators.php' => 'Main plugin file',
    'includes/class-database-handler.php' => 'Database Handler',
    'includes/class-base-calculator.php' => 'Base Calculator',
    'includes/class-calculator-manager.php' => 'Calculator Manager',
    'includes/class-analytics-tracker.php' => 'Analytics Tracker',
    'includes/class-shortcode-handler.php' => 'Shortcode Handler',
    'admin/class-admin-interface.php' => 'Admin Interface',
    'admin/views/dashboard.php' => 'Dashboard View',
    'admin/views/settings.php' => 'Settings View',
    'admin/views/help.php' => 'Help View'
);

?>
<!DOCTYPE html>
<html>
<head>
    <title>UQUAL Calculators - Installation Check</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 40px; }
        .check-item { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .exists { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .missing { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #e2f3ff; padding: 20px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🧮 UQUAL Financial Calculators - Installation Check</h1>
    
    <div class="info">
        <h3>📂 File Structure Verification</h3>
        <p>This script verifies that all required plugin files are properly uploaded to your server.</p>
        <p><strong>Plugin Directory:</strong> <?php echo htmlspecialchars($plugin_dir); ?></p>
    </div>
    
    <h2>📋 Required Files Check</h2>
    
    <?php
    $all_exists = true;
    $existing_files = array();
    $missing_files = array();
    
    foreach ($required_files as $file => $description) {
        $file_path = $plugin_dir . '/' . $file;
        $exists = file_exists($file_path);
        $class = $exists ? 'exists' : 'missing';
        $icon = $exists ? '✅' : '❌';
        
        if ($exists) {
            $existing_files[] = $file;
        } else {
            $missing_files[] = $file;
            $all_exists = false;
        }
        
        echo '<div class="check-item ' . $class . '">';
        echo $icon . ' <strong>' . htmlspecialchars($file) . '</strong> - ' . htmlspecialchars($description);
        if ($exists) {
            $size = filesize($file_path);
            echo ' <small>(' . number_format($size) . ' bytes)</small>';
        }
        echo '</div>';
    }
    ?>
    
    <div class="info">
        <h3>📊 Summary</h3>
        <p><strong>Total Files:</strong> <?php echo count($required_files); ?></p>
        <p><strong>Existing:</strong> <?php echo count($existing_files); ?></p>
        <p><strong>Missing:</strong> <?php echo count($missing_files); ?></p>
        
        <?php if ($all_exists): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <strong>✅ All Files Present!</strong><br>
                You can now safely activate the UQUAL Financial Calculators plugin.
            </div>
        <?php else: ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <strong>❌ Missing Files Detected</strong><br>
                Please upload the missing files before activating the plugin.
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!$all_exists): ?>
    <h2>🔧 Next Steps</h2>
    <ol>
        <li><strong>Upload Missing Files:</strong> Upload the missing files listed above to your server</li>
        <li><strong>Check File Structure:</strong> Ensure files are in the correct subdirectories (includes/, admin/, admin/views/)</li>
        <li><strong>Verify Permissions:</strong> Make sure files have read permissions (644)</li>
        <li><strong>Re-run Check:</strong> Refresh this page to verify all files are present</li>
        <li><strong>Activate Plugin:</strong> Once all files are present, activate the plugin in WordPress admin</li>
    </ol>
    <?php endif; ?>
    
    <div class="info">
        <h3>🐛 Troubleshooting</h3>
        <ul>
            <li><strong>File Upload Issues:</strong> Use FTP/SFTP client or hosting file manager</li>
            <li><strong>Permission Issues:</strong> Set file permissions to 644 for PHP files</li>
            <li><strong>Directory Structure:</strong> Maintain the exact folder structure shown above</li>
            <li><strong>File Corruption:</strong> Re-upload any files that seem corrupted</li>
        </ul>
    </div>
    
    <hr>
    <p><small>UQUAL Financial Calculators v1.0.0 | Installation Verification Script</small></p>
</body>
</html>