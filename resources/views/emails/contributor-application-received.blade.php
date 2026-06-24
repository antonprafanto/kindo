<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Diterima</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .body { padding: 32px; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:20px;">✓ Aplikasi Kontributor Diterima</h1>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $applicantName }}</strong>,</p>
            <p>Terima kasih sudah mengajukan diri sebagai kontributor Koding Indonesia. Tim kami akan meninjau aplikasimu dalam <strong>3–5 hari kerja</strong>.</p>
            <p>Kami akan menghubungimu via email ini begitu ada keputusan. Sambil menunggu, kamu bisa membaca pedoman lengkap di <a href="https://kodingindonesia.com/menjadi-kontributor">kodingindonesia.com/menjadi-kontributor</a>.</p>
            <p>Salam,<br><strong>Tim Koding Indonesia</strong></p>
        </div>
        <div class="footer">© {{ date('Y') }} Koding Indonesia</div>
    </div>
</body>
</html>
