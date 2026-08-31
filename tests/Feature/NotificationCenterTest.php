<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
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

    public function test_central_workflow_alerts_are_mirrored_to_active_administrators(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $recipient = User::factory()->create(['role' => 'tourist', 'is_active' => true]);

        app(NotificationService::class)->createForUserAndAdministrators(
            $recipient,
            'booking_request',
            'New booking request',
            'A new booking needs review.',
        );

        $this->assertDatabaseHas('notifications', ['user_id' => $recipient->user_id, 'title' => 'New booking request']);
        $this->assertDatabaseHas('notifications', ['user_id' => $administrator->user_id, 'title' => 'Platform alert: New booking request', 'read_status' => 0]);
    }

    public function test_user_can_navigate_notification_which_marks_it_as_read_and_redirects_to_target(): void
    {
        $user = User::factory()->create(['role' => 'tourist', 'is_active' => true]);
        $notification = Notification::create([
            'user_id' => $user->user_id,
            'type' => 'booking_accepted',
            'title' => 'Hotel reservation accepted',
            'message' => 'Your reservation was accepted.',
            'channel' => 'in_app',
            'read_status' => false,
            'sent_date' => now(),
            'action_url' => route('tourist.reservations.index'),
        ]);

        $response = $this->actingAs($user)->get(route('notifications.navigate', $notification));

        $response->assertRedirect(route('tourist.reservations.index'));
        $this->assertDatabaseHas('notifications', [
            'notification_id' => $notification->notification_id,
            'read_status' => 1,
        ]);
    }

    public function test_tourist_notification_with_booking_id_dynamically_resolves_to_booking_detail(): void
    {
        $user = User::factory()->create(['role' => 'tourist', 'is_active' => true]);
        $notification = Notification::create([
            'user_id' => $user->user_id,
            'type' => 'payment_success',
            'title' => 'Payment confirmed',
            'message' => 'Your payment for booking #15 was verified and the booking is confirmed.',
            'channel' => 'in_app',
            'read_status' => false,
            'sent_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('notifications.navigate', $notification));

        $response->assertRedirect(route('tourist.reservations.show', 15));
        $this->assertTrue($notification->fresh()->read_status);
    }

    public function test_other_user_cannot_navigate_private_notification(): void
    {
        $owner = User::factory()->create(['role' => 'tourist', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'tourist', 'is_active' => true]);

        $notification = Notification::create([
            'user_id' => $owner->user_id,
            'type' => 'booking_accepted',
            'title' => 'Private Alert',
            'message' => 'Private content',
            'channel' => 'in_app',
            'read_status' => false,
            'sent_date' => now(),
        ]);

        $this->actingAs($other)->get(route('notifications.navigate', $notification))->assertNotFound();
        $this->assertFalse($notification->fresh()->read_status);
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
