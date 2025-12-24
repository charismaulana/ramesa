<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::where('role', '!=', 'super_admin')
            ->orderBy('is_approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users', compact('users'));
    }

    public function approveUser(User $user)
    {
        $user->update(['is_approved' => true]);
        return back()->with('success', 'User ' . $user->name . ' berhasil disetujui.');
    }

    public function rejectUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:tim_catering,employee',
        ]);

        $user->update(['role' => $validated['role']]);
        return back()->with('success', 'Role user berhasil diubah.');
    }
}
