<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollectionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $itemTitle;
    public string $deadlineText;
    public string $officeLocation;
    public string $officeHours;
    public string $stageMessage;
    public string $contactEmail;
    public string $contactPhone;

    public function __construct(array $payload)
    {
        $this->recipientName = $payload['recipientName'] ?? 'NavistFind User';
        $this->itemTitle = $payload['itemTitle'] ?? 'your item';
        $this->deadlineText = $payload['deadlineText'] ?? 'the deadline';
        $this->officeLocation = $payload['officeLocation'] ?? 'Admin Office';
        $this->officeHours = $payload['officeHours'] ?? 'Monday-Friday, 8:00 AM - 5:00 PM';
        $this->stageMessage = $payload['stageMessage'] ?? 'Please collect your item soon.';
        $this->contactEmail = $payload['contactEmail'] ?? 'admin@school.edu';
        $this->contactPhone = $payload['contactPhone'] ?? '(555) 123-4567';
    }

    public function build(): self
    {
        return $this
            ->subject("Collection Reminder: {$this->itemTitle}")
            ->view('emails.collection_reminder')
            ->with([
                'recipientName' => $this->recipientName,
                'itemTitle' => $this->itemTitle,
                'deadlineText' => $this->deadlineText,
                'officeLocation' => $this->officeLocation,
                'officeHours' => $this->officeHours,
                'stageMessage' => $this->stageMessage,
                'contactEmail' => $this->contactEmail,
                'contactPhone' => $this->contactPhone,
            ]);
    }
}





