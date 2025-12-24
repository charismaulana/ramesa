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
        Schema::table('attendances', function (Blueprint $table) {
            $table->softDeletes(); // deleted_at column
            $table->string('edited_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamp('edited_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['edited_by', 'deleted_by', 'edited_at']);
        });
    }
};
