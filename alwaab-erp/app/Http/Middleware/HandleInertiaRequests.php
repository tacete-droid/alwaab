<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'name_ar' => $request->user()->name_ar,
                    'name_en' => $request->user()->name_en,
                    'email' => $request->user()->email,
                    'locale' => $request->user()->locale?->value ?? 'ar',
                    'roles' => $request->user()->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'locale' => app()->getLocale(),
            'translations' => fn () => app(\App\Services\TranslationService::class)->forFrontend(),
            'unread_notifications' => fn () => $request->user()
                ? app(\App\Services\AppNotificationService::class)->unreadCount($request->user())
                : 0,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'company' => fn () => array_merge(
                app(\App\Services\SettingsService::class)->companySettings(),
                ['logo' => '/images/alwaab-logo.png']
            ),
        ];
    }
}
