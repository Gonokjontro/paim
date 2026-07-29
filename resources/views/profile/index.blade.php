@extends('layouts.app')

@section('title', 'My Profile & Security - PAIM')
@section('page_title', 'My Account & Password Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header Profile Card -->
    <div class="flex items-center gap-5 p-6 rounded-2xl paim-card">
        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-indigo-500 shadow-md">
        <div>
            <h2 class="text-xl font-bold paim-title">{{ $user->name }}</h2>
            <p class="text-xs paim-subtitle">{{ $user->email }} &bull; Role: <strong class="text-indigo-600 dark:text-indigo-400 capitalize">{{ $user->role }}</strong></p>
            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase paim-badge-success">Active Account</span>
        </div>
    </div>

    <!-- Section 1: Personal Profile Data -->
    <div class="p-6 rounded-2xl paim-card space-y-6">
        <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="font-bold paim-title text-base">Personal Profile Information</h3>
            <p class="text-xs paim-subtitle">Update your display name and profile picture avatar</p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Email Address (Login ID)</label>
                    <input type="email" value="{{ $user->email }}" readonly disabled class="w-full paim-input rounded-xl px-4 py-2.5 text-sm opacity-70 cursor-not-allowed bg-slate-200 dark:bg-slate-900">
                    <span class="block text-[11px] paim-subtitle mt-1"><i class="bi bi-lock-fill"></i> Email is your primary login credential and cannot be modified directly.</span>
                </div>
            </div>

            <!-- Profile Picture File Upload -->
            <div class="pt-2">
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-2">Upload Profile Photo</label>
                <div class="flex items-center gap-4">
                    <img src="{{ $user->avatar }}" alt="Preview" class="w-14 h-14 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                    <div class="flex-1">
                        <input type="file" name="avatar" accept="image/*" class="w-full text-xs paim-subtitle file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                        <span class="block text-[11px] paim-subtitle mt-1">Supports PNG, JPG, GIF, WebP, SVG (Max 4MB)</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Profile Changes</button>
            </div>
        </form>
    </div>

    <!-- Section 2: Security & Password Change -->
    <div class="p-6 rounded-2xl paim-card space-y-6">
        <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="font-bold paim-title text-base">Security & Change Password</h3>
            <p class="text-xs paim-subtitle">Ensure your account uses a strong, unique password</p>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Current Password</label>
                <input type="password" name="current_password" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">New Password</label>
                    <input type="password" name="password" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-200 dark:border-slate-800">
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Change Password</button>
            </div>
        </form>
    </div>

</div>
@endsection
