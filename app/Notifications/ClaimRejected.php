<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClaimRejected extends Notification
{
    use Queueable;

    protected int $itemId;
    protected string $itemName;
    protected string $reason;

    public function __construct(int $itemId, string $itemName, string $reason)
    {
        $this->itemId = $itemId;
        $this->itemName = $itemName;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'claimRejected',
            'itemId' => $this->itemId,
            'title' => 'Claim Rejected',
            'message' => "Your claim for '{$this->itemName}' was rejected.",
            'reason' => $this->reason,
        ];
    }
}


