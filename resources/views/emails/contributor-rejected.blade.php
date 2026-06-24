<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Aplikasi</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #2D3748; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .body { padding: 32px; }
        .box { background: #f5f5f0; border: 2px solid #000; padding: 16px; margin: 16px 0; }
        .btn { display: inline-block; margin-top: 8px; padding: 12px 20px; background: #2979FF; color: #fff; text-decoration: none; font-weight: bold; border: 2px solid #000; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:20px;">Update Aplikasi Kontributor</h1>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $applicantName }}</strong>,</p>
            <p>Terima kasih atas minatmu untuk berkontribusi di Koding Indonesia. Setelah meninjau aplikasimu, kami belum dapat menyetujuinya saat ini.</p>
            @if($rejectionReason)
            <div class="box">{{ $rejectionReason }}</div>
            @endif
            <p>Kamu <strong>boleh mengajukan ulang</strong> kapan saja setelah memperbaiki profil, portofolio, atau contoh tulisan.</p>
            <a href="{{ $reapplyUrl }}" class="btn">Ajukan Ulang →</a>
            <p>Salam,<br><strong>Tim Koding Indonesia</strong></p>
        </div>
        <div class="footer">© {{ date('Y') }} Koding Indonesia</div>
    </div>
</body>
</html>
