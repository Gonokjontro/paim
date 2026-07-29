<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - PAIM AI Subscription Control</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #090D16;
            color: #F3F4F6;
        }

        .glass-card {
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.85) 0%, rgba(15, 23, 42, 0.65) 100%);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
        }

        .gradient-text {
            background: linear-gradient(135deg, #818CF8 0%, #C084FC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 lg:p-8 bg-[#090D16] relative overflow-hidden">

    <!-- Ambient Glowing Background Elements -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8 items-center relative z-10">

        <!-- Left Column: Branding & Features -->
        <div class="space-y-6 text-center lg:text-left">
            <div class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="bi bi-cpu-fill text-2xl text-white"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-white gradient-text">PAIM</span>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">AI Subscription Control</span>
                </div>
            </div>

            <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                Centralized AI & Software Subscription Governance
            </h1>

            <p class="text-sm text-slate-400 leading-relaxed">
                Single source of truth for tracking ChatGPT, Claude, Midjourney, and API token spending with RBAC permission management.
            </p>

            <!-- Role Badge Features -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-6 h-6 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">1</span>
                    <span><strong>Admin Role</strong>: Full CRUD, payment source governance & user management</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs">2</span>
                    <span><strong>Manager Role</strong>: Manage subscriptions, log token usage & add cards</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-300">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">3</span>
                    <span><strong>Viewer Role</strong>: Read-only access to dashboards, reports & audit trails</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Login Card & Demo Quick Select -->
        <div class="p-8 rounded-3xl glass-card space-y-6">
            <div>
                <h2 class="text-xl font-extrabold text-white">Sign In to Workspace</h2>
                <p class="text-xs text-slate-400 mt-1">Select a demo user role below or enter credentials</p>
            </div>

            <!-- Demo Credentials Banner -->
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-400 flex items-center gap-1.5">
                        <i class="bi bi-key-fill"></i> Quick Demo Logins
                    </span>
                    <span class="text-[11px] text-slate-500 font-mono">Password: password</span>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    @foreach($demoUsers as $demo)
                    <button type="button" onclick="fillLoginForm('{{ $demo['email'] }}', '{{ $demo['password'] }}')" class="p-2.5 rounded-xl bg-slate-950/80 hover:bg-indigo-950/40 border border-slate-800 hover:border-indigo-500/50 flex items-center justify-between text-left transition-all group">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $demo['role'] === 'Admin' ? 'bg-indigo-500/20 text-indigo-300' : ($demo['role'] === 'Manager' ? 'bg-purple-500/20 text-purple-300' : 'bg-emerald-500/20 text-emerald-300') }}">
                                    {{ $demo['role'] }}
                                </span>
                                <span class="text-xs font-semibold text-white">{{ $demo['email'] }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400 block mt-0.5">{{ $demo['desc'] }}</span>
                        </div>
                        <i class="bi bi-arrow-right-short text-xl text-slate-500 group-hover:text-indigo-400 transition-colors"></i>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Standard Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@paim.ai') }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none transition-all">
                    @error('email')
                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Password</label>
                    <input type="password" id="password" name="password" value="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none transition-all">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                        <input type="checkbox" name="remember" checked class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-0">
                        <span>Remember Me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-sm transition-all shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                    <i class="bi bi-box-arrow-in-right text-lg"></i>
                    <span>Sign In to Dashboard</span>
                </button>
            </form>
        </div>

    </div>

    <script>
        function fillLoginForm(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
