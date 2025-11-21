<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $itemTitle;
    public string $rejectionReason;
    public string $contactEmail;
    public string $contactPhone;

    public function __construct(array $payload)
    {
        $this->recipientName = $payload['recipientName'] ?? 'NavistFind User';
        $this->itemTitle = $payload['itemTitle'] ?? 'the item you claimed';
        $this->rejectionReason = $payload['rejectionReason'] ?? 'Unable to verify ownership.';
        $this->contactEmail = $payload['contactEmail'] ?? 'admin@school.edu';
        $this->contactPhone = $payload['contactPhone'] ?? '(555) 123-4567';
    }

    public function build(): self
    {
        return $this
            ->subject("Claim Update: {$this->itemTitle}")
            ->view('emails.claim_rejected')
            ->with([
                'recipientName' => $this->recipientName,
                'itemTitle' => $this->itemTitle,
                'rejectionReason' => $this->rejectionReason,
                'contactEmail' => $this->contactEmail,
                'contactPhone' => $this->contactPhone,
            ]);
    }
}





