@extends('layouts.app')

@section('title', 'Global SaaS Analytics - PAIM Super Admin')
@section('page_title', 'SaaS Platform Analytics & Health')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <h2 class="text-xl font-bold paim-title">Global SaaS Analytics & Revenue Health</h2>
        <p class="text-xs paim-subtitle">Systemic analytics across all customer organizations</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            @foreach($organizations as $org)
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                <h3 class="font-bold paim-title text-base">{{ $org->name }}</h3>
                <p class="text-xs paim-subtitle">Tier: <strong class="uppercase text-indigo-600 dark:text-indigo-400">{{ $org->plan_tier }}</strong></p>
                <div class="text-xs flex justify-between pt-2 border-t border-slate-200 dark:border-slate-800">
                    <span>Users: {{ $org->users_count }}</span>
                    <span>Subscriptions: {{ $org->subscriptions_count }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
