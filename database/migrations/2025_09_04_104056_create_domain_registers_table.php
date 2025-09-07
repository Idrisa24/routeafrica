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
        Schema::create('domain_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->text('api_key');
            $table->text('api_url');
            $table->ipAddress('client_ip')->nullable();
            $table->longText('api_secret')->nullable();
            $table->boolean('isLive')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_registers');
    }
};
