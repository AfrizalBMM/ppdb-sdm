# PPDB SD Muhammadiyah

Sistem Penerimaan Peserta Didik Baru  
Dibangun menggunakan **Laravel 10**

## Cara Install

1. git clone repo ini
2. composer install
3. copy .env.example ke .env
4. php artisan key:generate
5. php artisan migrate

## Catatan Perubahan Terbaru (28 April 2026)

- Hardening alur voucher dan pembiayaan:
    - Validasi voucher sekarang konsisten memakai aturan bisnis model `Voucher`, termasuk dukungan voucher dengan kuota tak terbatas.
    - Perhitungan voucher pada pendaftaran baru dan edit pendaftar diperketat agar voucher tidak aktif, habis periode, atau habis kuota tidak bisa tersimpan sebagai voucher aktif.
    - Proteksi tambahan pada penyimpanan pembayaran public agar field wajib seperti `tanggal_bayar` dan `metode` selalu sesuai schema database.

- Hardening akses public:
    - Endpoint cetak nota pembayaran dan nota rincian biaya dipindahkan ke jalur yang dilindungi middleware `akses_pembayaran`.
    - Validasi redirect pada verifikasi password public diperketat agar tidak membuka celah open redirect ke URL eksternal.

- Perbaikan stabilitas form pendaftaran:
    - Penanganan error database untuk kasus duplikasi NIK dibuat lebih ramah pengguna.
    - Guard tambahan pada akses schema `data_pendukung` agar form tidak mudah patah jika struktur tabel belum lengkap di environment tertentu.
    - Validasi edit legacy/public diselaraskan dengan schema database untuk NIK, No KK, dan field numerik pendukung.

- Perbaikan statistik keuangan:
    - Statistik keuangan public kini otomatis dibatasi ke **tahun ajaran aktif** saat filter tanggal kosong, sehingga label periode dan data yang dihitung tetap konsisten.

- Optimasi export admin:
    - Export keuangan siswa diperbaiki untuk menghindari query N+1 pada relasi `tagihan.biaya`.
    - Ditambahkan batas aman export **2000 baris** untuk export siswa/keuangan agar request tidak mudah kehabisan memori atau timeout.
    - Quick edit pendaftar admin sekarang ikut memvalidasi NIK unik sehingga tidak rawan error database saat bentrok.

- Sinkronisasi dokumentasi kode dengan schema:
    - Setup testing lama di repo telah dibersihkan, termasuk file `.env.testing`, `phpunit.xml`, dan folder `tests/`.
    - Field phantom `wali.alamat` di export/model dihapus karena tidak didukung schema migration yang aktif.

## Catatan Perubahan Terbaru (14 April 2026)

- **Optimasi Penebak Kode Pos AI (Groq):**
    - Upgrade model ke **`llama-3.3-70b-versatile`** untuk kecerdasan dan akurasi yang lebih tinggi.
    - Implementasi **Few-Shot Prompting**: Memberikan contoh wilayah yang mirip (seperti Wonorejo di Karanganyar vs Sukoharjo) agar AI tidak salah memberikan kode pos.
    - Pengaturan `temperature` ke **0** untuk hasil yang konsisten (deterministik).
    - Penanganan khusus untuk wilayah **Mojolaban** (Cangkol, Wonorejo, dsb) agar kode pos 57554/57555 tidak tertukar.
    - **UI Verification Logic**:
        - Penambahan indikator visual (badge dengan pulse animation) saat kode pos diisi oleh AI.
        - Peringatan bagi pengguna untuk melakukan verifikasi ulang demi akurasi.
        - Auto-reset status tanda "Ditebak AI" jika pengguna melakukan koreksi manual pada kolom kode pos.
    - **Logging & Debugging**: Penambahan sistem log di `laravel.log` untuk setiap input dan respon AI guna mempermudah audit akurasi data.

## Catatan Perubahan Sebelumnya (04 April 2026)

- Export PDF & Excel keuangan siswa:
    - Layout tabel modern, zebra striping, dan font besar.
    - Header dua baris (multi-row heading):
        - Baris 1: kolom utama, "Jenis pembayaran" merge cell (colspan 3)
        - Baris 2: subkolom P, DU, UDP
    - Merge cell otomatis di Excel agar header rapi dan identik dengan PDF
    - Tidak ada header double di Excel
    - Kolom dan urutan data Excel identik dengan PDF
    - Baris total kekurangan semua peserta didik otomatis di bawah data
    - Header berisi judul, kelas, waktu cetak, dan sumber sistem (PDF)
    - Nama kelas pada judul otomatis sesuai filter export
    - Penamaan jenis biaya sudah konsisten dengan enum migrasi
    - Perhitungan kekurangan diambil dari sisa tagihan per jenis biaya

- Redesign total halaman admin/pendaftar agar lebih modern dan konsisten.
- Dropdown filter dan export kini tetap berada di atas tabel/card, tidak terpotong konten.
- Sticky footer/pagination selalu rata dengan card, tidak lagi ikut lebar tabel.
- Penambahan identitas sistem "Sistem Pendaftaran Peserta Didik Baru - SD Muhammadiyah Wonorejo" di bawah tabel.
- Perbaikan z-index dan stacking context pada dropdown, modal, dan overlay.
- Standarisasi tombol aksi (dropdown tiga titik) di tabel pendaftar.
- Perbaikan UX filter, export, dan quick edit.
- Optimalisasi responsif dan tampilan mobile.
