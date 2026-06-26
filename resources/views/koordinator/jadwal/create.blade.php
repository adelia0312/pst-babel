<h2>Tambah Jadwal</h2>

<form action="{{ route('jadwal.store') }}" method="POST">
    @csrf

    <label>Petugas:</label>
<select name="user_id" required>
    <option value="">-- Pilih Petugas --</option>
    @foreach($petugas as $p)
        <option value="{{ $p->id }}">{{ $p->name }}</option>
    @endforeach
</select>

    <br><br>

    <label>Tanggal:</label>
    <input type="date" name="tanggal">

    <br><br>

    <label>Shift:</label>
    <select name="shift">
        <option value="pagi">Pagi</option>
        <option value="siang">Siang</option>
        <option value="malam">Malam</option>
    </select>

    <br><br>

    <label>Keterangan:</label>
    <textarea name="keterangan"></textarea>

    <br><br>

    <button type="submit">Simpan</button>
</form>