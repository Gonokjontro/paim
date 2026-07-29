@extends('layouts.app')

@section('title', 'Renewal Timeline Calendar - PAIM')
@section('page_title', 'Subscription Renewal Schedule & iCal Feed')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Renewal Timeline & iCal Sync</h2>
            <p class="text-xs paim-subtitle">Monitor upcoming renewal dates, trial cancellation countdowns, and export to Google / Apple Calendar</p>
        </div>
        <a href="{{ route('calendar.export-ical') }}" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
            <i class="bi bi-download"></i>
            <span>Export iCal Feed (.ics)</span>
        </a>
    </div>

    <!-- Renewal Events Timeline List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($events as $event)
        <div class="p-6 rounded-2xl paim-card space-y-4 relative overflow-hidden">
            @if($event['is_urgent'])
                <div class="absolute top-0 right-0 bg-rose-500 text-white text-[10px] font-extrabold px-3 py-1 rounded-bl-xl uppercase tracking-wider animate-pulse">
                    <i class="bi bi-clock-history"></i> Renews Soon
                </div>
            @endif

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div>
                    <h3 class="font-bold paim-title text-base">{{ $event['title'] }}</h3>
                    <p class="text-xs paim-subtitle">{{ $event['tool'] }} &bull; {{ str_replace('_', ' ', ucfirst($event['type'])) }}</p>
                </div>
            </div>

            <div class="border-t border-b border-slate-200 dark:border-slate-800/80 py-3 flex items-center justify-between text-xs">
                <div>
                    <span class="block paim-subtitle uppercase text-[10px]">Renewal Date</span>
                    <strong class="paim-title text-sm">{{ $event['renewal_date'] }}</strong>
                </div>
                <div class="text-right">
                    <span class="block paim-subtitle uppercase text-[10px]">Monthly Equivalent</span>
                    <strong class="text-indigo-600 dark:text-indigo-400 text-sm">${{ number_format($event['amount'], 2) }}</strong>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <span class="paim-subtitle">Cancellation Countdown:</span>
                @if($event['days_left'] !== null && $event['days_left'] >= 0)
                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ $event['is_urgent'] ? 'paim-badge-danger' : 'paim-badge-success' }}">
                        {{ $event['days_left'] }} {{ Str::plural('day', $event['days_left']) }} left
                    </span>
                @else
                    <span class="paim-subtitle">Ongoing</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full p-12 text-center paim-card rounded-2xl">
            <i class="bi bi-calendar-x text-4xl text-slate-400 mb-3 block"></i>
            <p class="paim-title font-bold text-base">No active subscription renewals scheduled.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
