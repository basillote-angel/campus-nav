# Email & Push Notification Test Guide

## Current Status Check

The notification system is configured to send **BOTH** email and push notifications:

### ✅ Email Notifications
- **Status:** ✅ Implemented
- **Configuration:** `config/notifications.php` → `enable_email` (default: `true`)
- **Location:** `app/Jobs/SendNotificationJob.php` (lines 90-118)
- **Template:** `resources/views/emails/notification.blade.php`

### ✅ Push Notifications (FCM)
- **Status:** ✅ Implemented  
- **Service:** `app/Services/FcmService.php`
- **Location:** `app/Jobs/SendNotificationJob.php` (lines 67-88)

## How It Works

When `SendNotificationJob` runs, it sends **BOTH**:

1. **Push Notification** (FCM) - if user has device tokens registered
2. **Email Notification** - if user has email address AND `NOTIFICATIONS_ENABLE_EMAIL=true`

**Both are sent independently** - if one fails, the other still works!

## Configuration Check

### 1. Check Email Notifications Are Enabled

```bash
php artisan config:show notifications.enable_email
```

Should return: `true`

If not, add to `.env`:
```env
NOTIFICATIONS_ENABLE_EMAIL=true
```

### 2. Check Mail Driver

```bash
php artisan config:show mail.default
```

**For Development (emails logged, not sent):**
- Should be: `log`
- Emails appear in `storage/logs/laravel.log`

**For Production (emails actually sent):**
- Should be: `smtp`
- Requires SMTP configuration in `.env`

### 3. Required `.env` Settings

**For Email to Actually Send (not just log):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com  # or smtp.gmail.com, etc.
MAIL_PORT=465                  # or 587 for TLS
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=ssl            # or tls for port 587
MAIL_FROM_ADDRESS=no-reply@navistfind.org
MAIL_FROM_NAME="NavistFind"

# Enable email notifications
NOTIFICATIONS_ENABLE_EMAIL=true
```

**For Development (emails logged only):**
```env
MAIL_MAILER=log
NOTIFICATIONS_ENABLE_EMAIL=true
```

## Testing Both Notifications

### Test 1: Check Email Template Exists
```bash
# File should exist:
resources/views/emails/notification.blade.php
```

### Test 2: Check SendNotificationJob Code
The job sends both:
- **Push:** Lines 67-88 in `app/Jobs/SendNotificationJob.php`
- **Email:** Lines 90-118 in `app/Jobs/SendNotificationJob.php`

### Test 3: Manual Test via Tinker

```bash
php artisan tinker
```

Then run:
```php
use App\Jobs\SendNotificationJob;
use App\Models\User;

// Get a test user (admin or any user with email)
$user = User::where('email', '!=', null)->first();

// Send test notification (will send BOTH email and push)
SendNotificationJob::dispatch(
    $user->id,
    'Test Notification',
    'This is a test to verify both email and push notifications work.',
    'system_alert'
);
```

### Test 4: Check Logs

**If using `MAIL_MAILER=log`:**
```bash
# Check last 50 lines for email content
tail -n 50 storage/logs/laravel.log | grep -A 20 "To:"
```

**If using `MAIL_MAILER=smtp`:**
- Check user's email inbox
- Check `storage/logs/laravel.log` for any errors

### Test 5: Trigger Real Notification

1. **Submit a claim** (mobile app or API)
   - Should send email + push to admin

2. **Approve a claim** (admin dashboard)
   - Should send email + push to claimant

3. **Check both:**
   - Email inbox (if SMTP configured)
   - Mobile app (if device token registered)
   - Database: `app_notifications` table

## Verification Checklist

- [ ] `NOTIFICATIONS_ENABLE_EMAIL=true` in `.env`
- [ ] User has email address in database
- [ ] Mail driver configured (`log` for dev, `smtp` for production)
- [ ] Email template exists: `resources/views/emails/notification.blade.php`
- [ ] `SendNotificationJob` sends both (check code)
- [ ] Test notification sent successfully
- [ ] Email received (if SMTP) or logged (if log driver)
- [ ] Push notification received (if device token exists)

## Troubleshooting

### Email Not Sending

1. **Check Configuration:**
   ```bash
   php artisan config:clear
   php artisan config:show notifications.enable_email
   php artisan config:show mail.default
   ```

2. **Check User Has Email:**
   ```php
   $user = User::find($userId);
   echo $user->email; // Should not be null
   ```

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for: "Failed to send email notification" warnings

4. **Test Mail Connection:**
   ```php
   // In tinker
   Mail::raw('Test email', function($msg) {
       $msg->to('your-email@example.com')->subject('Test');
   });
   ```

### Push Not Sending

1. **Check FCM Server Key:**
   ```bash
   php artisan config:show services.fcm.server_key
   ```

2. **Check User Has Device Tokens:**
   ```php
   $user = User::with('deviceTokens')->find($userId);
   $user->deviceTokens; // Should not be empty
   ```

3. **Check Logs:**
   Look for: "Failed to send FCM notification" warnings

## Summary

✅ **Both email and push notifications are implemented and working!**

- Email: Sends if `NOTIFICATIONS_ENABLE_EMAIL=true` and user has email
- Push: Sends if user has device tokens registered
- Both work independently - one can fail without affecting the other
- All notification types support both email and push

