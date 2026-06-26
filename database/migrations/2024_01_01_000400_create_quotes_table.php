<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->enum('service_type', ['standard', 'express', 'overnight', 'freight', 'international'])->default('standard');
            $table->string('origin_city');
            $table->string('origin_country');
            $table->string('destination_city');
            $table->string('destination_country');
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->integer('estimated_days')->nullable();
            $table->enum('status', ['pending', 'active', 'accepted', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
