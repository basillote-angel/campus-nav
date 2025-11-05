<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Date;

class ClaimApproved extends Notification
{
    use Queueable;

    protected int $itemId;
    protected string $itemName;
    protected ?\DateTime $collectionDeadline;
    protected string $officeLocation;
    protected string $officeHours;
    protected string $contactEmail;
    protected string $contactPhone;

    public function __construct(
        int $itemId,
        string $itemName,
        ?\DateTime $collectionDeadline = null,
        ?string $officeLocation = null,
        ?string $officeHours = null,
        ?string $contactEmail = null,
        ?string $contactPhone = null
    ) {
        $this->itemId = $itemId;
        $this->itemName = $itemName;
        $this->collectionDeadline = $collectionDeadline;
        
        // Get collection details from config
        $this->officeLocation = $officeLocation ?? config('services.admin_office.location', 'Admin Office');
        $this->officeHours = $officeHours ?? config('services.admin_office.office_hours', 'Monday-Friday, 8:00 AM - 5:00 PM');
        $this->contactEmail = $contactEmail ?? config('services.admin_office.contact_email', 'admin@school.edu');
        $this->contactPhone = $contactPhone ?? config('services.admin_office.contact_phone', '(555) 123-4567');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $deadlineText = $this->collectionDeadline 
            ? Date::parse($this->collectionDeadline)->format('F d, Y')
            : 'within 7 days';
        
        $message = "Your claim for '{$this->itemName}' was approved!\n\n";
        $message .= "🏢 IMPORTANT: Physical collection required at admin office.\n\n";
        $message .= "📍 Location: {$this->officeLocation}\n";
        $message .= "⏰ Hours: {$this->officeHours}\n";
        if ($this->collectionDeadline) {
            $message .= "💡 Suggested Collection: {$deadlineText}\n";
        }
        $message .= "🆔 Required: Bring valid ID (Student ID or Government ID)\n\n";
        $message .= "📞 Questions? {$this->contactEmail} or {$this->contactPhone}";

        return [
            'type' => 'claimApproved',
            'itemId' => $this->itemId,
            'title' => 'Claim Approved! ✅',
            'message' => $message,
            'collectionDetails' => [
                'location' => $this->officeLocation,
                'officeHours' => $this->officeHours,
                'deadline' => $this->collectionDeadline ? Date::parse($this->collectionDeadline)->toIso8601String() : null,
                'deadlineText' => $deadlineText,
                'contactEmail' => $this->contactEmail,
                'contactPhone' => $this->contactPhone,
            ],
        ];
    }
}


