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

<body class="bg-[#f5f7fb] text-[#1c2a40] antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-6 py-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="NavistFind logo" class="h-12 w-12 rounded-xl border border-slate-200 shadow-sm" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.45em] text-[#123a7d]">NavistFind</p>
                        <p class="text-base font-semibold text-[#1c2a40]">Carmen National High School</p>
                    </div>
                </div>

                <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 lg:flex">
                    <a href="#home" data-nav-target="home" class="nav-link group relative transition-colors hover:text-[#123a7d]">
                        Home
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 bg-[#123a7d] transition-transform duration-200 group-hover:scale-x-100"></span>
                    </a>
                    <a href="#features" data-nav-target="features" class="nav-link group relative transition-colors hover:text-[#123a7d]">
                        Features
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 bg-[#123a7d] transition-transform duration-200 group-hover:scale-x-100"></span>
                    </a>
                    <a href="#howitwork" data-nav-target="howitworks" class="nav-link group relative transition-colors hover:text-[#123a7d]">
                        How It Works
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 bg-[#123a7d] transition-transform duration-200 group-hover:scale-x-100"></span>
                    </a>
                </nav>


                <div class="hidden items-center gap-3 lg:flex">
                    <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center gap-2 rounded-full border border-[#123a7d] px-4 py-2 text-sm font-semibold text-[#123a7d] transition-transform hover:-translate-y-0.5 hover:bg-[#123a7d]/5">
                        <x-heroicon-o-device-phone-mobile class="h-4 w-4" />
                        Download App
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-[#123a7d] px-5 py-2 text-sm font-semibold text-white shadow-md shadow-blue-900/25 transition-transform hover:-translate-y-0.5 hover:bg-[#0f2f63]">
                        <x-heroicon-o-lock-closed class="h-4 w-4" />
                        Admin Login
                    </a>
                </div>

                <details class="relative w-max lg:hidden">
                    <summary class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-[#123a7d] shadow-sm">
                        Menu
                        <x-heroicon-o-bars-3 class="h-5 w-5" />
                    </summary>
                    <div class="absolute right-0 mt-3 w-48 rounded-2xl border border-slate-200 bg-white p-4 text-sm font-medium text-slate-600 shadow-xl">
                        <a href="#Home" class="block rounded-lg px-3 py-2 hover:bg-[#f1f5ff] hover:text-[#123a7d]">Home</a>
                        <a href="#features" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#f1f5ff] hover:text-[#123a7d]">Features</a>
                        <a href="#howitwork" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#f1f5ff] hover:text-[#123a7d]">How it works</a>
                        <hr class="my-3 border-slate-200">
                        <a href="{{ $androidDownloadUrl }}" class="block rounded-lg px-3 py-2 text-[#123a7d] hover:bg-[#f1f5ff]">Download App</a>
                        <a href="{{ route('login') }}" class="mt-1 block rounded-lg px-3 py-2 text-[#123a7d] hover:bg-[#f1f5ff]">Admin Login</a>
                    </div>
                </details>
            </div>
        </header>

        <main class="flex-1">
            <section class="bg-white">
                <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 md:grid-cols-2 md:items-center md:py-24">
                    <div class="space-y-6">
                        <span class="inline-flex w-max items-center gap-2 rounded-full bg-[#e4ecff] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[#123a7d]">
                            <x-heroicon-o-sparkles class="h-4 w-4" />
                            SMART LOST &amp; FOUND
                        </span>
                        <div class="space-y-4">
                            <h1 class="text-4xl font-bold leading-tight text-[#123a7d] md:text-5xl">
                                Connecting what's lost with who missed it
                            </h1>
                            <p class="text-base text-slate-600 md:text-lg">
                                NavistFind connects the student mobile app with the admin dashboard to streamline the entire process—from reporting and matching to approving and handing over items, all with clear status updates.
                            </p>

        </div>
                      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center justify-center gap-3 rounded-lg bg-[#123a7d] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-blue-900/20 transition-transform hover:-translate-y-0.5 hover:bg-[#0f2f63]">
                            <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
                            Download Mobile App
                        </a>

                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 rounded-lg border border-[#123a7d] px-6 py-3 text-sm font-semibold text-[#123a7d] hover:bg-[#123a7d]/5 transition-transform hover:-translate-y-0.5">
                            <x-heroicon-o-arrow-right-circle class="h-5 w-5" />
                            Admin Sign In
                        </a>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[#f7faff] px-5 py-3 text-xs text-slate-500 md:text-sm">
                        <strong class="text-[#123a7d]">Students:</strong> Post lost items, review AI-powered matches, and manage claims through the NavistFind mobile app.
                    </div>
</div>
            
                   <div class="relative flex items-center justify-center">
                        <div class="absolute -top-8 -left-6 h-24 w-24 rounded-full bg-[#d8e6ff] blur-2xl"></div>
                        <div class="absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-[#ffe7cc] blur-2xl"></div>

                        <img src="{{ asset('images/navistfind-mockup.png') }}" alt="NavistFind Mobile App Mockup" class="relative z-10 w-full max-w-sm rounded-3xl shadow-2xl border border-slate-200">
                    </div>      

            </section>

         <section id="features" class="bg-[#123A7D] py-20 md:py-28">
    <div class="max-w-6xl mx-auto px-6 text-center">
        
        <div class="mb-12 md:mb-16 text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-[#e4ecff] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[#123a7d]">
                Features
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-semibold text-white">Key Features by Roles</h2>
            <p class="mt-3 text-sm text-slate-200 md:text-base max-w-2xl mx-auto">
                Discover how each user contributes to a smarter, faster, and more organized lost-and-found experience.
            </p>
        </div>

        <div class="grid gap-8 md:grid-cols-3">

            <div class="rounded-3xl bg-white shadow-lg overflow-hidden transition duration-300 transform hover:-translate-y-2 hover:shadow-xl">
                
                <div class="p-6 md:p-8 text-center">
                    <div class="flex items-center gap-2 justify-center">
                        <svg class="w-5 h-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A1.5 1.5 0 0 1 18 21.75H6a1.5 1.5 0 0 1-1.499-1.632Z" />
                        </svg>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500 font-semibold">Students</p>
                    </div>
                    
                    <h3 class="mt-4 text-xl font-semibold text-[#123a7d]">Lost Item Reporting</h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Create detailed lost posts in seconds, attach photos, and receive push notifications when matches appear.
                    </p>
                </div>
                
                <div class="px-6 md:px-8 pb-8 flex justify-center items-center h-52">
                    <img src="{{ asset('images/feature-report-item.png') }}" alt="App screenshot of lost item reporting" class="rounded-lg shadow-md w-full h-full object-cover">
                </div>
            </div>

            <div class="rounded-3xl bg-white shadow-lg overflow-hidden transition duration-300 transform hover:-translate-y-2 hover:shadow-xl">
                
                <div class="p-6 md:p-8 text-center">
                    <div class="flex items-center gap-2 justify-center">
                        <svg class="w-5 h-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                        </svg>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500 font-semibold">AI Service</p>
                    </div>
                    
                    <h3 class="mt-4 text-xl font-semibold text-[#123a7d]">Smart Recommendations</h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Similarity scoring surfaces relevant found items, notifies students, and alerts admins when multiple claims occur.
                    </p>
                </div>

                <div class="px-6 md:px-8 pb-8 flex justify-center items-center h-52">
                    <img src="{{ asset('images/feature-ai-matching.png') }}" alt="AI smart recommendations" class="rounded-lg shadow-md w-full h-full object-cover">
                </div>
            </div>

            <div class="rounded-3xl bg-white shadow-lg overflow-hidden transition duration-300 transform hover:-translate-y-2 hover:shadow-xl">

                <div class="p-6 md:p-8 text-center">
                    <div class="flex items-center gap-2 justify-center">
                        <svg class="w-5 h-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500 font-semibold">Admins</p>
                    </div>
                    
                    <h3 class="mt-4 text-xl font-semibold text-[#123a7d]">Accountable Decisions</h3>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                        Review claims, communicate decisions, set pickup deadlines, and archive completed cases.
                    </p>
                </div>
                
                <div class="px-6 md:px-8 pb-8 flex justify-center items-center h-52">
                    <img src="{{ asset('images/feature-admin-dashboard.png') }}" alt="Admin dashboard for claims" class="rounded-lg shadow-md w-full h-full object-cover">
                </div>
            </div>

        </div>
    </div>
</section>



            <section id="howitwork" class="bg-white">
                <div class="mx-auto max-w-6xl px-6 py-18 md:py-20">
                    <div class="mb-10 text-center">
                        <span class="inline-flex items-center gap-2 rounded-full bg-[#e4ecff] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[#123a7d]">
                            How it Works
                        </span>
                        <h2 class="mt-4 text-3xl font-semibold text-[#123a7d]">From reported lost item to verified pickup</h2>
                        <p class="mt-2 text-sm text-slate-600 md:text-base">Each stage shares status updates with the owner and keeps admins prepared for the handover.</p>
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

                    <div class="grid gap-6 md:grid-cols-5">
                        @foreach ($steps as $index => $step)
                            <div class="relative flex h-full flex-col items-center rounded-3xl border border-slate-200 bg-gradient-to-br from-white via-[#f8faff] to-white px-5 py-8 text-center shadow-sm transition-transform hover:-translate-y-1 hover:shadow-lg">
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
                                <span class="mt-4 inline-flex items-center gap-2 rounded-full border border-{{ $step['tone'] }}-200 bg-{{ $step['tone'] }}-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-{{ $step['tone'] }}-600">
                                    Step {{ $index + 1 }} • {{ $step['badge'] }}
                                </span>
                                <h3 class="mt-4 text-lg font-semibold text-[#123a7d]">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $step['copy'] }}</p>
                                @if($index < count($steps) - 1)
                                    <span class="absolute right-[-18px] top-1/2 hidden h-0.5 w-9 rounded-full bg-slate-200 md:block"></span>
                                @endif
                                @if($index < count($steps) - 1)
                                    <span class="mt-5 block h-0.5 w-16 rounded-full bg-slate-200 md:hidden"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    

                </div>
            </section>
        </main>
                
             <section class="footer-cta">
            <div class="container">
                <p>Lost items? We’ve got you covered</p>
                <h2>Download the App Now!</h2>
               
                <div class="app-badges">
                     <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center justify-center gap-3 rounded-lg bg-[#123a7d] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-blue-900/20 transition-transform hover:-translate-y-0.5 hover:bg-[#0f2f63]">
                            <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
                            Download Mobile App
                        </a>
                    
                </div>
            </div>
        </section>

             <footer id="support" class="border-t border-slate-200 bg-white">
            <div class="mx-auto grid max-w-6xl gap-8 px-6 py-12 md:grid-cols-[1.2fr_1fr]">
                <div class="space-y-4">
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-400">NavistFind • Carmen National High School</p>
                    <h4 class="text-xl font-semibold text-[#123a7d]">Need help with the lost &amp; found process?</h4>
                    <p class="text-sm text-slate-600">
                        Our campus support team can guide you through reporting items, monitoring claims, or handling pickups. Reach out anytime during school office hours.
                    </p>
                    <div class="inline-flex items-center gap-3 rounded-full border border-[#123a7d] bg-[#f4f7ff] px-5 py-2 text-sm font-semibold text-[#123a7d]">
                        <x-heroicon-o-clock class="h-5 w-5" />
                        Monday – Friday • 8:00 AM – 5:00 PM
                    </div>
                </div>
                <div class="space-y-3 text-sm text-slate-600">
                    <a href="mailto:{{ $supportEmail }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-[#f8faff] px-4 py-3 hover:border-[#123a7d] hover:text-[#123a7d] transition-colors">
                        <span class="rounded-full bg-[#123a7d]/10 p-2 text-[#123a7d]">
                            <x-heroicon-o-envelope class="h-5 w-5" />
                        </span>
                        {{ $supportEmail }}
                    </a>
                    <a href="tel:+631234567890" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:border-[#123a7d] hover:text-[#123a7d] transition-colors">
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
                        link.classList.add('text-[#123a7d]');
                        if (underline) {
                            underline.style.transform = 'scaleX(1)';
                        }
            } else {
                        link.classList.remove('text-[#123a7d]');
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