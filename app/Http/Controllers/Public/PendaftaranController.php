<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Biaya;
use App\Models\PasswordPanitia;
use App\Models\Pembayaran;
use App\Models\Registration;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;


class PendaftaranController extends Controller
{
    private function validasiRiwayatPembayaranAcuan(Siswa $siswa): array
    {
        $siswa->loadMissing('registration');

        if (!$siswa->registration) {
            return [false, 'Data registrasi siswa tidak ditemukan.'];
        }

        $tahunAjaranId = (int) ($siswa->registration->tahun_ajaran_id ?? 0);

        $acuanBiayaIds = Biaya::query()
            ->where('is_acuan_status_ppdb', true)
            ->when($tahunAjaranId > 0, function ($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->pluck('id');

        if ($acuanBiayaIds->isEmpty()) {
            return [false, 'Belum ada biaya acuan status PPDB untuk tahun ajaran siswa ini.'];
        }

        $hasRiwayatBayarAcuan = Pembayaran::query()
            ->whereHas('tagihan', function ($q) use ($siswa, $acuanBiayaIds) {
                $q->where('siswa_id', $siswa->id)
                    ->where('total', '>', 0)
                    ->whereIn('biaya_id', $acuanBiayaIds);
            })
            ->exists();

        if (!$hasRiwayatBayarAcuan) {
            return [false, 'Siswa belum memiliki riwayat pembayaran pada biaya acuan status PPDB.'];
        }

        return [true, 'ok'];
    }

    private function formatFilterList(array $values): string
    {
        return empty($values) ? '-' : implode(', ', $values);
    }

    public function sukses(Siswa $siswa)
    {
        $siswa->load([
            'registration',
            'ibu',
            'ayah',
            'wali',
            'alamat',
            'dataPendukung.paudTk',
            'tagihan.biaya',
        ]);

        logAktivitas(
            'Panitia Public - Lihat Halaman Sukses',
            'Membuka halaman sukses pendaftaran siswa ' . ($siswa->nama ?? '-') .
            ' (ID: ' . $siswa->id . ', No Registrasi: ' . ($siswa->registration->nomor_registrasi ?? '-') . ').'
        );

        return view('pendaftaran.sukses', compact('siswa'));
    }

    public function list(Request $request)
    {
        if (!$request->has('order')) {
            return redirect()->route('pendaftaran.list', array_merge($request->query(), [
                'order' => 'terbaru',
            ]));
        }

        $search = $request->search;
        $paymentStatuses = collect((array) $request->input('payment_statuses', []))
            ->filter(fn ($status) => in_array($status, ['lunas', 'belum_lunas'], true))
            ->unique()
            ->values()
            ->all();
        $biayaIds = collect((array) $request->input('biaya_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $jenisKelamins = collect((array) $request->input('jenis_kelamins', []))
            ->filter(fn ($jenisKelamin) => in_array($jenisKelamin, ['laki-laki', 'perempuan'], true))
            ->unique()
            ->values()
            ->all();

        $hasilTesInput = $request->input('hasil_tes');
        if (is_array($hasilTesInput)) {
            $hasilTesInput = $hasilTesInput[0] ?? null;
        }
        $hasilTes = is_string($hasilTesInput) ? strtoupper(trim($hasilTesInput)) : null;
        if (!in_array($hasilTes, ['SB', 'B', 'PB', 'BT'], true)) {
            $hasilTes = null;
        }

        $validatedDateRange = validator($request->only(['date_from', 'date_to']), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ])->validate();

        $dateFrom = $validatedDateRange['date_from'] ?? null;
        $dateTo = $validatedDateRange['date_to'] ?? null;
        $order = $request->order === 'terbaru' ? 'terbaru' : 'terlama';
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }
        $statusPpdb = (int) $request->input('status_ppdb', 0);
        $allowedStatusPpdb = [
            Registration::STATUS_BAKAL_CALON,
            Registration::STATUS_CALON,
            Registration::STATUS_PESERTA_DIDIK,
        ];

        if (!in_array($statusPpdb, $allowedStatusPpdb, true)) {
            $statusPpdb = null;
        }

        $voucherActive = $request->boolean('voucher_active');

        $voucherCodeOptions = Voucher::query()
            ->orderBy('kode')
            ->pluck('kode')
            ->filter()
            ->values()
            ->all();

        $voucherCode = trim((string) $request->input('voucher_code', ''));
        if ($voucherCode !== '' && $voucherCode !== 'none' && !in_array($voucherCode, $voucherCodeOptions, true)) {
            $voucherCode = '';
        }

        $siswa = Siswa::with([
                'registration',
                'ibu',
                'tagihan.biaya',
                'tagihan.pembayaran',
            ])
            ->withSum('tagihan', 'total')
            ->when(!empty($paymentStatuses), function ($q) use ($paymentStatuses, $biayaIds) {
                $q->where(function ($q1) use ($paymentStatuses, $biayaIds) {
                    if (in_array('lunas', $paymentStatuses, true)) {
                        $q1->orWhere(function ($q2) use ($biayaIds) {
                            // Jika jenis biaya dipilih, status lunas dihitung pada biaya terpilih saja.
                            if (!empty($biayaIds)) {
                                $q2->whereHas('tagihan', function ($q3) use ($biayaIds) {
                                    $q3->whereIn('biaya_id', $biayaIds);
                                })->whereDoesntHave('tagihan', function ($q3) use ($biayaIds) {
                                    $q3->whereIn('biaya_id', $biayaIds)
                                        ->where('status', '!=', 'lunas')
                                        ->where('total', '>', 0);
                                });
                                return;
                            }

                            $q2->whereHas('tagihan')
                                ->whereDoesntHave('tagihan', function ($q3) {
                                    $q3->where('status', '!=', 'lunas')
                                        ->where('total', '>', 0);
                                });
                        });
                    }

                    if (in_array('belum_lunas', $paymentStatuses, true)) {
                        $q1->orWhere(function ($q2) use ($biayaIds) {
                            // Jika jenis biaya dipilih, status belum lunas dihitung pada biaya terpilih saja.
                            if (!empty($biayaIds)) {
                                $q2->whereHas('tagihan', function ($q3) use ($biayaIds) {
                                    $q3->whereIn('biaya_id', $biayaIds)
                                        ->where('status', '!=', 'lunas')
                                        ->where('total', '>', 0);
                                });
                                return;
                            }

                            $q2->whereDoesntHave('tagihan')
                                ->orWhereHas('tagihan', function ($q3) {
                                    $q3->where('status', '!=', 'lunas')
                                        ->where('total', '>', 0);
                                });
                        });
                    }
                });
            })
            ->when(!empty($biayaIds), function ($q) use ($biayaIds) {
                $q->whereHas('tagihan', function ($q2) use ($biayaIds) {
                    $q2->whereIn('biaya_id', $biayaIds);
                });
            })
            ->when(!empty($jenisKelamins), function ($q) use ($jenisKelamins) {
                $q->whereIn('jenis_kelamin', $jenisKelamins);
            })
            ->when(!empty($hasilTes), function ($q) use ($hasilTes) {
                if ($hasilTes === 'BT') {
                    $q->where(function ($q2) {
                        $q2->where('hasil_tes', 'BT')
                            ->orWhereRaw('LOWER(hasil_tes) = ?', ['belum test']);
                    });
                    return;
                }

                $q->where('hasil_tes', $hasilTes);
            })
            ->when($voucherActive, function ($q) {
                $now = Carbon::now();

                $q->whereHas('tagihan', function ($q2) use ($now) {
                    $q2->whereNotNull('voucher_id')
                        ->whereHas('voucher', function ($q3) use ($now) {
                            $q3->where('aktif', true)
                                ->whereDate('tanggal_mulai', '<=', $now)
                                ->whereDate('tanggal_selesai', '>=', $now)
                                ->where(function ($q4) {
                                    $q4->whereNull('maks_penggunaan')
                                        ->orWhereColumn('digunakan', '<', 'maks_penggunaan');
                                });
                        });
                });
            })
            ->when($voucherCode === 'none', function ($q) {
                $q->whereDoesntHave('tagihan', function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->whereNotNull('voucher_id')
                            ->orWhereNotNull('kode_voucher');
                    });
                });
            })
            ->when($voucherCode !== '' && $voucherCode !== 'none', function ($q) use ($voucherCode) {
                $q->whereHas('tagihan', function ($q2) use ($voucherCode) {
                    $q2->where('kode_voucher', $voucherCode);
                });
            })
            ->when(!empty($dateFrom), function ($q) use ($dateFrom) {
                $q->whereHas('registration', function ($q2) use ($dateFrom) {
                    $q2->whereDate('tanggal_daftar', '>=', $dateFrom);
                });
            })
            ->when(!empty($dateTo), function ($q) use ($dateTo) {
                $q->whereHas('registration', function ($q2) use ($dateTo) {
                    $q2->whereDate('tanggal_daftar', '<=', $dateTo);
                });
            })
            ->when(!empty($statusPpdb), function ($q) use ($statusPpdb) {
                $q->whereHas('registration', function ($q2) use ($statusPpdb) {
                    $q2->where('status', $statusPpdb);
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('nama', 'like', "%$search%")
                        ->orWhereHas('registration', function ($q3) use ($search) {
                            $q3->where('nomor_registrasi', 'like', "%$search%");
                        })
                        ->orWhereHas('ibu', function ($q4) use ($search) {
                            $q4->where('nama', 'like', "%$search%")
                                ->orWhere('no_hp', 'like', "%$search%");
                        });
                });
            })
            ->when($order === 'terbaru', function ($q) {
                $q->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
            }, function ($q) {
                $q->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
            })
            ->paginate($perPage)
            ->withQueryString();

