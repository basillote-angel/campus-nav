<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DomainEventService
{
	public function dispatch(
		string $eventType,
		array $payload,
		?array $actor = null,
		string $source = 'campus-nav.jobs',
		string $version = '1.0'
	): void {
		if (empty($payload)) {
			return;
		}

		$envelope = [
			'eventId' => (string) Str::uuid(),
			'eventType' => $eventType,
			'occurredAt' => now()->toIso8601String(),
			'version' => $version,
			'source' => $source,
			'actor' => $actor,
			'payload' => $payload,
		];

		ActivityLog::create([
			'user_id' => $actor['id'] ?? null,
			'action' => 'domain_event:' . $eventType,
			'details' => json_encode($envelope, JSON_THROW_ON_ERROR),
			'created_at' => now(),
		]);

		Log::info('domain_event_dispatched', $envelope);
	}
}



