<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest('sent_date')->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->user_id, 404);
        $notification->update(['read_status' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function navigate(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->user_id, 404);

        if (! $notification->read_status) {
            $notification->update(['read_status' => true]);
        }

        return redirect()->to($notification->target_url);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()->where('read_status', false)->update(['read_status' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
