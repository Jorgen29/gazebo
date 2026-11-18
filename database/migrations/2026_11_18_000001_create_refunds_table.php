<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('refund_id')->nullable()->unique();
            $table->string('reference_id');
            $table->string('payment_request_id');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('reason');
            $table->text('note')->nullable();
            $table->string('status')->index();
            $table->string('failure_code')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload');
            $table->timestamps();

            $table->index(['payment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
