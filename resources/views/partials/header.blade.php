<header class="h-20 paim-header px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <h1 class="text-xl font-extrabold paim-title tracking-tight">@yield('page_title', 'Dashboard Overview')</h1>
        
        <!-- Active Role Badge -->
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 paim-badge-role">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Role: {{ auth()->user()->role }}
        </span>
    </div>

    <div class="flex items-center gap-3">

        <!-- Dark / Light Mode Switcher Button -->
        <button type="button" onclick="toggleTheme()" aria-label="Toggle Theme" class="w-10 h-10 rounded-xl paim-btn-secondary flex items-center justify-center transition-all shadow-sm">
            <!-- Sun Icon (shown in Dark mode) -->
            <i class="bi bi-sun-fill text-lg hidden dark:block text-amber-400"></i>
            <!-- Moon Icon (shown in Light mode) -->
            <i class="bi bi-moon-stars-fill text-lg block dark:hidden text-indigo-600"></i>
        </button>

        <!-- My Profile Link / Avatar -->
        <a href="{{ route('profile.index') }}" title="My Profile & Password Settings" class="flex items-center gap-2 p-1 rounded-xl paim-btn-secondary hover:opacity-90 transition-opacity">
            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-lg object-cover border border-indigo-500">
        </a>

        @if(auth()->user()->role !== 'viewer')
        <a href="{{ route('subscriptions.index') }}" class="px-4 py-2.5 rounded-xl paim-btn-primary font-semibold text-sm transition-all flex items-center gap-2">
            <i class="bi bi-plus-lg text-base"></i>
            <span>Add Subscription</span>
        </a>
        @endif

        <!-- Logout Form -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-2.5 rounded-xl paim-btn-secondary font-semibold text-xs transition-all flex items-center gap-1.5 shadow-sm">
                <i class="bi bi-box-arrow-right text-sm"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</header>
