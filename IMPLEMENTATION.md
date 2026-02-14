# Secure OTP Login - Implementation Details

## Class Structure
All classes and functions use the prefix `SOTL_` (Secure OTP Login).

### 1. `SOTL_Main` (secure-otp-login.php)
-   Singleton pattern.
-   Initializes the plugin.
-   Hooks:
    -   `plugins_loaded`: Load text domain, init classes.
    -   `register_activation_hook`: Create DB table `wp_sotl_logs`.
    -   `register_deactivation_hook`: Cleanup.

### 2. `SOTL_OTP_Handler` (includes/class-otp-handler.php)
-   **Properties:** `$otp_validity`, `$max_attempts`.
-   **Methods:**
    -   `generate_otp($user_id)`: Generates 6-digit int.
    -   `send_otp_ajax()`: Handles AJAX request.
        -   Check nonce.
        -   `wp_authenticate_username_password`.
        -   Check rate limit.
        -   Generate & Hash OTP -> `set_transient`.
        -   Send Email.
        -   Return JSON success.
    -   `verify_otp_ajax()`: Handles AJAX request.
        -   Check nonce.
        -   Get transient.
        -   `wp_check_password(otp, hash)`.
        -   If valid: `wp_set_auth_cookie`, `wp_set_current_user`.
    -   `login_form_fields()`: Hooks into `login_form` to output HTML.
    -   `enqueue_assets()`: Load JS/CSS on login page.

### 3. `SOTL_Admin` (includes/class-otp-admin.php)
-   **Methods:**
    -   `add_menu_page()`: Settings > OTP Login.
    -   `render_settings_page()`: HTML for form + logs.
    -   `register_settings()`: Core WP Settings API.
    -   `get_logs()`: Helper to query `wp_sotl_logs`.

### 4. `SOTL_Email` (includes/class-otp-email.php)
-   **Methods:**
    -   `send_otp($email, $otp)`: Loads template, sends mail.
    -   `phpmailer_smtp($phpmailer)`: Configures SMTP (Mailtrap).

## Database Schema (`wp_sotl_logs`)
-   `id` (BIGINT, AI, PK)
-   `user_id` (BIGINT)
-   `action` (VARCHAR: 'send_otp', 'login_success', 'login_failed', 'lockout')
-   `ip_address` (VARCHAR)
-   `status` (VARCHAR)
-   `created_at` (DATETIME)

## Frontend (JS/CSS)
-   **JS:** jQuery based (WP default).
    -   `#sotl-send-otp-btn` click -> AJAX.
    -   `#sotl-login-btn` click -> AJAX.
    -   Timer logic `setInterval`.
-   **CSS:**
    -   Style buttons to match `.button-primary`.
    -   Hidden fields `display: none`.

## Security Measures
-   **Rate Limiting:** Store request count in transient `sotl_rate_limit_{IP}`.
-   **Lockout:** Store failed attempts in user meta or transient `sotl_failed_attempts_{user_id}`.
