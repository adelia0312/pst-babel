<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Transkrip Tahunan Evaluasi Kinerja Petugas</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9.5pt;
    color: #1a1a1a;
    line-height: 1.5;
    margin: 26mm 22mm;
  }

  .page { width: 100%; }

  /* ══ KOP SURAT ══════════════════════════════════════════ */
  .kop { border-bottom: 2.5px solid #1a3a6b; padding-bottom: 10px; margin-bottom: 12px; }
  .kop-instansi { font-size: 14pt; font-weight: bold; color: #1a3a6b; text-transform: uppercase; letter-spacing: .5px; }
  .kop-sub { font-size: 9pt; color: #444; margin-top: 2px; }
  .kop-meta { font-size: 7.5pt; color: #666; margin-top: 4px; }

  /* ══ JUDUL ══════════════════════════════════════════════ */
  .judul-box { text-align: center; margin: 10px 0 14px 0; }
  .judul-box h2 { font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1a3a6b; }
  .judul-garis { border: none; border-bottom: 1.5px solid #1a3a6b; width: 55%; margin: 5px auto 0 auto; }
  .judul-periode { font-size: 10pt; color: #333; margin-top: 5px; }

  /* ══ SECTION TITLE ══════════════════════════════════════ */
  .section-title {
    background: #1a3a6b; color: #fff; font-weight: bold; font-size: 9.5pt;
    padding: 5px 10px; margin-top: 14px; margin-bottom: 7px;
  }

  /* ══ IDENTITAS ══════════════════════════════════════════ */
  .identitas table { width: 100%; border-collapse: collapse; }
  .identitas td { padding: 2px 6px; font-size: 9.5pt; vertical-align: top; }
  .id-label { width: 170px; font-weight: bold; white-space: nowrap; }
  .id-sep   { width: 14px; }

  /* ══ GRADE BOX ══════════════════════════════════════════ */
  .grade-box { width: 100%; border: 1.5px solid #1a3a6b; border-collapse: collapse; margin: 8px 0 10px 0; }
  .grade-box td { padding: 10px 16px; vertical-align: middle; }
  .grade-td-nilai { width: 55%; border-right: 1.5px solid #1a3a6b; }
  .grade-angka { font-size: 26pt; font-weight: bold; color: #1a3a6b; line-height: 1; }
  .grade-sub { font-size: 9pt; color: #444; margin-top: 3px; }
  .grade-td-badge { width: 45%; text-align: center; }
  .grade-badge {
    display: inline-block; font-size: 24pt; font-weight: bold; width: 60px; height: 60px;
    line-height: 60px; text-align: center; border-radius: 8px; color: #fff;
  }
  .grade-badge-lbl { font-size: 8.5pt; color: #555; margin-top: 5px; }
  .grade-SB { background: #166534; } .grade-B { background: #1e40af; }
  .grade-C  { background: #92400e; } .grade-K { background: #9a3412; }
  .grade-SK { background: #991b1b; } .grade-NA { background: #6b7280; }

  /* ══ TABEL REKAP PER TRIWULAN ═══════════════════════════ */
  table.nilai { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 2px; }
  table.nilai th {
    background: #2e5da1; color: #fff; font-weight: bold; padding: 6px 8px;
    border: 1px solid #b0c4de; font-size: 8.7pt; text-align: center;
  }
  table.nilai td { padding: 5px 8px; border: 1px solid #d0d9ea; vertical-align: middle; text-align: center; }
  table.nilai tr:nth-child(even) td { background: #f4f8ff; }
  .td-left { text-align: left !important; }

  .row-total td {
    background: #1a3a6b !important; color: #fff !important; font-weight: bold;
    font-size: 9.5pt; border-color: #1a3a6b !important; padding: 7px 8px;
  }

  .pill-grade {
    display: inline-block; padding: 1.5px 7px; border-radius: 3px; font-weight: bold;
    font-size: 8pt; color: #fff;
  }

  /* ══ CATATAN / KETERANGAN ═══════════════════════════════ */
  .keterangan-grade { margin-top: 8px; font-size: 7.5pt; color: #555; }
  .catatan-box {
    border: 1px solid #c8d4e8; padding: 8px 12px; background: #fafbff;
    font-size: 8.3pt; color: #444; margin-top: 6px;
  }

  /* ══ TANDA TANGAN ═══════════════════════════════════════ */
  .ttd-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
  .ttd-table td { width: 50%; text-align: center; vertical-align: bottom; padding: 0 24px; }
  .ttd-label { font-size: 9pt; color: #333; margin-bottom: 8px; line-height: 1.6; }
  .ttd-area { height: 62px; border-bottom: 1px solid #555; margin: 0 auto; width: 78%; }
  .ttd-name { font-weight: bold; font-size: 9.5pt; margin-top: 5px; }
  .ttd-jabatan { font-size: 8.5pt; color: #555; margin-top: 2px; }

  /* ══ FOOTER ══════════════════════════════════════════════ */
  .footer-bar { margin-top: 18px; border-top: 1.5px solid #1a3a6b; padding-top: 5px; }
  .footer-bar table { width: 100%; border-collapse: collapse; }
  .footer-bar td { font-size: 7.5pt; color: #777; }
  .footer-right { text-align: right; }
</style>
</head>
<body>
<div class="page">

  {{-- ══ KOP SURAT ══════════════════════════════════════════ --}}
  <div class="kop">
    <div class="kop-instansi">Badan Pusat Statistik</div>
    <div class="kop-sub">Pelayanan Statistik Terpadu (PST)</div>
    <div class="kop-sub">{{ optional($wilayah)->nama ?? 'BPS Pusat' }}</div>
    <div class="kop-meta">Dicetak: {{ $tanggalCetak }} &nbsp;|&nbsp; Oleh: {{ $cetakOleh }}</div>
  </div>

  {{-- ══ JUDUL ══════════════════════════════════════════════ --}}
  <div class="judul-box">
    <h2>Transkrip Tahunan Evaluasi Kinerja Petugas</h2>
    <hr class="judul-garis">
    <div class="judul-periode">Tahun {{ $tahun }} &nbsp;—&nbsp; Rekap Seluruh Triwulan</div>
  </div>

  {{-- ══ A. IDENTITAS PETUGAS ════════════════════════════════ --}}
  <div class="section-title">A. Identitas Petugas</div>
  <div class="identitas">
    <table>
      <tr>
        <td class="id-label">Nama Lengkap</td><td class="id-sep">:</td>
        <td>{{ optional($petugas->user)->name ?? '-' }}</td>
      </tr>
      <tr>
        <td class="id-label">Wilayah Tugas</td><td class="id-sep">:</td>
        <td>{{ optional($wilayah)->nama ?? '-' }}</td>
      </tr>
      <tr>
        <td class="id-label">Koordinator</td><td class="id-sep">:</td>
        <td>{{ optional($koordinator)->name ?? '-' }}</td>
      </tr>
      <tr>
        <td class="id-label">Triwulan Dievaluasi</td><td class="id-sep">:</td>
        <td>{{ $evaluasiTahun->count() }} dari 4 triwulan tahun {{ $tahun }}</td>
      </tr>
    </table>
  </div>

  {{-- ══ B. NILAI KOMPOSIT TAHUNAN ═══════════════════════════ --}}
  <div class="section-title">B. Nilai Komposit Tahunan</div>
  <table class="grade-box">
    <tr>
      <td class="grade-td-nilai">
        <div class="grade-angka">{{ $nilaiTahun !== null ? number_format($nilaiTahun, 2) : '-' }}</div>
        <div class="grade-sub">Rata-rata Nilai Akhir — Tahun {{ $tahun }}</div>
      </td>
      <td class="grade-td-badge">
        <div><span class="grade-badge grade-{{ $gradeTahun ?? 'NA' }}">{{ $gradeTahun ?? '-' }}</span></div>
        <div class="grade-badge-lbl">{{ \App\Models\EvaluasiPetugas::labelGrade($gradeTahun ?? '-') }}</div>
      </td>
    </tr>
  </table>

  {{-- ══ C. REKAPITULASI PER TRIWULAN ════════════════════════ --}}
  <div class="section-title">C. Rekapitulasi Nilai per Triwulan</div>
  <table class="nilai">
    <thead>
      <tr>
        <th style="width:14%">Periode</th>
        <th style="width:14%">I — Sikap Kerja</th>
        <th style="width:15%">II.A — Ind. Hasil</th>
        <th style="width:15%">II.B — Ind. Proses</th>
        <th style="width:14%">III — Mutu Pelayanan</th>
        <th style="width:14%">Nilai Akhir</th>
        <th style="width:14%">Grade</th>
      </tr>
    </thead>
    <tbody>
      @foreach($evaluasiTahun as $ev)
      @php
        $tw = \App\Helpers\PeriodeHelper::isoLabel($ev->periode);
      @endphp
      <tr>
        <td class="td-left">{{ $tw }}</td>
        <td>{{ $ev->rata_sikap_kerja      !== null ? number_format($ev->rata_sikap_kerja, 2)      : '—' }}</td>
        <td>{{ $ev->rata_indikator_hasil  !== null ? number_format($ev->rata_indikator_hasil, 2)  : '—' }}</td>
        <td>{{ $ev->rata_indikator_proses !== null ? number_format($ev->rata_indikator_proses, 2) : '—' }}</td>
        <td>{{ $ev->rata_mutu_pelayanan   !== null ? number_format($ev->rata_mutu_pelayanan, 2)   : '—' }}</td>
        <td><strong>{{ $ev->jumlah_nilai !== null ? number_format($ev->jumlah_nilai, 2) : '—' }}</strong></td>
        <td>
          <span class="pill-grade grade-{{ $ev->grade ?? 'NA' }}">{{ $ev->grade ?? '-' }}</span>
        </td>
      </tr>
      @endforeach
      <tr class="row-total">
        <td class="td-left">RATA-RATA TAHUN {{ $tahun }}</td>
        <td>{{ $avgSikap  !== null ? number_format($avgSikap, 2)  : '—' }}</td>
        <td>{{ $avgHasil  !== null ? number_format($avgHasil, 2)  : '—' }}</td>
        <td>{{ $avgProses !== null ? number_format($avgProses, 2) : '—' }}</td>
        <td>{{ $avgMutu   !== null ? number_format($avgMutu, 2)   : '—' }}</td>
        <td>{{ $nilaiTahun !== null ? number_format($nilaiTahun, 2) : '—' }}</td>
        <td>{{ $gradeTahun ?? '-' }}</td>
      </tr>
    </tbody>
  </table>

  <div class="keterangan-grade">
    <strong>Keterangan Grade:</strong>
    SB = Sangat Baik (&gt;95) &nbsp;|&nbsp;
    B = Baik (86–95) &nbsp;|&nbsp;
    C = Cukup (66–85) &nbsp;|&nbsp;
    K = Kurang (51–65) &nbsp;|&nbsp;
    SK = Sangat Kurang (&lt;50)
  </div>

  @if($evaluasiTahun->count() < 4)
  <div class="catatan-box">
    Catatan: transkrip ini hanya mencakup {{ $evaluasiTahun->count() }} dari 4 triwulan tahun {{ $tahun }}
    karena triwulan lainnya belum dievaluasi / belum diselesaikan oleh koordinator.
  </div>
  @endif

  {{-- ══ TANDA TANGAN ═══════════════════════════════════════ --}}
  <table class="ttd-table">
    <tr>
      <td>
        <div class="ttd-label">Mengetahui,<br>Koordinator {{ optional($wilayah)->nama ?? '' }}</div>
        <div class="ttd-area"></div>
        <div class="ttd-name">{{ optional($koordinator)->name ?? '______________________' }}</div>
        <div class="ttd-jabatan">Koordinator PST</div>
      </td>
      <td>
        <div class="ttd-label">Yang Bersangkutan,<br>Petugas PST</div>
        <div class="ttd-area"></div>
        <div class="ttd-name">{{ optional($petugas->user)->name ?? '______________________' }}</div>
        <div class="ttd-jabatan">Petugas PST</div>
      </td>
    </tr>
  </table>

  <div class="footer-bar">
    <table>
      <tr>
        <td>Sistem PST — Portal Penilaian Kinerja Petugas</td>
        <td class="footer-right">Transkrip ini dihasilkan otomatis oleh sistem</td>
      </tr>
    </table>
  </div>

</div>
</body>
</html>