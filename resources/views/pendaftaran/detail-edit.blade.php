@extends('layouts.public')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="card space-y-6">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">✏️ Edit Data Calon Siswa</h2>
            <p class="text-sm text-slate-500 mt-1">Perubahan data akan langsung tersimpan setelah klik tombol Simpan Perubahan.</p>
        </div>

        <form method="POST" action="{{ route('pendaftaran.update', $siswa->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-slate-200">
                <h3 class="px-4 py-3 border-b border-slate-200 bg-slate-50 font-semibold text-slate-700">Data Siswa</h3>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-xs text-slate-500">Nama</label><input name="nama" value="{{ old('nama', $siswa->nama) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required></div>
                    <div><label class="text-xs text-slate-500">Jenis Kelamin</label><select name="jenis_kelamin" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="laki-laki" {{ old('jenis_kelamin', $siswa->jenis_kelamin) === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option><option value="perempuan" {{ old('jenis_kelamin', $siswa->jenis_kelamin) === 'perempuan' ? 'selected' : '' }}>Perempuan</option></select></div>
                    <div><label class="text-xs text-slate-500">NISN</label><input name="nisn" value="{{ old('nisn', $siswa->nisn) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Opsional, max 10 digit angka"></div>
                    <div><label class="text-xs text-slate-500">NIK</label><input name="nik" value="{{ old('nik', $siswa->nik) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">No KK</label><input name="no_kk" value="{{ old('no_kk', $siswa->no_kk) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Tempat Lahir</label><input name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($siswa->tanggal_lahir)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Akta No</label><input name="akta_no" value="{{ old('akta_no', $siswa->akta_no) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Transportasi</label><input name="transportasi" value="{{ old('transportasi', $siswa->transportasi) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Agama</label><input name="agama" value="{{ old('agama', $siswa->agama) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Kewarganegaraan</label><input name="kewarganegaraan" value="{{ old('kewarganegaraan', $siswa->kewarganegaraan) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Berkebutuhan Khusus</label><input name="berkebutuhan_khusus" value="{{ old('berkebutuhan_khusus', $siswa->berkebutuhan_khusus) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Tinggal Bersama</label><input name="tinggal_bersama" value="{{ old('tinggal_bersama', $siswa->tinggal_bersama) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200">
                <h3 class="px-4 py-3 border-b border-slate-200 bg-slate-50 font-semibold text-slate-700">Data Alamat</h3>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2"><label class="text-xs text-slate-500">Alamat</label><textarea name="alamat" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('alamat', optional($siswa->alamat)->alamat) }}</textarea></div>
                    <div><label class="text-xs text-slate-500">Provinsi</label><input name="provinsi" value="{{ old('provinsi', optional($siswa->alamat)->provinsi) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Kabupaten</label><input name="kabupaten" value="{{ old('kabupaten', optional($siswa->alamat)->kabupaten) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Kecamatan</label><input name="kecamatan" value="{{ old('kecamatan', optional($siswa->alamat)->kecamatan) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Kelurahan</label><input name="kelurahan" value="{{ old('kelurahan', optional($siswa->alamat)->kelurahan) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">RT</label><input name="rt" value="{{ old('rt', optional($siswa->alamat)->rt) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">RW</label><input name="rw" value="{{ old('rw', optional($siswa->alamat)->rw) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Kode Pos</label><input name="kode_pos" value="{{ old('kode_pos', optional($siswa->alamat)->kode_pos) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200">
                <h3 class="px-4 py-3 border-b border-slate-200 bg-slate-50 font-semibold text-slate-700">Data Orang Tua & Pendukung</h3>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-xs text-slate-500">Nama Ibu</label><input name="ibu_nama" value="{{ old('ibu_nama', optional($siswa->ibu)->nama) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">NIK Ibu</label><input name="ibu_nik" value="{{ old('ibu_nik', optional($siswa->ibu)->nik) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">No HP Ibu</label><input name="ibu_no_hp" value="{{ old('ibu_no_hp', optional($siswa->ibu)->no_hp) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Nama Ayah</label><input name="ayah_nama" value="{{ old('ayah_nama', optional($siswa->ayah)->nama) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">NIK Ayah</label><input name="ayah_nik" value="{{ old('ayah_nik', optional($siswa->ayah)->nik) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Tinggi (cm)</label><input name="tinggi" value="{{ old('tinggi', optional($siswa->dataPendukung)->tinggi) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Berat (kg)</label><input name="berat" value="{{ old('berat', optional($siswa->dataPendukung)->berat) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Jarak</label><input name="jarak" value="{{ old('jarak', optional($siswa->dataPendukung)->jarak) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Jumlah Saudara</label><input name="jumlah_saudara" value="{{ old('jumlah_saudara', optional($siswa->dataPendukung)->jumlah_saudara) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Anak ke berapa (berdasarkan KK)</label><input type="number" min="1" max="99" name="anak_ke" value="{{ old('anak_ke', optional($siswa->dataPendukung)->anak_ke) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Hobi</label><input name="hobi" value="{{ old('hobi', optional($siswa->dataPendukung)->hobi) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Cita-cita</label><input name="cita_cita" value="{{ old('cita_cita', optional($siswa->dataPendukung)->cita_cita) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                    <div class="md:col-span-2"><label class="text-xs text-slate-500">Alamat TK</label><input name="alamat_tk" value="{{ old('alamat_tk', optional($siswa->dataPendukung)->alamat_tk) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('pendaftaran.detail', $siswa->id) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm hover:bg-slate-100">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
