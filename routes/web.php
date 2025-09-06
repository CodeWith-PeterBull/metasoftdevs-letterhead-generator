<?php

/**
 * Web Routes Configuration
 * 
 * Defines all HTTP routes for the MetaSoft Letterhead Generator application.
 * Includes authentication-protected routes for letterhead generation functionality
 * and standard Laravel authentication routes.
 * 
 * Route Groups:
 * - Public routes: Welcome page
 * - Authenticated routes: Dashboard, Profile management, Letterhead generation
 * 
 * Security:
 * - All letterhead routes require authentication
 * - CSRF protection enabled on POST routes
 * - Middleware applied for user verification
 * 
 * @package     MetaSoft Letterhead Generator
 * @category    Route Configuration
 * @author      Metasoftdevs <info@metasoftdevs.com>
 * @copyright   2025 Metasoft Developers
 * @license     MIT License
 * @version     1.0.0
 * @link        https://www.metasoftdevs.com
 * @since       File available since Release 1.0.0
 * 
 * @see         \App\Http\Controllers\LetterHeads\LetterheadController Main letterhead controller
 * @see         \App\Http\Controllers\ProfileController User profile management
 */

use App\Http\Controllers\LetterHeads\LetterheadController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';




// Letterhead routes
Route::middleware('auth')->group(function () {
    Route::get('/letterhead', [LetterheadController::class, 'showForm'])->name('letterhead.form');
    Route::post('/letterhead/generate', [LetterheadController::class, 'generateLetterhead'])->name('letterhead.generate');
});
