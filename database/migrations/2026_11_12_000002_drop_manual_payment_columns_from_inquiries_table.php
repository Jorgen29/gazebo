<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('inquiries', 'payment_proof')) {
                $table->dropColumn('payment_proof');
            }
            if (Schema::hasColumn('inquiries', 'payment_requested_at')) {
                $table->dropColumn('payment_requested_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('inquiries', 'payment_proof')) {
                $table->string('payment_proof')->nullable();
            }
            if (! Schema::hasColumn('inquiries', 'payment_requested_at')) {
                $table->timestamp('payment_requested_at')->nullable();
            }
        });
    }
};