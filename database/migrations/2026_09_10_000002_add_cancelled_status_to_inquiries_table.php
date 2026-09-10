<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE inquiries MODIFY status ENUM('pending', 'approved', 'declined', 'cancelled') NOT NULL DEFAULT 'pending'");
            } catch (\Throwable $e) {
                // If the column is already a string field, this is already compatible.
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE inquiries MODIFY status ENUM('pending', 'approved', 'declined') NOT NULL DEFAULT 'pending'");
            } catch (\Throwable $e) {
                // Leave the column as-is if the schema is already using a string status field.
            }
        }
    }
};
