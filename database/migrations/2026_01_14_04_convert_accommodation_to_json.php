<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing accommodation string data to JSON format
        // First, get all employees with non-null accommodation
        $employees = DB::table('employees')
            ->whereNotNull('accommodation')
            ->where('accommodation', '!=', '')
            ->get();

        foreach ($employees as $employee) {
            // Convert simple string to JSON with location as key
            $location = $employee->location ?? 'Ramba';
            $accommodationJson = json_encode([$location => $employee->accommodation]);

            DB::table('employees')
                ->where('id', $employee->id)
                ->update(['accommodation' => $accommodationJson]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSON back to simple string (take first value)
        $employees = DB::table('employees')
            ->whereNotNull('accommodation')
            ->where('accommodation', '!=', '')
            ->get();

        foreach ($employees as $employee) {
            $data = json_decode($employee->accommodation, true);
            if (is_array($data) && !empty($data)) {
                $firstValue = array_values($data)[0] ?? '';
                DB::table('employees')
                    ->where('id', $employee->id)
                    ->update(['accommodation' => $firstValue]);
            }
        }
    }
};
