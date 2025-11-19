<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Helper class for generating formal pickup instruction messages
 * Used in notifications (push and email) for claimApproved, collectionReminder, and collectionOverdue types
 */
class PickupInstructionHelper
{
    /**
     * Generate a formal pickup instruction message
     *
     * @param array $data Contains:
     *   - 'item_title' (string): Title of the found item
     *   - 'collection_location' (string|null): Office location where item can be collected
     *   - 'collection_deadline' (string|Carbon|null): Deadline for collection (ISO 8601 or Carbon instance)
     *   - 'collection_instructions' (string|null): Additional instructions from admin
     *   - 'office_hours' (string|null): Office operating hours (e.g., "Monday-Friday, 8:00 AM - 5:00 PM")
     *   - 'contact_info' (string|null): Contact information for admin office
     *   - 'claimant_name' (string|null): Name of the claimant
     *
     * @return string Formatted pickup instruction message
     */
    public static function generateFormalMessage(array $data): string
    {
        $itemTitle = $data['item_title'] ?? 'your item';
        $location = $data['collection_location'] ?? 'Administrative Office';
        $deadline = self::formatDeadline($data['collection_deadline'] ?? null);
        $instructions = $data['collection_instructions'] ?? null;
        $officeHours = $data['office_hours'] ?? 'Monday-Friday, 8:00 AM - 5:00 PM';
        $contactInfo = $data['contact_info'] ?? null;
        $claimantName = $data['claimant_name'] ?? null;

        $greeting = $claimantName ? "Dear {$claimantName}," : "Dear Student,";

        $message = "{$greeting}\n\n";
        $message .= "Your claim for \"{$itemTitle}\" has been approved. Please follow the instructions below to collect your item.\n\n";
        
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "PICKUP INSTRUCTIONS\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // Location
        $message .= "📍 COLLECTION LOCATION:\n";
        $message .= "   {$location}\n\n";

        // Office Hours
        $message .= "🕐 OFFICE HOURS:\n";
        $message .= "   {$officeHours}\n\n";

        // Deadline
        if ($deadline) {
            $message .= "⏰ COLLECTION DEADLINE:\n";
            $message .= "   {$deadline}\n\n";
        }

        // Required Documents
        $message .= "📋 REQUIRED DOCUMENTS:\n";
        $message .= "   • Valid School ID or Government-issued ID\n";
        $message .= "   • Any supporting evidence (photos, receipts, etc.)\n";
        $message .= "   • Proof of ownership (if applicable)\n\n";

        // Additional Instructions
        if ($instructions) {
            $message .= "📝 ADDITIONAL INSTRUCTIONS:\n";
            $message .= "   {$instructions}\n\n";
        }

        // Important Notes
        $message .= "⚠️ IMPORTANT NOTES:\n";
        $message .= "   • You must collect the item in person at the office location\n";
        $message .= "   • Bring a valid ID for verification\n";
        if ($deadline) {
            $message .= "   • Please collect before the deadline to avoid expiration\n";
        }
        $message .= "   • If you cannot collect during office hours, contact the admin office in advance\n\n";

        // Contact Information
        if ($contactInfo) {
            $message .= "📞 CONTACT INFORMATION:\n";
            $message .= "   {$contactInfo}\n\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Thank you for using NavistFind.\n";
        $message .= "We look forward to assisting you with the collection.\n\n";
        $message .= "Best regards,\n";
        $message .= "NavistFind Administration\n";
        $message .= "Carmen National High School";

        return $message;
    }

    /**
     * Generate a reminder message (for collectionReminder notifications)
     *
     * @param array $data Same structure as generateFormalMessage
     * @param int $daysRemaining Number of days until deadline
     *
     * @return string Formatted reminder message
     */
    public static function generateReminderMessage(array $data, int $daysRemaining): string
    {
        $itemTitle = $data['item_title'] ?? 'your item';
        $location = $data['collection_location'] ?? 'Administrative Office';
        $deadline = self::formatDeadline($data['collection_deadline'] ?? null);
        $officeHours = $data['office_hours'] ?? 'Monday-Friday, 8:00 AM - 5:00 PM';
        $contactInfo = $data['contact_info'] ?? null;
        $claimantName = $data['claimant_name'] ?? null;

        $greeting = $claimantName ? "Dear {$claimantName}," : "Dear Student,";
        $urgency = $daysRemaining <= 1 ? "URGENT" : "REMINDER";

        $message = "{$greeting}\n\n";
        $message .= "⏰ {$urgency}: Collection Deadline Approaching\n\n";
        $message .= "This is a friendly reminder that your approved claim for \"{$itemTitle}\" is due for collection.\n\n";

        if ($daysRemaining <= 1) {
            $message .= "🚨 The collection deadline is very soon! Please collect your item immediately.\n\n";
        } else {
            $message .= "You have {$daysRemaining} day(s) remaining to collect your item.\n\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "QUICK REFERENCE\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        $message .= "📍 Location: {$location}\n";
        $message .= "🕐 Hours: {$officeHours}\n";
        if ($deadline) {
            $message .= "⏰ Deadline: {$deadline}\n";
        }
        $message .= "📋 Bring: Valid ID and supporting evidence\n\n";

        if ($contactInfo) {
            $message .= "📞 Contact: {$contactInfo}\n\n";
        }

        $message .= "Please make arrangements to collect your item before the deadline.\n\n";
        $message .= "Best regards,\n";
        $message .= "NavistFind Administration";

        return $message;
    }

    /**
     * Generate an overdue message (for collectionOverdue notifications)
     *
     * @param array $data Same structure as generateFormalMessage
     *
     * @return string Formatted overdue warning message
     */
    public static function generateOverdueMessage(array $data): string
    {
        $itemTitle = $data['item_title'] ?? 'your item';
        $location = $data['collection_location'] ?? 'Administrative Office';
        $deadline = self::formatDeadline($data['collection_deadline'] ?? null);
        $officeHours = $data['office_hours'] ?? 'Monday-Friday, 8:00 AM - 5:00 PM';
        $contactInfo = $data['contact_info'] ?? null;
        $claimantName = $data['claimant_name'] ?? null;

        $greeting = $claimantName ? "Dear {$claimantName}," : "Dear Student,";

        $message = "{$greeting}\n\n";
        $message .= "🚨 URGENT: Collection Deadline Has Passed\n\n";
        $message .= "The collection deadline for your approved claim of \"{$itemTitle}\" has passed.\n\n";
        $message .= "⚠️ IMPORTANT: You still have a grace period to collect your item, but please contact the admin office immediately to arrange collection.\n\n";

        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "COLLECTION DETAILS\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        $message .= "📍 Location: {$location}\n";
        $message .= "🕐 Hours: {$officeHours}\n";
        if ($deadline) {
            $message .= "⏰ Original Deadline: {$deadline}\n";
        }
        $message .= "📋 Bring: Valid ID and supporting evidence\n\n";

        if ($contactInfo) {
            $message .= "📞 Contact Immediately: {$contactInfo}\n\n";
        }

        $message .= "Please note that further delay may result in the claim being cancelled and the item being made available to other claimants.\n\n";
        $message .= "We strongly encourage you to contact the admin office as soon as possible.\n\n";
        $message .= "Best regards,\n";
        $message .= "NavistFind Administration";

        return $message;
    }

    /**
     * Format deadline date for display
     *
     * @param string|Carbon|null $deadline
     *
     * @return string|null Formatted deadline string
     */
    private static function formatDeadline($deadline): ?string
    {
        if (!$deadline) {
            return null;
        }

        try {
            if (is_string($deadline)) {
                $carbon = Carbon::parse($deadline);
            } elseif ($deadline instanceof Carbon) {
                $carbon = $deadline;
            } else {
                return null;
            }

            // Format: "Monday, November 25, 2025 at 7:35 PM"
            return $carbon->format('l, F j, Y') . ' at ' . $carbon->format('g:i A');
        } catch (\Exception $e) {
            return null;
        }
    }
}



