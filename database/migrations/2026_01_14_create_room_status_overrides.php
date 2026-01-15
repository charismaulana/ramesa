<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_status_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id'); // Employee ID
            $table->string('location'); // Ramba, Mangunjaya, etc.
            $table->date('date');
            $table->enum('status', ['ON', 'OFF']); // Forced status
            $table->unsignedBigInteger('user_id')->nullable(); // Who made the override
            $table->string('reason')->nullable(); // Reason for override
            $table->timestamps();

            $table->unique(['employee_id', 'location', 'date']);
            $table->index(['location', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_status_overrides');
    }
};
