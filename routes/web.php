<?php

use App\Http\Controllers\Auth\RegisterController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware(['guest', ProtectAgainstSpam::class]);

/*
|--------------------------------------------------------------------------
| Terms and Privacy Pages
|--------------------------------------------------------------------------
*/
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

/*
|--------------------------------------------------------------------------
| Marketing SEO Pages
|--------------------------------------------------------------------------
*/
Route::view('/monthly-budget-planner', 'marketing.monthly-budget-planner')->name('marketing.monthly-budget-planner');
Route::view('/expense-tracker', 'marketing.expense-tracker')->name('marketing.expense-tracker');
Route::view('/budget-tracker', 'marketing.budget-tracker')->name('marketing.budget-tracker');
Route::view('/cash-flow-planner', 'marketing.cash-flow-planner')->name('marketing.cash-flow-planner');
Route::view('/csv-import', 'marketing.csv-import')->name('marketing.csv-import');
