<?php

namespace App\Http\Controllers;

use App\Models\EmployeeGroup;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeGroupController extends Controller
{
    /**
     * Display a listing of the resource (JSON for AJAX).
     */
    public function index()
    {
        $groups = EmployeeGroup::with('employees:id,name,employee_number,department,employee_status')->get();
        return response()->json($groups);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:employee_groups,name',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $group = EmployeeGroup::create(['name' => $validated['name']]);
        $group->employees()->attach($validated['employee_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Group created successfully',
            'group' => $group->load('employees:id,name,employee_number,department,employee_status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $group = EmployeeGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:employee_groups,name,' . $id,
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $group->update(['name' => $validated['name']]);
        $group->employees()->sync($validated['employee_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Group updated successfully',
            'group' => $group->load('employees:id,name,employee_number,department,employee_status'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $group = EmployeeGroup::findOrFail($id);
        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully',
        ]);
    }
}
