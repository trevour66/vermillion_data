<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DuplicateChecker;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});


Route::post('/upload-file', [DuplicateChecker::class, 'check_for_duplicate'])->name('upload_file');
Route::get('/download-file', [DuplicateChecker::class, 'download_file'])->name('download_file');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/get-websites', [DashboardController::class, 'index_api'])->name('dashboard.index_api');

    Route::delete('/delete-website/{website_id}', [DashboardController::class, 'destroy'])->name('website.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
