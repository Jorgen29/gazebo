<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VenueBookingController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\Admin\AdminAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/events', function () {
    return view('events');
})->name('events');

Route::get('/gallery', function () {
    return view('gallery');
})->name('gallery');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/inquire', [VenueBookingController::class, 'inquire'])->name('inquire');

// Public Routes
Route::get('/inquiry', [VenueBookingController::class, 'index'])->name('inquiry.index');
Route::get('/book', [VenueBookingController::class, 'showBookingPage'])->name('venue.book');
Route::post('/book/submit', [VenueBookingController::class, 'storeInquiry'])->name('venue.submit');

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes (Uses custom AdminAuthenticate middleware)
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Admin Inquiries Actions
        Route::get('/inquiries', [VenueBookingController::class, 'adminInquiries'])->name('inquiries');
        Route::patch('/inquiries/{id}/status', [VenueBookingController::class, 'updateStatus'])->name('inquiries.update-status');
        Route::post('/inquiries/{id}/send-payment-email', [VenueBookingController::class, 'sendPaymentEmail'])->name('inquiries.send-payment');
        Route::post('/inquiries/{id}/upload-proof', [VenueBookingController::class, 'uploadPaymentProof'])->name('inquiries.upload-proof');

        // Admin Venues Management Routes
        Route::resource('venues', VenueController::class)->except(['create', 'show', 'edit']);
    });
});
