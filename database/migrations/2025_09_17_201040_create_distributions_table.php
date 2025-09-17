<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('beneficiary_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('donation_id')->constrained('donations')->onDelete('cascade');
            $table->enum('delivery_status', ['assigned', 'in_progress', 'delivered'])->default('assigned');
            $table->string('proof_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};