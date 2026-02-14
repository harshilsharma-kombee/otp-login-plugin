# 🔐 Secure OTP Login for WordPress Admin

Adds email-based OTP (Two-Factor Authentication) to the WordPress login page.

---

## Login Flow

1. User enters username + password → clicks **Send OTP**
2. Plugin verifies credentials → emails a 6-digit OTP
3. User enters OTP → gets logged into wp-admin

---

## Installation

1. Upload `secure-otp-login.zip` via **Plugins → Add New → Upload Plugin**
2. Activate the plugin
3. Go to **Settings → OTP Login** to configure

---

## Email Setup

**On any live / UAT site** — works automatically. No setup needed. Uses your site's existing email configuration (hosting SMTP, WP Mail SMTP plugin, Outlook relay, etc.).

**On localhost / XAMPP** — enable Custom SMTP in settings and add your [Mailtrap](https://mailtrap.io) credentials.

| Provider | Host | Port |
|---|---|---|
| Mailtrap (dev) | `sandbox.smtp.mailtrap.io` | `2525` |
| Gmail | `smtp.gmail.com` | `587` |
| Outlook / Office 365 | `smtp.office365.com` | `587` |

---

## Settings

Go to **Settings → OTP Login**

- Enable / disable the plugin
- Select which roles require OTP (default: Administrator, Editor)
- Set OTP expiry time (default: 5 minutes)
- Set max failed attempts (default: 3 → 15-min lockout)
- Send a test email to verify delivery
- View last 50 login attempt logs

---

## Security

- Credentials verified **before** OTP is sent
- OTP stored as a bcrypt hash — never plain text
- WordPress nonces on all AJAX requests
- Rate limit: 5 OTP requests per IP per 15 minutes
- 3 wrong attempts → 15-minute lockout

---

## Troubleshooting

**No email on live site** → Test your site's general email (not a plugin issue — check hosting SMTP).

**No email on localhost** → Enable Custom SMTP in settings and use Mailtrap.

**Locked out** → Wait 15 min, or run:
```sql
DELETE FROM wp_options WHERE option_name LIKE '_transient_sotl_%';
```

---

## Requirements

- WordPress 5.8+
- PHP 7.4+
