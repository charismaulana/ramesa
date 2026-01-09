<?php

namespace App\Http\Controllers;

use App\Models\LockedPeriod;
use Illuminate\Http\Request;

class LockedPeriodController extends Controller
{
    /**
     * Display a listing of locked periods.
     */
    public function index()
    {
        $lockedPeriods = LockedPeriod::with('lockedByUser')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('locked-periods.index', compact('lockedPeriods'));
    }

    /**
     * Store a newly created locked period.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|in:Ramba,Bentayan,Mangunjaya,Keluang',
            'reason' => 'nullable|string|max:255',
        ]);

        LockedPeriod::create([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'] ?? null,
            'locked_by' => auth()->id(),
            'reason' => $validated['reason'] ?? null,
        ]);

        $locationText = $validated['location'] ?? 'All Locations';
        return redirect()->route('admin.lockedPeriods')
            ->with('success', "Period locked successfully for {$locationText}");
    }

    /**
     * Remove the specified locked period.
     */
    public function destroy($id)
    {
        $lockedPeriod = LockedPeriod::findOrFail($id);
        $lockedPeriod->delete();

        return redirect()->route('admin.lockedPeriods')
            ->with('success', 'Locked period removed successfully');
    }
}
