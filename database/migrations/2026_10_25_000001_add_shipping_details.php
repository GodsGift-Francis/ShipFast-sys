<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('origin_postal_code', 30)->nullable();
            $table->string('destination_postal_code', 30)->nullable();
            $table->string('contents_description')->nullable();
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->string('hs_code', 10)->nullable();
            $table->string('country_of_manufacture', 120)->nullable();
            $table->boolean('signature_required')->default(false);
            $table->string('delivered_to', 120)->nullable();
            $table->json('rate_breakdown')->nullable();
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipment_id');
            $table->dropColumn('expires_at');
        });
        Schema::table('shipments', fn (Blueprint $table) => $table->dropColumn(['origin_postal_code', 'destination_postal_code', 'contents_description', 'declared_value', 'hs_code', 'country_of_manufacture', 'signature_required', 'delivered_to', 'rate_breakdown']));
    }
};
