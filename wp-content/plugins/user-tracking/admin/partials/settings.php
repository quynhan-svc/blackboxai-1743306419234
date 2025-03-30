<div class="wrap">
    <h1>User Tracking Settings</h1>

    <form method="post" action="options.php">
        <?php settings_fields('user_tracking_settings'); ?>
        <?php do_settings_sections('user-tracking-settings'); ?>
        <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
    </form>

    <div class="settings-advanced">
        <h2>Advanced Options</h2>
        
        <div class="card">
            <h3>Data Management</h3>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="clear_tracking_data">
                <?php wp_nonce_field('clear_tracking_data_nonce'); ?>
                <p>
                    <label>
                        <input type="checkbox" name="confirm_clear" required>
                        I understand this will permanently delete all tracking data
                    </label>
                </p>
                <button type="submit" class="button button-danger">Clear All Tracking Data</button>
            </form>
        </div>

        <div class="card">
            <h3>Blocklist Management</h3>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="update_blocklist">
                <?php wp_nonce_field('update_blocklist_nonce'); ?>
                <p>
                    <label for="blocklist">Add IPs or User Agents to Blocklist (one per line):</label><br>
                    <textarea name="blocklist" id="blocklist" rows="5" cols="50" class="large-text code"></textarea>
                </p>
                <button type="submit" class="button">Update Blocklist</button>
            </form>
        </div>

        <div class="card">
            <h3>System Information</h3>
            <table class="widefat">
                <tr>
                    <th>Last Data Purge</th>
                    <td><?php echo get_option('user_tracking_last_purge', 'Never'); ?></td>
                </tr>
                <tr>
                    <th>Tracking Active Since</th>
                    <td><?php echo get_option('user_tracking_install_date', 'Unknown'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>