@extends('layouts.app')

@section('title', 'Budgets & Alert Policy - PAIM')
@section('page_title', 'Budgets & Alert Center')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Spending Targets & Alerts</h2>
            <p class="text-xs paim-subtitle">Budget thresholds, renewal warnings, card expiry alerts, and notification policies</p>
        </div>
        @if(auth()->user()->role !== 'viewer')
        <button onclick="document.getElementById('targetModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold transition-all flex items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Create Budget Target</span>
        </button>
        @else
        <span class="px-3 py-1.5 rounded-xl paim-badge-role text-xs font-medium">
            <i class="bi bi-lock-fill mr-1"></i> Read-Only Mode (Viewer Role)
        </span>
        @endif
    </div>

    <!-- Active Targets Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($targets as $target)
        <div class="p-6 rounded-2xl paim-card space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold paim-title text-base">{{ $target->name }}</h3>
                    <span class="text-xs paim-subtitle capitalize">{{ $target->period_type }} Target Basis: {{ $target->basis }}</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold paim-badge-success">
                    Active Target
                </span>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold paim-title">${{ number_format($target->target_amount, 2) }}</span>
                <span class="text-xs paim-subtitle">/ {{ $target->period_type }}</span>
            </div>

            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs paim-subtitle">
                    <span>Warning Threshold: {{ $target->warning_threshold_pct }}%</span>
                    <span>Critical Threshold: {{ $target->critical_threshold_pct }}%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full bg-indigo-500" style="width: 75%"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Alerts History Table -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <h3 class="font-bold paim-title text-base">Alert & Notification History</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Severity</th>
                        <th class="py-3.5 px-4">Title & Message</th>
                        <th class="py-3.5 px-4">Created At</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($alerts as $alert)
                    <tr>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded text-xs font-bold uppercase {{ $alert->severity === 'critical' ? 'paim-badge-danger' : 'paim-badge-warning' }}">
                                {{ $alert->severity }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold paim-title">{{ $alert->title }}</div>
                            <div class="text-xs paim-subtitle">{{ $alert->message }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-xs paim-subtitle">
                            {{ $alert->created_at ? $alert->created_at->format('M d, Y H:i') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $alert->status === 'unacknowledged' ? 'paim-badge-warning' : 'paim-badge-success' }}">
                                {{ ucfirst($alert->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($alert->status === 'unacknowledged' && auth()->user()->role !== 'viewer')
                            <form action="{{ route('alerts.acknowledge', $alert->id) }}" method="POST">
                                @csrf
                                <button class="px-2.5 py-1 text-xs font-medium rounded-lg paim-btn-secondary transition-all">
                                    Acknowledge
                                </button>
                            </form>
                            @else
                            <span class="text-xs paim-subtitle">Acknowledged</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Create Budget Target -->
@if(auth()->user()->role !== 'viewer')
<div id="targetModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Create Budget Target</h3>
            <button onclick="document.getElementById('targetModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('targets.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Target Name</label>
                <input type="text" name="name" placeholder="e.g. Monthly AI Budget Ceiling" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Target Amount ($)</label>
                    <input type="number" step="0.01" name="target_amount" placeholder="250.00" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Period Type</label>
                    <select name="period_type" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Warning Threshold (% of limit)</label>
                <input type="number" name="warning_threshold_pct" value="80" min="50" max="99" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('targetModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Target</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
