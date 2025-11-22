<?php

namespace App\Jobs;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Mail\CollectionReminderMail;
use App\Jobs\SendNotificationJob;
use App\Models\ClaimedItem;
use App\Models\CollectionReminderLog;
use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SyncClaimedItemsJob implements ShouldQueue
{
	use Queueable;

	public function handle(): void
	{
		$now = Carbon::now();

		$firstReminderHours = (int) config('services.admin_office.collection_first_reminder_hours', 72);
		$secondReminderHours = (int) config('services.admin_office.collection_second_reminder_hours', 24);
		$reminderCooldownHours = (int) config('services.admin_office.collection_reminder_cooldown_hours', 12);
		$overdueGraceHours = (int) config('services.admin_office.collection_grace_hours', 72);

		$officeLocation = config('services.admin_office.location', 'Admin Office');
		$officeHours = config('services.admin_office.office_hours', 'Monday-Friday, 8:00 AM - 5:00 PM');
		$contactEmail = config('services.admin_office.contact_email', 'admin@school.edu');
		$contactPhone = config('services.admin_office.contact_phone', '(555) 123-4567');

		FoundItem::query()
			->where('status', FoundItemStatus::CLAIM_APPROVED->value)
			->whereNull('collected_at')
			->whereNotNull('collection_deadline')
			->select(['id'])
			->chunkById(100, function ($items) use (
				$now,
				$firstReminderHours,
				$secondReminderHours,
				$reminderCooldownHours,
				$overdueGraceHours,
				$officeLocation,
				$officeHours,
				$contactEmail,
				$contactPhone
			) {
				foreach ($items as $record) {
					DB::transaction(function () use (
						$record,
						$now,
						$firstReminderHours,
						$secondReminderHours,
						$reminderCooldownHours,
						$overdueGraceHours,
						$officeLocation,
						$officeHours,
						$contactEmail,
						$contactPhone
					) {
						/** @var FoundItem|null $item */
						$item = FoundItem::query()
							->whereKey($record->id)
							->lockForUpdate()
							->with('claimedBy')
							->first();

						if (!$item || $item->status !== FoundItemStatus::CLAIM_APPROVED || $item->collected_at || !$item->collection_deadline) {
							return;
						}

						$hoursUntilDeadline = $now->diffInHours($item->collection_deadline, false);
						$reminderStage = $this->determineReminderStage($hoursUntilDeadline, $firstReminderHours, $secondReminderHours);

						if ($reminderStage) {
							$this->maybeSendReminder(
								$item,
								$reminderStage,
								$now,
								$reminderCooldownHours,
								$officeLocation,
								$officeHours,
								$contactEmail,
								$contactPhone
							);
						}

						if ($hoursUntilDeadline < 0) {
							$this->handleOverdueItem(
								$item,
								$now,
								$overdueGraceHours,
								$officeLocation,
								$officeHours,
								$contactEmail,
								$contactPhone
							);
						}
					}, 5);
				}
			});
	}

	protected function determineReminderStage(int $hoursUntilDeadline, int $firstReminderHours, int $secondReminderHours): ?string
	{
		if ($hoursUntilDeadline < 0) {
			return null;
		}

		if ($hoursUntilDeadline <= $secondReminderHours) {
			return 'one_day';
		}

		if ($hoursUntilDeadline <= $firstReminderHours) {
			return 'three_day';
		}

		return null;
	}

	protected function maybeSendReminder(
		FoundItem $item,
		string $stage,
		Carbon $now,
		int $reminderCooldownHours,
		string $officeLocation,
		string $officeHours,
		string $contactEmail,
		string $contactPhone
	): void {
		$lastReminder = $item->last_collection_reminder_at;

		if ($item->collection_reminder_stage === $stage && $lastReminder && $lastReminder->gt($now->copy()->subHours($reminderCooldownHours))) {
			return;
		}

		if (!$item->claimed_by) {
			return;
		}

		$deadlineText = $item->collection_deadline ? $item->collection_deadline->format('F d, Y g:i A') : 'the deadline';
		$stageMessage = $stage === 'three_day'
			? "Your collection deadline is approaching in the next few days."
			: "Your collection deadline is within the next 24 hours.";

		$body = "Reminder for '{$item->title}': {$stageMessage}\n\n";
		$body .= "📍 Location: {$officeLocation}\n";
		$body .= "⏰ Office Hours: {$officeHours}\n";
		$body .= "📅 Deadline: {$deadlineText}\n";
		$body .= "🆔 Required: Bring a valid ID for verification\n\n";
		$body .= "📞 Need help? {$contactEmail} / {$contactPhone}";

		$eventContext = [
			'type' => 'found.collectionReminder',
			'payload' => [
				'foundItemId' => $item->id,
				'status' => $item->status->value,
				'claimantId' => $item->claimed_by,
				'collectionDeadline' => $item->collection_deadline?->toIso8601String(),
				'reminderStage' => $stage,
			],
			'actor' => null,
			'source' => 'campus-nav.jobs.sync-claimed-items',
		];

		SendNotificationJob::dispatch(
			$item->claimed_by,
			'Collection Reminder',
			$body,
			'collectionReminder',
			$item->id,
			null,
			$eventContext
		);

		CollectionReminderLog::query()
			->where('found_item_id', $item->id)
			->where('status', 'pending')
			->update(['status' => 'expired']);

		CollectionReminderLog::create([
			'found_item_id' => $item->id,
			'claimant_id' => $item->claimed_by,
			'stage' => $stage,
			'source' => 'auto',
			'status' => 'pending',
			'sent_at' => $now,
		]);

		$item->last_collection_reminder_at = $now;
		$item->collection_reminder_stage = $stage;
		$item->save();
		if ($item->claimedBy && $item->claimedBy->email) {
			Mail::to($item->claimedBy->email)
				->queue(new CollectionReminderMail([
					'recipientName' => $item->claimedBy->name ?? 'NavistFind User',
					'itemTitle' => $item->title,
					'deadlineText' => $deadlineText,
					'officeLocation' => $officeLocation,
					'officeHours' => $officeHours,
					'stageMessage' => $stageMessage,
					'contactEmail' => $contactEmail,
					'contactPhone' => $contactPhone,
				]));
		}

	}

	protected function handleOverdueItem(
		FoundItem $item,
		Carbon $now,
		int $overdueGraceHours,
		string $officeLocation,
		string $officeHours,
		string $contactEmail,
		string $contactPhone
	): void {
		$hoursPastDeadline = abs($now->diffInHours($item->collection_deadline, false));

		if ($item->claimed_by && !$item->overdue_notified_at) {
			$body = "Your claim for '{$item->title}' is past the collection deadline.\n\n";
			$body .= "Please visit {$officeLocation} (Hours: {$officeHours}) as soon as possible. ";
			$body .= "If you need more time, contact {$contactEmail} or {$contactPhone}.\n\n";
			$body .= "Failure to collect may result in the item being made available to other claimants.";

			SendNotificationJob::dispatch(
				$item->claimed_by,
				'Collection Deadline Passed',
				$body,
				'collectionOverdue',
				$item->id
			);

			$admins = User::query()->where('role', 'admin')->pluck('id');
			foreach ($admins as $adminId) {
				SendNotificationJob::dispatch(
					$adminId,
					'Collection Overdue Alert',
					"Item '{$item->title}' has not been collected by the claimant. Please follow up.",
					'collectionOverdueAdmin',
					$item->id
				);

			if ($item->claimedBy && $item->claimedBy->email) {
				Mail::to($item->claimedBy->email)
					->queue(new CollectionReminderMail([
						'recipientName' => $item->claimedBy->name ?? 'NavistFind User',
						'itemTitle' => $item->title,
						'deadlineText' => $item->collection_deadline
							? $item->collection_deadline->format('F d, Y g:i A')
							: 'the deadline',
						'officeLocation' => $officeLocation,
						'officeHours' => $officeHours,
						'stageMessage' => 'Your collection window has passed. Please contact the admin office immediately to avoid cancellation.',
						'contactEmail' => $contactEmail,
						'contactPhone' => $contactPhone,
					]));
			}
			}

			$item->overdue_notified_at = $now;
			$item->save();
		}

		if ($hoursPastDeadline < $overdueGraceHours) {
			return;
		}

		$this->reopenItemForNewClaims($item, $now);
	}

	protected function reopenItemForNewClaims(FoundItem $item, Carbon $now): void
	{
		try {
			ClaimedItem::query()
				->where('found_item_id', $item->id)
				->where('status', ClaimStatus::APPROVED->value)
				->update([
					'status' => ClaimStatus::REJECTED->value,
					'rejection_reason' => 'Collection deadline expired',
					'rejected_at' => $now,
				]);

			ItemMatch::query()
				->where('found_id', $item->id)
				->update(['status' => 'pending']);

			$item->markStatus(FoundItemStatus::FOUND_UNCLAIMED);
			$item->claimed_by = null;
			$item->claim_message = null;
			$item->claimant_contact_name = null;
			$item->claimant_contact_info = null;
			$item->claimant_email = null;
			$item->claimant_phone = null;
			$item->claim_image = null;
			$item->claimed_at = null;
			$item->approved_at = null;
			$item->approved_by = null;
			$item->collection_deadline = null;
			$item->pending_sla_notified_at = null;
			$item->last_collection_reminder_at = null;
			$item->collection_reminder_stage = null;
			$item->overdue_notified_at = null;
			$item->collected_at = null;
			$item->collected_by = null;
			$item->collection_notes = null;
			$item->save();

			CollectionReminderLog::query()
				->where('found_item_id', $item->id)
				->where('status', 'pending')
				->update(['status' => 'expired']);

			$admins = User::query()->where('role', 'admin')->pluck('id');
			foreach ($admins as $adminId) {
				SendNotificationJob::dispatch(
					$adminId,
					'Collection Window Closed',
					"Item '{$item->title}' was reopened after the claimant missed the collection deadline.",
					'collectionReopened',
					$item->id
				);
			}
		} catch (\Throwable $e) {
			Log::error('COLLECTION_REOPEN_ERROR', [
				'itemId' => $item->id,
				'message' => $e->getMessage(),
			]);
		}
	}
}
