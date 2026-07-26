<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('background_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_code')->unique();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('total_price');
            $table->string('payment_scheme'); // e.g., 'dp', 'lunas'
            $table->string('status'); // e.g., 'pending', 'paid', 'completed', 'cancelled'
            $table->integer('reschedule_count')->default(0);
            $table->text('notes')->nullable();
            $table->text('gdrive_link')->nullable();
            $table->string('source')->nullable(); // 'manual', 'frontdoor'
            $table->timestamp('reminder_sent_at')->nullable(); // Timestamp kapan reminder WA terakhir dikirim
            $table->timestamps();

            // Composite index for schedule lookup
            $table->index(['booking_date', 'start_time', 'end_time'], 'bookings_schedule_composite_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
