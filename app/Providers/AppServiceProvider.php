<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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
        Model::unguard();
        Model::shouldBeStrict(! app()->isProduction());
        Date::use(CarbonImmutable::class);
        DB::prohibitDestructiveCommands(app()->isProduction());
        JsonResource::withoutWrapping();

        RateLimiter::for('notifications', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute((int) config('app.rate_limits.notifications_per_minute', 60))->by((string) $key);
        });

        RateLimiter::for('user-actions', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute((int) config('app.rate_limits.user_actions_per_minute', 20))->by((string) $key);
        });
    }
}
