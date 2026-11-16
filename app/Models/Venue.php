<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'capacity',
        'price_per_hour',
        'image',
        'showcase_images',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price_per_hour'  => 'decimal:2',
        'capacity'        => 'integer',
        'is_active'       => 'boolean',
        'showcase_images' => 'array',
        'features'        => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Venue $venue): void {
            if (blank($venue->slug)) {
                $venue->slug = $venue->generateUniqueSlug();
            }
        });
    }

    /**
     * Build a slug from the title that does not collide with another venue.
     */
    public function generateUniqueSlug(): string
    {
        $base = Str::slug((string) $this->title) ?: 'venue';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    /**
     * Retry slug generation when a concurrent insert claims the same slug first.
     */
    public function save(array $options = []): bool
    {
        $autoSlug = blank($this->slug);
        $attempts = 0;

        while (true) {
            try {
                return parent::save($options);
            } catch (UniqueConstraintViolationException $exception) {
                if (! $autoSlug || $attempts++ >= 3) {
                    throw $exception;
                }

                $this->slug = null;
                $this->slug = $this->generateUniqueSlug();
            }
        }
    }
}
