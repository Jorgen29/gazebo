<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('email')->after('full_name')->nullable();
            $table->string('payment_proof')->nullable()->after('status');
            $table->timestamp('payment_requested_at')->nullable()->after('payment_proof');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['email', 'payment_proof', 'payment_requested_at']);
        });
    }
};