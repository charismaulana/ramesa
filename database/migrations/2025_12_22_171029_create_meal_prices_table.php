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
        Schema::create('meal_prices', function (Blueprint $table) {
            $table->id();
            $table->decimal('breakfast_price', 12, 2)->default(0);
            $table->decimal('lunch_price', 12, 2)->default(0);
            $table->decimal('dinner_price', 12, 2)->default(0);
            $table->decimal('supper_price', 12, 2)->default(0);
            $table->decimal('snack_price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_prices');
    }
};
