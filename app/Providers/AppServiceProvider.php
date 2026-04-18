<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Behind Railway / Cloudflare / any TLS-terminating reverse proxy,
        // the container receives plain HTTP and Laravel generates http://
        // asset URLs by default, which browsers block as mixed content.
        // Force the URL scheme to match APP_URL when it's https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrap();
        Validator::extend('isunique', function ($attribute, $value, $parameters, $validator) {
            $value = strtolower($value);
            $query = DB::table($parameters[0])->whereRaw("LOWER({$attribute}) = ?", [$value]);

            if (isset($parameters[1])) {
                $query->where($parameters[1], '!=', $parameters[2]);
            }

            return $query->count() === 0;
        });
        Validator::extend('exturl', function ($attribute, $value, $parameters, $validator) {
            $allowed_schemes = ['http', 'https', 'mailto', 'tel'];
            return in_array(parse_url($value, PHP_URL_SCHEME), $allowed_schemes, true);
        });
        View::addNamespace('blocks', base_path('blocks'));
    }
}
