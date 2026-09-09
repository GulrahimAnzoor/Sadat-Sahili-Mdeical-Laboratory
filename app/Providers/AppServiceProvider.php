<?php

namespace App\Providers;

use App\Enums\LabPermission;
use App\Models\User;
use App\Support\LabAlerts;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Gate::before(function (?User $user, string $ability): ?bool {
            if ($user === null) {
                return null;
            }

            if ($user->is_admin) {
                return true;
            }

            if (! LabPermission::accepts($ability)) {
                return null;
            }

            return $user->hasPermission($ability);
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer('components.layout', function ($view): void {
            $user = auth()->user();

            if (! $user instanceof User) {
                return;
            }

            $user->loadMissing(['staff.role']);

            LabAlerts::scanExpiryAlertsIfDue();

            $view->with([
                'unreadNotificationCount' => Cache::remember(
                    'unread-notifications:'.$user->id,
                    now()->addSeconds(15),
                    fn (): int => $user->unreadNotifications()->count(),
                ),
            ]);
        });
    }
}
