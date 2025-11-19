<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\DomainEventService;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $title;
    public string $body;
    public string $type;
    public ?int $relatedId;
    public ?string $score;
    public ?array $eventContext;

    public $tries = 3;
    public $backoff = 10;

    public function __construct(
        int $userId,
        string $title,
        string $body,
        string $type,
        ?int $relatedId = null,
        ?string $score = null,
        ?array $eventContext = null
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->relatedId = $relatedId;
        $this->score = $score;
        $this->eventContext = $eventContext;
    }

    public function handle(FcmService $fcm, DomainEventService $events): void
    {
        $notification = AppNotification::create([
            'user_id' => $this->userId,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'related_id' => $this->relatedId,
            'score' => $this->score,
        ]);

        $user = User::with('deviceTokens')->find($this->userId);
        if (!$user) {
            return;
        }

        // Send push notification via FCM if device tokens exist
        $tokens = $user->deviceTokens->pluck('token')->filter()->values()->all();
        if (!empty($tokens)) {
            $payload = [
                'notification' => ['title' => $this->title, 'body' => $this->body],
                'data' => [
                    'type' => $this->type,
                    'related_id' => $this->relatedId,
                    'score' => $this->score,
                    'notification_id' => (string) $notification->getKey(),
                ],
            ];

            try {
                $fcm->sendToTokens($tokens, $payload);
            } catch (\Exception $e) {
                Log::warning('Failed to send FCM notification', [
                    'user_id' => $this->userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send email notification if user has email address
        $enableEmail = config('notifications.enable_email', true);
        if ($enableEmail && $user->email) {
            try {
                Mail::to($user->email)->send(new NotificationMail(
                    $user->name ?? 'NavistFind User',
                    $this->title,
                    $this->body,
                    $this->type,
                    $this->relatedId,
                    $this->score
                ));
            } catch (\Illuminate\Mail\MailException $e) {
                // Mail connection errors (SMTP, etc.) - log but don't fail the job
                Log::warning('Failed to send email notification (connection error)', [
                    'user_id' => $this->userId,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'mail_driver' => config('mail.default'),
                ]);
            } catch (\Exception $e) {
                // Other email errors - log but don't fail the job
                Log::warning('Failed to send email notification', [
                    'user_id' => $this->userId,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->dispatchDomainEvent($events);
    }

    protected function dispatchDomainEvent(DomainEventService $events): void
    {
        if (!$this->eventContext || empty($this->eventContext['type']) || empty($this->eventContext['payload'])) {
            return;
        }

        $events->dispatch(
            $this->eventContext['type'],
            $this->eventContext['payload'],
            $this->eventContext['actor'] ?? null,
            $this->eventContext['source'] ?? 'campus-nav.jobs',
            $this->eventContext['version'] ?? '1.0'
        );
    }
}


