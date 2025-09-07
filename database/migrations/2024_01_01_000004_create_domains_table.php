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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('hosting_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->enum('status', ['pending', 'active', 'inactive', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->json('nameservers')->nullable();
            $table->json('dns_records')->nullable();
            $table->enum('ssl_status', ['pending', 'active', 'expired', 'error'])->default('pending');
            $table->timestamp('ssl_expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
