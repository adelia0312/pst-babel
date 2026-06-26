<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Selesai</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'IBM Plex Sans',sans-serif;background:#f4f5f7;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 16px}
        .box{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:48px 32px;max-width:440px;width:100%;text-align:center}
        .icon{width:64px;height:64px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
        h1{font-size:20px;font-weight:600;color:#111827;margin-bottom:10px}
        p{font-size:14px;color:#6b7280;line-height:1.6}
        .note{margin-top:24px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px;font-size:13px;color:#374151}
        .footer{margin-top:24px;font-size:12px;color:#9ca3af}
    </style>
</head>
<body>
<div class="box">
    <div class="icon">
        <svg width="32" height="32" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
    </div>
    <h1>Survey Telah Diisi!</h1>
    <p>Terima kasih atas partisipasi Anda. Pendapat Anda membantu kami meningkatkan kualitas pelayanan.</p>
    <div class="note">
        @if($survey->nama_responden)
        Jawaban dari <strong>{{ $survey->nama_responden }}</strong> berhasil disimpan pada
        {{ $survey->diisi_pada?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}.
        @else
        Jawaban Anda berhasil disimpan. Link ini sudah tidak dapat digunakan kembali.
        @endif
    </div>
    <div class="footer">BPS · Sistem PST Babel</div>
</div>
</body>
</html>