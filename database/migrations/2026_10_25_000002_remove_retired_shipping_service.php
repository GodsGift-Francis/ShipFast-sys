<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve historical data: deployments with retired-service records need an explicit data policy.
        foreach (['shipments', 'quotes', 'routes'] as $table) {
            if (DB::table($table)->where('service_type', 'overnight')->exists()) {
                throw new RuntimeException('Resolve historical retired-service records before applying this migration: '.$table);
            }
        }
        foreach (['shipments', 'quotes', 'routes'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->enum('service_type', ['standard', 'express', 'freight', 'international'])->default('standard')->change());
        }
    }

    public function down(): void
    {
        foreach (['shipments', 'quotes', 'routes'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->enum('service_type', ['standard', 'express', 'overnight', 'freight', 'international'])->default('standard')->change());
        }
    }
};
