<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationMarkReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $n = AppNotification::create([
            'user_id' => $user->id,
            'type' => 'system_alert',
            'title' => 'Alert',
            'body' => 'Body',
        ]);

        $res = $this->actingAs($user)->postJson('/api/notifications/'.$n->id.'/read');
        $res->assertNoContent();

        $this->assertNotNull($n->fresh()->read_at);
    }
}


