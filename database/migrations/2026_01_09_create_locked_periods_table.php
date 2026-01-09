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
        Schema::create('locked_periods', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location')->nullable(); // null = all locations
            $table->foreignId('locked_by')->constrained('users')->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locked_periods');
    }
};
