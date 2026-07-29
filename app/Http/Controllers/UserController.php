<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $users = User::where('workspace_id', $workspaceId)->orderBy('id', 'asc')->get();

        $stats = [
            'total' => $users->count(),
            'admins' => $users->where('role', 'admin')->count(),
            'managers' => $users->where('role', 'manager')->count(),
            'viewers' => $users->where('role', 'viewer')->count(),
            'inactive' => $users->where('status', 'inactive')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,manager,viewer',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => 'active',
            'password' => Hash::make($request->password),
        ]);

        AuditLog::create([
            'workspace_id' => $workspaceId,
            'event_type' => 'user_created',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'payload' => ['email' => $user->email, 'role' => $user->role],
        ]);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' ({$user->email}) created successfully.");
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:admin,manager,viewer',
        ]);

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        AuditLog::create([
            'workspace_id' => auth()->user()->workspace_id ?? 1,
            'event_type' => 'role_updated',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'payload' => ['old_role' => $oldRole, 'new_role' => $user->role],
        ]);

        return redirect()->route('users.index')->with('success', "Role for '{$user->name}' updated to " . ucfirst($user->role) . ".");
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', "You cannot deactivate your own admin account.");
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        AuditLog::create([
            'workspace_id' => auth()->user()->workspace_id ?? 1,
            'event_type' => 'user_status_toggled',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'payload' => ['new_status' => $user->status],
        ]);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' status changed to " . ucfirst($user->status) . ".");
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        AuditLog::create([
            'workspace_id' => auth()->user()->workspace_id ?? 1,
            'event_type' => 'password_reset',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);

        return redirect()->route('users.index')->with('success', "Password for '{$user->name}' reset successfully.");
    }

    public function regeneratePassword($id)
    {
        $user = User::findOrFail($id);

        $newPassword = 'paim-' . Str::random(8) . '!';
        $user->password = Hash::make($newPassword);
        $user->save();

        AuditLog::create([
            'workspace_id' => auth()->user()->workspace_id ?? 1,
            'event_type' => 'password_regenerated',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);

        return redirect()->route('users.index')->with('success', "Temporary password generated for '{$user->name}' ({$user->email}): {$newPassword}");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', "You cannot delete your own admin account.");
        }

        $name = $user->name;
        $user->delete();

        AuditLog::create([
            'workspace_id' => auth()->user()->workspace_id ?? 1,
            'event_type' => 'user_deleted',
            'entity_type' => 'User',
            'entity_id' => $id,
        ]);

        return redirect()->route('users.index')->with('success', "User '{$name}' removed successfully.");
    }
}
