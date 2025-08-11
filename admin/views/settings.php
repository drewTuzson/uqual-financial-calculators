<?php
/**
 * Admin Settings View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('UQUAL Calculators - Settings', 'uqual-calculators'); ?></h1>
    
    <?php settings_errors(); ?>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('uqual_calculators_settings');
        do_settings_sections('uqual-calculators-settings');
        submit_button();
        ?>
    </form>
    
    <div class="uqual-settings-help">
        <h2><?php _e('Need Help?', 'uqual-calculators'); ?></h2>
        <p><?php _e('Visit our documentation page for detailed setup instructions and troubleshooting tips.', 'uqual-calculators'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=uqual-calculators-help'); ?>" class="button">
            <?php _e('View Documentation', 'uqual-calculators'); ?>
        </a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize color picker
    if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.color-picker').wpColorPicker({
            defaultColor: false,
            change: function(event, ui) {
                // Optional: Add real-time preview here
                var element = $(this);
                var newColor = ui.color.toString();
                console.log('Color changed to: ' + newColor);
            },
            clear: function() {
                // Handle color clear
                console.log('Color cleared');
            }
        });
    } else {
        console.warn('WordPress Color Picker not available');
    }
    
    // Show save confirmation
    $('form').on('submit', function() {
        $(this).find('input[type="submit"]').val('Saving...');
    });
});
</script>

<style>
/* Ensure color picker displays properly */
.wp-picker-container {
    display: inline-block;
}
.color-picker.regular-text {
    width: 100px !important;
}
</style>