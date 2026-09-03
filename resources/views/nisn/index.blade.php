@extends('layouts.public')

@section('title', 'Input NISN Siswa – SD Muhammadiyah Wonorejo')

@section('content')
<div class="max-w-lg mx-auto px-4 py-8">

    {{-- HEADER CARD --}}
    <div class="rounded-3xl bg-blue-600 p-6 text-white shadow-xl shadow-blue-500/20 mb-6">
        <div class="flex items-center gap-4 mb-3">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                🎓
            </div>
            <div>
                <h1 class="text-xl font-bold leading-tight">Input NISN Siswa</h1>
                <p class="text-blue-100 text-xs mt-0.5">SD Muhammadiyah Wonorejo</p>
            </div>
        </div>
        <p class="text-sm text-blue-100 leading-relaxed">
            Masukkan <strong class="text-white">nama lengkap siswa</strong> dan
            <strong class="text-white">nama ibu kandung</strong> untuk menemukan data,
            kemudian tambahkan NISN jika belum ada.
        </p>
    </div>

    {{-- SEARCH CARD --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mb-4" id="searchCard">
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Cari Data Siswa</h2>

        <div class="space-y-4">
            {{-- NAMA --}}
            <div>
                <label for="inputNama" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">
                    Nama Lengkap Siswa <span class="text-red-500">*</span>
                </label>
                <input
                    id="inputNama"
                    type="text"
                    placeholder="Contoh: Budi Santoso"
                    autocomplete="off"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400
                           focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
                >
            </div>

            {{-- NAMA IBU --}}
            <div>
                <label for="inputNamaIbu" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">
                    Nama Ibu Kandung <span class="text-red-500">*</span>
                </label>
                <input
                    id="inputNamaIbu"
                    type="text"
                    placeholder="Contoh: Siti Rahayu"
                    autocomplete="off"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400
                           focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
                >
            </div>

            {{-- ERROR MESSAGE --}}
            <div id="searchError" class="hidden rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700"></div>

            {{-- TOMBOL CARI --}}
            <button
                id="btnCari"
                type="button"
                onclick="cariSiswa()"
                class="w-full rounded-xl bg-blue-600 py-3 text-sm font-bold text-white shadow-md shadow-blue-500/30
                       hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
            >
                <svg id="iconCari" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg id="iconCariSpin" class="h-4 w-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="lblCari">Cari Data Siswa</span>
            </button>
        </div>
    </div>

    {{-- NISN FORM CARD (hidden until student found) --}}
    <div id="nisnCard" class="hidden bg-white rounded-3xl border border-slate-200 shadow-sm p-6">

        {{-- Student info strip --}}
        <div class="flex items-center gap-3 mb-5 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0">✅</div>
            <div>
                <div class="text-xs text-emerald-700 font-semibold">Siswa ditemukan</div>
                <div id="foundNama" class="text-sm font-bold text-slate-800 mt-0.5"></div>
            </div>
        </div>

        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Tambah NISN</h2>

        {{-- NISN INPUT --}}
        <div class="mb-1.5">
            <label for="inputNisn" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">
                NISN (10 digit angka) <span class="text-red-500">*</span>
            </label>
            <input
                id="inputNisn"
                type="text"
                inputmode="numeric"
                maxlength="10"
                placeholder="Contoh: 0012345678"
                autocomplete="off"
                oninput="handleNisnInput(this)"
                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 tracking-widest
                       placeholder:text-slate-400 placeholder:tracking-normal
                       focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
            >
        </div>

        {{-- Live validation hint --}}
        <div id="nisnHint" class="mb-4 flex items-center gap-1.5 text-xs text-slate-400 min-h-[20px]">
            <span>Masukkan 10 digit angka NISN.</span>
        </div>

        {{-- Error --}}
        <div id="nisnError" class="hidden rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 mb-4"></div>

        {{-- SUBMIT BUTTON --}}
        <button
            id="btnSimpan"
            type="button"
            onclick="simpanNisn()"
            disabled
            class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-md shadow-emerald-500/30
                   hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2
                   disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none"
        >
            <svg id="iconSimpan" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg id="iconSimpanSpin" class="h-4 w-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>Kirim Data NISN</span>
        </button>

        {{-- Cari lagi --}}
        <button
            type="button"
            onclick="resetForm()"
            class="mt-3 w-full rounded-xl border border-slate-300 py-2.5 text-xs font-semibold text-slate-600
                   hover:bg-slate-50 transition"
        >
            ← Cari siswa lain
        </button>
    </div>

</div>

{{-- ============ MODAL: NISN sudah ada ============ --}}
<div id="modalSudahNisn"
     class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
     onclick="tutupModalSudahNisn()">
    <div class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-2xl text-center" onclick="event.stopPropagation()">
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">⚠️</div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">NISN Sudah Ditambahkan</h3>
        <p class="text-sm text-slate-500 leading-relaxed mb-1">
            Siswa <strong id="modalNama" class="text-slate-700"></strong> sudah memiliki NISN yang tercatat:
        </p>
        <p class="text-2xl font-black text-blue-700 tracking-widest my-3" id="modalNisnValue"></p>
        <p class="text-xs text-slate-400 mb-6">Jika ada kesalahan, hubungi pihak sekolah.</p>
        <button
            type="button"
            onclick="tutupModalSudahNisn()"
            class="w-full rounded-xl bg-slate-800 py-3 text-sm font-bold text-white hover:bg-slate-900 transition"
        >
            Tutup
        </button>
    </div>
</div>

{{-- ============ MODAL: Sukses ============ --}}
<div id="modalSukses"
     class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
     onclick="tutupModalSukses()">
    <div class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-2xl text-center" onclick="event.stopPropagation()">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">🎉</div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">NISN Berhasil Disimpan!</h3>
        <p id="modalSuksesMsg" class="text-sm text-slate-500 leading-relaxed mb-6"></p>
        <button
            type="button"
            onclick="tutupModalSukses()"
            class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition"
        >
            Selesai
        </button>
    </div>
</div>

<script>
let currentSiswaId = null;
let nisnCheckTimer  = null;
let nisnValid       = false;

const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

/* ─────────────── SEARCH ─────────────── */
async function cariSiswa() {
    const nama    = document.getElementById('inputNama').value.trim();
    const namaIbu = document.getElementById('inputNamaIbu').value.trim();
    const searchError  = document.getElementById('searchError');

    // reset state
    searchError.classList.add('hidden');
    hideNisnCard();

    if (!nama) {
        showSearchError('Nama lengkap siswa wajib diisi.');
        document.getElementById('inputNama').focus();
        return;
    }
    if (!namaIbu) {
        showSearchError('Nama ibu kandung wajib diisi.');
        document.getElementById('inputNamaIbu').focus();
        return;
    }

    setBtnCariLoading(true);

    try {
        const res  = await fetch('{{ route("nisn.public.cari") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ nama, nama_ibu: namaIbu }),
        });

        const data = await res.json();

        if (!res.ok || !data.found) {
            showSearchError(data.message || 'Siswa tidak ditemukan.');
            return;
        }

        currentSiswaId = data.id;

        if (data.has_nisn) {
            // Already has NISN → show modal
            document.getElementById('modalNama').textContent      = data.nama;
            document.getElementById('modalNisnValue').textContent = data.nisn;
            bukaModal('modalSudahNisn');
        } else {
            // No NISN yet → show input form
            document.getElementById('foundNama').textContent = data.nama;
            showNisnCard();
        }
    } catch {
        showSearchError('Terjadi kesalahan jaringan. Coba lagi.');
    } finally {
        setBtnCariLoading(false);
    }
}

/* ─────────────── NISN LIVE CHECK ─────────────── */
function handleNisnInput(el) {
    // Strip non-digits
    el.value = el.value.replace(/\D/g, '').slice(0, 10);

    const val  = el.value;
    const hint = document.getElementById('nisnHint');
    const btn  = document.getElementById('btnSimpan');

    // Reset
    nisnValid = false;
    btn.disabled = true;
    clearTimeout(nisnCheckTimer);
    document.getElementById('nisnError').classList.add('hidden');

    if (val.length === 0) {
        setNisnHint('Masukkan 10 digit angka NISN.', 'neutral');
        return;
    }
    if (val.length < 10) {
        setNisnHint(`${val.length}/10 digit – perlu ${10 - val.length} digit lagi.`, 'neutral');
        return;
    }

    // Exactly 10 digits → live-check uniqueness after 400ms debounce
    setNisnHint('Memeriksa ketersediaan NISN...', 'loading');

    nisnCheckTimer = setTimeout(async () => {
        try {
            const res  = await fetch('{{ route("nisn.public.cek") }}?' + new URLSearchParams({
                nisn: val,
                siswa_id: currentSiswaId ?? 0,
            }));
            const data = await res.json();

            if (data.valid) {
                setNisnHint('✓ NISN tersedia dan dapat digunakan.', 'ok');
                nisnValid = true;
                btn.disabled = false;
            } else {
                setNisnHint('✗ ' + data.message, 'error');
            }
        } catch {
            setNisnHint('Gagal memeriksa NISN. Coba lagi.', 'error');
        }
    }, 450);
}

function setNisnHint(text, type) {
    const hint = document.getElementById('nisnHint');
    const classes = {
        ok:      'text-emerald-600',
        error:   'text-red-600',
        loading: 'text-blue-500',
        neutral: 'text-slate-400',
    };
    hint.className = 'mb-4 flex items-center gap-1.5 text-xs min-h-[20px] transition-colors ' + (classes[type] || classes.neutral);
    hint.innerHTML = `<span>${text}</span>`;
}

/* ─────────────── SIMPAN NISN ─────────────── */
async function simpanNisn() {
    const nisn = document.getElementById('inputNisn').value.trim();

    if (!nisnValid || nisn.length !== 10) {
        setNisnHint('NISN harus 10 digit angka yang valid.', 'error');
        return;
    }

    setBtnSimpanLoading(true);
    document.getElementById('nisnError').classList.add('hidden');

    try {
        const res  = await fetch('{{ route("nisn.public.simpan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ siswa_id: currentSiswaId, nisn }),
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            showNisnError(data.message || 'Gagal menyimpan NISN.');
            return;
        }

        // Success
        document.getElementById('modalSuksesMsg').textContent = data.message;
        bukaModal('modalSukses');
        resetForm();

    } catch {
        showNisnError('Terjadi kesalahan jaringan. Coba lagi.');
    } finally {
        setBtnSimpanLoading(false);
    }
}

/* ─────────────── HELPERS ─────────────── */
function showSearchError(msg) {
    const el = document.getElementById('searchError');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function showNisnError(msg) {
    const el = document.getElementById('nisnError');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function showNisnCard() {
    document.getElementById('nisnCard').classList.remove('hidden');
    document.getElementById('inputNisn').value = '';
    setNisnHint('Masukkan 10 digit angka NISN.', 'neutral');
    nisnValid = false;
    document.getElementById('btnSimpan').disabled = true;
    document.getElementById('nisnError').classList.add('hidden');
    setTimeout(() => document.getElementById('inputNisn').focus(), 100);
}

function hideNisnCard() {
    document.getElementById('nisnCard').classList.add('hidden');
    currentSiswaId = null;
    nisnValid = false;
}

function resetForm() {
    hideNisnCard();
    document.getElementById('inputNama').value = '';
    document.getElementById('inputNamaIbu').value = '';
    document.getElementById('searchError').classList.add('hidden');
    document.getElementById('inputNama').focus();
}

function setBtnCariLoading(loading) {
    const btn   = document.getElementById('btnCari');
    const icon  = document.getElementById('iconCari');
    const spin  = document.getElementById('iconCariSpin');
    const lbl   = document.getElementById('lblCari');
    btn.disabled  = loading;
    icon.classList.toggle('hidden', loading);
    spin.classList.toggle('hidden', !loading);
    lbl.textContent = loading ? 'Mencari...' : 'Cari Data Siswa';
}

function setBtnSimpanLoading(loading) {
    const btn  = document.getElementById('btnSimpan');
    const icon = document.getElementById('iconSimpan');
    const spin = document.getElementById('iconSimpanSpin');
    btn.disabled  = loading;
    icon.classList.toggle('hidden', loading);
    spin.classList.toggle('hidden', !loading);
}

function bukaModal(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
}

function tutupModalSudahNisn() {
    const m = document.getElementById('modalSudahNisn');
    if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
}

function tutupModalSukses() {
    const m = document.getElementById('modalSukses');
    if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
}

// Allow pressing Enter to trigger search
document.addEventListener('DOMContentLoaded', () => {
    ['inputNama','inputNamaIbu'].forEach(id => {
        document.getElementById(id)?.addEventListener('keydown', e => {
            if (e.key === 'Enter') cariSiswa();
        });
    });
    document.getElementById('inputNisn')?.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !document.getElementById('btnSimpan').disabled) simpanNisn();
    });
});
</script>
@endsection
