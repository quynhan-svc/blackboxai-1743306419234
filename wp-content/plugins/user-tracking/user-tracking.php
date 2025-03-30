<?php
/**
 * Plugin Name: User Tracking for Google Ads
 * Description: Tracks user behavior from Google Ads and detects fraudulent clicks.
 * Version: 1.0
 * Author: Your Name
 */

defined('ABSPATH') or die('Direct access not allowed!');

// Define plugin constants
define('USER_TRACKING_VERSION', '1.0');
define('USER_TRACKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('USER_TRACKING_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once USER_TRACKING_PLUGIN_DIR . 'includes/class-database.php';
require_once USER_TRACKING_PLUGIN_DIR . 'includes/class-tracker.php';
require_once USER_TRACKING_PLUGIN_DIR . 'includes/class-fraud-detector.php';
require_once USER_TRACKING_PLUGIN_DIR . 'admin/class-admin.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, ['UserTracking\Database', 'install']);
register_deactivation_hook(__FILE__, ['UserTracking\Database', 'uninstall']);

// Initialize the plugin
add_action('plugins_loaded', function() {
    UserTracking\Tracker::init();
    UserTracking\FraudDetector::init();
    
    if (is_admin()) {
        UserTracking\Admin::init();
    }
});