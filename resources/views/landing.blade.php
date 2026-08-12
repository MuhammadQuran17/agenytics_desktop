@extends('layouts.main')

@section('content')

    <!-- Navbar -->
    <nav id="navbar" class="flex items-center justify-between fixed z-50 top-0 w-full px-6 md:px-16 lg:px-24 xl:px-32 py-4 border-b border-slate-200 bg-white/40 backdrop-blur">
        <a href="https://prebuiltui.com?utm_source=saasly">
            <img class="h-9 md:h-9.5 w-auto shrink-0" src="/assets/logo.svg" alt="Logo" width="140" height="40">
        </a>

        <div class="hidden items-center md:gap-8 lg:gap-12 md:flex lg:pl-20">
            <a href="/" class="hover:text-indigo-600">Home</a>
            <a href="/features" class="hover:text-indigo-600">Features</a>
            <a href="/pricing" class="hover:text-indigo-600">Pricing</a>
            <a href="/docs" class="hover:text-indigo-600">Docs</a>
        </div>

        <div id="mobileMenu" class="fixed inset-0 flex flex-col items-center justify-center gap-6 text-lg font-medium bg-white/40 backdrop-blur-md md:hidden transition duration-300 -translate-x-full">
            <a href="/">Home</a>
            <a href="/features">Features</a>
            <a href="/pricing">Pricing</a>
            <a href="/docs">Docs</a>
            <button>Sign in</button>
            <button id="closeMenu" class="aspect-square size-10 p-1 items-center justify-center bg-indigo-600 hover:bg-indigo-700 transition text-white rounded-md flex">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div class="flex items-center gap-4 md:gap-6">
            <a href="{{ route('chat.index') }}"  class="hidden md:block hover:bg-slate-100 transition px-4 py-2 border border-indigo-600 rounded-md">
                Sign in
            </a>
            <a href="{{ route('chat.index') }}"  class="hidden md:block px-4 py-2 bg-indigo-600 hover:bg-indigo-700 transition text-white rounded-md">
                Get started
            </a>
            <button id="openMenu" class="md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu"><path d="M4 12h16"/><path d="M4 18h16"/><path d="M4 6h16"/></svg>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
     <div class="flex flex-col items-center justify-center text-center bg-[url('/assets/hero-section-dot-image.png')] bg-cover bg-no-repeat">
        <a href="#" class="flex items-center gap-2 rounded-full p-1 pr-3 mt-44 text-indigo-600 bg-indigo-50">
            <span class="bg-indigo-600 text-white text-xs px-3.5 py-1 rounded-full">
                NEW
            </span>
            <p class="flex items-center gap-1">
                <span>Try 30 days free trial option </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
            </p>
        </a>
        <h1 class="text-[40px]/12 md:text-[54px]/16 font-semibold max-w-3xl mt-4">
            Build, launch and scale 
            <span class="bg-gradient-to-r from-indigo-600 to-pink-500 bg-clip-text text-transparent">
                your SaaS
            </span> faster
        </h1>
        <p class="text-base text-slate-600 max-w-lg mt-5">Workflows that never miss. automation that helps your team do more, effortlessly.</p>
        <div class="flex items-center gap-4 mt-6">
            <a href="{{ route('chat.index') }}" class="bg-indigo-600 hover:bg-indigo-700 transition px-8 py-3 rounded-md text-white">
                <span>Get Started</span>
            </a>
            <button class="flex items-center justify-center gap-2 border border-indigo-400 px-5 py-3 rounded-md text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles-icon lucide-sparkles"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"/><path d="M20 2v4"/><path d="M22 4h-4"/><circle cx="4" cy="20" r="2"/></svg>
                <span>AI Features</span>
            </button>
        </div>
        <img class="w-full max-w-xl mt-16 drop-shadow-2xl drop-shadow-blue-500/15 mx-auto" src="/assets/hero-section-card-image.svg" alt="Hero Section Card Image" width={1000} height={500} />
    </div>

    <!-- Trusted By Section -->

    <h3 class="text-base text-center text-slate-400 mt-28 pb-14 font-medium">
        Trusting by leading brands, including —
    </h3>
    <div class="overflow-hidden w-full relative max-w-5xl mx-auto select-none">
        <div
            class="absolute left-0 top-0 h-full w-20 z-10 pointer-events-none bg-gradient-to-r from-white to-transparent">
        </div>

        <div class="marquee-inner will-change-transform max-w-5xl mx-auto">
            <div id="logo-track" class="flex"></div>
        </div>

        <div
            class="absolute right-0 top-0 h-full w-20 md:w-40 z-10 pointer-events-none bg-gradient-to-l from-white to-transparent">
        </div>
    </div>

    <!-- Features Section -->

    <p class="text-center uppercase font-medium text-indigo-600 mt-28">Features</p>
    <h3 class="text-3xl font-semibold text-center mx-auto mt-2">Features Overview</h3>
    <p class="text-sm text-slate-500 text-center mt-4 max-w-lg mx-auto">A visual collection of our most recent works - each piece crafted with intention, emotion and style.</p>

    <div class="flex flex-wrap items-center justify-center gap-10 mt-16">
        <div class="max-w-80 hover:-translate-y-0.5 transition duration-300">
            <img class="rounded-xl" src="https://raw.githubusercontent.com/prebuiltui/prebuiltui/main/assets/features/image-1.png" alt="Card Image" height={400} width={400} />
            <h3 class="text-base font-semibold text-slate-700 mt-4">Feedback analyser</h3>
            <p class="text-sm text-slate-600 mt-1">Get instant insights into your finances with live dashboards.</p>
        </div>
        <div class="max-w-80 hover:-translate-y-0.5 transition duration-300">
            <img class="rounded-xl" src="https://raw.githubusercontent.com/prebuiltui/prebuiltui/main/assets/features/image-2.png" alt="Card Image" height={400} width={400} />
            <h3 class="text-base font-semibold text-slate-700 mt-4">User management</h3>
            <p class="text-sm text-slate-600 mt-1">Get instant insights into your finances with live dashboards.</p>
        </div>
        <div class="max-w-80 hover:-translate-y-0.5 transition duration-300">
            <img class="rounded-xl" src="https://raw.githubusercontent.com/prebuiltui/prebuiltui/main/assets/features/image-3.png" alt="Card Image" height={400} width={400} />
            <h3 class="text-base font-semibold text-slate-700 mt-4">Better invoicing</h3>
            <p class="text-sm text-slate-600 mt-1">Get instant insights into your finances with live dashboards.</p>
        </div>
    </div>

    <!-- Testimonials Section -->

    <p class="text-center uppercase font-medium text-indigo-600 mt-28">Testimonials</p>
    <h3 class="text-3xl font-semibold text-center mx-auto mt-2">Our Social Proof</h3>
    <p class="text-sm text-slate-500 text-center mt-4 max-w-lg mx-auto">A visual collection of our most recent works - each piece crafted with intention, emotion and style.</p>

    <div class="marquee-row w-full mx-auto max-w-5xl overflow-hidden relative">
        <div class="absolute left-0 top-0 h-full w-20 z-10 pointer-events-none bg-gradient-to-r from-white to-transparent"></div>
        <div class="marquee-inner-testimonials flex transform-gpu min-w-[200%] pt-10 pb-5" id="row1"></div>
        <div class="absolute right-0 top-0 h-full w-20 md:w-40 z-10 pointer-events-none bg-gradient-to-l from-white to-transparent"></div>
    </div>

    <div class="marquee-row w-full mx-auto max-w-5xl overflow-hidden relative">
        <div class="absolute left-0 top-0 h-full w-20 z-10 pointer-events-none bg-gradient-to-r from-white to-transparent"></div>
        <div class="marquee-inner-testimonials marquee-reverse flex transform-gpu min-w-[200%] pt-5 pb-10" id="row2"></div>
        <div class="absolute right-0 top-0 h-full w-20 md:w-40 z-10 pointer-events-none bg-gradient-to-l from-white to-transparent"></div>
    </div>

    <!-- Pricing Section -->
    <p class="text-center uppercase font-medium text-indigo-600 mt-28">PRICING</p>
    <h3 class="text-3xl font-semibold text-center mx-auto mt-2">Our Pricing Plans</h3>
    <p class="text-sm text-slate-500 text-center mt-4 max-w-lg mx-auto">Flexible pricing options designed to meet your needs — whether you're just getting started or scaling up.</p>

    <!-- Pricing Cards -->
    <div id="pricing" class="flex flex-wrap items-center justify-center gap-6 mt-16"></div>


    <!-- Faq Section -->
    <p class="text-center uppercase font-medium text-indigo-600 mt-28">FAQ's</p>
    <h3 class="text-3xl font-semibold text-center mx-auto mt-2">Frequently Asked Questions</h3>
    <p class="text-sm text-slate-500 text-center mt-4 max-w-lg mx-auto">A visual collection of our most recent works - each piece crafted with intention, emotion and style.</p>

    <div id="faq-container" class="mt-8 w-full max-w-xl mx-auto px-3"></div>

    <div class="border-y border-dashed border-slate-200 w-full max-w-5xl mx-auto mt-28 px-16">
        <div class="flex flex-col md:flex-row text-center md:text-left items-center justify-between gap-8 px-3 md:px-10 border-x border-dashed border-slate-200 py-20 -mt-10 -mb-10 w-full">
            <p class="text-xl font-medium max-w-sm">Join hundreds of developers building better SaaS products.</p>
            <button class="flex items-center gap-2 rounded-md py-3 px-5 bg-indigo-600 hover:bg-indigo-700 transition text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-github-icon lucide-github"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                <span>Star on Github</span>
            </button>
        </div>
    </div>

    <footer class="relative px-6 md:px-16 lg:px-24 xl:px-32 mt-40 w-full text-slate-500">
        <div class="flex flex-col md:flex-row justify-between w-full gap-10 border-b border-gray-200 pb-6">
            <div class="md:max-w-114">
                <img id="logo-footer" class="h-9 md:h-9.5 w-auto shrink-0" src="/assets/logo.svg" alt="Logo"
                    width={140} height={40} priority fetchPriority="high" />
                <p class="mt-6">
                    Launch your SaaS product in record time with our all-in-one platform designed for speed, flexibility
                    and growth. Whether you`&apos;re a solo founder or a fast-moving team, we provide everything you
                    need.
                </p>
            </div>
            <div class="flex-1 flex items-start md:justify-end gap-20">
                <div>
                    <h2 class="font-semibold mb-5">Company</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="/" class="hover:text-indigo-600 transition">Home</a>
                        </li>
                        <li>
                            <a href="/features" class="hover:text-indigo-600 transition">Features</a>
                        </li>
                        <li>
                            <a href="/pricing" class="hover:text-indigo-600 transition">Pricing</a>
                        </li>
                        <li>
                            <a href="/docs" class="hover:text-indigo-600 transition">Docs</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="font-semibold mb-5">Get in touch</h2>
                    <div class="space-y-2">
                        <p>+1-212-456-7890</p>
                        <p>contact@example.com</p>
                    </div>
                </div>
            </div>
        </div>
        <p class="pt-4 text-center pb-5">
            Copyright 2024 © <a href="https://prebuiltui.com?utm_source=saasly">PrebuiltUI</a>. All Right Reserved.
        </p>
    </footer>

    <script src="/script.js"></script>
@endsection
