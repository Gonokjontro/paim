@extends('layouts.app')

@section('title', 'Dashboard - PAIM AI Subscription Control')
@section('page_title', 'Dashboard Overview')

@section('content')
<div class="space-y-8">

    <!-- Top KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- KPI 1: Actual Monthly Spend -->
        <div class="p-6 rounded-2xl paim-card relative overflow-hidden group transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider paim-subtitle">Actual Spend (Month)</span>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold paim-title tracking-tight">{{ $currencySymbol }}{{ number_format($actualPostedMonth, 2) }}</span>
                <span class="block text-xs paim-subtitle mt-1 font-medium">Posted ledger transactions</span>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
        </div>

        <!-- KPI 2: Committed Monthly Cost -->
        <div class="p-6 rounded-2xl paim-card relative overflow-hidden group transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider paim-subtitle">Committed Cost</span>
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-500/20 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-repeat"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold paim-title tracking-tight">{{ $currencySymbol }}{{ number_format($committedCostMonthly, 2) }}</span>
                <span class="block text-xs paim-subtitle mt-1 font-medium">Normalized monthly subscriptions</span>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
        </div>

        <!-- KPI 3: Total Forecast Cost -->
        <div class="p-6 rounded-2xl paim-card relative overflow-hidden group transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider paim-subtitle">Total Forecast</span>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold paim-title tracking-tight">{{ $currencySymbol }}{{ number_format($totalForecastMonthly, 2) }}</span>
                <span class="block text-xs paim-subtitle mt-1 font-medium">Committed + Run-rate token forecast</span>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
        </div>

        <!-- KPI 4: Target Utilization -->
        <div class="p-6 rounded-2xl paim-card relative overflow-hidden group transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider paim-subtitle">Budget Target</span>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold paim-title tracking-tight">{{ $utilizationPct }}%</span>
                    <span class="text-xs paim-subtitle font-medium">of {{ $currencySymbol }}{{ number_format($targetAmount, 0) }} limit</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-200 dark:bg-slate-800/80 rounded-full h-2.5 mt-3 overflow-hidden p-0.5 border border-slate-300 dark:border-slate-700/50">
                    <div class="h-full rounded-full transition-all duration-500 {{ $utilizationPct >= 90 ? 'bg-gradient-to-r from-rose-500 to-red-600' : ($utilizationPct >= 75 ? 'bg-gradient-to-r from-amber-400 to-amber-600' : 'bg-gradient-to-r from-emerald-400 to-teal-500') }}" style="width: {{ min(100, $utilizationPct) }}%"></div>
                </div>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
        </div>
    </div>

    <!-- Active Alerts Banner -->
    @if(count($alerts) > 0)
    <div class="p-6 rounded-2xl paim-card border border-amber-200 dark:border-amber-500/40 bg-amber-50/60 dark:bg-gradient-to-r dark:from-amber-950/40 dark:via-slate-900/60 dark:to-slate-900/80 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <h3 class="font-bold paim-title text-base">Active Alerts & Risk Reminders ({{ count($alerts) }})</h3>
            </div>
            <a href="{{ route('targets.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View Alert Center &rarr;</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($alerts as $alert)
            <div class="p-4 rounded-xl paim-card flex items-start justify-between gap-4 shadow-sm">
                <div class="space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider {{ $alert->severity === 'critical' ? 'paim-badge-danger' : 'paim-badge-warning' }}">
                        {{ $alert->severity }}
                    </span>
                    <h4 class="font-bold paim-title text-sm leading-tight">{{ $alert->title }}</h4>
                    <p class="text-xs paim-subtitle leading-relaxed">{{ $alert->message }}</p>
                </div>
                @if(auth()->user()->role !== 'viewer')
                <form action="{{ route('alerts.acknowledge', $alert->id) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg paim-btn-secondary transition-all shadow-sm">
                        Acknowledge
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Category Breakdown & Token Packages Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Category Spend Mix -->
        <div class="p-6 rounded-2xl paim-card space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
                <div>
                    <h3 class="font-bold paim-title text-base">Category Spend Mix</h3>
                    <p class="text-xs paim-subtitle">Monthly Committed Allocation</p>
                </div>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm"><i class="bi bi-diagram-3-fill"></i></span>
            </div>
            <div class="space-y-5">
                @foreach($categoryBreakdown as $cat)
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="font-semibold paim-text flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $cat['color'] }}"></span>
                            {{ $cat['name'] }}
                        </span>
                        <span class="font-extrabold paim-title">{{ $currencySymbol }}{{ number_format($cat['amount'], 2) }}/mo</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800/80 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-300 dark:border-slate-700/50">
                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ $committedCostMonthly > 0 ? min(100, ($cat['amount'] / $committedCostMonthly) * 100) : 0 }}%; background-color: {{ $cat['color'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Token & Credit Packages Balance -->
        <div class="lg:col-span-2 p-6 rounded-2xl paim-card space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
                <div>
                    <h3 class="font-bold paim-title text-base">Prepaid Token & API Credit Lots</h3>
                    <p class="text-xs paim-subtitle">FIFO-by-expiry credit balance tracking</p>
                </div>
                <a href="{{ route('usage.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Log Usage / Purchase Lot &rarr;</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($tokenPackages as $pkg)
                @php
                    $pct = $pkg->granted_units > 0 ? round(($pkg->consumed_units / $pkg->granted_units) * 100, 1) : 0;
                @endphp
                <div class="p-5 rounded-xl paim-card space-y-4 shadow-sm transition-all">
                    <div class="flex items-center justify-between">
                        <span class="font-bold paim-title text-sm">{{ $pkg->package_name }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider paim-badge-success">Active Lot</span>
                    </div>
                    <div class="text-xs paim-subtitle">
                        Tool: <span class="paim-title font-semibold">{{ $pkg->subscription->tool->name ?? 'Tool' }}</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="paim-subtitle">Remaining Balance</span>
                            <span class="font-bold paim-title">{{ number_format($pkg->remaining_units, 0) }} / {{ number_format($pkg->granted_units, 0) }} units</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800/80 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-300 dark:border-slate-700/50">
                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width: {{ 100 - $pct }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs paim-subtitle pt-2 border-t border-slate-200 dark:border-slate-800/80">
                        <span>Cost: <strong class="paim-title">{{ $currencySymbol }}{{ number_format($pkg->purchase_cost, 2) }}</strong></span>
                        <span>Expires: {{ $pkg->expiry_date ? $pkg->expiry_date->format('M Y') : 'No expiry' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Active Subscriptions Table -->
    <div class="p-6 rounded-2xl paim-card space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
            <div>
                <h3 class="font-bold paim-title text-base">Active AI & Software Subscriptions</h3>
                <p class="text-xs paim-subtitle">Track normalized monthly commitments, vendors, renewal dates, and payment accounts</p>
            </div>
            <a href="{{ route('subscriptions.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View All Subscriptions &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-[11px] uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Tool & Subscription</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Normalized Monthly</th>
                        <th class="py-3.5 px-4">Payment Source</th>
                        <th class="py-3.5 px-4">Renewal Date</th>
                        <th class="py-3.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($subscriptions as $sub)
                    <tr class="transition-all">
                        <td class="py-4 px-4">
                            <div class="font-bold paim-title text-sm">{{ $sub->name }}</div>
                            <div class="text-xs paim-subtitle font-medium">{{ $sub->tool->vendor->name ?? 'Direct Vendor' }}</div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold paim-card border border-slate-200 dark:border-slate-800">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $sub->tool->category->color ?? '#6366F1' }}"></span>
                                {{ $sub->tool->category->name ?? 'General' }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="text-xs font-medium paim-text capitalize">{{ str_replace('_', ' ', $sub->type) }}</span>
                        </td>
                        <td class="py-4 px-4 font-extrabold paim-title text-base">
                            {{ $currencySymbol }}{{ number_format($sub->currentPlanVersion ? $sub->currentPlanVersion->normalized_monthly_amount : 0, 2) }}<span class="text-xs paim-subtitle font-normal">/mo</span>
                        </td>
                        <td class="py-4 px-4 text-xs font-medium paim-text">
                            {{ $sub->paymentAccount->friendly_name ?? 'Invoice/Manual' }}
                        </td>
                        <td class="py-4 px-4 text-xs font-medium">
                            @if($sub->end_date)
                                <span class="{{ $sub->end_date->diffInDays(now()) <= 7 ? 'text-amber-600 dark:text-amber-400 font-bold' : 'paim-text' }}">
                                    {{ $sub->end_date->format('M d, Y') }}
                                </span>
                            @else
                                <span class="paim-subtitle">N/A</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold paim-badge-success">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