        $biayaOptions = Biaya::orderBy('nama_biaya')->get(['id', 'nama_biaya']);

        if ($search || !empty($paymentStatuses) || !empty($biayaIds) || !empty($jenisKelamins) || !empty($hasilTes) || $voucherActive || $voucherCode !== '' || !empty($dateFrom) || !empty($dateTo) || !empty($statusPpdb) || (int) $request->input('page', 1) > 1) {
            logAktivitas(
                'Panitia Public - Lihat Daftar Pendaftaran',
                'Melihat daftar pendaftar dengan filter: '
                . 'kata kunci "' . ($search ?: '-') . '", '
                . 'status pembayaran ' . $this->formatFilterList($paymentStatuses) . ', '
                . 'biaya ID ' . $this->formatFilterList($biayaIds) . ', '
                . 'jenis kelamin ' . $this->formatFilterList($jenisKelamins) . ', '
                . 'hasil test ' . ($hasilTes ?: '-') . ', '
                . 'voucher aktif ' . ($voucherActive ? 'ya' : '-') . ', '
                . 'jenis voucher ' . ($voucherCode ?: '-') . ', '
                . 'rentang tanggal ' . ($dateFrom ?: '-') . ' s/d ' . ($dateTo ?: '-') . ', '
                . 'status PPDB ' . ($statusPpdb ? Registration::statusLabel($statusPpdb) : '-') . ', '
                . 'urutan ' . $order . ', '
                . 'halaman ' . (int) $request->input('page', 1) . '.'
            );
        }

