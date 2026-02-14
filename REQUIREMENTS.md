# Secure OTP Login for WordPress Admin - Requirements

## Overview
A WordPress plugin to secure the default admin login with a One-Time Password (OTP) sent via email.

## Login Flow
1.  **Step 1:** User accesses `wp-login.php`. Sees normal login form + "Send OTP" button + Hidden OTP field.
2.  **Step 2:** User enters Username/Password -> Clicks "Send OTP".
    -   Plugin verifies credentials.
    -   Generates 6-digit OTP.
    -   Sends OTP to registered email.
    -   Shows OTP input field & 5-minute countdown.
    -   Shows "OTP sent to e***@example.com".
3.  **Step 3:** User enters OTP -> Clicks "Log In".
    -   Plugin verifies OTP.
    -   If correct: Logs user in (redirect to dashboard).
    -   If wrong: Shows error (max 3 attempts).
    -   If expired: Shows "OTP expired".

## Technical Specifications
-   **Structure:**
    -   `secure-otp-login.php`
    -   `includes/class-otp-handler.php`
    -   `includes/class-otp-admin.php`
    -   `includes/class-otp-email.php`
    -   `assets/css/otp-login.css`
    -   `assets/js/otp-login.js`
    -   `templates/email-template.php`
-   **Storage:** `wp_sotl_logs` table for logging events. Transients for OTP storage.
-   **Security:**
    -   Verify password before sending OTP.
    -   Hash OTPs.
    -   Nonce verification.
    -   Input sanitization.
    -   Rate limiting (5 requests/15 mins).
    -   Lockout (3 failed attempts/15 mins).
-   **Email:** PHPMailer via `phpmailer_init` (Mailtrap default).

## Admin Settings
-   Enable/Disable plugin.
-   Select roles requiring OTP.
-   Set OTP expiry (default 5 mins).
-   Set Max Attempts (default 3).
-   View Logs (Last 50).
