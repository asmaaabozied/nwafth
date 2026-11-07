<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use App\Models\Admin;
use App\Services\ContactMessageService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(
        private readonly SettingService $settings,
        private readonly ContactMessageService $contactMessages,
    ) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = Locale::current();

        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
            ],
            'locale' => [
                'current' => $locale->value,
                'direction' => $locale->direction(),
                'available' => collect(Locale::cases())->map(fn (Locale $available) => [
                    'code' => $available->value,
                    'label' => $available->label(),
                ])->all(),
            ],
            'translations' => fn () => $this->translations($locale),
            'settings' => fn () => $this->settings->all(),
            'auth' => [
                'admin' => fn () => $this->admin($request),
            ],
            'unreadMessages' => fn () => $request->user('web') instanceof Admin
                ? $this->contactMessages->unreadCount()
                : null,
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ],
        ];
    }

    /**
     * The JSON translation strings for the given locale, used by the
     * front-end's `t()` helper. English keys are their own translation, so
     * a missing file or key falls back to the key itself.
     *
     * @return array<string, string>
     */
    private function translations(Locale $locale): array
    {
        $path = lang_path("{$locale->value}.json");

        return is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : [];
    }

    /**
     * @return array{id: int, name: string, email: string, role: string, role_label: string, is_super_admin: bool}|null
     */
    private function admin(Request $request): ?array
    {
        $admin = $request->user('web');

        if (! $admin instanceof Admin) {
            return null;
        }

        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role->value,
            'role_label' => __($admin->role->label()),
            'is_super_admin' => $admin->isSuperAdmin(),
        ];
    }
}
