<?php

namespace App\Providers;

use App\Models\ContentEntry;
use App\Support\SiteConfiguration;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SiteConfiguration::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('admin-login', function (Request $request): Limit {
            $username = Str::transliterate(Str::lower($request->input('username', '')));

            return Limit::perMinute(5)->by($username.'|'.$request->ip());
        });

        View::composer('pages.*', function ($view): void {
            $view->with('siteConfiguration', app(SiteConfiguration::class));
            $view->with('siteSettings', app(SiteConfiguration::class)->all());
            $view->with(
                'navigationPrograms',
                ContentEntry::query()
                    ->published()
                    ->where('type', 'program')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(),
            );
        });
    }
}
