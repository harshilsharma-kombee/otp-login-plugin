<?php
/**
 * Fired when the plugin is uninstalled.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'sotl_logs';

// Drop the table
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete options
delete_option('sotl_enable_plugin');
delete_option('sotl_otp_expiry');
delete_option('sotl_max_attempts');
delete_option('sotl_enabled_roles');
