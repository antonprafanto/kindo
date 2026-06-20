<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error · Koding Indonesia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body style="background: #F5F5F0; font-family: 'Space Grotesk', sans-serif; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem;">

    <div class="text-center max-w-lg">
        <div class="text-[120px] font-black leading-none mb-4" style="color: #FF7A2F; text-shadow: 6px 6px 0 #000; letter-spacing:-0.05em;">500</div>
        <div class="inline-block text-white text-sm font-bold px-3 py-1.5 border-2 border-black mb-6" style="background: #000; box-shadow: 3px 3px 0 #FF7A2F; text-transform:uppercase; letter-spacing:.05em;">Internal Server Error</div>
        <h1 class="text-2xl font-black mb-4">Server sedang bermasalah</h1>
        <p class="mb-8 text-base leading-relaxed" style="color:#4A5568; font-family:'Inter',sans-serif;">
            Maaf, server kami sedang mengalami masalah. Tim kami sudah diberitahu dan sedang memperbaikinya. Coba kembali beberapa menit lagi.
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="/" class="btn-brutal btn-primary px-8 py-3 text-sm">← Ke Beranda</a>
            <button onclick="location.reload()" class="btn-brutal btn-outline px-8 py-3 text-sm">Coba Lagi</button>
        </div>
        <div class="mt-10 font-mono text-xs" style="color:#718096;">
            Error code: 500 · <a href="/" style="color:#2979FF;">kodingindonesia.com</a>
        </div>
    </div>

</body>
</html>
