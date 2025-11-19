<?php

namespace App\Http\Resources;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class FoundItemResource extends JsonResource
{
	public function toArray($request): array
	{
		$status = $this->status instanceof FoundItemStatus ? $this->status->value : $this->status;

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
			'date_found' => optional($this->date_found)->toDateString(),
			'image_path' => $this->image_path,
			'collection_deadline' => optional($this->collection_deadline)->toIso8601String(),
			'collection_notes' => $this->collection_notes,
			'claim_status_summary' => $this->claimStatusSummary(),
			'transition_history' => $this->transitionHistory(),
			'claims' => ClaimResource::collection($this->whenLoaded('claims')),
			'created_at' => optional($this->created_at)->toIso8601String(),
			'updated_at' => optional($this->updated_at)->toIso8601String(),
		];
	}

	protected function claimStatusSummary(): array
	{
		if (!$this->relationLoaded('claims')) {
			return [];
		}

		return $this->claims
			->groupBy(function ($claim) {
				$status = $claim->status ?? ClaimStatus::PENDING->value;
				return $status instanceof ClaimStatus ? $status->value : $status;
			})
			->map->count()
			->sortKeys()
			->all();
	}

	protected function transitionHistory(): array
	{
		if ($this->relationLoaded('transitionLogs')) {
			return $this->transitionLogs->map(function ($log) {
				$details = (array) $log->details;

				return [
					'status' => $details['to'] ?? null,
					'from' => $details['from'] ?? null,
					'claim_id' => $details['claim_id'] ?? null,
					'claimant_id' => $details['claimant_id'] ?? null,
					'rejection_reason' => $details['rejection_reason'] ?? null,
					'occurred_at' => optional($log->created_at)->toIso8601String(),
					'actor' => $log->user ? [
						'id' => $log->user->id,
						'name' => $log->user->name,
					] : null,
				];
			})->filter()->values()->all();
		}

		$history = [];

		$history[] = $this->formatFallbackTransition(FoundItemStatus::FOUND_UNCLAIMED->value, $this->created_at);
		$history[] = $this->formatFallbackTransition(FoundItemStatus::CLAIM_PENDING->value, $this->claimed_at);
		$history[] = $this->formatFallbackTransition(FoundItemStatus::CLAIM_APPROVED->value, $this->approved_at);
		$history[] = $this->formatFallbackTransition(FoundItemStatus::COLLECTED->value, $this->collected_at);

		if ($this->rejected_at) {
			$history[] = [
				'status' => 'REJECTED',
				'occurred_at' => $this->rejected_at->toIso8601String(),
			];
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

