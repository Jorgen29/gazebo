<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VenueBookingController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Models\Inquiry;
use App\Models\Venue;
use Carbon\Carbon;

Route::get('/', function () {
    $spaces = Venue::query()
        ->where('is_active', true)
        ->orderBy('title')
        ->get()
        ->map(function ($venue) {
            $gallery = collect([$venue->image, ...(array) ($venue->showcase_images ?? [])])
                ->filter()
                ->map(function ($image) {
                    if (!$image) {
                        return null;
                    }

                    return str_starts_with((string) $image, 'http')
                        ? (string) $image
                        : asset('storage/' . ltrim((string) $image, '/'));
                })
                ->filter()
                ->values()
                ->all();

            if (empty($gallery)) {
                $gallery[] = 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80';
            }

            return [
                'id' => (string) $venue->id,
                'title' => $venue->title,
                'description' => $venue->description,
                'capacity' => 'up to ' . $venue->capacity . ' Guests',
                'price' => '₱' . number_format((float) $venue->price_per_hour, 2) . ' / Hour',
                'image' => $gallery[0],
                'gallery' => $gallery,
                'inclusions' => is_array($venue->features) ? array_values(array_filter($venue->features)) : [],
            ];
        })
        ->values()
        ->all();

    $reservedSchedule = Inquiry::query()
        ->where('status', 'approved')
        ->get()
        ->groupBy('venue_id')
        ->map(function ($inquiries) {
            return $inquiries->map(function ($item) {
                $startFormatted = Carbon::parse($item->start_time)->format('g:i A');
                $endFormatted = Carbon::parse($item->end_time)->format('g:i A');

                return [
                    'date' => Carbon::parse($item->booking_date)->format('Y-m-d'),
                    'start' => Carbon::parse($item->start_time)->format('H:i'),
                    'end' => Carbon::parse($item->end_time)->format('H:i'),
                    'label' => "{$startFormatted} - {$endFormatted} ({$item->full_name})",
                ];
            })->values()->all();
        })
        ->mapWithKeys(function ($entries, $key) {
            return [(string) $key => $entries];
        })
        ->all();

    return view('welcome', compact('spaces', 'reservedSchedule'));
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
            $totalInquiries = Inquiry::count();
            $pendingInquiries = Inquiry::where('status', 'pending')->count();
            $verifiedPayments = Inquiry::where('status', 'approved')->count();
            $activeVenues = Venue::where('is_active', true)->count();
            $recentPendingInquiries = Inquiry::query()
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get();

            return view('admin.dashboard', compact(
                'totalInquiries',
                'pendingInquiries',
                'verifiedPayments',
                'activeVenues',
                'recentPendingInquiries'
            ));
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
