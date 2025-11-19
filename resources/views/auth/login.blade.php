<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NavistFind Admin Login</title>
    @vite('resources/css/app.css')
</head>

@php
    $supportEmail = config('services.navistfind.support_email', 'support@navistfind.edu');
@endphp

<body class="min-h-screen bg-gradient-to-br from-[#0f4fa5] to-[#0b2d73] flex items-center justify-center px-4 py-10">
    <div class="relative w-full max-w-5xl rounded-[36px] bg-white shadow-[0_45px_90px_-40px_rgba(8,37,88,0.65)] overflow-hidden">
        <div class="absolute -left-28 -top-40 h-72 w-72 rounded-full bg-[#0f4fa5] opacity-60 blur-3xl"></div>
        <div class="absolute bottom-[-120px] left-8 h-72 w-72 rounded-full bg-[#0f67ff] opacity-40 blur-3xl"></div>
        <div class="absolute bottom-[-160px] right-[-80px] h-64 w-64 rounded-full bg-[#4c7dff] opacity-50 blur-3xl"></div>

        <div class="relative grid gap-0 md:grid-cols-[1.05fr_1fr]">
            <aside class="hidden md:flex flex-col justify-between bg-gradient-to-br from-[#0f4fa5] via-[#0f3b7d] to-[#10204f] p-10 text-white">
                <div>
                    <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-2 text-xs font-semibold">
                        <x-heroicon-o-sparkles class="h-4 w-4" />
                        NavistFind Admin
                    </div>
                    <h1 class="mt-6 text-3xl font-semibold leading-tight">Welcome to Carmen National High School’s lost &amp; found control center.</h1>
                    <p class="mt-4 text-sm leading-relaxed text-blue-50/90">
                        Approve claims, track handovers, and keep every student informed. All campus lost &amp; found activity is recorded here for safe, auditable returns.
                    </p>
                </div>
                <div class="space-y-3 text-sm text-blue-50/90">
                    <div class="flex items-center gap-3">
                        <span class="h-10 w-10 rounded-full bg-white/15 flex items-center justify-center"><x-heroicon-o-shield-check class="h-5 w-5" /></span>
                        <span>Only staff accounts may sign in. Students continue through the NavistFind mobile app.</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="h-10 w-10 rounded-full bg-white/15 flex items-center justify-center"><x-heroicon-o-envelope class="h-5 w-5" /></span>
                        <span>Support: <a href="mailto:{{ $supportEmail }}" class="font-semibold text-white hover:underline">{{ $supportEmail }}</a></span>
                    </div>
                </div>
            </aside>

            <section class="relative p-8 md:p-10">
                <div class="flex items-center justify-between pb-6 md:pb-8">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="NavistFind logo" class="h-10 w-10 rounded-2xl border border-slate-200" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-[#123a7d]">NavistFind</p>
                            <p class="text-sm font-semibold text-[#1c2a40]">Carmen National High School</p>
                        </div>
                    </div>
                    <a href="{{ url('/') }}" class="text-xs font-semibold text-[#123a7d] hover:underline">Back to welcome</a>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white/95 px-6 py-7 shadow-inner shadow-slate-200/40">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-semibold text-[#123a7d]">Sign in</h2>
                        <p class="text-sm text-slate-500">Administrator dashboard access</p>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-[#123a7d]">Email Address</label>
                            <div class="relative">
                                <x-heroicon-o-envelope class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required 
                                    placeholder="admin@carmennhs.edu.ph"
                                    class="w-full rounded-2xl border border-slate-200 bg-[#f9fbff] py-3 pl-11 pr-4 text-sm text-[#1c2a40] shadow-inner shadow-slate-200 focus:border-[#123a7d] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#123a7d]/30 transition"
                                >
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-[#123a7d]">Password</label>
                            <div class="relative">
                                <x-heroicon-o-key class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="••••••••"
                                    class="w-full rounded-2xl border border-slate-200 bg-[#f9fbff] py-3 pl-11 pr-12 text-sm text-[#1c2a40] shadow-inner shadow-slate-200 focus:border-[#123a7d] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#123a7d]/30 transition"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:text-[#123a7d] transition"
                                >
                                    <x-heroicon-o-eye class="h-5 w-5" id="eye-open" />
                                    <x-heroicon-o-eye-slash class="hidden h-5 w-5" id="eye-closed" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#123a7d] focus:ring-[#123a7d]/40">
                                Remember me
                            </label>
                            <a href="mailto:{{ $supportEmail }}" class="font-semibold text-[#123a7d] hover:underline">Forgot password?</a>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full rounded-2xl bg-[#123a7d] py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/25 transition hover:-translate-y-0.5 hover:bg-[#0f3b7d]"
                        >
                            Sign In
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center text-[11px] text-slate-400">
                    © {{ date('Y') }} NavistFind • Carmen National High School
                </div>
            </section>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
