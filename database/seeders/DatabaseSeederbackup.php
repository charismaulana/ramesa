<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gsramba.com',
            'password' => 'gsramba123',
            'role' => 'super_admin',
            'is_approved' => true,
        ]);

        // Create 50 employees
        $companies = ['PT PEP', 'PT Inconis Nusa Jaya', 'PT AKB'];
        $departments = ['PO', 'GS', 'HSSE', 'RAM', 'SCM', 'WOWS'];
        $positions = ['Sr Operator', 'Supervisor', 'Asst Manager', 'Sr Technician', 'Operator', 'Jr Officer', 'Technician'];
        $locations = ['Ramba', 'Bentayan', 'Keluang', 'Mangunjaya', 'Rig 01', 'Rig 02', 'Rig 03', 'Rig 06']; // Homebase options
        $accommodations = ['GRU', 'GRM', 'Block Staff', 'Block Non Staff', 'Portacamp'];
        $employeeStatuses = ['Pekerja', 'TKJP', 'TA', 'Sub Contractor', 'Visitor'];

        $employees = [];

        for ($i = 1; $i <= 50; $i++) {
            $employees[] = Employee::create([
                'employee_number' => '1901' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $this->generateName($i),
                'company' => $companies[array_rand($companies)],
                'position' => $positions[array_rand($positions)],
                'department' => $departments[array_rand($departments)],
                'location' => $locations[array_rand($locations)],
                'accommodation' => $accommodations[array_rand($accommodations)],
                'active_status' => $i <= 45 ? 'active' : 'inactive', // 45 active, 5 inactive
                'employee_status' => $employeeStatuses[array_rand($employeeStatuses)],
            ]);
        }

        // Create attendance records for the last 30 days
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'supper'];
        $scanMethods = ['qr_scan', 'manual'];
        $mealLocations = ['Ramba', 'Bentayan', 'Keluang', 'Mangunjaya']; // Meal locations (camps only)

        foreach ($employees as $employee) {
            if ($employee->active_status !== 'active')
                continue;

            // Random meals for past 30 days
            for ($day = 30; $day >= 0; $day--) {
                $date = Carbon::now()->subDays($day);

                // Randomly decide which meals this employee had (skip some days/meals)
                if (rand(0, 100) > 15) { // 85% chance of attending
                    $mealsToday = array_rand(array_flip($mealTypes), rand(1, 3));
                    if (!is_array($mealsToday))
                        $mealsToday = [$mealsToday];

                    foreach ($mealsToday as $meal) {
                        $hour = match ($meal) {
                            'breakfast' => rand(6, 8),
                            'lunch' => rand(11, 13),
                            'dinner' => rand(17, 19),
                            'supper' => rand(21, 23),
                        };

                        $scanMethod = $scanMethods[array_rand($scanMethods)];

                        // Use employee's location if it's a camp, otherwise random camp location
                        $mealLocation = in_array($employee->location, $mealLocations)
                            ? $employee->location
                            : $mealLocations[array_rand($mealLocations)];

                        Attendance::create([
                            'employee_id' => $employee->id,
                            'meal_type' => $meal,
                            'scan_method' => $scanMethod,
                            'recorded_by' => $scanMethod === 'manual' ? 'Catering Staff' : null,
                            'scanned_at' => $date->copy()->setTime($hour, rand(0, 59), rand(0, 59)),
                            'location' => $mealLocation,
                        ]);
                    }
                }
            }
        }
    }

    private function generateName(int $index): string
    {
        $firstNames = [
            'Ahmad',
            'Budi',
            'Cahya',
            'Dewi',
            'Eko',
            'Fitri',
            'Gunawan',
            'Hendra',
            'Indra',
            'Joko',
            'Kartini',
            'Lina',
            'Made',
            'Ningsih',
            'Oscar',
            'Putu',
            'Qori',
            'Rizky',
            'Sari',
            'Tono',
            'Umi',
            'Vina',
            'Wati',
            'Xander',
            'Yani',
            'Zahra',
            'Arief',
            'Bambang',
            'Citra',
            'Dimas',
            'Elsa',
            'Farhan',
            'Gita',
            'Hasan',
            'Irma',
            'Jihan',
            'Kevin',
            'Laras',
            'Maya',
            'Nanda',
            'Oktavia',
            'Prasetyo',
            'Qila',
            'Rahman',
            'Sinta',
            'Tirta',
            'Umar',
            'Vera',
            'Wira',
            'Yuda'
        ];

        $lastNames = [
            'Pratama',
            'Wijaya',
            'Santoso',
            'Kusuma',
            'Putra',
            'Sari',
            'Hidayat',
            'Syahputra',
            'Nugroho',
            'Rahayu',
            'Suryadi',
            'Permana',
            'Hartono',
            'Utomo',
            'Setiawan'
        ];

        return $firstNames[$index - 1] . ' ' . $lastNames[array_rand($lastNames)];
    }
}
