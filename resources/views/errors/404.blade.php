<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan · Koding Indonesia</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body style="background: #F5F5F0; font-family: 'Space Grotesk', sans-serif; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem;">

    <div class="text-center max-w-lg w-full">
        <a href="/" class="inline-flex items-center gap-2 mb-8 no-underline" style="color:#1A202C;">
            <img src="/logo.png" alt="Koding Indonesia" width="40" height="40" class="border-2 border-black" style="box-shadow: 2px 2px 0 #000;">
            <span class="font-bold text-base">Koding Indonesia</span>
        </a>

        <div class="text-[120px] font-black leading-none mb-4" style="color: #2979FF; text-shadow: 6px 6px 0 #000; letter-spacing:-0.05em;">404</div>
        <div class="inline-block text-white text-sm font-bold px-3 py-1.5 border-2 border-black mb-6" style="background: #FF7A2F; box-shadow: 3px 3px 0 #000; text-transform:uppercase; letter-spacing:.05em;">Halaman Tidak Ditemukan</div>
        <h1 class="text-2xl font-black mb-4">Ups! Halaman ini tidak ada</h1>
        <p class="mb-8 text-base leading-relaxed" style="color:#4A5568; font-family:'Inter',sans-serif;">
            Sepertinya kamu nyasar ke URL yang salah. Halaman yang kamu cari mungkin sudah dihapus, dipindah, atau belum pernah ada.
        </p>

        <form action="/cari" method="get" class="mb-8 text-left" style="font-family:'Inter',sans-serif;">
            <label for="error-search-q" class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:#2D3748;">Cari artikel</label>
            <div class="flex gap-2">
                <input
                    id="error-search-q"
                    type="search"
                    name="q"
                    placeholder="ESP32, IoT, Python..."
                    class="input-brutal flex-1 py-3 px-3 text-sm"
                    minlength="2"
                >
                <button type="submit" class="btn-brutal btn-primary px-5 py-3 text-sm shrink-0">Cari</button>
            </div>
        </form>

        <div class="mb-8" style="font-family:'Inter',sans-serif;">
            <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#2D3748;">Atau jelajahi kategori</p>
            <div class="flex flex-wrap justify-center gap-2">
                <a href="/kategori/esp32-arduino" class="btn-brutal btn-outline px-3 py-2 text-xs">ESP32 &amp; Arduino</a>
                <a href="/kategori/iot-smart-device" class="btn-brutal btn-outline px-3 py-2 text-xs">IoT</a>
                <a href="/kategori/programming" class="btn-brutal btn-outline px-3 py-2 text-xs">Programming</a>
                <a href="/kategori/web-development" class="btn-brutal btn-outline px-3 py-2 text-xs">Web Dev</a>
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="/" class="btn-brutal btn-primary px-8 py-3 text-sm">← Ke Beranda</a>
            <a href="/artikel" class="btn-brutal btn-outline px-8 py-3 text-sm">Lihat Artikel</a>
        </div>
        <div class="mt-10 font-mono text-xs" style="color:#718096;">
            Error code: 404 · <a href="/" style="color:#2979FF;">kodingindonesia.com</a>
        </div>
    </div>

</body>
</html>
