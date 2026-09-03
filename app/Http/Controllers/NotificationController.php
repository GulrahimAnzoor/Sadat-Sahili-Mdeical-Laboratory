<?php

namespace App\Http\Controllers;

use App\Support\LabAlerts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        LabAlerts::scanExpiryAlertsIfDue();

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $path = $this->safePath($item->data['url'] ?? null);
        $item->delete();

        return redirect()->to($path);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', __('Notifications cleared.'));
    }

    private function safePath(mixed $url): string
    {
        if (! is_string($url) || $url === '') {
            return route('dashboard', absolute: false);
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return route('dashboard', absolute: false);
        }

        return $path;
    }
}
