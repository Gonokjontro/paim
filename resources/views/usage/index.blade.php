@extends('layouts.app')

@section('title', 'Token & Usage Ledger - PAIM')
@section('page_title', 'Token & API Usage Ledger')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Token Packages & On-Demand Usage</h2>
            <p class="text-xs paim-subtitle">FIFO-by-expiry token lot consumption, model rates, and effective token cost tracking</p>
        </div>
        @if(auth()->user()->role !== 'viewer')
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('packageModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
                <i class="bi bi-bag-plus-fill"></i>
                <span>Purchase Token Package</span>
            </button>
            <button onclick="document.getElementById('usageModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
                <i class="bi bi-plus-lg"></i>
                <span>Log Metered Usage</span>
            </button>
        </div>
        @else
        <span class="px-3 py-1.5 rounded-xl paim-badge-role text-xs font-medium">
            <i class="bi bi-lock-fill mr-1"></i> Read-Only Mode (Viewer Role)
        </span>
        @endif
    </div>

    <!-- Active Credit Packages Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tokenPackages as $pkg)
        @php
            $pct = $pkg->granted_units > 0 ? round(($pkg->consumed_units / $pkg->granted_units) * 100, 1) : 0;
        @endphp
        <div class="p-6 rounded-2xl paim-card space-y-4">
            <div class="flex items-center justify-between">
                <span class="font-bold paim-title text-base">{{ $pkg->package_name }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold paim-badge-success">
                    {{ ucfirst($pkg->status) }}
                </span>
            </div>
            <div class="text-xs paim-subtitle">
                Subscription: <span class="paim-title font-medium">{{ $pkg->subscription->tool->name ?? 'Tool' }}</span>
            </div>
            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="paim-subtitle">Remaining Balance</span>
                    <span class="font-bold paim-title">{{ number_format($pkg->remaining_units, 0) }} / {{ number_format($pkg->granted_units, 0) }} units</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ 100 - $pct }}%"></div>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs paim-subtitle pt-2 border-t border-slate-200 dark:border-slate-800">
                <span>Cost: <strong class="paim-title">${{ number_format($pkg->purchase_cost, 2) }}</strong></span>
                <span>Expires: {{ $pkg->expiry_date ? $pkg->expiry_date->format('M d, Y') : 'Never' }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Usage History Table -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <h3 class="font-bold paim-title text-base">Usage Logs & FIFO Cost Allocation</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Subscription</th>
                        <th class="py-3.5 px-4">Model / Service</th>
                        <th class="py-3.5 px-4">Environment</th>
                        <th class="py-3.5 px-4">Units Consumed</th>
                        <th class="py-3.5 px-4">Effective FIFO Cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($usageEntries as $usage)
                    <tr>
                        <td class="py-3.5 px-4 text-xs font-medium paim-subtitle">
                            {{ $usage->usage_date ? $usage->usage_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold paim-title">
                            {{ $usage->subscription->name ?? 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 text-xs text-indigo-600 dark:text-indigo-400 font-semibold">
                            {{ $usage->model_name ?? 'gpt-4o' }}
                        </td>
                        <td class="py-3.5 px-4 text-xs paim-subtitle">
                            {{ $usage->environment_project ?? 'production' }}
                        </td>
                        <td class="py-3.5 px-4 font-bold paim-title">
                            {{ number_format($usage->unit_count, 0) }} {{ $usage->meterUnit->symbol ?? 'units' }}
                        </td>
                        <td class="py-3.5 px-4 font-extrabold text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($usage->calculated_cost, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Log Metered Usage -->
@if(auth()->user()->role !== 'viewer')
<div id="usageModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Log Metered Token Usage</h3>
            <button onclick="document.getElementById('usageModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('usage.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Subscription</label>
                <select name="subscription_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($subscriptions as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->tool->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Meter Unit</label>
                <select name="meter_unit_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($meterUnits as $meter)
                        <option value="{{ $meter->id }}">{{ $meter->name }} ({{ $meter->symbol }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">AI Model / Engine</label>
                    <input type="text" name="model_name" placeholder="e.g. gpt-4o" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Units Consumed</label>
                    <input type="number" step="0.01" name="unit_count" placeholder="500" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Usage Date</label>
                <input type="date" name="usage_date" value="{{ date('Y-m-d') }}" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('usageModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Log Usage</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Purchase Token Package -->
<div id="packageModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Purchase Token Package / Credit Lot</h3>
            <button onclick="document.getElementById('packageModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('usage.store-package') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Subscription</label>
                <select name="subscription_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($subscriptions as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->tool->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Meter Unit</label>
                <select name="meter_unit_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($meterUnits as $meter)
                        <option value="{{ $meter->id }}">{{ $meter->name }} ({{ $meter->symbol }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Package Name</label>
                <input type="text" name="package_name" placeholder="e.g. 5M API Token Credit Lot" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Purchase Cost ($)</label>
                    <input type="number" step="0.01" name="purchase_cost" placeholder="100.00" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Granted Units</label>
                    <input type="number" name="granted_units" placeholder="5000" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Expiry (Months)</label>
                    <input type="number" name="expiry_months" value="6" min="1" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('packageModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Package</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
