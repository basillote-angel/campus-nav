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

<body class="min-h-screen bg-white flex items-center justify-center px-4 py-10">
    <div class="relative w-full max-w-5xl rounded-[36px] bg-white shadow-[0_45px_90px_-40px_rgba(8,37,88,0.65)] overflow-hidden">

        <div class="relative grid gap-0 md:grid-cols-[1.05fr_1fr]">
            <aside class="hidden md:flex flex-col items-center justify-center bg-[#edf5ff] px-10 pt-5 pb-20 text-center text-[#0f2f63]">
                <img src="{{ asset('images/logo.png') }}" alt="NavistFind logo" class="h-56 w-56 rounded-[2.5rem]" />
                <h1 class="mt-0 text-3xl font-semibold leading-tight">Welcome to Carmen National High School’s lost &amp; found control center.</h1>
            </aside>

            <section class="relative bg-gradient-to-br from-[#0f4fa5] via-[#0f3b7d] to-[#10204f] p-8 md:p-10 text-white">
                <div class="flex justify-end pb-4">
                    <a href="{{ url('/') }}" class="text-xs font-semibold text-white/80 hover:text-white underline">Back to welcome page</a>
                </div>

                <div class="rounded-[28px] border border-white/10 bg-white/5 px-6 py-7 shadow-xl shadow-black/25 backdrop-blur">
                    <div class="mb-6 text-center">
                        <h2 class="text-2xl font-semibold text-white">Sign in</h2>
                        <p class="text-sm text-white/75">Administrator dashboard access</p>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 rounded-xl border border-white/20 bg-white/10 p-4 text-sm text-white">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-300/40 bg-red-500/10 p-4 text-sm text-red-100">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-white">Email Address</label>
                            <div class="relative">
                                <x-heroicon-o-envelope class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-white/60" />
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required 
                                    placeholder="Enter Email Address"
                                    class="w-full rounded-2xl border border-white/20 bg-white/90 py-3 pl-11 pr-4 text-sm text-[#1c2a40] shadow-inner shadow-black/10 focus:border-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-white/40 transition"
                                >
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-white">Password</label>
                            <div class="relative">
                                <x-heroicon-o-key class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-white/60" />
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    placeholder="••••••••"
                                    class="w-full rounded-2xl border border-white/20 bg-white/90 py-3 pl-11 pr-12 text-sm text-[#1c2a40] shadow-inner shadow-black/10 focus:border-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-white/40 transition"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-white/80 text-[#123a7d] hover:bg-white transition"
                                >
                                    <x-heroicon-o-eye class="h-5 w-5" id="eye-open" />
                                    <x-heroicon-o-eye-slash class="hidden h-5 w-5" id="eye-closed" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-white/80">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#123a7d] focus:ring-[#123a7d]/40">
                                Remember me
                            </label>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full rounded-2xl bg-white/90 py-3 text-sm font-semibold text-[#0f3b7d] shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-white"
                        >
                            Sign In
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center text-[11px] text-white/60">
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
