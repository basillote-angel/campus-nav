<?php

namespace App\Services;

use App\Helpers\PickupInstructionHelper;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Carbon\Carbon;

/**
 * Service for generating formal, professional notification messages
 * Ensures all notifications are clear, understandable, and consistent
 */
class NotificationMessageService
{
    /**
     * Generate notification title and body for a given notification type
     *
     * @param string $type Notification type (e.g., 'claimSubmitted', 'claimApproved')
     * @param array $data Context data for the notification
     * @return array ['title' => string, 'body' => string]
     */
    public static function generate(string $type, array $data): array
    {
        return match ($type) {
            'claimSubmitted' => self::claimSubmitted($data),
            'claimApproved' => self::claimApproved($data),
            'claimRejected' => self::claimRejected($data),
            'claimCancelled' => self::claimCancelled($data),
            'matchFound' => self::matchFound($data),
            'collectionReminder' => self::collectionReminder($data),
            'collectionOverdue' => self::collectionOverdue($data),
            'collectionConfirmed' => self::collectionConfirmed($data),
            'newClaim' => self::newClaim($data),
            'multipleClaims' => self::multipleClaims($data),
            'pendingClaimSla' => self::pendingClaimSla($data),
            'collectionExpired' => self::collectionExpired($data),
            'collectionReopened' => self::collectionReopened($data),
            'collectionArchived' => self::collectionArchived($data),
            'collectionOverdueAdmin' => self::collectionOverdueAdmin($data),
            default => self::defaultMessage($type, $data),
        };
    }

