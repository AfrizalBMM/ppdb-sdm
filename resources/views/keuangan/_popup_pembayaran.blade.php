<div class="card">

    <h3 class="font-semibold text-slate-800 mb-3">
        Pembayaran
    </h3>

    {{-- Ringkasan --}}
    <div class="text-sm space-y-1 mb-4">
        <p>
            Total :
            <span class="font-semibold">
                Rp {{ number_format($tagihan->total ?? 0,0,',','.') }}
            </span>
        </p>
        <p>
            Dibayar :
            <span class="font-semibold text-green-700">
                Rp {{ number_format($tagihan->total_dibayar ?? 0,0,',','.') }}
            </span>
        </p>
        <p>
            Sisa :
            <span class="font-semibold text-red-600">
                Rp {{ number_format($tagihan->sisa ?? 0,0,',','.') }}
            </span>
        </p>
    </div>

    {{-- Form Pembayaran --}}
    <form method="POST" action="{{ route('pembayaran.store') }}" class="space-y-3">
        @csrf

        <input
            type="hidden"
            name="tagihan_siswa_id"
            value="{{ $tagihan->id }}">

        <div>
            <label class="label">Tanggal Bayar</label>
            <input
                type="date"
                name="tanggal_bayar"
                class="input"
                value="{{ old('tanggal_bayar', date('Y-m-d')) }}">
        </div>

        <div>
            <label class="label">Nominal Bayar</label>
            <input
                type="number"
                name="nominal_bayar"
                class="input"
                placeholder="Nominal bayar"
                value="{{ old('nominal_bayar') }}">
        </div>

        <div>
            <label class="label">Metode Pembayaran</label>
            <select name="metode" class="input">
                <option value="cash" @selected(old('metode') === 'cash')>
                    Cash
                </option>
                <option value="transfer" @selected(old('metode') === 'transfer')>
                    Transfer
                </option>
            </select>
        </div>

        <div>
            <label class="label">Keterangan</label>
            <input
                name="keterangan"
                class="input"
                placeholder="Keterangan (opsional)"
                value="{{ old('keterangan') }}">
        </div>

        <button class="btn-primary w-full">
            Simpan Pembayaran
        </button>
    </form>

    <hr class="my-4">

    {{-- Riwayat --}}
    <h4 class="font-semibold text-sm text-slate-800 mb-2">
        Riwayat Pembayaran
    </h4>

    <ul class="text-sm space-y-1">
        @forelse($tagihan->pembayaran as $p)
            <li class="flex justify-between">
                <span>
                    {{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d-m-Y') }}
                    — {{ ui_label($p->metode) }}
                </span>
                <span class="font-medium">
                    Rp {{ number_format($p->nominal_bayar,0,',','.') }}
                </span>
            </li>
        @empty
            <li class="text-slate-500">
                Belum ada pembayaran
            </li>
        @endforelse
    </ul>

</div>
