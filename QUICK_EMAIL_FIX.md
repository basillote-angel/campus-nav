# Quick Fix: Receive Emails on Local Server

## 🔍 The Problem

Your mail driver is set to `log`, which means:
- ✅ Emails ARE being generated
- ✅ Emails ARE saved in `storage/logs/laravel.log`
- ❌ Emails are NOT sent to your inbox

**This is why you're not receiving emails!**

---

## ✅ Quick Solution: Use Gmail SMTP

### Step 1: Get Gmail App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Sign in with your Gmail
3. Select "Mail" → "Other (Custom name)" → Enter "NavistFind"
4. Click "Generate"
5. **Copy the 16-character password**

### Step 2: Update `.env` File

Open: `C:\CAPSTONE PROJECT\campus-nav\.env`

**Find this line:**
```env
MAIL_MAILER=log
```

**Change it to:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="NavistFind"
```

**Replace:**
- `your-email@gmail.com` → Your actual Gmail address
- `your-16-char-app-password` → The password from Step 1

### Step 3: Clear Config

```bash
cd "C:\CAPSTONE PROJECT\campus-nav"
php artisan config:clear
```

### Step 4: Test!

1. Approve a claim or trigger any notification
2. Check your Gmail inbox
3. You should receive the email! ✅

---

## 📧 Alternative: Use Mailtrap (No Real Email Needed)

If you don't want to use your real Gmail:

1. Sign up: https://mailtrap.io/ (free)
2. Get SMTP credentials from Mailtrap dashboard
3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```
4. Emails will appear in Mailtrap inbox (not your real email)

---

## 🔍 Verify Emails Are Being Generated

Even with `log` driver, you can see emails in the log:

```bash
# View recent email entries
Get-Content "storage/logs/laravel.log" -Tail 500 | Select-String -Pattern "To:|Subject:" -Context 5
```

This shows emails are being created, just not sent!

---

## ✅ After Setup

Once you configure SMTP:
- ✅ Emails will be sent to real inbox
- ✅ You'll receive notifications via email
- ✅ Works alongside push notifications

---

**Need help?** Check `docs/LOCAL_EMAIL_SETUP.md` for detailed instructions.









