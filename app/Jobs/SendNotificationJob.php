<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $title;
    public string $body;
    public string $type;
    public ?int $relatedId;
    public ?string $score;

    public $tries = 3;
    public $backoff = 10;

    public function __construct(
        int $userId,
        string $title,
        string $body,
        string $type,
        ?int $relatedId = null,
        ?string $score = null
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->relatedId = $relatedId;
        $this->score = $score;
    }

    public function handle(FcmService $fcm): void
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

        $tokens = $user->deviceTokens->pluck('token')->filter()->values()->all();
        if (empty($tokens)) {
            return;
        }

        $payload = [
            'notification' => ['title' => $this->title, 'body' => $this->body],
            'data' => [
                'type' => $this->type,
                'related_id' => $this->relatedId,
                'score' => $this->score,
                'notification_id' => (string) $notification->getKey(),
            ],
        ];

        $fcm->sendToTokens($tokens, $payload);
    }
}


