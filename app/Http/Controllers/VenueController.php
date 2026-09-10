<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VenueController extends Controller
{
    /**
     * Display a listing of all venues for the admin.
     */
    public function index(Request $request)
    {
        $venues = Venue::latest()->get()->map(function (Venue $venue) {
            $bookings = Inquiry::query()
                ->where('venue_id', (string) $venue->id)
                ->where('status', 'approved')
                ->get()
                ->map(function ($item) {
                    $startTime = \Carbon\Carbon::parse($item->start_time)->format('g:i A');
                    $endTime = \Carbon\Carbon::parse($item->end_time)->format('g:i A');

                    return [
                        'date' => \Carbon\Carbon::parse($item->booking_date)->format('Y-m-d'),
                        'start' => \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                        'end' => \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                        'label' => "{$startTime} - {$endTime} ({$item->full_name})",
                    ];
                })
                ->values()
                ->all();

            return array_merge($venue->toArray(), [
                'bookings' => $bookings,
            ]);
        })->all();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($venues);
        }

        return view('admin.venues', compact('venues'));
    }



    /**
     * Store a newly created venue in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'             => 'required|string|max:255',
                'price_per_hour'    => 'required|numeric|min:0',
                'capacity'          => 'required|integer|min:1',
                'is_active'         => 'required|boolean',
                'description'      => 'nullable|string',
                'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp',
                'showcase_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
                'features'          => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        // Cover Image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('venues', 'public');
        }

        // Showcase Images
        if ($request->hasFile('showcase_images')) {
            $showcasePaths = [];
            foreach ($request->file('showcase_images') as $file) {
                $showcasePaths[] = $file->store('venues/showcase', 'public');
            }
            $validated['showcase_images'] = $showcasePaths;
        }

        // Filter null/empty features
        if (!empty($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        }

        Venue::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Venue created successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Venue created successfully!');
    }

    public function update(Request $request, $id)
    {
        $venue = Venue::findOrFail($id);

        try {
            $validated = $request->validate([
                'title'             => 'required|string|max:255',
                'price_per_hour'    => 'required|numeric|min:0',
                'capacity'          => 'required|integer|min:1',
                'is_active'         => 'required|boolean',
                'description'      => 'nullable|string',
                'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp',
                'showcase_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
                'features'          => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        $existingShowcaseImages = is_array($venue->showcase_images) ? $venue->showcase_images : [];
        $removedShowcaseImages = array_values(array_filter(array_map(function ($item) {
            $clean = trim((string) $item, "\t\n\r/");
            if ($clean === '') {
                return null;
            }

            return preg_replace('#^https?://[^/]+#i', '', $clean)
                ? preg_replace('#^https?://[^/]+#i', '', $clean)
                : $clean;
        }, preg_split('/[,]+/', (string) $request->input('removed_showcase_images', ''), -1, PREG_SPLIT_NO_EMPTY))));

        $remainingShowcaseImages = $existingShowcaseImages;

        if (!empty($removedShowcaseImages)) {
            foreach ($removedShowcaseImages as $removedPath) {
                $normalized = trim((string) preg_replace('#^/?storage/?#', '', $removedPath), "/\t\n\r");
                if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
                    Storage::disk('public')->delete($normalized);
                }

                $remainingShowcaseImages = array_values(array_filter($remainingShowcaseImages, function ($path) use ($removedPath, $normalized) {
                    $current = trim((string) preg_replace('#^/?storage/?#', '', (string) $path), "/\t\n\r");
                    $comparison = trim((string) preg_replace('#^/?storage/?#', '', $removedPath), "/\t\n\r");
                    return $current !== $comparison && $current !== $normalized;
                }));
            }
        }

        if ($request->hasFile('image')) {
            if ($venue->image && Storage::disk('public')->exists($venue->image)) {
                Storage::disk('public')->delete($venue->image);
            }
            $validated['image'] = $request->file('image')->store('venues', 'public');
        }

        if ($request->hasFile('showcase_images')) {
            $showcasePaths = [];
            foreach ($request->file('showcase_images') as $file) {
                $showcasePaths[] = $file->store('venues/showcase', 'public');
            }

            $remainingShowcaseImages = array_values(array_merge(
                $remainingShowcaseImages,
                $showcasePaths
            ));
        }

        if (!empty($remainingShowcaseImages)) {
            $validated['showcase_images'] = array_values(array_unique(array_filter($remainingShowcaseImages, fn($path) => !empty($path))));
        } else {
            $validated['showcase_images'] = [];
        }

        if (!empty($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        }

        $venue->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Venue updated successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Venue updated successfully!');
    }

    /**
     * Remove the specified venue from storage.
     */
    public function destroy($id)
    {
        $venue = Venue::findOrFail($id);

        // Remove associated image asset if exists
        if ($venue->image && Storage::disk('public')->exists($venue->image)) {
            Storage::disk('public')->delete($venue->image);
        }

        $venue->delete();

        return redirect()->back()->with('success', 'Venue deleted successfully!');
    }
}
