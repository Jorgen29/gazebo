<?php

namespace App\Http\Controllers;

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
        $venues = Venue::latest()->get();

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
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'price_per_hour'    => 'required|numeric|min:0',
            'capacity'          => 'required|integer|min:1',
            'is_active'         => 'required|boolean',
            'description'      => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'showcase_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'features'          => 'nullable|array',
        ]);

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

        return redirect()->back()->with('success', 'Venue created successfully!');
    }

    public function update(Request $request, $id)
    {
        $venue = Venue::findOrFail($id);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'price_per_hour'    => 'required|numeric|min:0',
            'capacity'          => 'required|integer|min:1',
            'is_active'         => 'required|boolean',
            'description'      => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'showcase_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'features'          => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            if ($venue->image && Storage::disk('public')->exists($venue->image)) {
                Storage::disk('public')->delete($venue->image);
            }
            $validated['image'] = $request->file('image')->store('venues', 'public');
        }

        if ($request->hasFile('showcase_images')) {
            // Delete old showcase images
            if ($venue->showcase_images) {
                foreach ($venue->showcase_images as $oldImg) {
                    if (Storage::disk('public')->exists($oldImg)) {
                        Storage::disk('public')->delete($oldImg);
                    }
                }
            }

            $showcasePaths = [];
            foreach ($request->file('showcase_images') as $file) {
                $showcasePaths[] = $file->store('venues/showcase', 'public');
            }
            $validated['showcase_images'] = $showcasePaths;
        }

        if (!empty($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        }

        $venue->update($validated);

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
