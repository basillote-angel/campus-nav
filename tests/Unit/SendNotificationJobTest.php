<?php

namespace Tests\Unit;

use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Models\DeviceToken;
use App\Services\DomainEventService;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_notification_and_calls_fcm(): void
    {
        $user = User::factory()->create();
        DeviceToken::create([
            'user_id' => $user->id,
            'platform' => 'android',
            'token' => 'tok_abc',
        ]);

        $called = false;
        $fake = new class($called) extends FcmService {
            private $ref;
            public function __construct(& $ref = null) { $this->ref = & $ref; }
            public function sendToTokens(array $tokens, array $payload): void { $this->ref = true; }
        };

        $this->app->instance(FcmService::class, $fake);

        $job = new SendNotificationJob($user->id, 'T', 'B', 'system_alert');
        $job->handle(app(FcmService::class), app(DomainEventService::class));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'T',
            'body' => 'B',
            'type' => 'system_alert',
        ]);
    }
}


