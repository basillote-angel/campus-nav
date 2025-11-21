# Local Server Email Setup Guide

## 🔍 Current Situation

**Your mail driver is set to `log`**, which means:
- ✅ Emails ARE being generated
- ✅ Emails ARE being logged to `storage/logs/laravel.log`
- ❌ Emails are NOT being sent to your inbox

**This is normal for development!** The `log` driver prevents connection errors during development.

---

## 📧 Option 1: Use Hostinger Email (Your Current Setup)

Since you already have **Hostinger email** (`no-reply@navistfind.org`), you can use it for local testing!

### Update `.env` File

Open `C:\CAPSTONE PROJECT\campus-nav\.env` and update:

```env
# Change from 'log' to 'smtp'
MAIL_MAILER=smtp

# Hostinger SMTP Settings
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@navistfind.org
MAIL_PASSWORD=Navistfind888.
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=no-reply@navistfind.org
MAIL_FROM_NAME="NavistFind"

# Make sure email notifications are enabled
NOTIFICATIONS_ENABLE_EMAIL=true
```

**Alternative Hostinger Settings (if above doesn't work):**

If `smtp.hostinger.com` doesn't work, try:
```env
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### Step 2: Clear Config Cache

```bash
php artisan config:clear
```

### Step 3: Test It!

Trigger a notification (approve a claim, etc.) and check the inbox for `no-reply@navistfind.org` or the recipient's email!

**Note:** Make sure your Hostinger email account is active and accessible.

---

## 📧 Option 2: Use Gmail SMTP (Alternative for Local Testing)

### Step 1: Get Gmail App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Sign in with your Gmail account
3. Select "Mail" and "Other (Custom name)"
4. Enter name: "NavistFind Local"
5. Click "Generate"
6. **Copy the 16-character password** (you'll need this)

### Step 2: Update `.env` File

Open `C:\CAPSTONE PROJECT\campus-nav\.env` and update:

```env
# Change from 'log' to 'smtp'
MAIL_MAILER=smtp

# Gmail SMTP Settings
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="NavistFind"

# Make sure email notifications are enabled
NOTIFICATIONS_ENABLE_EMAIL=true
```

**Important:**
- Use the **16-character app password**, NOT your regular Gmail password
- Replace `your-email@gmail.com` with your actual Gmail address
- Replace `your-16-character-app-password` with the password from Step 1

### Step 3: Clear Config Cache

```bash
php artisan config:clear
```

### Step 4: Test It!

Trigger a notification (approve a claim, etc.) and check your Gmail inbox!

---

## 📧 Option 2: Use Mailtrap (Best for Local Development)

Mailtrap is a fake SMTP server that captures emails for testing - perfect for local development!

### Step 1: Sign Up for Mailtrap

1. Go to: https://mailtrap.io/
2. Sign up for free account
3. Go to "Email Testing" → "Inboxes"
4. Click on your inbox
5. Copy the SMTP credentials

### Step 2: Update `.env` File

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@navistfind.org
MAIL_FROM_NAME="NavistFind"

NOTIFICATIONS_ENABLE_EMAIL=true
```

### Step 3: Clear Config Cache

```bash
php artisan config:clear
```

### Step 4: Test It!

1. Trigger a notification
2. Go to Mailtrap inbox
3. See the email there (it won't actually send to real inbox, but you can see it)

**Benefits:**
- ✅ No need for real email account
- ✅ See all emails in one place
- ✅ Test email formatting
- ✅ Free for development

---

## 📧 Option 3: Keep Using Log Driver (View in Logs)

If you just want to see the emails without actually sending them:

### View Emails in Log File

```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 100 | Select-String -Pattern "To:|Subject:|From:" -Context 10
```

Or open: `storage/logs/laravel.log` and search for:
- `To: your-email@example.com`
- `Subject:`
- The email HTML content

**Note:** With `log` driver, emails are written to the log file but NOT sent to your inbox.

---

## 🧪 Quick Test

After configuring SMTP, test with:

```bash
php artisan tinker
```

Then run:
```php
use App\Jobs\SendNotificationJob;

SendNotificationJob::dispatch(
    1, // Your user ID
    'Test Email',
    'This is a test email notification!',
    'system_alert'
);
```

Check your email inbox (or Mailtrap inbox)!

---

## ✅ Verification Checklist

After setting up SMTP:

- [ ] `.env` file updated with SMTP settings
- [ ] `php artisan config:clear` run
- [ ] Test notification sent
- [ ] Email received in inbox (or Mailtrap)

---

## 🐛 Troubleshooting

### "Connection could not be established" Error

**For Gmail:**
- ✅ Make sure you're using **App Password**, not regular password
- ✅ Enable 2-factor authentication first
- ✅ Try port `465` with `MAIL_ENCRYPTION=ssl` instead

**For Mailtrap:**
- ✅ Double-check username and password from Mailtrap dashboard
- ✅ Make sure you're using port `2525`

### Email Not Received

1. **Check spam folder**
2. **Check Laravel logs:** `storage/logs/laravel.log`
3. **Verify SMTP settings:**
   ```bash
   php artisan config:show mail
   ```
4. **Test SMTP connection:**
   ```bash
   php artisan tinker
   Mail::raw('Test', fn($m) => $m->to('your@email.com')->subject('Test'));
   ```

---

## 📝 Summary

**Current Status:**
- Mail driver: `log` (emails logged, not sent)
- Email notifications: Enabled
- Emails are being generated ✅
- Emails are NOT being sent to inbox ❌

**To Receive Real Emails:**
1. Choose Option 1 (Gmail) or Option 2 (Mailtrap)
2. Update `.env` with SMTP settings
3. Run `php artisan config:clear`
4. Test by triggering a notification

**For Production:**
- Use real SMTP (Gmail, SendGrid, Mailgun, etc.)
- Set `MAIL_MAILER=smtp` with proper credentials






