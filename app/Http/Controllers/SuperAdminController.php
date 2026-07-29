<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Subscription;
use App\Models\PlanVersion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_subscriptions' => Subscription::count(),
            'total_mrr' => PlanVersion::sum('normalized_monthly_amount'),
        ];

        $recentOrganizations = Organization::withCount(['users', 'subscriptions'])
            ->latest()
            ->take(5)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentOrganizations'));
    }

    public function organizations()
    {
        $organizations = Organization::withCount(['users', 'subscriptions'])
            ->latest()
            ->get();

        return view('superadmin.organizations.index', compact('organizations'));
    }

    public function storeOrganization(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug',
            'plan_tier' => 'required|in:starter,pro,enterprise',
            'max_users' => 'required|integer|min:1',
            'max_subscriptions' => 'required|integer|min:1',
        ]);

        Organization::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'plan_tier' => $validated['plan_tier'],
            'max_users' => $validated['max_users'],
            'max_subscriptions' => $validated['max_subscriptions'],
            'status' => 'active',
        ]);

        return redirect()->route('superadmin.organizations')->with('success', 'Customer Organization tenant created successfully.');
    }

    public function updateOrganization(Request $request, $id)
    {
        $org = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plan_tier' => 'required|in:starter,pro,enterprise',
            'max_users' => 'required|integer|min:1',
            'max_subscriptions' => 'required|integer|min:1',
        ]);

        $org->update($validated);

        return redirect()->route('superadmin.organizations')->with('success', "Organization {$org->name} updated successfully.");
    }

    public function toggleOrganizationStatus($id)
    {
        $org = Organization::findOrFail($id);
        $newStatus = $org->status === 'active' ? 'suspended' : 'active';
        $org->update(['status' => $newStatus]);

        return redirect()->route('superadmin.organizations')->with('success', "Organization {$org->name} status changed to {$newStatus}.");
    }

    public function users()
    {
        $users = User::with('organization')->latest()->get();
        $organizations = Organization::where('status', 'active')->get();

        return view('superadmin.users.index', compact('users', 'organizations'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,manager,viewer',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'organization_id' => $validated['organization_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        return redirect()->route('superadmin.users')->with('success', 'Organization user account created successfully.');
    }

    public function resetUserPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('superadmin.users')->with('success', "Password updated for user {$user->name}.");
    }

    public function regenerateUserPassword($id)
    {
        $user = User::findOrFail($id);
        $tempPassword = 'paim-' . Str::random(8) . '!';
        $user->update(['password' => Hash::make($tempPassword)]);

        return redirect()->route('superadmin.users')->with('success', "Password regenerated for {$user->name}: {$tempPassword}");
    }

    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return redirect()->route('superadmin.users')->with('success', "User {$user->name} status changed to {$newStatus}.");
    }

    public function analytics()
    {
        $organizations = Organization::withCount(['users', 'subscriptions'])->get();
        return view('superadmin.analytics.index', compact('organizations'));
    }

    public function settings()
    {
        return view('superadmin.settings.index');
    }
}
