<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_status')) {
            $query->where('employee_status', $request->employee_status);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $allowedSorts = ['employee_number', 'name', 'company', 'department', 'location', 'employee_status', 'active_status'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $employees = $query->paginate(15)->withQueryString();

        $departments = Employee::distinct()->pluck('department')->filter();
        $locations = Employee::distinct()->pluck('location')->filter();

        return view('employees.index', compact('employees', 'departments', 'locations', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_number' => 'nullable|string|max:50|unique:employees',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'accommodation' => 'nullable|string|max:255',
            'active_status' => 'required|in:active,inactive',
            'employee_status' => 'nullable|string|max:255',
        ]);

        // Auto-generate employee number if not provided (for visitors/subcontractors)
        if (empty($validated['employee_number'])) {
            $validated['employee_number'] = Employee::generateEmployeeNumber($validated['company'] ?? null);
        }

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully. QR code generated automatically.');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|max:50|unique:employees,employee_number,' . $employee->id,
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'accommodation' => 'nullable|string|max:255',
            'active_status' => 'required|in:active,inactive',
            'employee_status' => 'nullable|string|max:255',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        // Delete QR code file
        if ($employee->qr_code_path) {
            Storage::disk('public')->delete($employee->qr_code_path);
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function printCard(Employee $employee)
    {
        return view('employees.card', compact('employee'));
    }

    public function downloadCard(Employee $employee)
    {
        // Generate meal card image using GD
        $width = 400;
        $height = 600;

        // Create image with GD directly
        $image = imagecreatetruecolor($width, $height);

        // Define colors
        $darkBg = imagecolorallocate($image, 26, 10, 10);
        $gold = imagecolorallocate($image, 255, 215, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        $orange = imagecolorallocate($image, 255, 107, 53);
        $gray = imagecolorallocate($image, 136, 136, 136);
        $lightGray = imagecolorallocate($image, 204, 204, 204);
        $red = imagecolorallocate($image, 255, 69, 0);
        $greenBg = imagecolorallocate($image, 0, 50, 25);
        $green = imagecolorallocate($image, 0, 255, 136);
        $redBg = imagecolorallocate($image, 50, 15, 15);
        $redText = imagecolorallocate($image, 255, 68, 68);

        // Fill background
        imagefill($image, 0, 0, $darkBg);

        // Draw gradient-like accent line at bottom
        imagefilledrectangle($image, 30, $height - 20, 130, $height - 16, $red);
        imagefilledrectangle($image, 140, $height - 20, 240, $height - 16, $orange);
        imagefilledrectangle($image, 250, $height - 20, 370, $height - 16, $gold);

        // Title
        $title = "MEAL CARD";
        imagestring($image, 5, ($width - strlen($title) * 9) / 2, 30, $title, $gold);

        // Subtitle
        $subtitle = "Ramesa - Ramba Meal System";
        imagestring($image, 2, ($width - strlen($subtitle) * 6) / 2, 55, $subtitle, $gray);

        // Employee name
        $name = strtoupper($employee->name);
        if (strlen($name) > 30)
            $name = substr($name, 0, 27) . '...';
        imagestring($image, 5, ($width - strlen($name) * 9) / 2, 100, $name, $white);

        // Employee number
        $empNum = "ID: " . $employee->employee_number;
        imagestring($image, 4, ($width - strlen($empNum) * 8) / 2, 130, $empNum, $orange);

        // Department
        if ($employee->department) {
            $dept = $employee->department;
            imagestring($image, 3, ($width - strlen($dept) * 7) / 2, 160, $dept, $lightGray);
        }

        // Position
        if ($employee->position) {
            $pos = $employee->position;
            imagestring($image, 3, ($width - strlen($pos) * 7) / 2, 180, $pos, $lightGray);
        }

        // QR Code placeholder box
        $qrSize = 200;
        $qrX = ($width - $qrSize) / 2;
        $qrY = 220;

        // White background for QR
        $qrWhite = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, $qrX - 10, $qrY - 10, $qrX + $qrSize + 10, $qrY + $qrSize + 10, $qrWhite);

        // Try to read QR code if it exists (PNG version)
        if ($employee->qr_code_path && Storage::disk('public')->exists($employee->qr_code_path)) {
            $qrFullPath = Storage::disk('public')->path($employee->qr_code_path);

            // If SVG, render QR code text instead
            if (str_ends_with($employee->qr_code_path, '.svg')) {
                // Draw QR code representation text
                $qrText = $employee->employee_number;
                $black = imagecolorallocate($image, 0, 0, 0);
                imagestring($image, 5, $qrX + 50, $qrY + 80, "QR CODE", $black);
                imagestring($image, 4, $qrX + 30, $qrY + 110, $qrText, $black);
            } else {
                // Try to load PNG QR
                $qrImg = @imagecreatefrompng($qrFullPath);
                if ($qrImg) {
                    imagecopyresampled($image, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));
                    imagedestroy($qrImg);
                }
            }
        } else {
            // No QR - show placeholder
            $black = imagecolorallocate($image, 0, 0, 0);
            imagestring($image, 4, $qrX + 60, $qrY + 90, "NO QR CODE", $black);
        }

        // Status badge
        $statusY = $qrY + $qrSize + 30;
        $statusText = strtoupper($employee->active_status);
        $statusWidth = strlen($statusText) * 8 + 40;
        $statusX = ($width - $statusWidth) / 2;

        if ($employee->active_status === 'active') {
            imagefilledrectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $greenBg);
            imagerectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $green);
            imagestring($image, 3, $statusX + 20, $statusY + 5, $statusText, $green);
        } else {
            imagefilledrectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $redBg);
            imagerectangle($image, $statusX, $statusY, $statusX + $statusWidth, $statusY + 25, $redText);
            imagestring($image, 3, $statusX + 20, $statusY + 5, $statusText, $redText);
        }

        // Footer
        $footer = "Scan QR code for meal attendance";
        imagestring($image, 2, ($width - strlen($footer) * 6) / 2, $height - 50, $footer, $gray);

        // Output as PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $filename = 'meal_card_' . $employee->employee_number . '.png';

        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
