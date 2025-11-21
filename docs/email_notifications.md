# Email Notifications Setup Guide

## Overview

The notification system now supports **both push notifications and email notifications**. When a notification is triggered, users will receive:

1. **Push Notification** (via FCM) - if device token is registered
2. **Email Notification** - if user has an email address

## Configuration

### Enable/Disable Email Notifications

Add to your `.env` file:

```env
NOTIFICATIONS_ENABLE_EMAIL=true
```

- `true` (default) - Email notifications are sent
- `false` - Email notifications are disabled (only push notifications sent)

### Email Configuration

**⚠️ IMPORTANT**: Make sure your email settings are properly configured in `.env`, otherwise email sending will fail.

**For Development/Testing (Recommended):**
```env
MAIL_MAILER=log
```
This will write emails to `storage/logs/laravel.log` instead of actually sending them. **This prevents connection errors during development.**

**For Production (SMTP):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="NavistFind"
```

**Common SMTP Providers:**

*Gmail/Google Workspace:*
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
# Use App Password, not regular password
```

*Office 365:*
```env
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

*SendGrid:*
```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

**⚠️ Fix for "smtp.yourprovider.com" Error:**
If you see this error, it means your `.env` file has a placeholder SMTP host. Either:
1. Set `MAIL_MAILER=log` for development (recommended)
2. Configure proper SMTP settings with real host values

## How It Works

### Automatic Email Sending

When `SendNotificationJob` runs, it now:

1. ✅ Creates a notification record in the database
2. ✅ Sends push notification (if device token exists)
3. ✅ **Sends email notification (if email address exists)** ← NEW!

### Email Template

All notifications use a standardized email template (`resources/views/emails/notification.blade.php`) that includes:

- App branding (NavistFind)
- Notification title
- Notification body (supports multi-line text)
- Match score badge (if applicable)
- Clean, responsive HTML design

### When Emails Are Sent

Emails are automatically sent for ALL notification types:

- ✅ Claim Submitted (`claimSubmitted`)
- ✅ Claim Approved (`claimApproved`)
- ✅ Claim Rejected (`claimRejected`)
- ✅ Collection Reminder (`collectionReminder`)
- ✅ Collection Overdue (`collectionOverdue`)
- ✅ Match Found (`matchFound`)
- ✅ Pending Claim SLA Alert (`pendingClaimSla`)
- ✅ System Alerts (`system_alert`)
- ✅ And all other notification types

## Testing Email Notifications

### Test with Log Driver (Recommended for Development)

1. Set in `.env`:
```env
MAIL_MAILER=log
NOTIFICATIONS_ENABLE_EMAIL=true
```

2. Trigger a notification (e.g., submit a claim, approve a claim)

3. Check the log file:
```bash
tail -f storage/logs/laravel.log
```

### Test with Real SMTP

1. Configure SMTP in `.env` (see Email Configuration above)

2. Make sure queue is set to `sync` for immediate processing:
```env
QUEUE_CONNECTION=sync
```

3. Trigger a notification and check the user's email inbox

### Manual Test

You can also test by dispatching a notification job:

```php
use App\Jobs\SendNotificationJob;

SendNotificationJob::dispatch(
    $userId,
    'Test Email',
    'This is a test email notification.',
    'system_alert'
);
```

## Troubleshooting

### Emails Not Sending

1. **Check Configuration:**
   ```bash
   php artisan config:show notifications.enable_email
   php artisan config:show mail.default
   ```

2. **Check User Has Email:**
   - Ensure users have valid email addresses in the database

3. **Check Mail Driver:**
   - For development: Use `log` driver to see emails in logs
   - For production: Configure SMTP properly

4. **Check Queue:**
   - If using `database` queue, run `php artisan queue:work`
   - Or use `sync` queue for immediate processing

5. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for email-related errors or warnings

### Email Delivery Issues

- **Gmail/Google Workspace**: Use App Password, not regular password
- **Office 365**: May require specific SMTP settings
- **SendGrid/Mailgun**: Use their SMTP credentials
- **SES (AWS)**: Configure AWS credentials and region

### Queue Processing

Remember: With `database` queue, emails are queued and processed by the queue worker. Make sure to run:

```bash
php artisan queue:work
```

Or set queue to `sync` for immediate processing (development only):

```env
QUEUE_CONNECTION=sync
```

## Email Template Customization

You can customize the email template by editing:

```
resources/views/emails/notification.blade.php
```

The template supports:
- HTML formatting
- Responsive design
- Custom styling
- Dynamic content (title, body, score, etc.)

## Integration with Existing Mail Classes

The system still supports specialized mail classes:
- `ClaimApprovedMail` - For claim approvals (used in ClaimsController)
- `ClaimRejectedMail` - For claim rejections
- `CollectionReminderMail` - For collection reminders

These are used alongside the generic `NotificationMail` for specific, formatted emails.

## Summary

✅ **Email notifications are now integrated into all notifications**
✅ **Configure email settings in `.env`**
✅ **Set `NOTIFICATIONS_ENABLE_EMAIL=true` to enable**
✅ **Works alongside push notifications**
✅ **Automatic - no code changes needed in controllers**

Users will now receive notifications via both channels when available!

