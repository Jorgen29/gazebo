<?php

namespace App\Support;

use App\Models\Venue;

class VenuePresenter
{
    /**
     * Generic cover image used when a venue has no uploaded gallery.
     */
    public const PLACEHOLDER_IMAGE = 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80';

    /**
     * Map a venue to the array shape the public views consume.
     */
    public static function toArray(Venue $venue): array
    {
        $gallery = static::gallery($venue);

        return [
            'id' => (string) $venue->id,
            'slug' => $venue->slug,
            'title' => $venue->title,
            'capacity' => 'up to ' . $venue->capacity . ' Guests',
            'type' => 'Venue / Event Space',
            'price' => '₱' . number_format((float) $venue->price_per_hour, 2) . ' / Hour',
            'description' => (string) $venue->description,
            'image' => $gallery[0],
            'gallery' => $gallery,
            'inclusions' => is_array($venue->features) ? array_values(array_filter($venue->features)) : [],
        ];
    }

    /**
     * Normalise the venue's cover and showcase images into public URLs.
     */
    public static function gallery(Venue $venue): array
    {
        $images = [$venue->image, ...(array) ($venue->showcase_images ?? [])];
        $gallery = [];

        foreach ($images as $image) {
            if (blank($image)) {
                continue;
            }

            $gallery[] = str_starts_with((string) $image, 'http')
                ? (string) $image
                : asset('storage/' . ltrim((string) $image, '/'));
        }

        $gallery = array_values(array_unique($gallery));

        return $gallery ?: [static::PLACEHOLDER_IMAGE];
    }
}
