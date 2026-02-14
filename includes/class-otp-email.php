<?php

if (!defined('ABSPATH')) {
    exit;
}

class SOTL_Email
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
        add_action('phpmailer_init', array($this, 'phpmailer_smtp'));
    }

    public function phpmailer_smtp($phpmailer)
    {
        $host = get_option('sotl_smtp_host');

        // Only configure SMTP if Host is set
        if (empty($host)) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->Port = get_option('sotl_smtp_port', '2525');

        // Encryption
        $encryption = get_option('sotl_smtp_encryption', 'none');
        if ($encryption !== 'none') {
            $phpmailer->SMTPSecure = $encryption;
        } else {
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }

        // Auth
        $username = get_option('sotl_smtp_username');
        $password = get_option('sotl_smtp_password');

        if (!empty($username) && !empty($password)) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
        } else {
            $phpmailer->SMTPAuth = false;
        }
    }

    public function send_otp($to, $otp)
    {
        $subject = 'Your Login OTP';

        ob_start();
        include SOTL_PLUGIN_DIR . 'templates/email-template.php';
        $message = ob_get_clean();

        // Set strict headers to improve deliverability
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            "From: $site_name <$admin_email>"
        );

        // Add a temporary hook to catch PHPMailer errors
        add_action('wp_mail_failed', array($this, 'log_mailer_errors'));

        $sent = wp_mail($to, $subject, $message, $headers);

        remove_action('wp_mail_failed', array($this, 'log_mailer_errors'));

        return $sent;
    }

    public function log_mailer_errors($error)
    {
        $error_message = $error->get_error_message();
        // Log to our custom table or error log
        error_log('Secure OTP Login Mail Error: ' . print_r($error, true));

        // Also log to our DB table if possible? 
        // We'd need to access SOTL_OTP_Handler's log_action but that is private and takes user_id.
        // For now, let's just use error_log which is safer for debugging.
    }
}
