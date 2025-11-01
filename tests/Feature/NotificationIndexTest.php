<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_notifications_with_unread_count(): void
    {
        $user = User::factory()->create();
        AppNotification::create([
            'user_id' => $user->id,
            'type' => 'admin_message',
            'title' => 'Message from Admin',
            'body' => 'Please bring your ID...',
            'related_id' => 45,
        ]);

        $res = $this->actingAs($user)->getJson('/api/notifications');
        $res->assertOk();
        $res->assertJsonStructure([
            'data' => [['id','type','title','body','related_id','score','created_at','read_at']],
            'meta' => ['current_page','last_page'],
            'unread_count',
        ]);
        $this->assertSame(1, $res->json('unread_count'));
    }
}


