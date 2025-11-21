# Queue Setup Guide

## Issue: Notifications Not Appearing

If you're not seeing notifications, it's likely because **queue jobs are not being processed**. The system uses Laravel queues to handle notification sending asynchronously.

## Quick Fix: Process Queue Manually

Run this command to process all pending jobs:

```bash
php artisan queue:work
```

Or process a specific number of jobs:

```bash
php artisan queue:work --max-jobs=100
```

## Check Queue Status

To see how many jobs are pending:

```bash
php artisan queue:work --once
```

Or check the database directly:

```sql
SELECT COUNT(*) FROM jobs;
```

## Solution 1: Run Queue Worker (Production/Long-term)

For production or when testing notifications, you should run a queue worker:

### Option A: Run in Terminal (Development)
```bash
php artisan queue:work
```
Keep this terminal window open while testing.

### Option B: Run as Background Process (Production)
```bash
php artisan queue:work --daemon
```

### Option C: Use Supervisor (Recommended for Production)
Configure Supervisor to automatically restart the queue worker if it crashes.

## Solution 2: Use Sync Queue (Development Only)

For development/testing, you can change the queue to process immediately:

1. Edit `.env` file:
```
QUEUE_CONNECTION=sync
```

2. Clear config cache:
```bash
php artisan config:clear
```

**Note**: `sync` queue processes jobs immediately but blocks the request until completion. Only use for development.

## Verify Notifications Are Working

After running the queue worker, check:

1. **API Endpoint**: `GET /api/notifications`
2. **Database**: Check `notifications` table
3. **Queue**: Jobs should be processed (check `jobs` table count should decrease)

## Queue Configuration Status

✅ **Current Configuration**: Queue is set to `sync` for immediate processing.

With `sync` queue:
- Notifications process immediately when triggered
- No queue worker needed
- Jobs execute synchronously (blocks request until complete)
- Perfect for development/testing
- **Note**: For production, consider using `database` queue with a queue worker for better performance

## Scheduled Jobs

The system also has scheduled jobs (via cron) that send notifications:
- `MonitorPendingClaimsSlaJob` - Sends SLA alerts
- `SendCollectionReminderJob` - Sends collection reminders
- `ProcessOverdueCollectionsJob` - Processes overdue collections

Make sure you have the scheduler running:
```bash
php artisan schedule:work
```
Or add to crontab:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

