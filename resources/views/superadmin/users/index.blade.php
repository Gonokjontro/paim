@extends('layouts.app')

@section('title', 'Global Tenant Users - PAIM SaaS')
@section('page_title', 'Global Tenant User Directory & Support')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Global Tenant Users Directory</h2>
            <p class="text-xs paim-subtitle">Manage users across all customer organizations, reset passwords, and provision new team accounts</p>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
            <i class="bi bi-person-plus-fill"></i>
            <span>Create Tenant User</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">User</th>
                        <th class="py-3.5 px-4">Customer Organization</th>
                        <th class="py-3.5 px-4">Org Role</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($users as $user)
                    <tr>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-xl object-cover border border-indigo-500">
                                <div>
                                    <div class="font-bold paim-title text-base flex items-center gap-2">
                                        {{ $user->name }}
                                        @if($user->isSuperAdmin())
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase paim-badge-warning">Super Admin</span>
                                        @endif
                                    </div>
                                    <div class="text-xs paim-subtitle">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 font-semibold paim-title">
                            {{ $user->organization ? $user->organization->name : 'Platform SaaS Owner' }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase paim-badge-role">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <form action="{{ route('superadmin.users.toggle-status', $user->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" {{ $user->isSuperAdmin() ? 'disabled' : '' }} class="px-3 py-1 rounded-full text-xs font-semibold transition-all {{ $user->status === 'active' ? 'paim-badge-success hover:opacity-80' : 'paim-badge-danger hover:opacity-80' }} {{ $user->isSuperAdmin() ? 'opacity-60 cursor-not-allowed' : '' }}">
                                    <i class="bi {{ $user->status === 'active' ? 'bi-check-circle-fill' : 'bi-slash-circle-fill' }} mr-1"></i>
                                    {{ ucfirst($user->status) }}
                                </button>
                            </form>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="openResetModal({{ $user->id }}, '{{ $user->name }}')" title="Reset Password" class="p-2 rounded-lg paim-btn-secondary text-xs">
                                    <i class="bi bi-key-fill"></i>
                                </button>
                                <form action="{{ route('superadmin.users.regenerate-password', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Regenerate random password for {{ $user->name }}?')">
                                    @csrf
                                    <button type="submit" title="1-Click Auto Password" class="p-2 rounded-lg paim-btn-secondary text-xs text-indigo-600 dark:text-indigo-400">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Add Tenant User -->
<div id="addUserModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Provision Tenant User Account</h3>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('superadmin.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Target Customer Organization</label>
                <select name="organization_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($organizations as $org)
                    <option value="{{ $org->id }}">{{ $org->name }} ({{ $org->plan_tier }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Email Address</label>
                <input type="email" name="email" placeholder="john@company.com" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Organization Role</label>
                    <select name="role" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="admin">Org Admin</option>
                        <option value="manager">Org Manager</option>
                        <option value="viewer">Org Viewer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Initial Password</label>
                    <input type="password" name="password" placeholder="••••••••" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="resetModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-md w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Reset User Password</h3>
            <button onclick="document.getElementById('resetModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form id="resetForm" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs paim-subtitle">Resetting password for <strong id="resetUserName" class="paim-title"></strong>.</p>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">New Password</label>
                <input type="password" name="password" placeholder="Min 6 characters" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('resetModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(userId, userName) {
    document.getElementById('resetUserName').innerText = userName;
    document.getElementById('resetForm').action = "/super-admin/users/" + userId + "/reset-password";
    document.getElementById('resetModal').classList.remove('hidden');
}
</script>
@endsection
