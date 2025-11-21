<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $itemTitle;
    public string $collectionDeadlineText;
    public string $officeLocation;
    public string $officeHours;
    public string $contactEmail;
    public string $contactPhone;

    public function __construct(array $payload)
    {
        $this->recipientName = $payload['recipientName'] ?? 'NavistFind User';
        $this->itemTitle = $payload['itemTitle'] ?? 'your item';
        $this->collectionDeadlineText = $payload['collectionDeadlineText'] ?? 'the next 7 days';
        $this->officeLocation = $payload['officeLocation'] ?? 'Admin Office';
        $this->officeHours = $payload['officeHours'] ?? 'Monday-Friday, 8:00 AM - 5:00 PM';
        $this->contactEmail = $payload['contactEmail'] ?? 'admin@school.edu';
        $this->contactPhone = $payload['contactPhone'] ?? '(555) 123-4567';
    }

    public function build(): self
    {
        return $this
            ->subject("Claim Approved: {$this->itemTitle}")
            ->view('emails.claim_approved')
            ->with([
                'recipientName' => $this->recipientName,
                'itemTitle' => $this->itemTitle,
                'collectionDeadlineText' => $this->collectionDeadlineText,
                'officeLocation' => $this->officeLocation,
                'officeHours' => $this->officeHours,
                'contactEmail' => $this->contactEmail,
                'contactPhone' => $this->contactPhone,
            ]);
    }
}





