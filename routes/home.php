<?php
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

Route::middleware('disableCookies')->group(function () {

$host = request()->getHost();

// Mail Minted-style per-domain routing: look up the user whose
// `custom_domain` matches the incoming host. Guarded behind a Schema
// check so the lookup is skipped on a fresh install (before the
// migration that adds the column has run).
if (Schema::hasColumn('users', 'custom_domain')) {
    $mappedUser = User::where('custom_domain', $host)->first();
    if ($mappedUser) {
        $routeCallback = function () use ($mappedUser) {
            $request = app('request');
            $request->merge(['littlelink' => $mappedUser->littlelink_name]);
            return app(UserController::class)->littlelink($request);
        };

        Route::get('/', $routeCallback)->name('littlelink');
        return;
    }
}

// Upstream custom_domains config-file fallback (unchanged behavior).
$customConfigs = config('advanced-config.custom_domains', []);

foreach ($customConfigs as $config) {
    if ($host == $config['domain']) {
    $routeCallback = function () use ($config) {
        $request = app('request');
        $request->merge(['littlelink' => isset($config['name']) ? $config['name'] : $config['id']]);
        if (isset($config['id'])) {
            $request->merge(['useif' => 'true']);
        }
        return app(UserController::class)->littlelink($request);
    };

    Route::get('/', $routeCallback)->name('littlelink');

    return;
    }
}

$customHomeUrl = config('advanced-config.custom_home_url', '/home');
$disableHomePageConfig = config('advanced-config.disable_home_page');
$redirectHomePageConfig = config('advanced-config.redirect_home_page');

if (env('HOME_URL') != '') {
    Route::get('/', [UserController::class, 'littlelinkhome'])->name('littlelink');
    if ($disableHomePageConfig == 'redirect') {
        Route::get($customHomeUrl, function () use ($redirectHomePageConfig) {
            return redirect($redirectHomePageConfig);
        });
    } elseif ($disableHomePageConfig != 'true') {
        Route::get($customHomeUrl, [App\Http\Controllers\HomeController::class, 'home'])->name('home');
    }
} else {
    if ($disableHomePageConfig == 'redirect') {
        Route::get('/', function () use ($redirectHomePageConfig) {
            return redirect($redirectHomePageConfig);
        });
    } elseif ($disableHomePageConfig != 'true') {
        Route::get('/', [App\Http\Controllers\HomeController::class, 'home'])->name('home');
    }
}

});