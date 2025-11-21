<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NavistFind | Campus Lost & Found</title>
    @vite('resources/css/app.css')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="/resource/css/app.css">
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>

@php
    $androidDownloadUrl = config('services.navistfind.android_url', '#');
    $supportEmail = config('services.navistfind.support_email', 'support@navistfind.org');
    $adminAccessEmail = config('services.navistfind.admin_access_email', 'admin-support@navistfind.edu');
@endphp

<body class="bg-[#eef1fc] text-[#1c2a40] antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-40 bg-gradient-to-r from-[#123a7d] to-[#0f2f63] px-4 py-3">
            <div class="mx-auto flex w-full max-w-6xl items-center gap-4 rounded-full border border-white/20 bg-white/95 px-6 py-3 text-[#1e1e3e] shadow-2xl shadow-[#1b0d4f]/20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="NavistFind logo" class="h-12 w-12 rounded-2xl border border-[#123a7d]/20 shadow-sm" />
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.45em] text-[#123a7d]">NavistFind</p>
                        <p class="text-base font-semibold text-[#1c1c3f]">Carmen National High School</p>
                    </div>
                </div>
                <nav class="hidden flex-1 items-center justify-center gap-7 text-sm font-semibold text-[#4a4c75] md:flex">
                    <a href="#home" data-nav-target="home" class="nav-link relative px-2 py-1 transition-colors hover:text-[#f9c74f]">
                        Home
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 rounded-full bg-[#f9c74f] transition-transform duration-200"></span>
                    </a>
                    <a href="#features" data-nav-target="features" class="nav-link relative px-2 py-1 transition-colors hover:text-[#f9c74f]">
                        Features
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 rounded-full bg-[#f9c74f] transition-transform duration-200"></span>
                    </a>
                    <a href="#howitwork" data-nav-target="howitwork" class="nav-link relative px-2 py-1 transition-colors hover:text-[#f9c74f]">
                        How It Works
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 rounded-full bg-[#f9c74f] transition-transform duration-200"></span>
                    </a>
                    <a href="#support" data-nav-target="support" class="nav-link relative px-2 py-1 transition-colors hover:text-[#f9c74f]">
                        Support
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 rounded-full bg-[#f9c74f] transition-transform duration-200"></span>
                    </a>
                </nav>
                <div class="hidden items-center gap-3 md:flex">
                    <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center gap-2 rounded-full border border-[#123a7d]/50 px-4 py-2 text-sm font-semibold text-[#123a7d] transition-all hover:-translate-y-0.5 hover:bg-[#e6eefc]">
                        <x-heroicon-o-device-phone-mobile class="h-4 w-4" />
                        Get the App
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-[#123a7d] px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-[#0a1b3f]/40 transition-all hover:-translate-y-0.5 hover:bg-[#0f2f63]">
                        <x-heroicon-o-lock-closed class="h-4 w-4" />
                        Admin Login
                    </a>
                </div>
                <details class="relative ml-auto w-max md:hidden">
                    <summary class="flex cursor-pointer items-center gap-2 rounded-full border border-white/60 bg-white/20 px-4 py-2 text-sm font-semibold text-white shadow-sm backdrop-blur">
                        Menu
                        <x-heroicon-o-bars-3 class="h-5 w-5" />
                    </summary>
                    <div class="absolute right-0 mt-3 w-56 rounded-2xl border border-white/40 bg-white p-4 text-sm font-semibold text-[#4a4c75] shadow-xl">
                        <a href="#home" class="block rounded-lg px-3 py-2 hover:bg-[#fff5d7] hover:text-[#f9c74f]">Home</a>
                        <a href="#features" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#fff5d7] hover:text-[#f9c74f]">Features</a>
                        <a href="#howitwork" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#fff5d7] hover:text-[#f9c74f]">How it works</a>
                        <a href="#support" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#fff5d7] hover:text-[#f9c74f]">Support</a>
                        <hr class="my-3 border-slate-200">
                        <a href="{{ $androidDownloadUrl }}" class="block rounded-lg px-3 py-2 text-[#123a7d] hover:bg-[#e6eefc]">Get the App</a>
                        <a href="{{ route('login') }}" class="mt-1 block rounded-lg px-3 py-2 text-[#123a7d] hover:bg-[#e6eefc]">Admin Login</a>
                    </div>
                </details>
            </div>
        </header>

        <main class="flex-1">
            <section id="home" class="relative overflow-hidden bg-gradient-to-br from-[#123a7d] via-[#0f2f63] to-[#061842] text-white">
                <div class="pointer-events-none absolute inset-0 opacity-20">
                    <div class="h-full w-full bg-[radial-gradient(circle_at_center,_rgba(255,255,255,0.35)_1px,_transparent_1px)] bg-[length:80px_80px]"></div>
                </div>
                <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-6 py-18 lg:grid-cols-2 lg:py-28">
                    <div class="max-w-xl text-center lg:text-left">
                        <p class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.35em]">
                            Find lost items now
                            <span class="h-1 w-1 rounded-full bg-[#ffb86c]"></span>
                            Live updates
                        </p>
                        <h1 class="mt-6 text-4xl font-bold leading-tight md:text-5xl lg:text-[3.5rem]">
                            Find your campus essentials with
                            <span class="text-[#ffb86c]">NavistFind</span>
                        </h1>
                        <p class="mt-5 text-lg text-white/85">
                            NavistFind unifies student reports, AI matches, and admin approvals so every lost-and-found journey feels effortless and transparent.
                        </p>
                        <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-start">
                            <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-white px-6 py-3 font-semibold text-[#123a7d] shadow-lg shadow-black/15 transition-transform hover:-translate-y-0.5">
                                <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
                                Download Mobile App
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 rounded-2xl border border-white/40 bg-white/5 px-6 py-3 font-semibold text-white backdrop-blur transition-all hover:-translate-y-0.5 hover:bg-white/15">
                                <x-heroicon-o-arrow-right-circle class="h-5 w-5" />
                                Admin Sign In
                            </a>
                        </div>
                        <p class="mt-2 text-xs uppercase tracking-[0.25em] text-white/70">Android first • iOS beta soon</p>
                        <div class="mt-10 grid gap-4 rounded-2xl border border-white/20 bg-white/5 p-5 text-left shadow-lg shadow-black/10 sm:grid-cols-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-white/70">Active students</p>
                                <p class="mt-1 text-2xl font-bold">{{ number_format($activeStudentCount ?? 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-white/70">Resolved pickups</p>
                                <p class="mt-1 text-2xl font-bold">1,240+</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-white/70">Median response</p>
                                <p class="mt-1 text-2xl font-bold">2h 45m</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <div class="relative w-full max-w-5xl">
                            <div class="absolute -left-12 top-6 h-16 w-16 rounded-full bg-white/15 blur-3xl"></div>
                            <div class="absolute -right-6 bottom-6 h-24 w-24 rounded-full bg-[#ffb86c]/30 blur-3xl"></div>
                            <div class="flex items-center justify-center gap-6">
                                <div class="relative w-full max-w-xs">
                                    <div class="rounded-[2.5rem] bg-white/10 p-4 shadow-2xl shadow-[#0b1633]/40 backdrop-blur">
                                        <div class="rounded-[2rem] bg-white p-3">
                                            <img src="{{ asset('images/home_page.jpg') }}" alt="Mobile app interface" class="h-full w-full rounded-[1.7rem] object-cover">
                                        </div>
                                    </div>
                                </div>
                                <div class="relative w-full max-w-xs -ml-8 rotate-3">
                                    <div class="rounded-[2.5rem] bg-white/10 p-4 shadow-2xl shadow-[#0b1633]/40 backdrop-blur">
                                        <div class="rounded-[2rem] bg-white p-3">
                                            <img src="{{ asset('images/post_item.jpg') }}" alt="Mobile app interface" class="h-full w-full rounded-[1.7rem] object-cover">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

         <section id="features" class="bg-gradient-to-b from-white to-[#edf3ff] py-20 md:py-28">
    <div class="max-w-6xl mx-auto px-6">
        
        <div class="mb-12 md:mb-16 text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-[#e6eefc] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[#123a7d]">
                Features
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-semibold text-[#1c1c3f]">Every role gets superpowers</h2>
            <p class="mt-3 text-sm text-[#4b4e6b] md:text-base max-w-2xl mx-auto">
                Discover how students, AI, and admins collaborate across one guided workflow to return essentials faster.
            </p>
        </div>

        <div class="grid gap-8 md:grid-cols-3">

            <div class="group rounded-3xl border border-transparent bg-white/90 p-6 shadow-xl shadow-[#8aa7d7]/25 transition duration-300 hover:-translate-y-2 hover:border-[#c6d8ff] hover:shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-[#e6eefc] p-3 text-[#123a7d]">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A1.5 1.5 0 0 1 18 21.75H6a1.5 1.5 0 0 1-1.499-1.632Z" />
                        </svg>
                    </div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#123a7d] font-semibold">Students</p>
                </div>
                <h3 class="mt-5 text-2xl font-semibold text-[#1c1c3f]">Lost item reporting</h3>
                <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                    Capture details, attach proof, and submit within seconds. Smart validation keeps every post clean and match-ready.
                </p>
                <div class="mt-6 h-48 overflow-hidden rounded-2xl bg-gradient-to-br from-[#f7f8ff] to-white p-4">
                    <img src="{{ asset('images/post_item.jpg') }}" alt="App screenshot of lost item reporting" class="h-full w-full rounded-2xl object-cover shadow-lg shadow-[#c7cbff]/40">
                </div>
            </div>

            <div class="group rounded-3xl border border-transparent bg-white/90 p-6 shadow-xl shadow-[#8aa7d7]/25 transition duration-300 hover:-translate-y-2 hover:border-[#c6d8ff] hover:shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-[#eaf5ff] p-3 text-[#0f63a1]">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                        </svg>
                    </div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#0f63a1] font-semibold">AI Service</p>
                </div>
                <h3 class="mt-5 text-2xl font-semibold text-[#1c1c3f]">Smart recommendations</h3>
                <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                    SBERT similarity scoring surfaces potential matches, notifies students, and escalates duplicate claims to admins.
                </p>
                <div class="mt-6 h-48 overflow-hidden rounded-2xl bg-gradient-to-br from-[#eef6ff] to-white p-4">
                    <img src="{{ asset('images/recomendation.jpg') }}" alt="AI smart recommendations" class="h-full w-full rounded-2xl object-cover shadow-lg shadow-[#9ed3ff]/40">
                </div>
            </div>

            <div class="group rounded-3xl border border-transparent bg-white/90 p-6 shadow-xl shadow-[#8aa7d7]/25 transition duration-300 hover:-translate-y-2 hover:border-[#c6d8ff] hover:shadow-2xl">

                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-[#f6f2eb] p-3 text-[#c2612d]">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#123a7d] font-semibold">Admins</p>
                </div>
                <h3 class="mt-5 text-2xl font-semibold text-[#1c1c3f]">Accountable decisions</h3>
                <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                    Evaluate claims with evidence, lock items for pickup, and archive releases with complete audit logs.
                </p>
                <div class="mt-6 h-48 overflow-hidden rounded-2xl bg-gradient-to-br from-[#fff3ec] to-white p-4">
                    <img src="{{ asset('images/claim.png') }}" alt="Admin dashboard for claims" class="h-full w-full rounded-2xl object-cover shadow-lg shadow-[#ffd4ba]/50">
                </div>
            </div>

        </div>
    </div>
</section>



            <section id="howitwork" class="bg-white">
                <div class="mx-auto max-w-6xl px-6 py-18 md:py-20">
                    <div class="mb-12 text-center">
                        <span class="inline-flex items-center gap-2 rounded-full bg-[#e6eefc] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[#123a7d]">
                            How it works
                        </span>
                        <h2 class="mt-4 text-3xl font-semibold text-[#1c1c3f]">From lost alert to verified pickup</h2>
                        <p class="mt-2 text-sm text-slate-600 md:text-base">Each milestone keeps students informed while giving admins everything they need to make the handoff seamless.</p>
                    </div>

                    @php
                        $steps = [
                            [
                                'title' => 'Lost item reported',
                                'copy' => 'Student captures the essentials in the NavistFind mobile app (title, description, photo, location).',
                                'badge' => 'Mobile',
                                'icon' => 'pencil',
                                'tone' => 'sky'
                            ],
                            [
                                'title' => 'AI matches suggested',
                                'copy' => 'SBERT engine acts instantly, comparing the post against found inventory and notifying the student.',
                                'badge' => 'AI Engine',
                                'icon' => 'sparkles',
                                'tone' => 'violet'
                            ],
                            [
                                'title' => 'Claim submitted',
                                'copy' => 'Student taps “This is mine”, leaves proof, and the found item locks into a pending claim state.',
                                'badge' => 'Mobile',
                                'icon' => 'chat-bubble',
                                'tone' => 'emerald'
                            ],
                            [
                                'title' => 'Admin decision',
                                'copy' => 'Staff validates evidence inside the web portal, approving or rejecting with documented reasoning.',
                                'badge' => 'Admin',
                                'icon' => 'check-badge',
                                'tone' => 'amber'
                            ],
                            [
                                'title' => 'Pickup & archive',
                                'copy' => 'Admin records ID verification, hands over the item, and archives the case for audit readiness.',
                                'badge' => 'Admin',
                                'icon' => 'archive-box',
                                'tone' => 'slate'
                            ],
                        ];
                    @endphp

                    <div class="relative">
                        <div class="pointer-events-none absolute left-0 right-0 top-[84px] hidden h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent md:block"></div>
                        <div class="grid gap-6 md:grid-cols-5">
                            @foreach ($steps as $index => $step)
                                <div class="group relative flex h-full flex-col gap-4 rounded-3xl border border-slate-200 bg-white px-5 py-8 text-left shadow-sm transition-transform hover:-translate-y-1 hover:shadow-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-{{ $step['tone'] }}-100 text-{{ $step['tone'] }}-600 shadow-inner shadow-{{ $step['tone'] }}-200/60">
                                            @switch($step['icon'])
                                                @case('pencil')
                                                    <x-heroicon-o-pencil-square class="h-6 w-6" />
                                                    @break
                                                @case('sparkles')
                                                    <x-heroicon-o-sparkles class="h-6 w-6" />
                                                    @break
                                                @case('chat-bubble')
                                                    <x-heroicon-o-chat-bubble-left-right class="h-6 w-6" />
                                                    @break
                                                @case('check-badge')
                                                    <x-heroicon-o-check-badge class="h-6 w-6" />
                                                    @break
                                                @case('archive-box')
                                                    <x-heroicon-o-archive-box class="h-6 w-6" />
                                                    @break
                                            @endswitch
                                        </div>
                                        <span class="inline-flex items-center gap-2 rounded-full border border-{{ $step['tone'] }}-200 bg-{{ $step['tone'] }}-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-{{ $step['tone'] }}-600">
                                            Step {{ $index + 1 }} • {{ $step['badge'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-[#1c1c3f]">{{ $step['title'] }}</h3>
                                    <p class="text-sm text-slate-600">{{ $step['copy'] }}</p>
                                    @if($index < count($steps) - 1)
                                        <span class="absolute right-[-20px] top-16 hidden h-0.5 w-10 rounded-full bg-slate-200 md:block"></span>
                                    @endif
                                    @if($index < count($steps) - 1)
                                        <span class="block h-0.5 w-20 rounded-full bg-slate-200 md:hidden"></span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    

                </div>
            </section>
        </main>
                
             <section class="bg-gradient-to-r from-[#123a7d] to-[#0f2f63] py-16 text-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-8 px-6 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <p class="text-sm uppercase tracking-[0.4em] text-white/70">Lost items? We’ve got you covered</p>
                    <h2 class="mt-3 text-3xl font-semibold leading-tight lg:text-4xl">Bring NavistFind to every student pocket.</h2>
                    <p class="mt-4 text-white/80">Download the Android app or log into the admin console to keep every handoff organized and auditable.</p>
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-[#123a7d] shadow-lg shadow-black/20 transition-transform hover:-translate-y-0.5">
                            <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
                            Download Mobile App
                        </a>
                        <span class="text-xs uppercase tracking-[0.3em] text-white/70">v1.4.0 • Android 11+</span>
                    </div>
                </div>
                <div class="flex flex-1 items-center justify-center">
                    <div class="rounded-3xl border border-white/30 bg-white/10 p-6 text-center shadow-2xl backdrop-blur">
                        <p class="text-xs uppercase tracking-[0.4em] text-white/70">Scan to preview</p>
                        <div class="mt-4 rounded-2xl bg-white/80 p-4">
                            <img src="{{ asset('images/navistfind-mockup.png') }}" alt="NavistFind preview" class="h-32 w-32 rounded-2xl object-cover shadow-inner shadow-black/10">
                        </div>
                        <p class="mt-4 text-sm text-white/80">Show this to students during orientation to onboard faster.</p>
                    </div>
                </div>
            </div>
        </section>

             <footer id="support" class="border-t border-slate-200 bg-white">
            <div class="mx-auto grid max-w-6xl gap-10 px-6 py-12 md:grid-cols-[1.3fr_1fr]">
                <div class="space-y-4">
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-400">NavistFind • Carmen National High School</p>
                    <h4 class="text-2xl font-semibold text-[#1c1c3f]">Need help with the lost &amp; found process?</h4>
                    <p class="text-sm text-slate-600">
                        Our campus support team can guide you through reporting items, monitoring claims, or handling pickups. Reach out anytime during school office hours.
                    </p>
                    <div class="inline-flex items-center gap-3 rounded-full border border-[#123a7d]/40 bg-[#e6eefc] px-5 py-2 text-sm font-semibold text-[#123a7d]">
                        <x-heroicon-o-clock class="h-5 w-5" />
                        Monday – Friday • 8:00 AM – 5:00 PM
                    </div>
                </div>
                <div class="space-y-3 text-sm text-slate-600">
                    <a href="mailto:{{ $supportEmail }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-[#f8faff] px-4 py-3 transition-colors hover:border-[#123a7d] hover:text-[#123a7d]">
                        <span class="rounded-full bg-[#123a7d]/10 p-2 text-[#123a7d]">
                            <x-heroicon-o-envelope class="h-5 w-5" />
                        </span>
                        {{ $supportEmail }}
                    </a>
                    <a href="tel:+631234567890" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition-colors hover:border-[#123a7d] hover:text-[#123a7d]">
                        <span class="rounded-full bg-[#123a7d]/10 p-2 text-[#123a7d]">
                            <x-heroicon-o-phone class="h-5 w-5" />
                        </span>
                        +63 123 456 7890
                    </a>
                    <p class="text-xs text-slate-400">
                        © {{ date('Y') }} NavistFind • All rights reserved.
                    </p>
                </div>
            </div>
        </footer>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = [];
            navLinks.forEach(link => {
                const id = link.dataset.navTarget;
                const section = document.getElementById(id);
                if (section) {
                    sections.push({ id, section, link });
                }
            });

            const activateLink = (id) => {
                sections.forEach(({ id: sectionId, link }) => {
                    const underline = link.querySelector('.nav-underline');
                    if (sectionId === id) {
                        link.classList.add('text-[#f9c74f]');
                        if (underline) {
                            underline.style.transform = 'scaleX(1)';
                        }
            } else {
                        link.classList.remove('text-[#f9c74f]');
                        if (underline) {
                            underline.style.transform = 'scaleX(0)';
                        }
                    }
                });
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        activateLink(entry.target.id);
                    }
                });
            }, { root: null, rootMargin: '-40% 0px -50% 0px', threshold: [0, 0.4] });

            sections.forEach(({ section }) => observer.observe(section));

            if (sections.length) {
                activateLink(sections[0].id);
            }
        });
    </script>



</body>
</html>