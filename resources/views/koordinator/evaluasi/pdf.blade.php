<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Laporan Evaluasi Kinerja Petugas</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9.5pt;
    color: #1a1a1a;
    line-height: 1.5;
    margin: 30mm;
  }

  .page {
    width: 100%;
  }

  /* ══ KOP SURAT ══════════════════════════════════════════ */
  .kop {
    border-bottom: 2.5px solid #1a3a6b;
    padding-bottom: 10px;
    margin-bottom: 12px;
  }
  .kop-instansi {
    font-size: 14pt;
    font-weight: bold;
    color: #1a3a6b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .kop-sub {
    font-size: 9pt;
    color: #444;
    margin-top: 2px;
  }
  .kop-meta {
    font-size: 7.5pt;
    color: #666;
    margin-top: 4px;
  }

  /* ══ JUDUL ══════════════════════════════════════════════ */
  .judul-box {
    text-align: center;
    margin: 10px 0 14px 0;
  }
  .judul-box h2 {
    font-size: 12pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #1a3a6b;
  }
  .judul-garis {
    border: none;
    border-bottom: 1.5px solid #1a3a6b;
    width: 55%;
    margin: 5px auto 0 auto;
  }
  .judul-periode {
    font-size: 10pt;
    color: #333;
    margin-top: 5px;
  }

  /* ══ SECTION TITLE ══════════════════════════════════════ */
  .section-title {
    background: #1a3a6b;
    color: #fff;
    font-weight: bold;
    font-size: 9.5pt;
    padding: 5px 10px;
    margin-top: 14px;
    margin-bottom: 7px;
  }

  /* ══ IDENTITAS ══════════════════════════════════════════ */
  .identitas table {
    width: 100%;
    border-collapse: collapse;
  }
  .identitas td {
    padding: 2px 6px;
    font-size: 9.5pt;
    vertical-align: top;
  }
  .id-label { width: 170px; font-weight: bold; white-space: nowrap; }
  .id-sep   { width: 14px; }

  /* ══ GRADE BOX ══════════════════════════════════════════ */
  .grade-box {
    width: 100%;
    border: 1.5px solid #1a3a6b;
    border-collapse: collapse;
    margin: 8px 0 10px 0;
  }
  .grade-box td { padding: 10px 16px; vertical-align: middle; }
  .grade-td-nilai {
    width: 55%;
    border-right: 1.5px solid #1a3a6b;
  }
  .grade-angka {
    font-size: 26pt;
    font-weight: bold;
    color: #1a3a6b;
    line-height: 1;
  }
  .grade-sub {
    font-size: 9pt;
    color: #444;
    margin-top: 3px;
  }
  .grade-nama {
    font-size: 10pt;
    font-weight: bold;
    color: #1a3a6b;
    margin-top: 2px;
  }
  .grade-td-badge {
    width: 45%;
    text-align: center;
  }
  .grade-badge {
    display: inline-block;
    font-size: 24pt;
    font-weight: bold;
    width: 60px;
    height: 60px;
    line-height: 60px;
    text-align: center;
    border-radius: 8px;
    color: #fff;
  }
  .grade-badge-lbl { font-size: 8.5pt; color: #555; margin-top: 5px; }
  .grade-SB { background: #166534; }
  .grade-B  { background: #1e40af; }
  .grade-C  { background: #92400e; }
  .grade-K  { background: #9a3412; }
  .grade-SK { background: #991b1b; }
  .grade-NA { background: #6b7280; }

  /* ══ TABEL NILAI ════════════════════════════════════════ */
  table.nilai {
    width: 100%;
    border-collapse: collapse;
    font-size: 9pt;
    margin-top: 2px;
  }
  table.nilai th {
    background: #2e5da1;
    color: #fff;
    font-weight: bold;
    padding: 5px 8px;
    border: 1px solid #b0c4de;
    font-size: 9pt;
  }
  table.nilai td {
    padding: 4px 8px;
    border: 1px solid #d0d9ea;
    vertical-align: middle;
  }
  table.nilai tr:nth-child(even) td { background: #f4f8ff; }

  /* Baris header komponen (I, IIA, IIB, III) */
  .group-header td {
    background: #dce8f7 !important;
    font-weight: bold;
    color: #1a3a6b;
    font-size: 9pt;
    border-top: 1.5px solid #2e5da1;
    padding: 5px 8px;
  }
  .group-rata {
    font-weight: normal;
    font-size: 8.5pt;
    color: #2e5da1;
  }

  /* Baris sub-indikator null */
  .row-null td {
    color: #b0b8c8;
    font-style: italic;
  }

  /* Baris total */
  .row-total td {
    background: #1a3a6b !important;
    color: #fff !important;
    font-weight: bold;
    font-size: 9.5pt;
    border-color: #1a3a6b !important;
    padding: 6px 8px;
  }

  .td-center { text-align: center; }
  .td-bold   { font-weight: bold; }
  .td-indent { padding-left: 20px !important; }

  /* ══ CATATAN ════════════════════════════════════════════ */
  .catatan-box {
    border: 1px solid #c8d4e8;
    padding: 9px 12px;
    min-height: 44px;
    background: #fafbff;
    font-size: 9pt;
    color: #444;
    margin-top: 2px;
  }

  /* ══ TANDA TANGAN ═══════════════════════════════════════ */
  .ttd-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
  }
  .ttd-table td {
    width: 50%;
    text-align: center;
    vertical-align: bottom;
    padding: 0 24px;
  }
  .ttd-label {
    font-size: 9pt;
    color: #333;
    margin-bottom: 8px;
    line-height: 1.6;
  }
  .ttd-area {
    height: 62px;
    border-bottom: 1px solid #555;
    margin: 0 auto;
    width: 78%;
  }
  .ttd-name {
    font-weight: bold;
    font-size: 9.5pt;
    margin-top: 5px;
  }
  .ttd-jabatan {
    font-size: 8.5pt;
    color: #555;
    margin-top: 2px;
  }

  /* ══ FOOTER ═════════════════════════════════════════════ */
  .footer-bar {
    margin-top: 18px;
    border-top: 1.5px solid #1a3a6b;
    padding-top: 5px;
  }
  .footer-bar table {
    width: 100%;
    border-collapse: collapse;
  }
  .footer-bar td {
    font-size: 7.5pt;
    color: #777;
  }
  .footer-right { text-align: right; }

  .no-data {
    color: #999;
    font-style: italic;
    text-align: center;
    padding: 14px;
    font-size: 9pt;
  }

  .keterangan-grade {
    margin-top: 6px;
    font-size: 7.5pt;
    color: #555;
  }
</style>
</head>
<body>
<div class="page">

  {{-- ══ KOP SURAT ══════════════════════════════════════════════════ --}}
  <div class="kop">
    <div class="kop-instansi">Badan Pusat Statistik</div>
    <div class="kop-sub">Pelayanan Statistik Terpadu (PST)</div>
    <div class="kop-sub">{{ optional($wilayah)->nama ?? 'BPS Pusat' }}</div>
    <div class="kop-meta">Dicetak: {{ $tanggalCetak }} &nbsp;|&nbsp; Oleh: {{ $cetakOleh }}</div>
  </div>

  {{-- ══ JUDUL ══════════════════════════════════════════════════════ --}}
  <div class="judul-box">
    <h2>Laporan Evaluasi Kinerja Petugas</h2>
    <hr class="judul-garis">
    <div class="judul-periode">Periode: {{ $periodeLabel }}</div>
  </div>

  {{-- ══ A. IDENTITAS PETUGAS ════════════════════════════════════════ --}}
  <div class="section-title">A. Identitas Petugas</div>
  <div class="identitas">
    <table>
      <tr>
        <td class="id-label">Nama Lengkap</td>
        <td class="id-sep">:</td>
        <td>{{ optional($petugas->user)->name ?? '-' }}</td>
      </tr>
      <tr>
        <td class="id-label">Wilayah Tugas</td>
        <td class="id-sep">:</td>
        <td>{{ optional($wilayah)->nama ?? '-' }}</td>
      </tr>
      <tr>
        <td class="id-label">Koordinator</td>
        <td class="id-sep">:</td>
        <td>{{ optional($koordinator)->name ?? '-' }}</td>
      </tr>
      <tr>
        <td class="id-label">Tanggal Evaluasi</td>
        <td class="id-sep">:</td>
        <td>
          {{ $evaluasi && $evaluasi->tanggal_evaluasi
              ? $evaluasi->tanggal_evaluasi->format('d/m/Y')
              : '-' }}
        </td>
      </tr>
    </table>
  </div>

  {{-- ══ B. REKAPITULASI NILAI ══════════════════════════════════════ --}}
  <div class="section-title">B. Rekapitulasi Nilai</div>

  @if ($evaluasi)

    {{-- Grade summary --}}
    <table class="grade-box">
      <tr>
        <td class="grade-td-nilai">
          <div class="grade-angka">
            {{ $evaluasi->jumlah_nilai !== null ? number_format($evaluasi->jumlah_nilai, 2) : '-' }}
          </div>
          <div class="grade-sub">Nilai Akhir Komposit</div>
          <div class="grade-nama">
            {{ \App\Models\EvaluasiPetugas::labelGrade($evaluasi->grade ?? '-') }}
          </div>
        </td>
        <td class="grade-td-badge">
          <div>
            <span class="grade-badge grade-{{ $evaluasi->grade ?? 'NA' }}">
              {{ $evaluasi->grade ?? '-' }}
            </span>
          </div>
          <div class="grade-badge-lbl">Grade Kinerja</div>
        </td>
      </tr>
    </table>

    @php
      $gradeLabel = fn($v) => $v !== null
        ? \App\Models\EvaluasiPetugas::labelGrade(\App\Models\EvaluasiPetugas::hitungGrade($v))
        : null;
    @endphp

    <table class="nilai">
      <thead>
        <tr>
          <th style="width:44%; text-align:left; padding-left:10px;">Komponen Penilaian</th>
          <th style="width:18%;">Nilai</th>
          <th style="width:38%; text-align:left;">Keterangan</th>
        </tr>
      </thead>
      <tbody>

        {{-- I. SIKAP KERJA --}}
        <tr class="group-header">
          <td colspan="3">
            I. Sikap Kerja
            <span class="group-rata">
              — Rata-rata:
              {{ $evaluasi->rata_sikap_kerja !== null
                  ? number_format($evaluasi->rata_sikap_kerja, 2) : '-' }}
            </span>
          </td>
        </tr>
        @foreach([
          ['Kehadiran',       $evaluasi->nilai_kehadiran],
          ['Disiplin Waktu',  $evaluasi->nilai_disiplin],
          ['Komunikasi',      $evaluasi->nilai_komunikasi],
          ['Kerjasama',       $evaluasi->nilai_kerjasama],
          ['Inovatif',        $evaluasi->nilai_inovatif],
        ] as [$lbl, $val])
        <tr class="{{ $val === null ? 'row-null' : '' }}">
          <td class="td-indent">{{ $lbl }}</td>
          <td class="td-center">{{ $val !== null ? number_format($val, 2) : '—' }}</td>
          <td>{{ $gradeLabel($val) ?? '—' }}</td>
        </tr>
        @endforeach

        {{-- II.A INDIKATOR HASIL --}}
        <tr class="group-header">
          <td colspan="3">
            II.A Kinerja Pelayanan — Indikator Hasil
            <span class="group-rata">
              — Rata-rata:
              {{ $evaluasi->rata_indikator_hasil !== null
                  ? number_format($evaluasi->rata_indikator_hasil, 2) : '-' }}
            </span>
          </td>
        </tr>
        @foreach([
          ['Kepastian Waktu', $evaluasi->nilai_kepastian_waktu],
          ['Akurasi Data',    $evaluasi->nilai_akurasi],
        ] as [$lbl, $val])
        <tr class="{{ $val === null ? 'row-null' : '' }}">
          <td class="td-indent">{{ $lbl }}</td>
          <td class="td-center">{{ $val !== null ? number_format($val, 2) : '—' }}</td>
          <td>{{ $gradeLabel($val) ?? '—' }}</td>
        </tr>
        @endforeach

        {{-- II.B INDIKATOR PROSES --}}
        <tr class="group-header">
          <td colspan="3">
            II.B Kinerja Pelayanan — Indikator Proses
            <span class="group-rata">
              — Rata-rata:
              {{ $evaluasi->rata_indikator_proses !== null
                  ? number_format($evaluasi->rata_indikator_proses, 2) : '-' }}
            </span>
          </td>
        </tr>
        @foreach([
          ['Tanggungjawab Pelayanan',         $evaluasi->nilai_tanggungjawab],
          ['Kesopanan & Keramahan',           $evaluasi->nilai_kesopanan_keramahan],
          ['Penampilan & Kesesuaian Atribut',  $evaluasi->nilai_kesesuaian_atribut],
        ] as [$lbl, $val])
        <tr class="{{ $val === null ? 'row-null' : '' }}">
          <td class="td-indent">{{ $lbl }}</td>
          <td class="td-center">{{ $val !== null ? number_format($val, 2) : '—' }}</td>
          <td>{{ $gradeLabel($val) ?? '—' }}</td>
        </tr>
        @endforeach

        {{-- III. MUTU PELAYANAN --}}
        <tr class="group-header">
          <td colspan="3">
            III. Mutu Pelayanan
            <span class="group-rata">
              — Rata-rata:
              {{ $evaluasi->rata_mutu_pelayanan !== null
                  ? number_format($evaluasi->rata_mutu_pelayanan, 2) : '-' }}
            </span>
          </td>
        </tr>
        @foreach([
          ['Kepatuhan SOP',      $evaluasi->nilai_kepatuhan_sop],
          ['Kepuasan Pelanggan', $evaluasi->nilai_kepuasan_pelanggan],
        ] as [$lbl, $val])
        <tr class="{{ $val === null ? 'row-null' : '' }}">
          <td class="td-indent">{{ $lbl }}</td>
          <td class="td-center">{{ $val !== null ? number_format($val, 2) : '—' }}</td>
          <td>{{ $gradeLabel($val) ?? '—' }}</td>
        </tr>
        @endforeach

        {{-- TOTAL --}}
        <tr class="row-total">
          <td class="td-bold">NILAI KOMPOSIT AKHIR</td>
          <td class="td-center td-bold">
            {{ $evaluasi->jumlah_nilai !== null ? number_format($evaluasi->jumlah_nilai, 2) : '-' }}
          </td>
          <td class="td-bold">
            {{ \App\Models\EvaluasiPetugas::labelGrade($evaluasi->grade ?? '-') }}
            ({{ $evaluasi->grade ?? '-' }})
          </td>
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

  @else
    <p class="no-data">Belum ada data evaluasi untuk periode ini.</p>
  @endif

  {{-- ══ C. CATATAN KOORDINATOR ════════════════════════════════════ --}}
  <div class="section-title">C. Catatan Koordinator</div>
  <div class="catatan-box">
    {{ $evaluasi && $evaluasi->catatan ? $evaluasi->catatan : '(tidak ada catatan)' }}
  </div>

  {{-- ══ TANDA TANGAN ═══════════════════════════════════════════════ --}}
  <table class="ttd-table">
    <tr>
      <td>
        <div class="ttd-label">
          Mengetahui,<br>
          Koordinator {{ optional($wilayah)->nama ?? '' }}
        </div>
        <div class="ttd-area"></div>
        <div class="ttd-name">{{ optional($koordinator)->name ?? '______________________' }}</div>
        <div class="ttd-jabatan">Koordinator PST</div>
      </td>
      <td>
        <div class="ttd-label">
          Yang Bersangkutan,<br>
          Petugas PST
        </div>
        <div class="ttd-area"></div>
        <div class="ttd-name">{{ optional($petugas->user)->name ?? '______________________' }}</div>
        <div class="ttd-jabatan">Petugas PST</div>
      </td>
    </tr>
  </table>


</div>
</body>
</html>