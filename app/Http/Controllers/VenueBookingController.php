<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Venue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\NewInquiryNotification;
use App\Mail\PaymentInstructionsMail;
use Carbon\Carbon;

class VenueBookingController extends Controller
{
    // Shared master list of venue details & reservations
    private function getVenuesData()
    {
        return [
            'grand-hall' => [
                'id' => 'grand-hall',
                'title' => 'The Grand Hall',
                'capacity' => 'up to 300 Guests',
                'type' => 'Indoor / Air-Conditioned',
                'price' => '₱6,500 / Hour',
                'description' => 'Our flagship indoor grand ballroom featuring crystal chandeliers, full climate control, and elevated stage setup.',
                'gallery' => [
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80'
                ],
                'inclusions' => [
                    'Full Air-Conditioning System',
                    'Private Bridal Suite Access',
                    'Basic Sound & Lighting System',
                    'Tables & Chivari Chairs Set',
                    'Commercial Prep Kitchen Access'
                ]
            ],
            'grotto' => [
                'id' => 'grotto',
                'title' => 'The Grotto & Garden',
                'capacity' => 'up to 120 Guests',
                'type' => 'Outdoor / Garden',
                'price' => '₱3,000 / Hour',
                'description' => 'An enchanting open-air sanctuary surrounded by manicured flora and a stone water grotto.',
                'gallery' => [
                    'https://images.unsplash.com/photo-1545232979-fbf59202c396?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1200&q=80'
                ],
                'inclusions' => [
                    'Lush Garden Ceremony Area',
                    'Ambient Fairy Lights Canopy',
                    'Acoustic Sound Integration',
                    'Weather Backup Canopy'
                ]
            ],
            'pavilion' => [
                'id' => 'pavilion',
                'title' => 'The Pavilion Hall',
                'capacity' => 'up to 200 Guests',
                'type' => 'Semi-Open / Covered',
                'price' => '₱4,500 / Hour',
                'description' => 'High-ceiling open architectural space providing natural airflow with full weather shelter.',
                'gallery' => [
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80'
                ],
                'inclusions' => [
                    'High Ceiling Fans',
                    'Raised Performance Stage',
                    'Integrated Surround Sound',
                    'Barcounter & Buffet Station'
                ]
            ],
            'glass-house' => [
                'id' => 'glass-house',
                'title' => 'The Glass House',
                'capacity' => 'up to 150 Guests',
                'type' => 'Indoor / Glass Structure',
                'price' => '₱5,200 / Hour',
                'description' => 'Panoramic glass pavilion with seamless views of surrounding greenery for botanical-themed events.',
                'gallery' => [
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1545232979-fbf59202c396?auto=format&fit=crop&w=1200&q=80'
                ],
                'inclusions' => [
                    '360° Glass Wall View',
                    'Full Air-Conditioning System',
                    'Dimmable Mood Lighting',
                    'VIP Holding Room'
                ]
            ],
            'poolside' => [
                'id' => 'poolside',
                'title' => 'The Poolside Veranda',
                'capacity' => 'up to 80 Guests',
                'type' => 'Outdoor / Poolside',
                'price' => '₱2,500 / Hour',
                'description' => 'A relaxed outdoor setting by the resort pool for cocktail hours and birthday bashes.',
                'gallery' => [
                    'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1200&q=80'
                ],
                'inclusions' => [
                    'Underwater Pool Mood Lights',
                    'Cocktail High Tables & Stools',
                    'Lounge Seating Furniture Set',
                    'Grill & Bar Station Access'
                ]
            ],
            'surroundings' => [
                'id' => 'surroundings',
                'title' => 'The Courtyard & Grounds',
                'capacity' => 'up to 250 Guests',
                'type' => 'Outdoor / Open-Air',
                'price' => '₱3,800 / Hour',
                'description' => 'Sprawling grass grounds offering unlimited flexibility for custom marquee tent setups.',
                'gallery' => [
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80'
                ],
                'inclusions' => [
                    'Open Lawn Space',
                    'Drive-in Food Truck Access',
                    'Perimeter Safety Lighting',
                    'High Capacity Power Outlets'
                ]
            ]
        ];
    }

