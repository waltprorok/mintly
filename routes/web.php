<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Mail\WelcomeMintlyUser;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/session-test', function () {
    session(['test' => 'working']);
    return session('test');

});

/*
 * TODO: Temporary code to test emails LIVE
 */
Route::get('/test-email', function () {
    // Grab an existing user (or fake one)
    $user = User::first();
    if (! $user) {
        return 'No users found in database.';
    }
    Mail::to($user->email)->send(
        new WelcomeMintlyUser($user)
    );
    return 'Test email sent!';
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'register'])
    ->middleware(['guest', ProtectAgainstSpam::class]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');
