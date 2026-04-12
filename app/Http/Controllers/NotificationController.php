<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $request->user()->notifications()->where('id', $id)->first()?->markAsRead();

        $url = $request->query('redirect');
        // Only allow internal relative paths — block external/protocol-relative redirects
        if ($url && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return redirect($url);
        }
        return back();
    }

    public function markAllRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}
