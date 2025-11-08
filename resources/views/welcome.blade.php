<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NavistFind | Campus Lost & Found</title>
    @vite('resources/css/app.css')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>

@php
    $androidDownloadUrl = config('services.navistfind.android_url', '#');
    $supportEmail = config('services.navistfind.support_email', 'support@navistfind.edu');
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
                    <a href="#about" data-nav-target="about" class="nav-link group relative transition-colors hover:text-[#123a7d]">
                        About
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 bg-[#123a7d] transition-transform duration-200 group-hover:scale-x-100"></span>
                    </a>
                    <a href="#workflow" data-nav-target="workflow" class="nav-link group relative transition-colors hover:text-[#123a7d]">
                        Workflow
                        <span class="nav-underline absolute -bottom-1 left-0 h-0.5 w-full scale-x-0 bg-[#123a7d] transition-transform duration-200 group-hover:scale-x-100"></span>
                    </a>
                    <a href="#support" data-nav-target="support" class="nav-link group relative transition-colors hover:text-[#123a7d]">
                        Support
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
                        <a href="#about" class="block rounded-lg px-3 py-2 hover:bg-[#f1f5ff] hover:text-[#123a7d]">About</a>
                        <a href="#workflow" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#f1f5ff] hover:text-[#123a7d]">Workflow</a>
                        <a href="#support" class="mt-1 block rounded-lg px-3 py-2 hover:bg-[#f1f5ff] hover:text-[#123a7d]">Support</a>
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
                                Streamlined support for every campus lost &amp; found moment
            </h1>
                            <p class="text-base text-slate-600 md:text-lg">
                                NavistFind connects the student mobile app with the admin dashboard. Report, match, approve, and hand over items with clear status updates at each step.
            </p>
        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a href="{{ $androidDownloadUrl }}" class="inline-flex items-center justify-center gap-3 rounded-full bg-[#123a7d] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-blue-900/20 transition-transform hover:-translate-y-0.5 hover:bg-[#0f2f63]">
                                <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
                                Download Mobile App
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 rounded-full border border-[#123a7d] px-6 py-3 text-sm font-semibold text-[#123a7d] hover:bg-[#123a7d]/5 transition-transform hover:-translate-y-0.5">
                                <x-heroicon-o-arrow-right-circle class="h-5 w-5" />
                                Admin Sign In
                            </a>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-[#f7faff] px-5 py-3 text-xs text-slate-500 md:text-sm">
                            <strong class="text-[#123a7d]">Students:</strong> Post lost items, review AI-powered matches, and manage claims through the NavistFind mobile app.
                        </div>
</div>
            
                    <div class="relative">
                        <div class="absolute -top-8 -left-6 h-24 w-24 rounded-full bg-[#d8e6ff] blur-2xl"></div>
                        <div class="absolute -bottom-8 -right-8 h-28 w-28 rounded-full bg-[#ffe7cc] blur-2xl"></div>
                        <div class="relative rounded-3xl border border-slate-200 bg-gradient-to-br from-[#fefefe] via-[#f6f9ff] to-[#f0f3ff] p-10 shadow-xl shadow-slate-200/70">
                            <div class="space-y-12">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admin Console Snapshot</p>
                                    <h2 class="mt-2 text-xl font-semibold text-[#123a7d]">Monitor claims with clarity</h2>
                                    <p class="mt-3 text-sm text-slate-600">See match scores, claimant messages, and item photos side by side before making a decision.</p>
                                </div>
                                <div class="space-y-6">
                                    <div class="flex items-start gap-3">
                                        <div class="rounded-xl bg-[#e4ecff] p-3 text-[#123a7d]">
                                            <x-heroicon-o-magnifying-glass-circle class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#0f2f63]">AI-ranked queue</p>
                                            <p class="text-xs text-slate-500">Highlight top matches, flag multiple claims, and keep admins focused.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="rounded-xl bg-[#e8f6ef] p-3 text-[#1f7a55]">
                                            <x-heroicon-o-clipboard-document-check class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#1f4a4f]">Decision tracking</p>
                                            <p class="text-xs text-slate-500">Approve or reject with reasons saved in the claim history.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="rounded-xl bg-[#fff1e0] p-3 text-[#c27a1f]">
                                            <x-heroicon-o-shield-check class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#7f5013]">Secure handover</p>
                                            <p class="text-xs text-slate-500">Record ID verification and pickup details when students collect their items.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-xs text-amber-600">
                                    <strong>Reminder:</strong> Admins should sign out after each session, especially on shared devices.
                                </div>
                            </div>
                        </div>
                </div>
                </div>
            </section>

            <section id="about" class="bg-[#f5f7fb]">
                <div class="mx-auto grid max-w-6xl gap-8 px-6 py-16 md:grid-cols-3">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Students</p>
                        <h3 class="mt-3 text-lg font-semibold text-[#123a7d]">Lost item reporting</h3>
                        <p class="mt-3 text-sm text-slate-600">Create detailed lost posts in seconds, attach photos, and receive push notifications when matches appear.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">AI Service</p>
                        <h3 class="mt-3 text-lg font-semibold text-[#123a7d]">Smart recommendations</h3>
                        <p class="mt-3 text-sm text-slate-600">Similarity scoring surfaces relevant found items, notifies students, and alerts admins when multiple claims occur.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Admins</p>
                        <h3 class="mt-3 text-lg font-semibold text-[#123a7d]">Accountable decisions</h3>
                        <p class="mt-3 text-sm text-slate-600">Review claims, communicate decisions, set pickup deadlines, and archive completed cases.</p>
                    </div>
                </div>
            </section>

            <section id="workflow" class="bg-white">
                <div class="mx-auto max-w-6xl px-6 py-18 md:py-20">
                    <div class="mb-10 text-center">
                        <span class="inline-flex items-center gap-2 rounded-full bg-[#e4ecff] px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[#123a7d]">
                            Workflow
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