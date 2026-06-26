<?php

namespace App\Http\Controllers;

use App\Models\SurveySetting;
use App\Models\Wilayah;
use App\Models\WilayahSurveyToken;
use Illuminate\Support\Facades\Auth;

/**
 * PetugasSurveyController
 *
 * Letak file : app/Http/Controllers/PetugasSurveyController.php
 * Status     : FILE LAMA — tambah method cetakBarcode
 */
class PetugasSurveyController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        $wst     = $wilayahId ? WilayahSurveyToken::where('wilayah_id', $wilayahId)->first() : null;
        $wilayah = $user->wilayah ?? null;

        $linkSurvey = $wst
            ? route('survey.link', ['tokenLink' => $wst->token_link])
            : null;

        $templateRaw = SurveySetting::get('template_pesan');
        $template    = $linkSurvey
            ? str_replace('{link}', $linkSurvey, $templateRaw)
            : $templateRaw;

        return view('petugas.survey.index', compact('linkSurvey', 'wilayah', 'template'));
    }

    // ── BARU: Cetak / download barcode QR wilayah ──────────────────

    public function cetakBarcode()
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        abort_if(!$wilayahId, 403, 'Akun Anda belum terdaftar di wilayah manapun.');

        $wilayah = Wilayah::findOrFail($wilayahId);
        $wst     = WilayahSurveyToken::firstOrGenerate($wilayahId);

        $urlBarcode = route('survey.barcode', ['tokenBarcode' => $wst->token_barcode]);
        $linkOnline = route('survey.link',    ['tokenLink'    => $wst->token_link]);

        return view('petugas.survey.cetak_barcode', compact(
            'wilayah', 'wst', 'urlBarcode', 'linkOnline'
        ));
    }
}