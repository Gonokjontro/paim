<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        $user->name = $request->name;

        // Handle Direct File Upload for Profile Picture
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $uploadDir = public_path('uploads/avatars');

            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            // Delete old custom avatar file if it exists
            if ($user->avatar_url && str_contains($user->avatar_url, '/uploads/avatars/')) {
                $oldPath = public_path(ltrim($user->avatar_url, '/'));
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $user->avatar_url = '/uploads/avatars/' . $filename;
        }

        $user->save();

        AuditLog::create([
            'workspace_id' => $user->workspace_id,
            'event_type' => 'profile_updated',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);

        return redirect()->route('profile.index')->with('success', 'Profile information and profile picture updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        AuditLog::create([
            'workspace_id' => $user->workspace_id,
            'event_type' => 'user_password_changed',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);

        return redirect()->route('profile.index')->with('success', 'Your password has been changed successfully.');
    }
}
