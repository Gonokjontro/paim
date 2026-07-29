@extends('layouts.app')

@section('title', 'Organizations Tenant Control - PAIM SaaS')
@section('page_title', 'Customer Organization Tenant Control')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Customer Organization Tenants</h2>
            <p class="text-xs paim-subtitle">Provision customer organizations, manage plan tiers, suspend/activate tenants, and enforce quota caps</p>
        </div>
        <button onclick="document.getElementById('addOrgModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
            <i class="bi bi-building-add"></i>
            <span>Create New Organization</span>
        </button>
    </div>

    <!-- Organizations Table -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Organization</th>
                        <th class="py-3.5 px-4">Plan Tier</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">User Quota Limit</th>
                        <th class="py-3.5 px-4">Sub Quota Limit</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($organizations as $org)
                    <tr>
                        <td class="py-4 px-4">
                            <div class="font-bold paim-title text-base">{{ $org->name }}</div>
                            <div class="text-xs paim-subtitle font-mono">{{ $org->slug }}</div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase paim-badge-role">
                                {{ ucfirst($org->plan_tier) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <form action="{{ route('superadmin.organizations.toggle-status', $org->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="px-3 py-1 rounded-full text-xs font-semibold transition-all {{ $org->status === 'active' ? 'paim-badge-success hover:opacity-80' : 'paim-badge-danger hover:opacity-80' }}">
                                    <i class="bi {{ $org->status === 'active' ? 'bi-check-circle-fill' : 'bi-slash-circle-fill' }} mr-1"></i>
                                    {{ ucfirst($org->status) }}
                                </button>
                            </form>
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold paim-text">
                            {{ $org->users_count }} / {{ $org->max_users }} users
                        </td>
                        <td class="py-4 px-4 text-xs font-semibold paim-text">
                            {{ $org->subscriptions_count }} / {{ $org->max_subscriptions }} subs
                        </td>
                        <td class="py-4 px-4 text-right">
                            <button onclick="openEditOrgModal({{ json_encode($org) }})" title="Edit Plan Tier & Quota Limits" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs font-semibold">
                                <i class="bi bi-pencil-square mr-1"></i> Edit Quotas
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Add Organization -->
<div id="addOrgModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Create Customer Organization Tenant</h3>
            <button onclick="document.getElementById('addOrgModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('superadmin.organizations.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Organization Name</label>
                <input type="text" name="name" placeholder="e.g. Acme Corporation" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Organization Identifier (Slug)</label>
                <input type="text" name="slug" placeholder="acme-corp" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">SaaS Tier</label>
                    <select name="plan_tier" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="starter">Starter</option>
                        <option value="pro" selected>Pro</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Max Users</label>
                    <input type="number" name="max_users" value="10" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Max Subs</label>
                    <input type="number" name="max_subscriptions" value="50" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addOrgModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Provision Tenant</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Organization Quotas -->
<div id="editOrgModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Edit Organization Tier & Quota Limits</h3>
            <button onclick="document.getElementById('editOrgModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form id="editOrgForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Organization Name</label>
                <input type="text" name="name" id="editOrgName" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">SaaS Tier</label>
                    <select name="plan_tier" id="editOrgTier" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="starter">Starter</option>
                        <option value="pro">Pro</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Max Users</label>
                    <input type="number" name="max_users" id="editOrgUsers" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Max Subs</label>
                    <input type="number" name="max_subscriptions" id="editOrgSubs" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('editOrgModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Quotas</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditOrgModal(org) {
    document.getElementById('editOrgForm').action = "/super-admin/organizations/" + org.id;
    document.getElementById('editOrgName').value = org.name;
    document.getElementById('editOrgTier').value = org.plan_tier;
    document.getElementById('editOrgUsers').value = org.max_users;
    document.getElementById('editOrgSubs').value = org.max_subscriptions;
    document.getElementById('editOrgModal').classList.remove('hidden');
}
</script>
@endsection
