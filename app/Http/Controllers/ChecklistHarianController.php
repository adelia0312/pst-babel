<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ChecklistHarian;
use App\Models\ChecklistTemplate;
use App\Models\JadwalPetugas;
use App\Models\User;
use App\Models\Wilayah;
use Carbon\Carbon;

class ChecklistHarianController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  ADMIN — Monitor SEMUA wilayah + kelola template global
    // ══════════════════════════════════════════════════════════════

    public function adminIndex(Request $request)
    {
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : Carbon::today();
        $sesi    = $request->sesi ?? 'semua';
        $wilayahFilter = $request->wilayah_id;

        // Query checklist dengan eager load petugas & wilayah
        $query = ChecklistHarian::with(['user.petugas.wilayah', 'user'])
            ->whereDate('tanggal', $tanggal);

        if ($sesi !== 'semua') {
            $query->where('sesi', $sesi);
        }

        if ($wilayahFilter) {
            $query->whereHas('user.petugas', fn($q) => $q->where('wilayah_id', $wilayahFilter));
        }

        $checklists = $query->orderByDesc('updated_at')->get();

        // Stats: ikut filter wilayah jika dipilih
        // Basis "Total Petugas": petugas yang TERJADWAL pada tanggal (dan wilayah) ini,
        // bukan seluruh petugas aktif (yang belum tentu bertugas hari itu).
        $jadwalQuery = JadwalPetugas::whereDate('tanggal', $tanggal);
        if ($wilayahFilter) {
            $jadwalQuery->where('wilayah_id', $wilayahFilter);
        }
        $totalPetugas = $jadwalQuery->distinct('user_id')->count('user_id');

        $statsQuery = ChecklistHarian::whereDate('tanggal', $tanggal);
        if ($wilayahFilter) {
            $statsQuery->whereHas('user.petugas', fn($q) => $q->where('wilayah_id', $wilayahFilter));
        }
        $totalSubmit   = (clone $statsQuery)->whereIn('status', ['submit','verified'])->count();
        $totalVerified = (clone $statsQuery)->where('status', 'verified')->count();
        $totalDraft    = (clone $statsQuery)->where('status', 'draft')->count();

        $wilayahList   = Wilayah::orderBy('nama')->get();

        return view('admin.checklist.index', compact(
            'checklists', 'tanggal', 'sesi',
            'totalPetugas', 'totalSubmit', 'totalVerified', 'totalDraft',
            'wilayahList', 'wilayahFilter'
        ));
    }

    public function adminDetail($id)
    {
        $checklist = ChecklistHarian::with(['user.wilayah', 'verifier'])->findOrFail($id);
        $wilayahId = $checklist->user->wilayah_id ?? null;

        // Template AKTIF saat ini (belum dihapus/nonaktif)
        $templates = $wilayahId
            ? ChecklistTemplate::forWilayah($wilayahId)->get()
            : ChecklistTemplate::global()->get();

        // ── Label map historis ─────────────────────────────────────────
        // withTrashed() → ambil juga yang sudah soft-deleted supaya label
        // checklist lama tetap tampil meski template sudah dihapus admin.
        $itemsJson = $checklist->items ?? [];
        $labelsMap = ChecklistTemplate::withTrashed()
            ->whereIn('id', array_keys($itemsJson))
            ->pluck('label', 'id');   // [ id => label ]

        return view('admin.checklist.detail', compact(
            'checklist', 'templates', 'itemsJson', 'labelsMap'
        ));
    }

    public function adminVerify($id)
    {
        $checklist = ChecklistHarian::findOrFail($id);
        if ($checklist->status !== 'submit') {
            return back()->with('error', 'Checklist belum disubmit oleh petugas.');
        }
        $checklist->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);
        return back()->with('success', 'Checklist berhasil diverifikasi.');
    }

    // ── Admin: Kelola Template (semua wilayah) ────────────────────

    public function adminTemplateIndex(Request $request)
    {
        $wilayahId = $request->wilayah_id;
        $wilayahList = Wilayah::orderBy('nama')->get();

        $templates = ChecklistTemplate::when($wilayahId,
            fn($q) => $q->where('wilayah_id', $wilayahId),
            fn($q) => $q->whereNull('wilayah_id')
        )->orderBy('urutan')->get();

        $wilayahDipilih = $wilayahId ? Wilayah::find($wilayahId) : null;

        return view('admin.checklist.template', compact(
            'templates', 'wilayahList', 'wilayahId', 'wilayahDipilih'
        ));
    }

    public function adminTemplateStore(Request $request)
    {
        $request->validate([
            'label'      => 'required|string|max:255',
            'link'       => 'nullable|url|max:255',
            'wilayah_id' => 'nullable|exists:wilayah,id',
        ]);

        $max = ChecklistTemplate::where('wilayah_id', $request->wilayah_id ?: null)->max('urutan') ?? 0;

        ChecklistTemplate::create([
            'wilayah_id' => $request->wilayah_id ?: null,
            'label'      => $request->label,
            'link'       => $request->link,
            'urutan'     => $max + 1,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Item checklist berhasil ditambahkan.');
    }

    public function adminTemplateUpdate(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'link'  => 'nullable|url|max:255',
        ]);

        $tpl = ChecklistTemplate::findOrFail($id);
        $tpl->update([
            'label'     => $request->label,
            'link'      => $request->link,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Item checklist diperbarui.');
    }

    public function adminTemplateDestroy($id)
    {
        // SoftDeletes: hanya mengisi deleted_at, row tidak benar-benar dihapus.
        // Label tetap tersimpan untuk keperluan tampilan historis checklist.
        ChecklistTemplate::findOrFail($id)->delete();
        return back()->with('success', 'Item checklist dihapus.');
    }

    public function adminTemplateReorder(Request $request)
    {
        foreach ($request->order as $idx => $id) {
            ChecklistTemplate::where('id', $id)->update(['urutan' => $idx + 1]);
        }
        return response()->json(['ok' => true]);
    }

    // ══════════════════════════════════════════════════════════════
    //  KOORDINATOR — Monitor wilayah sendiri + kelola template wilayah
    // ══════════════════════════════════════════════════════════════

    public function koordinatorIndex(Request $request)
    {
        $koordinator = Auth::user();
        $wilayahId   = $koordinator->wilayah_id;

        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : Carbon::today();
        $sesi    = $request->sesi ?? 'semua';

        $query = ChecklistHarian::with('user')
            ->whereDate('tanggal', $tanggal)
            ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId));

        if ($sesi !== 'semua') {
            $query->where('sesi', $sesi);
        }

        $checklists = $query->orderByDesc('updated_at')->get();

        $totalPetugas  = JadwalPetugas::where('wilayah_id', $wilayahId)
                             ->whereDate('tanggal', $tanggal)->distinct('user_id')->count('user_id');
        $totalSubmit   = ChecklistHarian::whereDate('tanggal', $tanggal)
                             ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
                             ->whereIn('status', ['submit','verified'])->count();
        $totalVerified = ChecklistHarian::whereDate('tanggal', $tanggal)
                             ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
                             ->where('status', 'verified')->count();

        return view('koordinator.checklist.index', compact(
            'checklists', 'tanggal', 'sesi',
            'totalPetugas', 'totalSubmit', 'totalVerified'
        ));
    }

    public function koordinatorPolling(Request $request)
    {
        $wilayahId = Auth::user()->wilayah_id;
        $tanggal   = $request->input('tanggal', now()->toDateString());

        $rows = ChecklistHarian::with(['user'])
            ->whereDate('tanggal', $tanggal)
            ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($cl) => [
                'id'     => $cl->id,
                'nama'   => $cl->user->name ?? '-',
                'sesi'   => ucfirst($cl->sesi),
                'jam'    => $cl->updated_at->format('H:i'),
                'status' => $cl->status,
            ]);

        $statsQuery    = ChecklistHarian::whereDate('tanggal', $tanggal)
            ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId));
        $totalSubmit   = (clone $statsQuery)->whereIn('status', ['submit', 'verified'])->count();
        $totalVerified = (clone $statsQuery)->where('status', 'verified')->count();
        $totalDraft    = (clone $statsQuery)->where('status', 'draft')->count();

        return response()->json([
            'rows'  => $rows,
            'stats' => [
                'total_submit'   => $totalSubmit,
                'total_verified' => $totalVerified,
                'total_draft'    => $totalDraft,
            ],
        ]);
    }

    public function koordinatorVerify($id)
    {
        $koordinator = Auth::user();
        $checklist   = ChecklistHarian::findOrFail($id);

        if ($checklist->user->wilayah_id !== $koordinator->wilayah_id) {
            return back()->with('error', 'Anda tidak berhak memverifikasi checklist ini.');
        }

        if ($checklist->status !== 'submit') {
            return back()->with('error', 'Checklist belum disubmit.');
        }

        $checklist->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);
        return back()->with('success', 'Checklist berhasil diverifikasi.');
    }

    public function koordinatorDetail($id)
    {
        $koordinator = Auth::user();
        $checklist   = ChecklistHarian::with(['user', 'verifier'])->findOrFail($id);

        // Pastikan checklist milik wilayah koordinator ini
        if ($checklist->user->wilayah_id !== $koordinator->wilayah_id) {
            abort(403, 'Anda tidak berhak melihat checklist ini.');
        }

        $wilayahId = $checklist->user->wilayah_id ?? null;

        // Template AKTIF saat ini
        $templates = $wilayahId
            ? ChecklistTemplate::forWilayah($wilayahId)->get()
            : ChecklistTemplate::global()->get();

        // ── Label map historis ─────────────────────────────────────────
        $itemsJson   = $checklist->items ?? [];
        $templateIds = $templates->pluck('id')->map(fn($i) => (string)$i)->toArray();
        $orphanIds   = array_diff(array_keys($itemsJson), $templateIds);

        // withTrashed() supaya label item yang sudah dihapus tetap tampil
        $labelsMap = ChecklistTemplate::withTrashed()
            ->whereIn('id', array_keys($itemsJson))
            ->pluck('label', 'id');

        return view('koordinator.checklist.detail', compact(
            'checklist', 'templates', 'orphanIds', 'itemsJson', 'labelsMap'
        ));
    }

    public function koordinatorTemplateIndex()
    {
        $wilayahId = Auth::user()->wilayah_id;
        $templates = ChecklistTemplate::forWilayah($wilayahId)->get();

        return view('koordinator.checklist.template', compact('templates', 'wilayahId'));
    }

    public function koordinatorTemplateStore(Request $request)
    {
        $wilayahId = Auth::user()->wilayah_id;
        $request->validate([
            'label' => 'required|string|max:255',
            'link'  => 'nullable|url|max:255',
        ]);

        $max = ChecklistTemplate::where('wilayah_id', $wilayahId)->max('urutan') ?? 0;

        ChecklistTemplate::create([
            'wilayah_id' => $wilayahId,
            'label'      => $request->label,
            'link'       => $request->link,
            'urutan'     => $max + 1,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Item checklist berhasil ditambahkan.');
    }

    public function koordinatorTemplateUpdate(Request $request, $id)
    {
        $wilayahId = Auth::user()->wilayah_id;
        $tpl = ChecklistTemplate::where('wilayah_id', $wilayahId)->findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:255',
            'link'  => 'nullable|url|max:255',
        ]);

        $tpl->update([
            'label'     => $request->label,
            'link'      => $request->link,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Item diperbarui.');
    }

    public function koordinatorTemplateDestroy($id)
    {
        $wilayahId = Auth::user()->wilayah_id;
        // SoftDeletes: label tetap tersimpan untuk historis checklist lama
        ChecklistTemplate::where('wilayah_id', $wilayahId)->findOrFail($id)->delete();
        return back()->with('success', 'Item dihapus.');
    }

    public function koordinatorTemplateReorder(Request $request)
    {
        $wilayahId = Auth::user()->wilayah_id;
        foreach ($request->order as $idx => $id) {
            ChecklistTemplate::where('id', $id)->where('wilayah_id', $wilayahId)
                ->update(['urutan' => $idx + 1]);
        }
        return response()->json(['ok' => true]);
    }

    // ══════════════════════════════════════════════════════════════
    //  PETUGAS — Isi checklist (1 form, shift otomatis dari jam)
    // ══════════════════════════════════════════════════════════════

    public function petugasIndex()
    {
        $user      = Auth::user();
        $tanggal   = Carbon::now('Asia/Jakarta')->startOfDay();
        $wilayahId = $user->wilayah_id;

        // ── Deteksi shift aktif berdasarkan jam sekarang ──
        // Pagi  : 07.00 – 11.59
        // Siang : 12.00 – 16.59
        // Di luar jam → null (tampilkan pesan)
        $jam = now('Asia/Jakarta')->hour;
        if ($jam >= 7 && $jam < 12) {
            $shiftAktif = 'pagi';
        } elseif ($jam >= 12 && $jam < 17) {
            $shiftAktif = 'siang';
        } else {
            $shiftAktif = null; // Di luar jam shift
        }

        // ── Ambil template (wilayah dulu, fallback global) ──
        $templates = ChecklistTemplate::forWilayah($wilayahId)->get();
        if ($templates->isEmpty()) {
            $templates = ChecklistTemplate::global()->get();
        }

        // ── Jika di luar jam shift, kirim null ──
        $checklist = null;
        $jadwal    = null;
        if ($shiftAktif) {
            // Cek jadwal DB — wajib ada, bukan sekadar info
            $jadwal = JadwalPetugas::where('user_id', $user->id)
                ->where('tanggal', $tanggal->toDateString())
                ->where('shift', $shiftAktif)
                ->first();

            // Jika koordinator belum isi jadwal, blokir form
            if (!$jadwal) {
                $shiftAktif = 'tidak_terjadwal'; // tampilkan pesan khusus di view
            } else {
                // Ambil/siapkan checklist shift aktif hari ini
                $checklist = ChecklistHarian::firstOrNew([
                    'user_id' => $user->id,
                    'tanggal' => $tanggal->toDateString(),
                    'sesi'    => $shiftAktif,
                ]);
            }
        }

        // ── Riwayat 7 hari terakhir ──
        $riwayat = ChecklistHarian::where('user_id', $user->id)
            ->whereDate('tanggal', '>=', Carbon::now('Asia/Jakarta')->subDays(7)->toDateString())
            ->orderByDesc('tanggal')->orderBy('sesi')
            ->get();

        return view('petugas.checklist.index', compact(
            'templates', 'checklist', 'shiftAktif', 'jadwal', 'tanggal', 'riwayat'
        ));
    }

    public function petugasSave(Request $request)
    {
        $user    = Auth::user();
        $tanggal = now('Asia/Jakarta')->toDateString();
        $sesi    = $request->sesi;

        // Validasi nilai shift
        if (!in_array($sesi, ['pagi', 'siang'])) {
            return back()->with('error', 'Shift tidak valid.');
        }

        // Cek jadwal — petugas hanya boleh isi jika koordinator sudah jadwalkan
        $jadwalAda = JadwalPetugas::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->where('shift', $sesi)
            ->exists();

        if (!$jadwalAda) {
            return back()->with('error', 'Anda tidak memiliki jadwal shift ' . ucfirst($sesi) . ' hari ini. Hubungi koordinator.');
        }

        // Ambil atau buat checklist
        $checklist = ChecklistHarian::firstOrNew([
            'user_id' => $user->id,
            'tanggal' => $tanggal,
            'sesi'    => $sesi,
        ]);

        // Cegah edit checklist yang sudah diverifikasi
        if ($checklist->exists && $checklist->status === 'verified') {
            return back()->with('error', 'Checklist sudah diverifikasi, tidak bisa diubah.');
        }

        // Cegah double-submit
        if ($checklist->exists && $checklist->status === 'submit' && $request->action === 'submit') {
            return back()->with('error', 'Checklist shift ini sudah disubmit sebelumnya.');
        }

        // Kumpulkan centang dari request
        $items = [];
        foreach ($request->input('items', []) as $tplId => $val) {
            $items[$tplId] = (bool) $val;
        }

        $isSubmit = $request->action === 'submit';

        $checklist->items   = $items;
        $checklist->catatan = $request->catatan;
        $checklist->status  = $isSubmit ? 'submit' : 'draft';
        $checklist->save();

        $msg = $isSubmit
            ? 'Checklist shift ' . ucfirst($sesi) . ' berhasil disubmit ke koordinator.'
            : 'Draft checklist tersimpan.';

        return back()->with('success', $msg);
    }

    public function adminPolling(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());

        $rows = ChecklistHarian::with(['user.petugas.wilayah'])
            ->whereDate('tanggal', $tanggal)
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($cl) => [
                'id'     => $cl->id,
                'nama'   => $cl->user->name ?? '-',
                'sesi'   => ucfirst($cl->sesi),
                'wilayah'=> $cl->user->petugas->wilayah->nama ?? '-',
                'jam'    => $cl->updated_at->format('H:i'),
                'status' => $cl->status,
            ]);

        $statsQuery    = ChecklistHarian::whereDate('tanggal', $tanggal);
        $totalSubmit   = (clone $statsQuery)->whereIn('status', ['submit','verified'])->count();
        $totalVerified = (clone $statsQuery)->where('status', 'verified')->count();
        $totalDraft    = (clone $statsQuery)->where('status', 'draft')->count();

        return response()->json([
            'rows'  => $rows,
            'stats' => [
                'total_submit'   => $totalSubmit,
                'total_verified' => $totalVerified,
                'total_draft'    => $totalDraft,
            ],
        ]);
    }

}