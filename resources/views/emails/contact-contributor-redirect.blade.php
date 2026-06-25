<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Aplikasi Kontributor</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 32px; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Menjadi Kontributor Koding Indonesia</h1>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $senderName }}</strong>,</p>
            <p>Terima kasih sudah menghubungi Koding Indonesia dan tertarik menjadi kontributor!</p>
            <p>Kami punya <strong>halaman &amp; formulir aplikasi resmi</strong> untuk program kontributor. Silakan baca pedoman lengkap dan ajukan diri di:</p>
            @include('emails.partials.btn', ['href' => $contributorUrl, 'label' => 'Formulir Aplikasi Kontributor →', 'bg' => '#FF7A2F'])
            <p style="margin-top: 20px; font-size: 14px; color: #718096;">
                Setelah mengajukan, tim kami meninjau dalam <strong>3–5 hari kerja</strong>. Pesan kontakmu tetap kami terima dan akan dibalas jika ada pertanyaan tambahan.
            </p>
        </div>
        <div class="footer">Koding Indonesia — kodingindonesia.com</div>
    </div>
</body>
</html>
