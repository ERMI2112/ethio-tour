<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function createForUser(?User $user, string $type, string $title, string $message, ?string $actionUrl = null): ?Notification
    {
        if (! $user) {
            return null;
        }

        try {
            return Notification::create([
                'user_id' => $user->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'channel' => 'in_app',
                'sent_date' => now(),
                'read_status' => false,
                'action_url' => $actionUrl,
            ]);
        } catch (Throwable $exception) {
            Log::warning('In-app notification could not be persisted.', ['type' => $type, 'user_id' => $user->user_id, 'error' => $exception->getMessage()]);

            return null;
        }
    }

    public function unreadCount(User $user): int
    {
        return $user->notifications()->where('read_status', false)->count();
    }

    /**
     * Send a central in-app alert to every active administrator.
     *
     * The recipient set is resolved on the server so domain workflows never
     * need to accept an administrator id from a browser request.
     */
    public function createForAdministrators(string $type, string $title, string $message, ?int $excludeUserId = null, ?string $actionUrl = null): int
    {
        $created = 0;

        User::query()
            ->where('role', 'administrator')
            ->where('is_active', true)
            ->when($excludeUserId !== null, fn ($query) => $query->where('user_id', '!=', $excludeUserId))
            ->get()
            ->each(function (User $administrator) use ($type, $title, $message, $actionUrl, &$created): void {
                if ($this->createForUser($administrator, $type, $title, $message, $actionUrl)) {
                    $created++;
                }
            });

        return $created;
    }

    /**
     * Persist the normal recipient notification and mirror the event to the
     * administrator alert queue. This keeps all notification storage in one
     * place while making governance-relevant activity visible centrally.
     */
    public function createForUserAndAdministrators(?User $user, string $type, string $title, string $message, ?int $excludeAdministratorId = null, ?string $userActionUrl = null, ?string $adminActionUrl = null): ?Notification
    {
        $notification = $this->createForUser($user, $type, $title, $message, $userActionUrl);
        $this->createForAdministrators($type, 'Platform alert: '.$title, $message, $excludeAdministratorId, $adminActionUrl);

        return $notification;
    }
}
