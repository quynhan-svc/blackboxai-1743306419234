<?php
namespace UserTracking;

class Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_pages']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function add_admin_pages() {
        add_menu_page(
            'User Tracking',
            'User Tracking',
            'manage_options',
            'user-tracking',
            [__CLASS__, 'render_dashboard'],
            'dashicons-chart-line',
            30
        );

        add_submenu_page(
            'user-tracking',
            'Tracking Dashboard',
            'Dashboard',
            'manage_options',
            'user-tracking',
            [__CLASS__, 'render_dashboard']
        );

        add_submenu_page(
            'user-tracking',
            'Fraud Detection',
            'Fraud Logs',
            'manage_options',
            'user-tracking-fraud',
            [__CLASS__, 'render_fraud_logs']
        );

        add_submenu_page(
            'user-tracking',
            'Settings',
            'Settings',
            'manage_options',
            'user-tracking-settings',
            [__CLASS__, 'render_settings']
        );
    }

    public static function register_settings() {
        register_setting('user_tracking_settings', 'user_tracking_settings');

        add_settings_section(
            'user_tracking_alert_settings',
            'Alert Settings',
            null,
            'user-tracking-settings'
        );

        add_settings_field(
            'email_alerts',
            'Enable Email Alerts',
            [__CLASS__, 'render_checkbox_field'],
            'user-tracking-settings',
            'user_tracking_alert_settings',
            [
                'name' => 'email_alerts',
                'label' => 'Send email alerts for detected fraud'
            ]
        );

        add_settings_field(
            'alert_email',
            'Alert Email Address',
            [__CLASS__, 'render_text_field'],
            'user-tracking-settings',
            'user_tracking_alert_settings',
            [
                'name' => 'alert_email',
                'placeholder' => 'admin@example.com'
            ]
        );

        add_settings_field(
            'telegram_bot_token',
            'Telegram Bot Token',
            [__CLASS__, 'render_text_field'],
            'user-tracking-settings',
            'user_tracking_alert_settings',
            [
                'name' => 'telegram_bot_token',
                'placeholder' => '123456789:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'
            ]
        );

        add_settings_field(
            'telegram_chat_id',
            'Telegram Chat ID',
            [__CLASS__, 'render_text_field'],
            'user-tracking-settings',
            'user_tracking_alert_settings',
            [
                'name' => 'telegram_chat_id',
                'placeholder' => '-123456789'
            ]
        );
    }

    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'user-tracking') === false) return;

        // Enqueue Chart.js
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '3.7.1',
            true
        );

        // Enqueue admin CSS
        wp_enqueue_style(
            'user-tracking-admin',
            USER_TRACKING_PLUGIN_URL . 'assets/css/admin.css',
            [],
            USER_TRACKING_VERSION
        );

        // Enqueue admin JS
        wp_enqueue_script(
            'user-tracking-admin',
            USER_TRACKING_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'chart-js'],
            USER_TRACKING_VERSION,
            true
        );
    }

    public static function render_dashboard() {
        global $wpdb;

        // Get stats with optimized queries
        $stats = [
            'total_sessions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}user_tracking_sessions LIMIT 1"),
            'today_sessions' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}user_tracking_sessions WHERE DATE(created_at) = %s LIMIT 1", 
                current_time('mysql', 1)
            )),
            'fraud_attempts' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}user_tracking_fraud_logs LIMIT 1"),
            'top_countries' => $wpdb->get_results(
                "SELECT country, COUNT(*) as count 
                 FROM {$wpdb->prefix}user_tracking_sessions 
                 WHERE country != '' 
                 GROUP BY country 
                 ORDER BY count DESC 
                 LIMIT 5"
            )
        ];

        // Get chart data with date range limit
        $chart_data = $wpdb->get_results(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM {$wpdb->prefix}user_tracking_sessions 
             WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC
             LIMIT 30"
        );

        // Add caching layer
        $cache_key = 'user_tracking_dashboard_data';
        $cached_data = get_transient($cache_key);
        
        if (false === $cached_data) {
            $cached_data = [
                'stats' => $stats,
                'chart_data' => $chart_data
            ];
            set_transient($cache_key, $cached_data, HOUR_IN_SECONDS);
        } else {
            $stats = $cached_data['stats'];
            $chart_data = $cached_data['chart_data'];
        }

        include USER_TRACKING_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }

    public static function render_fraud_logs() {
        global $wpdb;

        $fraud_logs = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}user_tracking_fraud_logs 
            ORDER BY created_at DESC 
            LIMIT 100
        ");

        include USER_TRACKING_PLUGIN_DIR . 'admin/partials/fraud-logs.php';
    }

    public static function render_settings() {
        include USER_TRACKING_PLUGIN_DIR . 'admin/partials/settings.php';
    }

    public static function render_text_field($args) {
        $options = get_option('user_tracking_settings');
        $value = $options[$args['name']] ?? '';
        ?>
        <input type="text" 
               name="user_tracking_settings[<?php echo esc_attr($args['name']); ?>]" 
               value="<?php echo esc_attr($value); ?>"
               placeholder="<?php echo esc_attr($args['placeholder'] ?? ''); ?>"
               class="regular-text">
        <?php
    }

    public static function render_checkbox_field($args) {
        $options = get_option('user_tracking_settings');
        $checked = isset($options[$args['name']]) ? checked(1, $options[$args['name']], false) : '';
        ?>
        <label>
            <input type="checkbox" 
                   name="user_tracking_settings[<?php echo esc_attr($args['name']); ?>]" 
                   value="1" <?php echo $checked; ?>>
            <?php echo esc_html($args['label']); ?>
        </label>
        <?php
    }
}