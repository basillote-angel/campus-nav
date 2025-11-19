<?php

namespace App\Http\Resources;

use App\Enums\LostItemStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class LostItemResource extends JsonResource
{
	public function toArray($request): array
	{
		try {
			$status = $this->status instanceof LostItemStatus ? $this->status->value : (is_string($this->status) ? $this->status : LostItemStatus::LOST_REPORTED->value);

		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'status' => $status,
			'category' => $this->whenLoaded('category', fn () => [
				'id' => $this->category->id,
				'name' => $this->category->name,
			]),
			'location' => $this->location,
			'date_lost' => optional($this->date_lost)->toDateString(),
			'image_path' => $this->image_path,
			'transition_history' => $this->transitionHistory($status),
			'created_at' => optional($this->created_at)->toIso8601String(),
			'updated_at' => optional($this->updated_at)->toIso8601String(),
		];
		} catch (\Exception $e) {
			\Log::error('LostItemResource serialization error', [
				'item_id' => $this->id ?? null,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			// Return minimal safe data
			return [
				'id' => $this->id ?? null,
				'title' => $this->title ?? 'Unknown',
				'description' => $this->description ?? '',
				'status' => is_string($this->status) ? $this->status : 'LOST_REPORTED',
				'error' => 'Failed to serialize resource',
			];
		}
	}

	protected function transitionHistory(string $status): array
	{
		if ($this->relationLoaded('transitionLogs')) {
			return $this->transitionLogs->map(function ($log) {
				$details = (array) $log->details;

				return [
					'status' => $details['to'] ?? null,
					'from' => $details['from'] ?? null,
					'occurred_at' => optional($log->created_at)->toIso8601String(),
					'actor' => $log->user ? [
						'id' => $log->user->id,
						'name' => $log->user->name,
					] : null,
				];
			})->filter()->values()->all();
		}

		$history = [];
		$history[] = $this->formatFallbackTransition(LostItemStatus::LOST_REPORTED->value, $this->created_at);

		if ($status === LostItemStatus::RESOLVED->value) {
			$history[] = $this->formatFallbackTransition(LostItemStatus::RESOLVED->value, $this->updated_at);
		}

		return array_values(array_filter($history));
	}

	protected function formatFallbackTransition(string $status, $timestamp): ?array
	{
		if (!$timestamp) {
			return null;
		}

		return [
			'status' => $status,
			'occurred_at' => $timestamp->toIso8601String(),
		];
	}
}

