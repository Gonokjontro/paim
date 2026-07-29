<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PAIM - Personal AI Subscription Management')</title>
    <meta name="description" content="Centralized AI tool inventory, cost control, token usage, payment governance, budgets, renewals, and alerts.">

    <!-- Theme Initialization Script (prevents FOUC) -->
    <script>
        (function() {
            var theme = localStorage.getItem('paim-theme');
            if (theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Metronic v9 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Smooth Theme Transitions */
        *, ::before, ::after {
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        }

        /* Smooth Custom Scrollbars */
        .paim-custom-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .paim-custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .paim-custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.25);
            border-radius: 9999px;
        }
        .paim-custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.6);
        }

        /* ----------------------------------------------------
           LIGHT MODE DESIGN SYSTEM RULES
        ---------------------------------------------------- */
        html:not(.dark) body {
            background-color: #F8FAFC !important;
            color: #1E293B !important;
        }

        html:not(.dark) .paim-header {
            background-color: #FFFFFF !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }

        html:not(.dark) .paim-sidebar {
            background-color: #FFFFFF !important;
            border-right: 1px solid #E2E8F0 !important;
        }

        html:not(.dark) .paim-footer {
            background-color: #FFFFFF !important;
            border-top: 1px solid #E2E8F0 !important;
            color: #64748B !important;
        }

        html:not(.dark) .paim-card {
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
            color: #1E293B !important;
        }

        html:not(.dark) .paim-card:hover {
            border-color: #CBD5E1 !important;
            box-shadow: 0 10px 25px -3px rgba(15, 23, 42, 0.08) !important;
        }

        /* Primary Action Buttons (Always White Text on Indigo Background) */
        html:not(.dark) .paim-btn-primary, html.dark .paim-btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%) !important;
            color: #FFFFFF !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25) !important;
        }
        html:not(.dark) .paim-btn-primary:hover, html.dark .paim-btn-primary:hover {
            background: linear-gradient(135deg, #4338CA 0%, #6D28D9 100%) !important;
            color: #FFFFFF !important;
            transform: translateY(-1px);
        }
        html:not(.dark) .paim-btn-primary *, html.dark .paim-btn-primary * {
            color: #FFFFFF !important;
        }

        /* Secondary & Ghost Buttons */
        html:not(.dark) .paim-btn-secondary {
            background-color: #F1F5F9 !important;
            color: #334155 !important;
            border: 1px solid #CBD5E1 !important;
        }
        html:not(.dark) .paim-btn-secondary:hover {
            background-color: #E2E8F0 !important;
            color: #0F172A !important;
        }

        /* Active Sidebar Menu Link */
        html:not(.dark) .paim-nav-active {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%) !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25) !important;
        }
        html:not(.dark) .paim-nav-active * {
            color: #FFFFFF !important;
        }

        html:not(.dark) .paim-nav-inactive {
            color: #475569 !important;
            background: transparent !important;
        }
        html:not(.dark) .paim-nav-inactive:hover {
            background-color: #F1F5F9 !important;
            color: #4F46E5 !important;
        }

        /* Headings & Text in Light Mode */
        html:not(.dark) .paim-title {
            color: #0F172A !important;
        }
        html:not(.dark) .paim-subtitle {
            color: #64748B !important;
        }
        html:not(.dark) .paim-text {
            color: #334155 !important;
        }

        /* Tables in Light Mode */
        html:not(.dark) .paim-table th {
            background-color: #F8FAFC !important;
            color: #475569 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        html:not(.dark) .paim-table td {
            border-bottom: 1px solid #F1F5F9 !important;
            color: #1E293B !important;
        }
        html:not(.dark) .paim-table tr:hover td {
            background-color: #F8FAFC !important;
        }

        /* Modals in Light Mode */
        html:not(.dark) .paim-modal-box {
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            color: #0F172A !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
        }
        html:not(.dark) .paim-input {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
            border: 1px solid #CBD5E1 !important;
        }
        html:not(.dark) .paim-input:focus {
            border-color: #6366F1 !important;
            background-color: #FFFFFF !important;
        }

        /* Badges in Light Mode */
        html:not(.dark) .paim-badge-role {
            background-color: #EEF2FF !important;
            color: #3730A3 !important;
            border: 1px solid #C7D2FE !important;
        }
        html:not(.dark) .paim-badge-success {
            background-color: #ECFDF5 !important;
            color: #065F46 !important;
            border: 1px solid #A7F3D0 !important;
        }
        html:not(.dark) .paim-badge-warning {
            background-color: #FFFBEB !important;
            color: #92400E !important;
            border: 1px solid #FDE68A !important;
        }
        html:not(.dark) .paim-badge-danger {
            background-color: #FEF2F2 !important;
            color: #991B1B !important;
            border: 1px solid #FECACA !important;
        }


        /* ----------------------------------------------------
           DARK MODE DESIGN SYSTEM RULES
        ---------------------------------------------------- */
        html.dark body {
            background-color: #090D16 !important;
            color: #F3F4F6 !important;
        }

        html.dark .paim-header {
            background-color: rgba(11, 15, 25, 0.90) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(16px);
        }

        html.dark .paim-sidebar {
            background-color: #0D111D !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        html.dark .paim-footer {
            background-color: #0B0F19 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #94A3B8 !important;
        }

        html.dark .paim-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.90) 0%, rgba(11, 15, 25, 0.85) 100%) !important;
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.40) !important;
            color: #F3F4F6 !important;
        }

        html.dark .paim-card:hover {
            border-color: rgba(99, 102, 241, 0.40) !important;
            box-shadow: 0 12px 40px 0 rgba(99, 102, 241, 0.18) !important;
        }

        /* Active Sidebar Menu Link in Dark Mode */
        html.dark .paim-nav-active {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%) !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35) !important;
        }
        html.dark .paim-nav-active * {
            color: #FFFFFF !important;
        }

        html.dark .paim-nav-inactive {
            color: #94A3B8 !important;
            background: transparent !important;
        }
        html.dark .paim-nav-inactive:hover {
            background-color: rgba(30, 41, 59, 0.6) !important;
            color: #FFFFFF !important;
        }

        html.dark .paim-btn-secondary {
            background-color: #0F172A !important;
            color: #CBD5E1 !important;
            border: 1px solid #334155 !important;
        }
        html.dark .paim-btn-secondary:hover {
            background-color: #1E293B !important;
            color: #FFFFFF !important;
        }

        /* Headings & Text in Dark Mode */
        html.dark .paim-title {
            color: #FFFFFF !important;
        }
        html.dark .paim-subtitle {
            color: #94A3B8 !important;
        }
        html.dark .paim-text {
            color: #CBD5E1 !important;
        }

        /* Tables in Dark Mode */
        html.dark .paim-table th {
            background-color: rgba(15, 23, 42, 0.90) !important;
            color: #94A3B8 !important;
            border-bottom: 1px solid #1E293B !important;
        }
        html.dark .paim-table td {
            border-bottom: 1px solid rgba(30, 41, 59, 0.6) !important;
            color: #CBD5E1 !important;
        }
        html.dark .paim-table tr:hover td {
            background-color: rgba(30, 41, 59, 0.4) !important;
        }

        /* Modals in Dark Mode */
        html.dark .paim-modal-box {
            background-color: #0F172A !important;
            border: 1px solid #334155 !important;
            color: #F8FAFC !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }
        html.dark .paim-input {
            background-color: #020617 !important;
            color: #F8FAFC !important;
            border: 1px solid #334155 !important;
        }
        html.dark .paim-input:focus {
            border-color: #6366F1 !important;
        }

        /* Badges in Dark Mode */
        html.dark .paim-badge-role {
            background-color: rgba(99, 102, 241, 0.2) !important;
            color: #A5B4FC !important;
            border: 1px solid rgba(99, 102, 241, 0.3) !important;
        }
        html.dark .paim-badge-success {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #6EE7B7 !important;
            border: 1px solid rgba(16, 185, 129, 0.3) !important;
        }
        html.dark .paim-badge-warning {
            background-color: rgba(245, 158, 11, 0.2) !important;
            color: #FCD34D !important;
            border: 1px solid rgba(245, 158, 11, 0.3) !important;
        }
        html.dark .paim-badge-danger {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #FCA5A5 !important;
            border: 1px solid rgba(239, 68, 68, 0.3) !important;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #A855F7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white">

    <div class="flex min-h-screen w-full">
        <!-- Sidebar Navigation -->
        @include('partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
            <!-- Header Bar -->
            @include('partials.header')

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-check-circle-fill text-xl text-emerald-500"></i>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-200"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-6 mt-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-300 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill text-xl text-rose-500"></i>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-200"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            <!-- Main Page Content -->
            <main class="flex-1 p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('paim-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('paim-theme', 'dark');
            }
        }
    </script>

    <!-- Metronic Core Scripts -->
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    @stack('scripts')
</body>
</html>
