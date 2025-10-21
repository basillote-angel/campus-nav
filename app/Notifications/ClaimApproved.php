<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClaimApproved extends Notification
{
    use Queueable;

    protected int $itemId;
    protected string $itemName;

    public function __construct(int $itemId, string $itemName)
    {
        $this->itemId = $itemId;
        $this->itemName = $itemName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'claimApproved',
            'itemId' => $this->itemId,
            'title' => 'Claim Approved',
            'message' => "Your claim for '{$this->itemName}' was approved.",
        ];
    }
}