    private function getReservedSchedules()
    {
        return [
            'grand-hall' => [
                ['date' => '2026-09-15', 'start' => '08:00', 'end' => '12:00', 'label' => '8:00 AM - 12:00 PM (Wedding Banquet)'],
                ['date' => '2026-09-18', 'start' => '10:00', 'end' => '14:00', 'label' => '10:00 AM - 2:00 PM (Corporate Summit)'],
                ['date' => '2026-09-22', 'start' => '18:00', 'end' => '22:00', 'label' => '6:00 PM - 10:00 PM (Gala Reception)'],
                ['date' => '2026-09-25', 'start' => '13:00', 'end' => '17:00', 'label' => '1:00 PM - 5:00 PM (Debut Celebration)'],
                ['date' => '2026-09-28', 'start' => '09:00', 'end' => '16:00', 'label' => '9:00 AM - 4:00 PM (Seminar Workshop)']
            ],
            'grotto' => [
                ['date' => '2026-09-15', 'start' => '08:00', 'end' => '12:00', 'label' => '8:00 AM - 12:00 PM (Garden Wedding Ceremony)'],
                ['date' => '2026-09-17', 'start' => '16:00', 'end' => '19:00', 'label' => '4:00 PM - 7:00 PM (Sunset Vow Renewal)'],
                ['date' => '2026-09-20', 'start' => '18:00', 'end' => '22:00', 'label' => '6:00 PM - 10:00 PM (Acoustic Night)'],
                ['date' => '2026-09-24', 'start' => '09:00', 'end' => '12:00', 'label' => '9:00 AM - 12:00 PM (Photoshoot Booking)'],
                ['date' => '2026-09-29', 'start' => '14:00', 'end' => '18:00', 'label' => '2:00 PM - 6:00 PM (Engagement Party)']
            ],
            'pavilion' => [
                ['date' => '2026-09-15', 'start' => '12:00', 'end' => '16:00', 'label' => '12:00 PM - 4:00 PM (Birthday Bash)'],
                ['date' => '2026-09-19', 'start' => '08:00', 'end' => '12:00', 'label' => '8:00 AM - 12:00 PM (Morning Brunch)']
            ],
            'glass-house' => [
                ['date' => '2026-09-16', 'start' => '08:00', 'end' => '12:00', 'label' => '8:00 AM - 12:00 PM (Botanical High Tea)']
            ],
            'poolside' => [
                ['date' => '2026-09-15', 'start' => '08:00', 'end' => '12:00', 'label' => '8:00 AM - 12:00 PM (Poolside Barbecue)']
            ],
            'surroundings' => [
                ['date' => '2026-09-16', 'start' => '09:00', 'end' => '17:00', 'label' => '9:00 AM - 5:00 PM (Outdoor Team Building)']
            ]
        ];
    }

    private function normalizeVenueGallery(mixed $images): array
    {
        $gallery = [];

        foreach ((array) $images as $image) {
            if (!$image) {
                continue;
            }

            $gallery[] = str_starts_with((string) $image, 'http') ? (string) $image : Storage::url((string) $image);
        }

        return array_values(array_unique($gallery));
    }

