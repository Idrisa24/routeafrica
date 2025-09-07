<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tlds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // .com, .net, .org, etc.
            $table->string('registrar')->nullable(); // Namecheap, ResellerClub, etc.
            $table->decimal('register_price', 10, 2);
            $table->decimal('renewal_price', 10, 2);
            $table->decimal('transfer_price', 10, 2);
            $table->integer('min_years')->default(1);
            $table->integer('max_years')->default(10);
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_privacy')->default(true);
            $table->boolean('supports_transfer')->default(true);
            $table->json('requirements')->nullable(); // Special requirements
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tlds');
    }
};
