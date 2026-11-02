<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('customer_id')->nullable()->unique()->constrained('customers')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->json('preferences')->nullable();
        });
        DB::table('users')->update(['approved_at' => now()]);
        Schema::table('app_notifications', fn (Blueprint $t) => $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete());
        Schema::create('profile_change_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->json('changes');
            $t->string('status', 20)->default('pending')->index();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('review_note')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_change_requests');
        Schema::table('app_notifications', fn (Blueprint $t) => $t->dropConstrainedForeignId('user_id'));
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('customer_id');
            $t->dropColumn(['approved_at', 'preferences']);
        });
    }
};
