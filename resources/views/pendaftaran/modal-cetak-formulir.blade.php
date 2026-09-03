<div id="modalPetugas" class="fixed inset-0 z-[300] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl transform transition-all duration-300" onclick="event.stopPropagation()">
        
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
            🖨️
        </div>
        
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Cetak Formulir</h3>
            <p class="text-sm text-slate-500 mt-1">Masukkan nama panitia untuk kuitansi / formulir.</p>
        </div>

        <form id="formCetakFormulirSukses" method="POST" action="{{ route('cetak.formulir.post') }}" target="cetakFormulirFrameSukses" onsubmit="submitCetakFormulirSukses()" class="space-y-4">
            @csrf
            <input type="hidden" name="siswa_id" id="modalSiswaId">

            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Nama Panitia</label>
                <input type="text" name="nama_panitia" required
                       class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                       placeholder="Misal: Ahmad S.Pd">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModalPetugas()"
                        class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button id="btnCetakFormulirSukses" class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all">
                    Download
                </button>
            </div>
        </form>
    </div>
</div>

<iframe name="cetakFormulirFrameSukses" id="cetakFormulirFrameSukses" class="hidden"></iframe>

<div id="toastCetakFormulirSukses" class="fixed bottom-4 right-4 z-toast pointer-events-none opacity-0 translate-y-2 transition-all duration-200">
    <div class="rounded-lg border border-green-200 bg-green-100/90 px-3 py-2 text-xs font-medium text-green-700 shadow-lg">
        Download formulir dimulai
    </div>
</div>

<script>
let isCetakFormulirSuksesSubmitting = false;

function showCetakFormulirSuksesToast() {
    const toast = document.getElementById('toastCetakFormulirSukses');
    if (!toast) {
        return;
    }

    toast.classList.remove('opacity-0', 'translate-y-2');

    setTimeout(function () {
        toast.classList.add('opacity-0', 'translate-y-2');
    }, 1500);
}

function openModalPetugas(id){
    document.getElementById('modalSiswaId').value = id;
    // Pakai helper global (app.js): tambah `flex` + kunci scroll body,
    // supaya modal selalu ter-center (tidak muncul di pojok).
    window.openModal('modalPetugas');
}

function closeModalPetugas(){
    window.closeModal('modalPetugas');
}

function submitCetakFormulirSukses(){
    isCetakFormulirSuksesSubmitting = true;

    const button = document.getElementById('btnCetakFormulirSukses');
    if (button) {
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    const frame = document.getElementById('cetakFormulirFrameSukses');
    const form = document.getElementById('formCetakFormulirSukses');
    const button = document.getElementById('btnCetakFormulirSukses');

    if (!frame) {
        return;
    }

    frame.addEventListener('load', function () {
        if (!isCetakFormulirSuksesSubmitting) {
            return;
        }

        closeModalPetugas();
        showCetakFormulirSuksesToast();

        if (form) {
            form.reset();
        }

        if (button) {
            button.disabled = false;
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }

        isCetakFormulirSuksesSubmitting = false;
    });
});
</script>