    /**
     * Claim Submitted - User submitted a claim for a found item
     */
    private static function claimSubmitted(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'the item';
        $userName = $data['user_name'] ?? 'Student';

        $title = "Claim Submission Confirmed";
        
        $body = "Dear {$userName},\n\n";
        $body .= "We have successfully received your claim request for \"{$itemTitle}\".\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "NEXT STEPS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "1. Your claim is now under review by our administration team\n";
        $body .= "2. You will receive a notification once a decision has been made\n";
        $body .= "3. The review process typically takes 1-3 business days\n";
        $body .= "4. Please ensure your contact information is up to date\n\n";
        $body .= "You can check the status of your claim at any time through the NavistFind mobile application.\n\n";
        $body .= "Thank you for using NavistFind.\n\n";
        $body .= "Best regards,\n";
        $body .= "NavistFind Administration\n";
        $body .= "Carmen National High School";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Claim Approved - User's claim has been approved
     */
    private static function claimApproved(array $data): array
    {
        // Use PickupInstructionHelper for formal pickup instructions
        $pickupData = [
            'item_title' => $data['item_title'] ?? 'your item',
            'collection_location' => $data['collection_location'] ?? null,
            'collection_deadline' => $data['collection_deadline'] ?? null,
            'collection_instructions' => $data['collection_instructions'] ?? null,
            'office_hours' => $data['office_hours'] ?? null,
            'contact_info' => $data['contact_info'] ?? null,
            'claimant_name' => $data['claimant_name'] ?? null,
        ];

        $title = "Claim Approved - Collection Instructions";
        $body = PickupInstructionHelper::generateFormalMessage($pickupData);

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Claim Rejected - User's claim has been rejected
     */
    private static function claimRejected(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'the item';
        $rejectionReason = $data['rejection_reason'] ?? 'The provided information did not sufficiently match the item details.';
        $userName = $data['user_name'] ?? 'Student';
        $contactEmail = $data['contact_email'] ?? 'admin@school.edu';
        $contactPhone = $data['contact_phone'] ?? '(555) 123-4567';

        $title = "Claim Status Update - Not Approved";

        $body = "Dear {$userName},\n\n";
        $body .= "We regret to inform you that your claim for \"{$itemTitle}\" could not be approved at this time.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "REASON FOR DECISION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "{$rejectionReason}\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "WHAT YOU CAN DO\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "If you believe this decision was made in error, or if you have additional information that may support your claim, please:\n\n";
        $body .= "1. Review the item details carefully in the application\n";
        $body .= "2. Ensure all information provided is accurate and complete\n";
        $body .= "3. Include specific identifying features (brand, model, serial numbers, etc.)\n";
        $body .= "4. Provide any supporting evidence (photos, receipts, etc.)\n";
        $body .= "5. Contact our administration office for assistance\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "CONTACT INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Email: {$contactEmail}\n";
        $body .= "Phone: {$contactPhone}\n\n";
        $body .= "Our team is available to assist you with any questions or concerns.\n\n";
        $body .= "Thank you for your understanding.\n\n";
        $body .= "Best regards,\n";
        $body .= "NavistFind Administration\n";
        $body .= "Carmen National High School";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Claim Cancelled - Approved claim was cancelled
     */
    private static function claimCancelled(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'the item';
        $userName = $data['user_name'] ?? 'Student';
        $contactEmail = $data['contact_email'] ?? 'admin@school.edu';
        $contactPhone = $data['contact_phone'] ?? '(555) 123-4567';

        $title = "Claim Approval Cancelled";

        $body = "Dear {$userName},\n\n";
        $body .= "We are writing to inform you that the approval for your claim of \"{$itemTitle}\" has been cancelled.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "WHAT THIS MEANS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "The item is no longer reserved for you and may be made available to other claimants.\n\n";
        $body .= "If you have questions about this decision or wish to discuss your claim further, please contact our administration office.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "CONTACT INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Email: {$contactEmail}\n";
        $body .= "Phone: {$contactPhone}\n\n";
        $body .= "Thank you for your understanding.\n\n";
        $body .= "Best regards,\n";
        $body .= "NavistFind Administration\n";
        $body .= "Carmen National High School";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Match Found - AI found a potential match
     */
    private static function matchFound(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';
        $matchTitle = $data['match_title'] ?? 'a matching item';
        $score = $data['score'] ?? '0';
        $scorePercent = number_format((float) $score, 1);
        $userName = $data['user_name'] ?? 'Student';

        $title = "Potential Match Found - {$scorePercent}% Similarity";

        $body = "Dear {$userName},\n\n";
        $body .= "Our system has identified a potential match for your item \"{$itemTitle}\".\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "MATCH DETAILS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Matched Item: \"{$matchTitle}\"\n";
        $body .= "Similarity Score: {$scorePercent}%\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "NEXT STEPS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "1. Review the matched item details in the NavistFind application\n";
        $body .= "2. Compare the item features with your lost/found item\n";
        $body .= "3. If it matches your item, you can submit a claim\n";
        $body .= "4. If it does not match, you can dismiss the match\n\n";
        $body .= "Please note: This is an automated match suggestion. Please verify all details carefully before submitting a claim.\n\n";
        $body .= "Thank you for using NavistFind.\n\n";
        $body .= "Best regards,\n";
        $body .= "NavistFind Administration\n";
        $body .= "Carmen National High School";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Reminder - Reminder to collect approved item
     */
    private static function collectionReminder(array $data): array
    {
        $pickupData = [
            'item_title' => $data['item_title'] ?? 'your item',
            'collection_location' => $data['collection_location'] ?? null,
            'collection_deadline' => $data['collection_deadline'] ?? null,
            'collection_instructions' => $data['collection_instructions'] ?? null,
            'office_hours' => $data['office_hours'] ?? null,
            'contact_info' => $data['contact_info'] ?? null,
            'claimant_name' => $data['claimant_name'] ?? null,
        ];

        $daysRemaining = $data['days_remaining'] ?? null;
        
        if ($daysRemaining !== null) {
            $body = PickupInstructionHelper::generateReminderMessage($pickupData, (int) $daysRemaining);
            $title = $daysRemaining <= 1 
                ? "URGENT: Collection Deadline Tomorrow"
                : "Collection Reminder - {$daysRemaining} Days Remaining";
        } else {
            $body = PickupInstructionHelper::generateFormalMessage($pickupData);
            $title = "Collection Reminder";
        }

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Overdue - Collection deadline has passed
     */
    private static function collectionOverdue(array $data): array
    {
        $pickupData = [
            'item_title' => $data['item_title'] ?? 'your item',
            'collection_location' => $data['collection_location'] ?? null,
            'collection_deadline' => $data['collection_deadline'] ?? null,
            'collection_instructions' => $data['collection_instructions'] ?? null,
            'office_hours' => $data['office_hours'] ?? null,
            'contact_info' => $data['contact_info'] ?? null,
            'claimant_name' => $data['claimant_name'] ?? null,
        ];

        $title = "URGENT: Collection Deadline Has Passed";
        $body = PickupInstructionHelper::generateOverdueMessage($pickupData);

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Confirmed - Item was successfully collected
     */
    private static function collectionConfirmed(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'your item';
        $userName = $data['user_name'] ?? 'Student';

        $title = "Item Collection Confirmed";

        $body = "Dear {$userName},\n\n";
        $body .= "We are pleased to confirm that your collection of \"{$itemTitle}\" has been successfully recorded.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "TRANSACTION COMPLETE\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Your claim has been marked as collected and the case is now closed.\n\n";
        $body .= "Thank you for using NavistFind. We hope you found your item successfully!\n\n";
        $body .= "If you have any feedback about your experience, please feel free to contact us.\n\n";
        $body .= "Best regards,\n";
        $body .= "NavistFind Administration\n";
        $body .= "Carmen National High School";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * New Claim (Admin) - New claim submitted
     */
    private static function newClaim(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';
        $claimantName = $data['claimant_name'] ?? 'A user';
        $claimantEmail = $data['claimant_email'] ?? '';
        $category = $data['category'] ?? 'Unknown';
        $location = $data['location'] ?? 'Unknown';
        $messagePreview = $data['message_preview'] ?? 'No message provided';

        $title = "New Claim Submitted - Review Required";

        $body = "A new claim has been submitted and requires your review.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "CLAIM DETAILS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Item: \"{$itemTitle}\"\n";
        $body .= "Category: {$category}\n";
        $body .= "Location: {$location}\n";
        $body .= "Claimant: {$claimantName}";
        if ($claimantEmail) {
            $body .= " ({$claimantEmail})";
        }
        $body .= "\n";
        $body .= "Message: {$messagePreview}\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ACTION REQUIRED\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Please review this claim in the admin dashboard and make a decision:\n";
        $body .= "• Approve - If the claim is valid\n";
        $body .= "• Reject - If the claim does not match\n";
        $body .= "• Request More Information - If additional details are needed\n\n";
        $body .= "Please process this claim within 1-3 business days.\n\n";
        $body .= "NavistFind Administration";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Multiple Claims (Admin) - Multiple claims for same item
     */
    private static function multipleClaims(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';

        $title = "Multiple Claims Detected - Review Required";

        $body = "Multiple claims have been submitted for the same item.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ITEM INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Item: \"{$itemTitle}\"\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ACTION REQUIRED\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "This item has multiple pending claims. Please:\n\n";
        $body .= "1. Review all claims carefully\n";
        $body .= "2. Compare the evidence provided by each claimant\n";
        $body .= "3. Verify ownership details\n";
        $body .= "4. Approve the most valid claim\n";
        $body .= "5. Reject other claims with clear reasoning\n\n";
        $body .= "Please process this case promptly to ensure fair resolution.\n\n";
        $body .= "NavistFind Administration";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Pending Claim SLA (Admin) - Claim pending too long
     */
    private static function pendingClaimSla(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';
        $waitingDuration = $data['waiting_duration'] ?? 'some time';

        $title = "Pending Claim Alert - SLA Reminder";

        $body = "A claim has been pending for an extended period and requires attention.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "CLAIM INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Item: \"{$itemTitle}\"\n";
        $body .= "Time Pending: {$waitingDuration}\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ACTION REQUIRED\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Please review and process this claim as soon as possible to:\n";
        $body .= "• Maintain service quality standards\n";
        $body .= "• Ensure timely resolution for the claimant\n";
        $body .= "• Keep the lost and found system efficient\n\n";
        $body .= "NavistFind Administration";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Expired - Collection window closed
     */
    private static function collectionExpired(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'the item';
        $userName = $data['user_name'] ?? 'Student';

        $title = "Collection Window Expired";

        $body = "Dear {$userName},\n\n";
        $body .= "We are writing to inform you that the collection window for \"{$itemTitle}\" has expired.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "WHAT THIS MEANS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Your approved claim has been cancelled due to the collection deadline passing. The item may be made available to other claimants.\n\n";
        $body .= "If you believe this was an error or have extenuating circumstances, please contact the administration office immediately.\n\n";
        $body .= "Thank you for your understanding.\n\n";
        $body .= "Best regards,\n";
        $body .= "NavistFind Administration\n";
        $body .= "Carmen National High School";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Reopened (Admin) - Item reopened after missed deadline
     */
    private static function collectionReopened(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';

        $title = "Item Reopened - Available for Claims";

        $body = "An item has been reopened and is now available for new claims.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ITEM INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Item: \"{$itemTitle}\"\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "STATUS UPDATE\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "The previous claimant missed the collection deadline. The item has been returned to \"Unclaimed\" status and is now available for new claims.\n\n";
        $body .= "NavistFind Administration";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Archived (Admin) - Item collected and archived
     */
    private static function collectionArchived(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';

        $title = "Item Collected - Case Closed";

        $body = "An item has been successfully collected and the case has been closed.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ITEM INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Item: \"{$itemTitle}\"\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "STATUS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "The item has been collected by the claimant and marked as resolved. This case is now archived.\n\n";
        $body .= "NavistFind Administration";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Collection Overdue Admin - Admin alert for overdue collection
     */
    private static function collectionOverdueAdmin(array $data): array
    {
        $itemTitle = $data['item_title'] ?? 'an item';

        $title = "Collection Overdue Alert - Follow-up Required";

        $body = "An item has not been collected by the claimant after the deadline.\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ITEM INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Item: \"{$itemTitle}\"\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "ACTION REQUIRED\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Please follow up with the claimant or consider reopening the item for new claims.\n\n";
        $body .= "NavistFind Administration";

        return ['title' => $title, 'body' => $body];
    }

    /**
     * Default message for unknown notification types
     */
    private static function defaultMessage(string $type, array $data): array
    {
        $title = $data['title'] ?? 'Notification from NavistFind';
        $body = $data['body'] ?? "You have received a notification from NavistFind.\n\nPlease check the application for more details.";

        return ['title' => $title, 'body' => $body];
    }
}