    public function inquire()
    {
        $spaces = Venue::query()
            ->where('is_active', true)
            ->latest()
            ->get()
            ->map(function (Venue $venue) {
                $gallery = $this->normalizeVenueGallery([$venue->image, ...(array) ($venue->showcase_images ?? [])]);

                if (empty($gallery)) {
                    $gallery[] = 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80';
                }

                $features = is_array($venue->features) ? array_values(array_filter($venue->features)) : [];

                return [
                    'id' => (string) $venue->id,
                    'title' => $venue->title,
                    'capacity' => 'up to ' . $venue->capacity . ' Guests',
                    'type' => 'Venue / Event Space',
                    'image' => $gallery[0],
                    'gallery' => $gallery,
                    'price' => '₱' . number_format((float) $venue->price_per_hour, 2) . ' / Hour',
                    'description' => $venue->description ?: 'A versatile venue space ready for your next memorable event.',
                    'inclusions' => $features ?: ['Flexible layout', 'Event-ready setup', 'Guest-friendly atmosphere'],
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

        return view('inquire', compact('spaces', 'reservedSchedule'));
    }

    public function index()
    {
        $spaces = array_values($this->getVenuesData());
        $reservedSchedule = $this->getReservedSchedules();

        return view('inquiry', compact('spaces', 'reservedSchedule'));
    }

    public function showBookingPage(Request $request)
    {
        $spaceId = $request->query('space', 'grand-hall');
        $selectedDate = $request->query('date', '2026-09-15');

        $databaseVenue = null;
        if (is_numeric($spaceId)) {
            $databaseVenue = Venue::query()->where('is_active', true)->find((int) $spaceId);
        }

        $spaces = $this->getVenuesData();
        $space = $databaseVenue ? [
            'id' => (string) $databaseVenue->id,
            'title' => $databaseVenue->title,
            'capacity' => 'up to ' . $databaseVenue->capacity . ' Guests',
            'type' => 'Venue / Event Space',
            'price' => '₱' . number_format((float) $databaseVenue->price_per_hour, 2) . ' / Hour',
            'description' => $databaseVenue->description ?: 'A versatile venue space ready for your next memorable event.',
            'gallery' => $this->normalizeVenueGallery([$databaseVenue->image, ...(array) ($databaseVenue->showcase_images ?? [])]),
            'inclusions' => is_array($databaseVenue->features) ? array_values(array_filter($databaseVenue->features)) : ['Flexible layout', 'Event-ready setup', 'Guest-friendly atmosphere'],
        ] : ($spaces[$spaceId] ?? $spaces['grand-hall']);

        if ($databaseVenue && empty($space['gallery'])) {
            $space['gallery'] = ['https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80'];
        }

        // 1. Get static/hardcoded schedules
        $reservedSchedules = $this->getReservedSchedules();

        // 2. Query only approved inquiries for the selected venue.
        $dbInquiries = Inquiry::where('venue_id', $spaceId)
            ->where('status', 'approved')
            ->get()
            ->map(function ($item) {
                $startFormatted = Carbon::parse($item->start_time)->format('g:i A');
                $endFormatted = Carbon::parse($item->end_time)->format('g:i A');

                return [
                    'date'  => Carbon::parse($item->booking_date)->format('Y-m-d'),
                    'start' => Carbon::parse($item->start_time)->format('H:i'),
                    'end'   => Carbon::parse($item->end_time)->format('H:i'),
                    'label' => "{$startFormatted} - {$endFormatted} ({$item->full_name})"
                ];
            })
            ->toArray();

        // 3. Merge database records into the reserved schedules for this space
        $existingForSpace = $reservedSchedules[$spaceId] ?? [];
        $reservedSchedules[$spaceId] = array_merge($existingForSpace, $dbInquiries);

        $allBookings = $reservedSchedules[$spaceId];

        return view('book', compact('space', 'selectedDate', 'allBookings', 'reservedSchedules'));
    }

    public function storeInquiry(Request $request)
    {
        $validated = $request->validate([
            'venue_id'      => 'required|string',
            'venue_title'   => 'required|string',
            'booking_date'  => 'required|date',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'email_contact' => 'required|string|max:255',
        ]);

        $inquiry = Inquiry::create($validated);

        // Send email notification to Admin
        try {
            Mail::to('bacolodjorgen29@gmail.com')->send(new NewInquiryNotification($inquiry));
        } catch (\Exception $e) {
            // Log error if mail server fails
            \Log::error('Mail sending failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Your reservation request has been submitted successfully!');
    }

    // Admin Dashboard View: List all submitted inquiries
    public function adminInquiries(Request $request)
    {
        $status = $request->query('status');

        $query = Inquiry::latest();

        if ($status) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(10);

        return view('admin.inquiries', compact('inquiries'));
    }

    // Send GCash Payment instructions email to customer
    public function sendPaymentEmail($id)
    {
        $inquiry = Inquiry::findOrFail($id);

        if (!$inquiry->email) {
            return back()->with('error', 'Customer email address is missing.');
        }

        Mail::to($inquiry->email)->send(new PaymentInstructionsMail($inquiry));

        $inquiry->update([
            'payment_requested_at' => now(),
        ]);

        return back()->with('success', 'Payment instruction email sent to customer!');
    }

    // Upload proof of payment manually from admin (when customer replies with screenshot)
    public function uploadPaymentProof(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $inquiry = Inquiry::findOrFail($id);

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $inquiry->update([
                'payment_proof' => $path,
            ]);
        }

        return back()->with('success', 'Payment proof attached successfully. You can now approve the booking.');
    }

    // Update Inquiry status
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,approved,declined']);

        $inquiry = Inquiry::findOrFail($id);
        $inquiry->update(['status' => $request->status]);

        return back()->with('success', 'Inquiry status updated successfully.');
    }
}
