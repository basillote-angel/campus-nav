<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FcmService
{
    private string $serverKey;

    public function __construct()
    {
        $this->serverKey = (string) config('services.fcm.server_key', '');
    }

	public function sendToTokens(array $tokens, array $payload): void
	{
		if (empty($tokens) || trim($this->serverKey) === '') {
			return;
		}

		$body = [
			'registration_ids' => array_values($tokens),
			'notification' => [
				'title' => $payload['notification']['title'] ?? '',
				'body' => $payload['notification']['body'] ?? '',
			],
			'data' => $payload['data'] ?? [],
		];

		if (!empty($payload['data']['cta'])) {
			$body['data']['cta'] = $payload['data']['cta'];
		}

		Http::withHeaders([
			'Authorization' => 'key ' . $this->serverKey,
			'Content-Type' => 'application/json',
		])->post('https://fcm.googleapis.com/fcm/send', $body)->throw();
	}

	public function sendToTopic(string $topic, array $payload): void
	{
		if ($topic === '' || trim($this->serverKey) === '') {
		 return;
		}

		$body = [
			'to' => '/topics/' . ltrim($topic, '/'),
			'notification' => [
				'title' => $payload['notification']['title'] ?? '',
				'body' => $payload['notification']['body'] ?? '',
			],
			'data' => $payload['data'] ?? [],
		];

		Http::withHeaders([
			'Authorization' => 'key ' . $this->serverKey,
			'Content-Type' => 'application/json',
		])->post('https://fcm.googleapis.com/fcm/send', $body)->throw();
	}
}


