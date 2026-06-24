<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Kontributor Baru</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 32px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #718096; }
        .value { font-size: 15px; margin-top: 4px; }
        .box { background: #f5f5f0; border: 2px solid #000; padding: 16px; white-space: pre-wrap; }
        .btn { display: inline-block; margin-top: 16px; padding: 12px 20px; background: #FF7A2F; color: #fff; text-decoration: none; font-weight: bold; border: 2px solid #000; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Aplikasi Kontributor Baru</h1>
        </div>
        <div class="body">
            <div class="field">
                <div class="label">Nama</div>
                <div class="value"><strong>{{ $application->name }}</strong></div>
            </div>
            <div class="field">
                <div class="label">Email</div>
                <div class="value"><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></div>
            </div>
            <div class="field">
                <div class="label">Bidang Keahlian</div>
                <div class="value">{{ $application->topic_expertise }}</div>
            </div>
            @if($application->sample_url)
            <div class="field">
                <div class="label">Portofolio / Contoh Tulisan</div>
                <div class="value"><a href="{{ $application->sample_url }}">{{ $application->sample_url }}</a></div>
            </div>
            @endif
            <div class="field">
                <div class="label">Motivasi</div>
                <div class="box">{{ $application->motivation }}</div>
            </div>
            <a href="{{ $adminUrl }}" class="btn">Buka Panel Admin →</a>
        </div>
        <div class="footer">Koding Indonesia — Sistem Aplikasi Kontributor</div>
    </div>
</body>
</html>
