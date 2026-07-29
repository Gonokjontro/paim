@extends('layouts.app')

@section('title', 'Organization Spend Reports - PAIM')
@section('page_title', 'Organization-Wise Financial & Subscription Reports')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Organization Financial & Spend Reports</h2>
            <p class="text-xs paim-subtitle">Detailed Breakdown of AI Subscriptions, Vendor Allocation, and Category Costs for {{ auth()->user()->organization ? auth()->user()->organization->name : 'Your Workspace' }}</p>
        </div>
        <a href="{{ route('reports.export-csv') }}" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
            <i class="bi bi-download"></i>
            <span>Export Org Report (CSV)</span>
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">${{ number_format($totalMonthlySpend, 2) }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Total Monthly Spend</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold paim-title">{{ count($byVendor) }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Active AI Vendors</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-grid"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold paim-title">{{ count($byCategory) }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Category Segments</span>
            </div>
        </div>
    </div>

    <!-- Vendor Spend Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- By Vendor -->
        <div class="p-6 rounded-2xl paim-card space-y-4">
            <h3 class="font-bold paim-title text-base border-b border-slate-200 dark:border-slate-800 pb-3">Spend by AI Vendor</h3>
            <div class="space-y-3">
                @foreach($byVendor as $vendor => $amount)
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold paim-title">{{ $vendor }}</span>
                    <span class="font-extrabold text-indigo-600 dark:text-indigo-400">${{ number_format($amount, 2) }}/mo</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="bg-indigo-600 h-full rounded-full" style="width: {{ $totalMonthlySpend > 0 ? min(100, ($amount / $totalMonthlySpend) * 100) : 0 }}%;"></div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- By Category -->
        <div class="p-6 rounded-2xl paim-card space-y-4">
            <h3 class="font-bold paim-title text-base border-b border-slate-200 dark:border-slate-800 pb-3">Spend by Category Segment</h3>
            <div class="space-y-3">
                @foreach($byCategory as $category => $amount)
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold paim-title">{{ $category }}</span>
                    <span class="font-extrabold text-purple-600 dark:text-purple-400">${{ number_format($amount, 2) }}/mo</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="bg-purple-600 h-full rounded-full" style="width: {{ $totalMonthlySpend > 0 ? min(100, ($amount / $totalMonthlySpend) * 100) : 0 }}%;"></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
