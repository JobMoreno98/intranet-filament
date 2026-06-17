<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\RecursosArchivos;
use App\Models\TipoAcervo;
use Carbon\CarbonImmutable;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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
        if (config('app.env') === 'local') {
            URL::forceRootUrl(config('app.url'));
        }

        $this->configureDefaults();

        RecursosArchivos::observe(\App\Observers\RecursoArchivoObserver::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
        RateLimiter::for('media', function ($request) {

            $key = optional($request->user())->id ?: $request->ip();

            if ($request->boolean('preload')) {
                return Limit::perMinute(500)->by($key);
            }

            return Limit::perMinute(200)->by($key);
        });

        Import::resolveRelationUsing('user', function ($importModel) {
            return $importModel->belongsTo(Admin::class, 'user_id');
        });
        View::composer('*', function ($view) {
            $view->with('tiposAcervo', TipoAcervo::orderBy('nombre')->get());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
