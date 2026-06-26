<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('origin_city');
            $table->string('origin_country');
            $table->string('destination_city');
            $table->string('destination_country');
            $table->enum('service_type', ['standard', 'express', 'overnight', 'freight', 'international'])->default('standard');
            $table->integer('transit_days')->default(1);
            $table->decimal('base_price', 12, 2)->default(0);
            $table->string('currency', 8)->default('USD');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
