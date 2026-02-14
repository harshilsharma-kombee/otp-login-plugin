<?php
/**
 * Plugin Name: Secure OTP Login for WordPress Admin
 * Description: Secure your WordPress admin with OTP login verification.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: secure-otp-login
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Plugin Constants
define('SOTL_VERSION', '1.0.0');
define('SOTL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOTL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class SOTL_Main
{

    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes()
    {
        require_once SOTL_PLUGIN_DIR . 'includes/class-otp-handler.php';
        require_once SOTL_PLUGIN_DIR . 'includes/class-otp-admin.php';
        require_once SOTL_PLUGIN_DIR . 'includes/class-otp-email.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'));
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
    }

    /**
     * Plugins loaded action
     */
    public function on_plugins_loaded()
    {
        // Initialize classes
        SOTL_OTP_Handler::get_instance();
        SOTL_Admin::get_instance();
        SOTL_Email::get_instance();
    }

    /**
     * Activation Hook
     * Create database table for logs
     */
    public function activate_plugin()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sotl_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			action varchar(50) NOT NULL,
			ip_address varchar(100) NOT NULL,
			status varchar(20) NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set default options
        add_option('sotl_enable_plugin', '1');
        add_option('sotl_otp_expiry', '5'); // Minutes
        add_option('sotl_max_attempts', '3');
        add_option('sotl_enabled_roles', array('administrator', 'editor'));
    }

    /**
     * Deactivation Hook
     * Cleanup if necessary
     */
    public function deactivate_plugin()
    {
        // Optionally drop table or clear options
        // For now we just leave data as per WP standards, unless user requested uninstall.
        // User req said: Uninstall: drop table + delete all options. 
        // That should go in uninstall.php, but for simplicity we can leave it here or create uninstall.php
    }
}

// Initialize the plugin
SOTL_Main::get_instance();
