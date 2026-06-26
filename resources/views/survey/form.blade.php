<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Kepuasan — BPS Babel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 24px 16px 60px;
        }

        .wrap { max-width: 480px; margin: 0 auto; }

        /* Header sederhana */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px; font-weight: 700; color: #111;
            letter-spacing: -.3px;
        }
        .header p {
            font-size: 13px; color: #6b7280; margin-top: 5px;
        }

        /* Card */
        .card {
            background: #fff; border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden; margin-bottom: 12px;
        }

        /* Info petugas */
        .petugas-bar {
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            background: #fafafa;
        }
        .petugas-lbl { font-size: 10.5px; color: #9ca3af; margin-bottom: 3px; }
        .petugas-nama { font-size: 15px; font-weight: 600; color: #111; }
        .petugas-wilayah { font-size: 12px; color: #6b7280; margin-top: 1px; }

        /* Form body */
        .form-body { padding: 20px 18px; }

        /* Divider */
        .sec { font-size: 10.5px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; color: #9ca3af; margin-bottom: 14px; }

        /* Fields */
        .fgrp { margin-bottom: 12px; }
        .flabel { display: block; font-size: 12.5px; font-weight: 500; color: #374151; margin-bottom: 5px; }
        .flabel-opt { font-weight: 400; color: #9ca3af; font-size: 11px; }
        .fc {
            width: 100%; border: 1px solid #d1d5db; border-radius: 8px;
            padding: 10px 12px; font-family: inherit; font-size: 13.5px;
            color: #111; outline: none; background: #fff;
            transition: border-color .15s;
        }
        .fc:focus { border-color: #2563eb; }
        .frow { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media(max-width:380px) { .frow { grid-template-columns: 1fr; } }

        /* Pertanyaan */
        .q-sep { height: 1px; background: #f3f4f6; margin: 18px 0; }
        .q-num { font-size: 11px; color: #9ca3af; margin-bottom: 4px; }
        .q-text { font-size: 14px; font-weight: 500; color: #111; line-height: 1.5; margin-bottom: 12px; }
        .q-req { color: #ef4444; }

        /* Bintang */
        .star-group { display: flex; gap: 4px; flex-direction: row-reverse; justify-content: flex-end; }
        .star-group input { display: none; }
        .star-group label {
            font-size: 34px; color: #d1d5db; cursor: pointer;
            transition: color .1s; line-height: 1;
        }
        .star-group input:checked ~ label,
        .star-group label:hover,
        .star-group label:hover ~ label { color: #f59e0b; }
        .star-hint { font-size: 11px; color: #9ca3af; margin-top: 6px; }

        /* Pilihan */
        .radio-group { display: flex; flex-direction: column; gap: 7px; }
        .radio-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 13px; border: 1px solid #e5e7eb;
            border-radius: 8px; cursor: pointer;
            transition: border-color .12s, background .12s;
        }
        .radio-item:hover { border-color: #93c5fd; background: #f0f9ff; }
        .radio-item:has(input:checked) { border-color: #2563eb; background: #eff6ff; }
        .radio-item input { accent-color: #2563eb; width: 15px; height: 15px; flex-shrink: 0; }
        .radio-item span { font-size: 13.5px; color: #374151; }

        /* Error */
        .err { color: #ef4444; font-size: 11px; margin-top: 5px; }

        /* Submit */
        .btn-submit {
            width: 100%; padding: 13px;
            background: #1d4ed8; color: #fff;
            border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: inherit;
            transition: background .15s;
            margin-top: 20px;
        }
        .btn-submit:hover { background: #1e40af; }

        .ft { text-align: center; font-size: 11.5px; color: #9ca3af; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <h1>Survey Kepuasan Pelanggan</h1>
        <p>Penilaian Anda membantu kami meningkatkan kualitas layanan.</p>
    </div>

    <div class="card">

        <div class="petugas-bar">
            <div class="petugas-lbl">Petugas yang melayani Anda</div>
            <div class="petugas-nama">{{ $survey->petugas?->user?->name ?? 'Petugas BPS' }}</div>
            <div class="petugas-wilayah">{{ $survey->wilayah?->nama ?? 'BPS Babel' }}</div>
        </div>

        <div class="form-body">
            <form method="POST" action="{{ route('survey.submit', $survey->token) }}">
                @csrf

                <div class="sec">Identitas Anda</div>
                <div class="frow">
                    <div class="fgrp">
                        <label class="flabel">Nama <span class="flabel-opt">(opsional)</span></label>
                        <input type="text" name="nama_responden" class="fc"
                               value="{{ old('nama_responden') }}" placeholder="Nama Anda">
                    </div>
                    <div class="fgrp">
                        <label class="flabel">Instansi <span class="flabel-opt">(opsional)</span></label>
                        <input type="text" name="instansi" class="fc"
                               value="{{ old('instansi') }}" placeholder="Instansi Anda">
                    </div>
                </div>

                <div class="q-sep"></div>
                <div class="sec">Penilaian Layanan</div>

                @foreach($pertanyaan as $i => $p)
                    @if($i > 0)<div class="q-sep"></div>@endif

                    <div class="q-num">Pertanyaan {{ $i + 1 }}</div>
                    <div class="q-text">
                        {{ $p->pertanyaan }}
                        @if($p->tipe !== 'teks')<span class="q-req">*</span>@endif
                    </div>

                    @if($p->tipe === 'rating')
                    <div class="star-group">
                        @for($v = 5; $v >= 1; $v--)
                        <input type="radio" id="s{{ $p->id }}-{{ $v }}"
                               name="jawaban[{{ $p->id }}]" value="{{ $v }}"
                               {{ old("jawaban.{$p->id}") == $v ? 'checked' : '' }} required>
                        <label for="s{{ $p->id }}-{{ $v }}">★</label>
                        @endfor
                    </div>
                    <div class="star-hint">1 = Sangat Buruk &nbsp;·&nbsp; 5 = Sangat Baik</div>

                    @elseif($p->tipe === 'pilihan')
                    <div class="radio-group">
                        @foreach($p->opsi_pilihan ?? [] as $opsi)
                        <label class="radio-item">
                            <input type="radio" name="jawaban[{{ $p->id }}]" value="{{ $opsi }}"
                                   {{ old("jawaban.{$p->id}") === $opsi ? 'checked' : '' }} required>
                            <span>{{ $opsi }}</span>
                        </label>
                        @endforeach
                    </div>

                    @else
                    <textarea name="jawaban[{{ $p->id }}]" class="fc" rows="3"
                              placeholder="Tulis komentar Anda...">{{ old("jawaban.{$p->id}") }}</textarea>
                    @endif

                    @if($errors->has("jawaban.{$p->id}"))
                    <div class="err">{{ $errors->first("jawaban.{$p->id}") }}</div>
                    @endif
                @endforeach

                <button type="submit" class="btn-submit">Kirim Penilaian</button>
            </form>
        </div>
    </div>

    <div class="ft">BPS Babel — Badan Pusat Statistik</div>
</div>
</body>
</html>