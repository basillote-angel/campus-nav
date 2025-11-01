<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_device_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/device-tokens', [
            'platform' => 'android',
            'token' => 'tok_123',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'platform' => 'android',
            'token' => 'tok_123',
        ]);
    }
}


