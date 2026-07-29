@extends('layouts.app')

@section('title', 'Subscriptions - PAIM')
@section('page_title', 'AI & Software Subscriptions')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">All Subscriptions</h2>
            <p class="text-xs paim-subtitle">Manage recurring, prepaid, trial, on-demand, and custom subscription plans</p>
        </div>
        @if(auth()->user()->role !== 'viewer')
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold transition-all flex items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Add New Subscription</span>
        </button>
        @else
        <span class="px-3 py-1.5 rounded-xl paim-badge-role text-xs font-medium">
            <i class="bi bi-lock-fill mr-1"></i> Read-Only Mode (Viewer Role)
        </span>
        @endif
    </div>

    <!-- Subscriptions Table Card -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Subscription & Tool</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Recurring Fee</th>
                        <th class="py-3.5 px-4">Normalized Monthly</th>
                        <th class="py-3.5 px-4">Payment Account</th>
                        <th class="py-3.5 px-4">Renewal / End Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        @if(auth()->user()->role !== 'viewer')
                        <th class="py-3.5 px-4">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($subscriptions as $sub)
                    <tr>
                        <td class="py-3.5 px-4">
                            <div class="font-semibold paim-title text-base">{{ $sub->name }}</div>
                            <div class="text-xs paim-subtitle">{{ $sub->tool->name ?? 'Tool' }} &bull; {{ $sub->tool->vendor->name ?? 'Vendor' }}</div>
                        </td>
                        <td class="py-3.5 px-4 capitalize text-xs font-medium paim-text">
                            {{ str_replace('_', ' ', $sub->type) }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold paim-title">
                            ${{ number_format($sub->currentPlanVersion->recurring_amount ?? 0, 2) }}
                        </td>
                        <td class="py-3.5 px-4 font-extrabold paim-title">
                            ${{ number_format($sub->currentPlanVersion->normalized_monthly_amount ?? 0, 2) }}/mo
                        </td>
                        <td class="py-3.5 px-4 text-xs paim-text">
                            {{ $sub->paymentAccount->friendly_name ?? 'Invoice/Manual' }}
                        </td>
                        <td class="py-3.5 px-4 text-xs paim-text">
                            {{ $sub->end_date ? $sub->end_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $sub->status === 'active' ? 'paim-badge-success' : 'paim-badge-warning' }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        @if(auth()->user()->role !== 'viewer')
                        <td class="py-3.5 px-4">
                            <form action="{{ route('subscriptions.update-status', $sub->id) }}" method="POST" class="inline-block">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="paim-input text-xs rounded-lg px-2 py-1 focus:outline-none">
                                    <option value="active" {{ $sub->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="paused" {{ $sub->status === 'paused' ? 'selected' : '' }}>Paused</option>
                                    <option value="cancelled" {{ $sub->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Add New Subscription -->
@if(auth()->user()->role !== 'viewer')
<div id="addModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Add New Subscription</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('subscriptions.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Subscription Name</label>
                <input type="text" name="name" placeholder="e.g. ChatGPT Plus Monthly" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Tool / Service Name</label>
                <input type="text" name="tool_name" placeholder="e.g. ChatGPT" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Subscription Type</label>
                    <select name="type" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="monthly_recurring">Monthly Recurring</option>
                        <option value="annual_recurring">Annual Recurring</option>
                        <option value="prepaid_token">Prepaid Token Lot</option>
                        <option value="on_demand">On-Demand Usage</option>
                        <option value="trial">Trial</option>
                        <option value="free">Free</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Recurring Amount ($)</label>
                    <input type="number" step="0.01" name="recurring_amount" placeholder="20.00" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Billing Cadence (Months)</label>
                    <input type="number" name="billing_cadence_months" value="1" min="1" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Payment Account</label>
                    <select name="payment_account_id" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="">Invoice / Manual</option>
                        @foreach($paymentAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->friendly_name }} ({{ $account->masked_identifier }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Subscription</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