        return view('pendaftaran.list', compact('siswa', 'search', 'paymentStatuses', 'biayaIds', 'jenisKelamins', 'hasilTes', 'voucherActive', 'voucherCode', 'voucherCodeOptions', 'dateFrom', 'dateTo', 'order', 'statusPpdb', 'perPage', 'biayaOptions'));
    }

    public function show($id)
    {
        $siswa = Siswa::with([
            'registration',
            'alamat',
            'ibu',
            'ayah',
            'wali',
            'dataPendukung.paudTk',
            'tagihan.biaya'
        ])->findOrFail($id);

        $showNik = (bool) session()->pull('show_nik_once_' . $id, false);

        logAktivitas(
            'Panitia Public - Lihat Detail Pendaftaran',
            'Membuka detail pendaftar ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . ($siswa->registration->nomor_registrasi ?? '-')
            . ', NIK terlihat: ' . ($showNik ? 'ya' : 'tidak') . ').'
        );

        return view('pendaftaran.detail', compact('siswa', 'showNik'));
    }

    public function showNik($id)
    {
        session(['show_nik_once_' . $id => true]);

        logAktivitas('Panitia Public - Tampilkan NIK', 'Meminta menampilkan NIK untuk siswa ID ' . $id . '.');

        return redirect()->route('pendaftaran.detail', [
            'id' => $id,
        ]);
    }

    public function edit($id)
    {
        $siswa = Siswa::with([
            'registration',
            'alamat',
            'ibu',
            'ayah',
            'wali',
            'dataPendukung.paudTk',
            'tagihan.biaya'
        ])->findOrFail($id);

        logAktivitas(
            'Panitia Public - Buka Form Edit',
            'Membuka form edit data pendaftar ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . ($siswa->registration->nomor_registrasi ?? '-') . ').'
        );

        return view('pendaftaran.detail-edit', compact('siswa'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::with(['registration', 'alamat', 'ibu', 'ayah', 'dataPendukung'])->findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'nisn' => 'nullable|digits_between:1,10|unique:siswa,nisn,' . $siswa->id,
            'nik' => 'required|digits:16|unique:siswa,nik,' . $siswa->id,
            'no_kk' => 'required|digits:16',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'akta_no' => 'nullable|string|max:50',
            'agama' => 'nullable|string|max:30',
            'kewarganegaraan' => 'nullable|string|max:30',
            'berkebutuhan_khusus' => 'nullable|string|max:100',
            'tinggal_bersama' => 'nullable|string|max:30',
            'transportasi' => 'nullable|string|max:50',

            'alamat' => 'nullable|string|max:500',
            'provinsi' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kode_pos' => 'nullable|string|max:10',

            'ibu_nama' => 'nullable|string|max:255',
            'ibu_nik' => 'nullable|string|max:20',
            'ibu_no_hp' => 'nullable|string|max:20',

            'ayah_nama' => 'nullable|string|max:255',
            'ayah_nik' => 'nullable|string|max:20',

            'tinggi' => 'nullable|integer|min:0|max:999',
            'berat' => 'nullable|integer|min:0|max:999',
            'jarak' => ['nullable', 'regex:/^\d{1,4}([.,]\d{1,2})?$/'],
            'jumlah_saudara' => 'nullable|integer|min:0|max:99',
            'anak_ke' => 'nullable|integer|min:1|max:99',
            'hobi' => 'nullable|string|max:255',
            'cita_cita' => 'nullable|string|max:255',
            'alamat_tk' => 'nullable|string|max:255',
        ]);

        try {
            $siswa->update([
                'nama' => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nisn' => $validated['nisn'] ?? null,
                'nik' => $validated['nik'],
                'no_kk' => $validated['no_kk'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'akta_no' => $validated['akta_no'] ?? null,
                'agama' => $validated['agama'] ?? null,
                'kewarganegaraan' => $validated['kewarganegaraan'] ?? null,
                'berkebutuhan_khusus' => $validated['berkebutuhan_khusus'] ?? null,
                'tinggal_bersama' => $validated['tinggal_bersama'] ?? null,
                'transportasi' => $validated['transportasi'] ?? null,
            ]);

            $siswa->alamat()->updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'alamat' => $validated['alamat'] ?? null,
                    'provinsi' => $validated['provinsi'] ?? null,
                    'kabupaten' => $validated['kabupaten'] ?? null,
                    'kecamatan' => $validated['kecamatan'] ?? null,
                    'kelurahan' => $validated['kelurahan'] ?? null,
                    'rt' => $validated['rt'] ?? null,
                    'rw' => $validated['rw'] ?? null,
                    'kode_pos' => $validated['kode_pos'] ?? null,
                ]
            );

            $ibuPayload = [
                'nama' => $validated['ibu_nama'] ?? null,
                'nik' => $validated['ibu_nik'] ?? null,
                'no_hp' => $validated['ibu_no_hp'] ?? null,
            ];
            if (collect($ibuPayload)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty()) {
                $siswa->ibu()->updateOrCreate(['siswa_id' => $siswa->id], $ibuPayload);
            } else {
                $siswa->ibu()->delete();
            }

            $ayahPayload = [
                'nama' => $validated['ayah_nama'] ?? null,
                'nik' => $validated['ayah_nik'] ?? null,
            ];
            if (collect($ayahPayload)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty()) {
                $siswa->ayah()->updateOrCreate(['siswa_id' => $siswa->id], $ayahPayload);
            } else {
                $siswa->ayah()->delete();
            }

            $dataPendukungPayload = [
                'tinggi' => $validated['tinggi'] ?? null,
                'berat' => $validated['berat'] ?? null,
                'jarak' => $this->normalizeJarakValue($validated['jarak'] ?? null),
                'jumlah_saudara' => $validated['jumlah_saudara'] ?? null,
                'anak_ke' => $validated['anak_ke'] ?? null,
                'hobi' => $validated['hobi'] ?? null,
                'cita_cita' => $validated['cita_cita'] ?? null,
                'alamat_tk' => $validated['alamat_tk'] ?? null,
            ];
            if (collect($dataPendukungPayload)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty()) {
                $siswa->dataPendukung()->updateOrCreate(['siswa_id' => $siswa->id], $dataPendukungPayload);
            } else {
                $siswa->dataPendukung()->delete();
            }
        } catch (QueryException $e) {
            $message = strtolower($e->getMessage());
            if (str_contains($message, 'nik')) {
                return back()->withErrors(['nik' => 'NIK sudah terdaftar.'])->withInput();
            }

            return back()->with('error', 'Gagal menyimpan perubahan data. Silakan periksa input lalu coba lagi.')->withInput();
        }

        logAktivitas(
            'Panitia Public - Update Data Pendaftaran',
            'Menyimpan perubahan data pendaftar ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . ($siswa->registration->nomor_registrasi ?? '-') . ').'
        );

        return redirect()
            ->route('pendaftaran.detail', ['id' => $siswa->id])
            ->with('success', 'Data berhasil diperbarui.');
    }

    private function normalizeJarakValue($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        if (!is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    public function terimaPeserta(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama_panitia' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $tahunAjaran = TahunAjaran::where('aktif', 1)->first();
        if (!$tahunAjaran) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun ajaran aktif tidak ditemukan.',
            ], 400);
        }

        $passwordPanitia = PasswordPanitia::where('tahun_ajaran_id', $tahunAjaran->id)->first();
        if (!$passwordPanitia) {
            return response()->json([
                'success' => false,
                'message' => 'Password panitia belum dibuat.',
            ], 400);
        }

        if (!Hash::check($validated['password'], $passwordPanitia->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password panitia tidak sesuai.',
            ], 422);
        }

        $siswa->loadMissing('registration');
        if (!$siswa->registration) {
            return response()->json([
                'success' => false,
                'message' => 'Data registrasi siswa tidak ditemukan.',
            ], 422);
        }

        $statusSebelumnya = (int) ($siswa->registration->status ?? Registration::STATUS_BAKAL_CALON);
        if ($statusSebelumnya === Registration::STATUS_PESERTA_DIDIK) {
            return response()->json([
                'success' => true,
                'message' => 'Data sudah berstatus Peserta Didik.',
                'status' => Registration::STATUS_PESERTA_DIDIK,
            ]);
        }

        [$bolehJadikanPesertaDidik, $pesanValidasi] = $this->validasiRiwayatPembayaranAcuan($siswa);

        if (!$bolehJadikanPesertaDidik) {
            return response()->json([
                'success' => false,
                'message' => $pesanValidasi,
            ], 422);
        }

        if ($statusSebelumnya !== Registration::STATUS_PESERTA_DIDIK) {
            $siswa->registration->status = Registration::STATUS_PESERTA_DIDIK;
            $siswa->registration->save();
        }

        $nomorRegistrasi = $siswa->registration->nomor_registrasi ?? '-';

        logAktivitas(
            'Panitia Public - Jadikan Peserta Didik',
            'Panitia ' . $validated['nama_panitia']
            . ' mengubah status siswa ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . $nomorRegistrasi
            . ') dari ' . Registration::statusLabel($statusSebelumnya)
            . ' menjadi ' . Registration::statusLabel(Registration::STATUS_PESERTA_DIDIK) . '.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menjadikan ' . ($siswa->nama ?? '-')
                . ' (No. Registrasi ' . $nomorRegistrasi . ')'
                . ' sebagai Peserta Didik SD Muhammadiyah Wonorejo.',
            'status' => Registration::STATUS_PESERTA_DIDIK,
        ]);
    }

    public function showBiaya(Siswa $siswa)
    {
        $siswa->load([
            'registration.voucher',
            'ibu',
            'tagihan.biaya'
        ]);

        logAktivitas(
            'Panitia Public - Lihat Pembiayaan',
            'Membuka ringkasan pembiayaan siswa ' . ($siswa->nama ?? '-')
            . ' (ID: ' . $siswa->id
            . ', No Registrasi: ' . ($siswa->registration->nomor_registrasi ?? '-') . ').'
        );

        return view('pendaftaran.pembiayaan.biaya', compact('siswa'));
    }

}
