<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
        });

        // Backfill existing venues with a unique slug derived from their title.
        $taken = [];
        $venues = DB::table('venues')->orderBy('id')->get(['id', 'title', 'slug']);

        foreach ($venues as $venue) {
            $base = Str::slug((string) $venue->title) ?: 'venue';
            $slug = $base;
            $suffix = 2;

            while (isset($taken[$slug]) || DB::table('venues')->where('slug', $slug)->where('id', '!=', $venue->id)->exists()) {
                $slug = $base . '-' . $suffix++;
            }

            $taken[$slug] = true;

            if ((string) $venue->slug !== $slug) {
                DB::table('venues')->where('id', $venue->id)->update(['slug' => $slug]);
            }
        }

        Schema::table('venues', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
