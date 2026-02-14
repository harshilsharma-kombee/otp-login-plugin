<?php

if (!defined('ABSPATH')) {
    exit;
}

class SOTL_Admin
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_menu_page()
    {
        add_options_page(
            'Secure OTP Login',
            'OTP Login',
            'manage_options',
            'secure-otp-login',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings()
    {
        register_setting('sotl_options_group', 'sotl_enable_plugin');
        register_setting('sotl_options_group', 'sotl_enabled_roles');
        register_setting('sotl_options_group', 'sotl_otp_expiry');
        register_setting('sotl_options_group', 'sotl_max_attempts');
        register_setting('sotl_options_group', 'sotl_smtp_host');
        register_setting('sotl_options_group', 'sotl_smtp_port');
        register_setting('sotl_options_group', 'sotl_smtp_encryption');
        register_setting('sotl_options_group', 'sotl_smtp_username');
        register_setting('sotl_options_group', 'sotl_smtp_password');
    }

    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('sotl_options_group');
                do_settings_sections('sotl_options_group');
                ?>
                <table class="form-table">
                    <!-- Enable Plugin -->
                    <tr valign="top">
                        <th scope="row">Enable Plugin</th>
                        <td>
                            <input type="checkbox" name="sotl_enable_plugin" value="1" <?php checked(1, get_option('sotl_enable_plugin'), true); ?> />
                        </td>
                    </tr>

                    <!-- Enabled Roles -->
                    <tr valign="top">
                        <th scope="row">Enabled Roles</th>
                        <td>
                            <?php
                            global $wp_roles;
                            $roles = $wp_roles->get_names();
                            $enabled_roles = get_option('sotl_enabled_roles', array());
                            if (!is_array($enabled_roles))
                                $enabled_roles = array();

                            foreach ($roles as $role_key => $role_name) {
                                ?>
                                <label>
                                    <input type="checkbox" name="sotl_enabled_roles[]" value="<?php echo esc_attr($role_key); ?>"
                                        <?php checked(in_array($role_key, $enabled_roles)); ?> />
                                    <?php echo esc_html($role_name); ?>
                                </label><br>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>

                    <!-- OTP Expiry -->
                    <tr valign="top">
                        <th scope="row">OTP Expiry (Minutes)</th>
                        <td>
                            <input type="number" name="sotl_otp_expiry"
                                value="<?php echo esc_attr(get_option('sotl_otp_expiry', 5)); ?>" min="1" max="60" />
                        </td>
                    </tr>

                    <!-- Max Attempts -->
                    <tr valign="top">
                        <th scope="row">Max Failed Attempts</th>
                        <td>
                            <input type="number" name="sotl_max_attempts"
                                value="<?php echo esc_attr(get_option('sotl_max_attempts', 3)); ?>" min="1" max="10" />
                        </td>
                    </tr>

                    <!-- SMTP Settings Divider -->
                    <tr>
                        <th colspan="2">
                            <h3>SMTP Settings</h3>
                        </th>
                    </tr>

                    <!-- SMTP Host -->
                    <tr valign="top">
                        <th scope="row">SMTP Host</th>
                        <td>
                            <input type="text" name="sotl_smtp_host"
                                value="<?php echo esc_attr(get_option('sotl_smtp_host')); ?>" class="regular-text"
                                placeholder="Leave empty to use default WP Mail" />
                        </td>
                    </tr>

                    <!-- SMTP Port -->
                    <tr valign="top">
                        <th scope="row">SMTP Port</th>
                        <td>
                            <input type="number" name="sotl_smtp_port"
                                value="<?php echo esc_attr(get_option('sotl_smtp_port')); ?>" class="small-text"
                                placeholder="e.g. 587" />
                        </td>
                    </tr>

                    <!-- SMTP Encryption -->
                    <tr valign="top">
                        <th scope="row">SMTP Encryption</th>
                        <td>
                            <select name="sotl_smtp_encryption">
                                <option value="none" <?php selected(get_option('sotl_smtp_encryption'), 'none'); ?>>None
                                </option>
                                <option value="ssl" <?php selected(get_option('sotl_smtp_encryption'), 'ssl'); ?>>SSL</option>
                                <option value="tls" <?php selected(get_option('sotl_smtp_encryption'), 'tls'); ?>>TLS</option>
                            </select>
                        </td>
                    </tr>

                    <!-- SMTP Username -->
                    <tr valign="top">
                        <th scope="row">SMTP Username</th>
                        <td>
                            <input type="text" name="sotl_smtp_username"
                                value="<?php echo esc_attr(get_option('sotl_smtp_username')); ?>" class="regular-text" />
                        </td>
                    </tr>

                    <!-- SMTP Password -->
                    <tr valign="top">
                        <th scope="row">SMTP Password</th>
                        <td>
                            <input type="password" name="sotl_smtp_password"
                                value="<?php echo esc_attr(get_option('sotl_smtp_password')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Attempt Logs (Last 50)</h2>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logs = $this->get_logs();
                    if ($logs) {
                        foreach ($logs as $log) {
                            $user_info = get_userdata($log->user_id);
                            $username = $user_info ? $user_info->user_login : 'Unknown (' . $log->user_id . ')';
                            ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($log->created_at); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($username); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($log->action); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($log->status); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($log->ip_address); ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="5">No logs found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function get_logs()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sotl_logs';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50");
    }
}
