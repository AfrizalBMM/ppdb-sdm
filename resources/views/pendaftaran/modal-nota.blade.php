<div id="modalNota" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300" onclick="event.stopPropagation()">
        
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🧾
        </div>
        
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Cetak Kuitansi</h3>
            <p class="text-sm text-slate-500 mt-1">Cetak bukti pembayaran pendaftaran siswa.</p>
        </div>

        <form method="GET" action="{{ route('pembayaran.nota', $siswa->pembayaranPendaftaran->id ?? '') }}" target="_blank" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Admin / Panitia</label>
                <input name="nama_admin" required
                       class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                       value="{{ auth()->user()->name ?? '' }}"
                       placeholder="Misal: Ahmad S.Pd">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalNota').classList.add('hidden')"
                        class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Cetak
                </button>
            </div>
        </form>
    </div>
</div>
