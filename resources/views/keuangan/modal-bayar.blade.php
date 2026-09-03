<div id="modalBayar"
    class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div
        class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300 overflow-y-auto max-h-screen"
        onclick="event.stopPropagation()">

        <div
            class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            💳
        </div>

        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Input Pembayaran</h3>
            <p class="text-sm text-slate-500 mt-1">Catat transaksi pembayaran baru untuk siswa.</p>
        </div>

        <form method="POST" action="{{ route('pembayaran.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="tagihan_siswa_id" id="tagihan_id">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Siswa / Jenis
                        Biaya</label>
                    <div class="flex flex-col gap-1">
                        <input id="nama_siswa" disabled
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2 text-[11px] font-bold text-slate-700">
                        <input id="nama_biaya" disabled
                            class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2 text-[10px] font-medium text-slate-500">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Sisa
                        Tagihan</label>
                    <input id="sisa_tagihan" disabled
                        class="w-full rounded-xl border-slate-200 bg-red-50 px-4 py-2.5 text-sm font-bold text-red-600">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Tanggal Bayar</label>
                <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" required
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold">
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nominal Bayar</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp.</span>
                    <input type="text" id="nominal_display"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 pl-12 pr-4 py-3 text-sm font-bold text-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                        placeholder="0" oninput="validateNominal()" required autocomplete="off">
                </div>
                <input type="hidden" name="nominal_bayar" id="nominal_bayar">
                <p id="nominal_error"
                    class="mt-1.5 text-[11px] font-medium text-red-500 hidden bg-red-50 p-2 rounded-lg border border-red-100 italic">
                    ⚠️ Melebihi sisa tagihan!
                </p>
                <div class="flex flex-wrap gap-1.5 mt-3">
                    @foreach([50, 100, 150, 200, 500] as $n)
                    <button type="button" onclick="setNominal({{ $n * 1000 }})"
                        class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200">
                        {{ $n }}.000
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Metode</label>
                    <select name="metode"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Penerima
                        (Wajib)</label>
                    <input type="text" name="admin_penerima"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                        placeholder="..." required>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Keterangan</label>
                <input type="text" name="keterangan"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                    placeholder="opsional">
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeBayar()"
                    class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit"
                    class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    let sisaTagihanGlobal = 0;

    function openBayar(id, siswa, biaya, sisa) {
        const modal = document.getElementById('modalBayar');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        document.getElementById('tagihan_id').value = id;
        document.getElementById('nama_siswa').value = siswa;
        document.getElementById('nama_biaya').value = biaya;
        document.getElementById('sisa_tagihan').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);
        sisaTagihanGlobal = parseInt(sisa);
        document.getElementById('nominal_display').value = '';
        document.getElementById('nominal_bayar').value = '';
        document.getElementById('nominal_error').classList.add('hidden');
    }

    function closeBayar() {
        const modal = document.getElementById('modalBayar');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        document.getElementById('nominal_display').value = '';
        document.getElementById('nominal_bayar').value = '';
        document.getElementById('nominal_error').classList.add('hidden');
    }


    function formatRupiah(input) {
        let angka = input.value.replace(/\D/g, '');
        document.getElementById('nominal_bayar').value = angka;
        let formatted = new Intl.NumberFormat('id-ID').format(angka);
        input.value = formatted;
    }

    function validateNominal() {
        let input = document.getElementById('nominal_display');
        let angka = input.value.replace(/\D/g, '');
        document.getElementById('nominal_bayar').value = angka;
        let formatted = new Intl.NumberFormat('id-ID').format(angka);
        input.value = formatted;
        if (angka === '') {
            document.getElementById('nominal_error').classList.add('hidden');
            return;
        }
        if (parseInt(angka) > sisaTagihanGlobal) {
            document.getElementById('nominal_error').classList.remove('hidden');
        } else {
            document.getElementById('nominal_error').classList.add('hidden');
        }
    }

    function setNominal(nominal) {
        document.getElementById('nominal_display').value = new Intl.NumberFormat('id-ID').format(nominal);
        document.getElementById('nominal_bayar').value = nominal;
        validateNominal();
    }
</script>