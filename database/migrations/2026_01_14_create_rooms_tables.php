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
        // Room groups (columns in the dashboard)
        Schema::create('room_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Group 1A-1J", "Group 2A-2F"
            $table->string('location'); // Ramba, Mangunjaya, Keluang, Bentayan
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Individual rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code'); // e.g., "1A", "2B", "3C"
            $table->foreignId('room_group_id')->constrained()->onDelete('cascade');
            $table->string('location'); // Ramba, Mangunjaya, Keluang, Bentayan
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['room_code', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_groups');
    }
};
