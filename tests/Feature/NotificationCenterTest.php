<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_only_their_notifications_and_unread_count(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createNotifications($user, 2, false);
        $this->createNotifications($user, 1, true);
        $this->createNotifications($other, 1, false, 'Private message');

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk()->assertSee('Notifications')->assertSee('2')->assertDontSee('Private message');
    }

    public function test_notification_center_requires_authentication_and_ownership(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotifications($user, 1, false)->first();

        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->patch(route('notifications.read', $notification))->assertNotFound();
    }

    public function test_user_can_mark_one_or_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $one = $this->createNotifications($user, 1, false)->first();
        $this->createNotifications($user, 2, false);

        $this->actingAs($user)->patch(route('notifications.read', $one))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['notification_id' => $one->notification_id, 'read_status' => 1]);

        $this->actingAs($user)->patch(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $user->notifications()->where('read_status', false)->count());
    }

    public function test_notification_center_paginates_notifications(): void
    {
        $user = User::factory()->create();
        $this->createNotifications($user, 16, false);

        $this->actingAs($user)->get(route('notifications.index'))->assertOk()->assertSee('page=2');
    }

    private function createNotifications(User $user, int $count, bool $read, string $title = 'Test notification')
    {
        return collect(range(1, $count))->map(fn () => Notification::create([
            'user_id' => $user->user_id,
            'type' => 'system',
            'title' => $title,
            'message' => 'Notification body',
            'channel' => 'in_app',
            'read_status' => $read,
            'sent_date' => now(),
        ]));
    }
}
