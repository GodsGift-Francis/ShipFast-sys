<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->enum('status', ['pending', 'processing', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled', 'on_hold'])->default('pending');
            $table->enum('service_type', ['standard', 'express', 'overnight', 'freight', 'international'])->default('standard');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->string('origin_address')->nullable();
            $table->string('origin_city');
            $table->string('origin_country');
            $table->string('destination_address')->nullable();
            $table->string('destination_city');
            $table->string('destination_country');

            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();

            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('shipping_cost', 12, 2)->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('actual_delivery')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
