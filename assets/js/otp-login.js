jQuery(document).ready(function ($) {
    var timerInterval;

    // Initially hide the standard login button to force OTP flow?
    // Requirement: "The normal 'Log In' button should only work after OTP is verified"
    // We can't easily hide it because it's part of the standard form.
    // Instead we will intercept the click.

    $('#sotl-send-otp-btn').on('click', function (e) {
        e.preventDefault();

        var username = $('#user_login').val();
        var password = $('#user_pass').val();
        var $btn = $(this);
        var $msg = $('#sotl-otp-message');

        if (!username || !password) {
            $msg.text('Please enter username and password first.').removeClass('success').addClass('error');
            return;
        }

        $btn.prop('disabled', true).text('Sending...');
        $msg.text('');

        $.ajax({
            url: sotl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sotl_send_otp',
                user_login: username,
                user_password: password,
                security: sotl_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $msg.text(response.data.message).removeClass('error').addClass('success');
                    $('#sotl-otp-field-group').slideDown();
                    $btn.hide(); // Hide Send OTP button

                    // Start Timer
                    startTimer(response.data.expiry);
                } else {
                    $msg.text(response.data.message).removeClass('success').addClass('error');
                    $btn.prop('disabled', false).text('Send OTP');
                }
            },
            error: function () {
                $msg.text('Server error. Please try again.').removeClass('success').addClass('error');
                $btn.prop('disabled', false).text('Send OTP');
            }
        });
    });

    // Intercept Login Form Submission
    $('#loginform').on('submit', function (e) {
        // If OTP field works are not visible/active, maybe we should let it pass?
        // But requirement says "normal Log In button should only work after OTP is verified".
        // This implies even if they bypass Send OTP, they shouldn't be able to login.
        // But if we intercept 'submit', we stop standard login.
        // We only want to handle this if OTP is required/active.

        // Check if OTP field is visible (implies OTP flow started)
        if ($('#sotl-otp-field-group').is(':visible')) {
            e.preventDefault(); // Stop standard submission
            verifyOTP();
        } else {
            // If OTP flow hasn't started, we should probably stop them and say "Please click Send OTP"
            // EXCEPT if the plugin is disabled or user doesn't need OTP.
            // But we don't know that on client side easily without checking specific flags.
            // However, the "Send OTP" button is there.
            // Let's assume we force them to use Send OTP button first.
            if ($('#sotl-send-otp-btn').is(':visible')) {
                e.preventDefault();
                $('#sotl-otp-message').text('Please click "Send OTP" first.').removeClass('success').addClass('error');
                // Optionally trigger the click?
                // $('#sotl-send-otp-btn').click();
            }
        }
    });

    function verifyOTP() {
        var username = $('#user_login').val();
        var otp = $('#sotl_otp_input').val();
        var $msg = $('#sotl-otp-message');
        var $submitBtn = $('#wp-submit');

        if (!otp) {
            $msg.text('Please enter the OTP.').removeClass('success').addClass('error');
            return;
        }

        $submitBtn.prop('disabled', true).val('Verifying...');

        $.ajax({
            url: sotl_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sotl_verify_otp',
                user_login: username,
                otp: otp,
                security: sotl_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $msg.text('OTP Verified! Logging in...').removeClass('error').addClass('success');
                    window.location.href = response.data.redirect_url;
                } else {
                    $msg.text(response.data.message).removeClass('success').addClass('error');
                    $submitBtn.prop('disabled', false).val('Log In');
                }
            },
            error: function () {
                $msg.text('Server error.').removeClass('success').addClass('error');
                $submitBtn.prop('disabled', false).val('Log In');
            }
        });
    }

    function startTimer(duration) {
        var timer = duration, minutes, seconds;
        var $display = $('#sotl-otp-timer');
        var $resend = $('#sotl-resend-link');
        $resend.hide(); // Hide resend initially

        clearInterval(timerInterval);

        timerInterval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            $display.text("Time remaining: " + minutes + ":" + seconds);

            if (--timer < 0) {
                // Expired
                clearInterval(timerInterval);
                $display.text("OTP Expired.");
                $('#sotl_otp_input').prop('disabled', true);
                $('#sotl-otp-message').text('OTP expired, click Send OTP again').addClass('error');

                // Show Send OTP button again? Or Resend link?
                // Req: "Resend OTP link (available after 60 seconds)"
                // But specifically for expiry: "If OTP expires -> show message 'OTP expired, click Send OTP again'"
                $('#sotl-send-otp-btn').show().text('Send OTP');
                $('#sotl-otp-field-group').hide();
            }

            // Resend link logic (e.g. show after 60 seconds)
            if (duration - timer >= 60) {
                $resend.show();
            }

        }, 1000);
    }

    $('#sotl-resend-otp').on('click', function (e) {
        e.preventDefault();
        $('#sotl-send-otp-btn').click();
    });

});
