@extends('layouts.app')

@section('title', 'Super Admin Control Center - PAIM SaaS')
@section('page_title', 'SaaS Platform Operator Control Center')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Super Admin Platform Overview</h2>
            <p class="text-xs paim-subtitle">Global SaaS multi-tenant performance metrics, organization management, and tenant provisioning</p>
        </div>
        <a href="{{ route('superadmin.organizations') }}" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
            <i class="bi bi-building-plus"></i>
            <span>Manage Customer Tenants</span>
        </a>
    </div>

    <!-- Platform KPI Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-building-fill"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold paim-title">{{ $stats['total_organizations'] }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Total Customer Orgs</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['active_organizations'] }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Active Tenants</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold paim-title">{{ $stats['total_users'] }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Platform Users</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold text-amber-600 dark:text-amber-400">${{ number_format($stats['total_mrr'], 2) }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Tracked Monthly Spend</span>
            </div>
        </div>
    </div>

    <!-- Recent Organizations Table -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="font-bold paim-title text-base">Customer Organization Tenants</h3>
            <a href="{{ route('superadmin.organizations') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View All Tenants &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Organization</th>
                        <th class="py-3.5 px-4">Plan Tier</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Users Count</th>
                        <th class="py-3.5 px-4">Subscriptions Count</th>
                        <th class="py-3.5 px-4 text-right">Created Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($recentOrganizations as $org)
                    <tr>
                        <td class="py-4 px-4 font-bold paim-title">
                            {{ $org->name }}
                            <span class="block text-xs paim-subtitle font-mono">{{ $org->slug }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase paim-badge-role">
                                {{ ucfirst($org->plan_tier) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $org->status === 'active' ? 'paim-badge-success' : 'paim-badge-danger' }}">
                                {{ ucfirst($org->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 paim-text">
                            {{ $org->users_count }} / {{ $org->max_users }}
                        </td>
                        <td class="py-4 px-4 paim-text">
                            {{ $org->subscriptions_count }} / {{ $org->max_subscriptions }}
                        </td>
                        <td class="py-4 px-4 text-right text-xs paim-subtitle">
                            {{ $org->created_at ? $org->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
