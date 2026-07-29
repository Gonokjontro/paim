<aside class="w-64 paim-sidebar flex-shrink-0 flex flex-col justify-between sticky top-0 h-screen z-40">
    <div>
        <!-- Logo & Branding -->
        <div class="h-20 flex items-center px-6 border-b border-slate-200 dark:border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                    <i class="bi bi-cpu-fill text-xl text-white"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-indigo-600 dark:gradient-text">PAIM</span>
                    <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">AI Subscription Control</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1.5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-grid-1x2-fill text-lg"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('subscriptions.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('subscriptions.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-card-checklist text-lg"></i>
                <span>Subscriptions</span>
            </a>

            <a href="{{ route('usage.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('usage.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-lightning-charge-fill text-lg"></i>
                <span>Token & Usage Ledger</span>
            </a>

            <a href="{{ route('payment-accounts.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('payment-accounts.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-credit-card-2-front-fill text-lg"></i>
                <span>Payment Accounts</span>
            </a>

            <a href="{{ route('targets.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('targets.*') || request()->routeIs('alerts.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-crosshair text-lg"></i>
                <span>Budgets & Alerts</span>
            </a>

            <a href="{{ route('import.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('import.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-file-earmark-arrow-up-fill text-lg"></i>
                <span>Import & Audit Trail</span>
            </a>

            <a href="{{ route('profile.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('profile.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-person-bounding-box text-lg"></i>
                <span>My Profile & Password</span>
            </a>

            @if(auth()->user()->role === 'admin')
            <!-- Admin Only Section Divider -->
            <div class="pt-3 pb-1 px-4 text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                Administration
            </div>

            <a href="{{ route('users.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('users.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-people-fill text-lg"></i>
                <span>User Management</span>
            </a>

            <a href="{{ route('permissions.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('permissions.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-shield-lock-fill text-lg"></i>
                <span>Role Permissions</span>
            </a>

            <a href="{{ route('settings.index') }}" class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('settings.*') ? 'paim-nav-active' : 'paim-nav-inactive' }}">
                <i class="bi bi-gear-fill text-lg"></i>
                <span>Settings & Config</span>
            </a>
            @endif
        </nav>
    </div>

    <!-- Active User Profile Card -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800/80">
        <a href="{{ route('profile.index') }}" class="p-3 rounded-xl paim-card flex items-center gap-3 hover:opacity-90 transition-opacity">
            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-xl object-cover border border-indigo-500">
            <div class="min-w-0 flex-1">
                <span class="block text-sm font-bold paim-title leading-tight truncate">{{ auth()->user()->name ?? 'User' }}</span>
                <span class="block text-[11px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ auth()->user()->role ?? 'User' }}</span>
            </div>
        </a>
    </div>
</aside>
