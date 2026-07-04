<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

if(config('advanced-config.register_url') != '') {
    $register = config('advanced-config.register_url');
} else {
    $register = "/register";
}

if(config('advanced-config.login_url') != '') {
    $login = config('advanced-config.login_url');
} else {
    $login = "/login";
}

if(config('advanced-config.forgot_password_url') != '') {
    $forgot_password = config('advanced-config.forgot_password_url');
} else {
    $forgot_password = "/forgot-password";
}

Route::post('/validate-handle', [RegisteredUserController::class, 'validateHandle']);

/*
 * Customer-facing auth is fully owned by Mail Minted:
 *   - Signup happens through /checkout on Mail Minted; the backend
 *     provisions the matching LinkStack account programmatically.
 *   - Login goes through Supabase; customers arrive here via the
 *     SSO handoff in routes/sso-mailminted.php.
 *   - Password resets, email verification, and password confirmation
 *     all target the customer's Supabase account, not a local
 *     LinkStack credential (which is an auto-generated random string
 *     the customer never sees).
 *
 * So every customer-facing auth surface below is dead. We keep the
 * route names registered (register / password.request / verification.*
 * etc.) so any framework helper or view partial that calls route(...)
 * still resolves — but the response is always 404. That preserves the
 * "single cohesive Mail Minted app" feel: customers never see a
 * LinkStack signup / reset / verification screen, even by accident.
 *
 * The two things still alive here are the login POST (admin / scripted
 * submissions) and the logout POST (backwards-compat for anything that
 * still POSTs the stock Laravel logout route).
 */
$dead = function () { abort(404); };

// Register — accounts are auto-provisioned by Mail Minted's checkout.
Route::get($register,  $dead)->name('register');
Route::post($register, $dead);

// Login: GET → redirect to Mail Minted's /login (never expose the
// LinkStack form). POST kept alive for admin panel + scripted use.
Route::get($login, function () {
    $mmUrl = rtrim((string) env('MAILMINTED_APP_URL', ''), '/');
    if ($mmUrl) {
        return redirect()->away($mmUrl . '/login');
    }
    return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)->create();
})->middleware('guest')->name('login');

Route::post($login, [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest');

// Password reset — resets Supabase credentials on Mail Minted.
Route::get($forgot_password,           $dead)->name('password.request');
Route::post($forgot_password,          $dead)->name('password.email');
Route::get('/reset-password/{token}',  $dead)->name('password.reset');
Route::post('/reset-password',         $dead)->name('password.update');

// Email verification — Supabase handles this.
Route::get('/verify-email',                     $dead)->name('verification.notice');
Route::get('/verify-email/{id}/{hash}',         $dead)->name('verification.verify');
Route::post('/email/verification-notification', $dead)->name('verification.send');

// Password confirmation — customers have no LinkStack password to confirm.
Route::get('/confirm-password',  $dead)->name('password.confirm');
Route::post('/confirm-password', $dead);

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth')
                ->name('logout');

Route::get('/blocked', function () {
                    $user = Auth::user();
                    if ($user && $user->block == 'yes') {
                        return view('auth.blocked');
                    } else {
                        return redirect(url('dashboard'));
                    }
                })->name('blocked');
                