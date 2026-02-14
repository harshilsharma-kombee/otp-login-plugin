<?php
/**
 * Email Template for OTP
 * 
 * Variables available:
 * $otp : The OTP code
 */
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 5px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
        }

        .otp-code {
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
            letter-spacing: 5px;
            margin: 20px 0;
        }

        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Login Verification</h2>
        <p>Hello,</p>
        <p>You requested a One-Time Password (OTP) to log in to your WordPress admin dashboard.</p>
        <p>Your OTP is:</p>
        <div class="otp-code">
            <?php echo esc_html($otp); ?>
        </div>
        <p>This code will expire in
            <?php echo esc_html(get_option('sotl_otp_expiry', 5)); ?> minutes.
        </p>
        <p>If you did not request this, please ignore this email.</p>
        <div class="footer">
            <p>&copy;
                <?php echo date('Y'); ?>
                <?php echo get_bloginfo('name'); ?>. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>