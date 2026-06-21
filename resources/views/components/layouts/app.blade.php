<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Koding Indonesia — Tutorial ESP32, IoT & Pemrograman' }}</title>
    <meta name="description" content="{{ $description ?? 'Platform edukasi pemrograman berbahasa Indonesia. Tutorial ESP32, Arduino, IoT, dan pemrograman umum yang mudah dipahami.' }}">

    {{-- OG / Social --}}
    <meta property="og:title" content="{{ $title ?? 'Koding Indonesia' }}">
    <meta property="og:description" content="{{ $description ?? 'Platform edukasi pemrograman berbahasa Indonesia.' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('og-default.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="Koding Indonesia">
    <meta property="og:locale" content="id_ID">

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Koding Indonesia' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Platform edukasi pemrograman berbahasa Indonesia.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('og-default.png') }}">
    <meta name="twitter:site" content="@kodingindonesia">

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#2979FF">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    {{-- Fonts (self-hosted, no external requests) --}}
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/space-grotesk-latin-400-normal.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/space-grotesk-latin-700-normal.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/inter-latin-400-normal.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/jetbrains-mono-latin-400-normal.woff2') }}" crossorigin>

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- JSON-LD --}}
    @stack('schema')
</head>
<body class="antialiased" style="background-color: var(--color-surface); color: var(--color-dark); font-family: var(--font-sans);">

    <x-navbar />

    <main>{{ $slot }}</main>

    <x-footer />

    {{-- Back to Top --}}
    <button
        id="back-to-top"
        onclick="window.scrollTo({top:0,behavior:'smooth'})"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-black text-white border-2 border-black font-bold hidden transition-all"
        style="box-shadow: 3px 3px 0 #2979FF"
        title="Kembali ke atas"
    >↑</button>

    @livewireScripts

    {{-- Highlight.js --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/cpp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/arduino.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/python.min.js"></script>

    <script>
        hljs.highlightAll();

        // Back to Top visibility
        const btn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            btn.classList.toggle('hidden', window.scrollY < 400);
        });

        // Copy button for code blocks
        document.querySelectorAll('pre').forEach(pre => {
            const wrap = document.createElement('div');
            wrap.style.position = 'relative';
            pre.parentNode.insertBefore(wrap, pre);
            wrap.appendChild(pre);

            const btn = document.createElement('button');
            btn.textContent = 'Salin';
            btn.className = 'copy-code-btn';
            btn.style.cssText = 'position:absolute;top:8px;right:8px;padding:3px 10px;font-size:12px;font-weight:700;font-family:inherit;background:#2979FF;color:#fff;border:2px solid #000;cursor:pointer;';
            btn.addEventListener('click', () => {
                navigator.clipboard.writeText(pre.querySelector('code')?.textContent || pre.textContent);
                btn.textContent = 'Tersalin!';
                setTimeout(() => btn.textContent = 'Salin', 2000);
            });
            wrap.appendChild(btn);
        });
    </script>

    @stack('scripts')
</body>
</html>
