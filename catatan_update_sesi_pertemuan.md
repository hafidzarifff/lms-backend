# Dokumentasi Pembaruan: API Sesi Pertemuan

Dokumen ini merangkum secara komprehensif penambahan fitur "Sesi Pertemuan" pada backend Learning Management System (LMS), termasuk struktur basis data, modifikasi berkas, dan fungsi logika bisnis yang diimplementasikan.

---

## 1. Penambahan Struktur Basis Data (Tabel Baru)

Sebuah tabel baru telah ditambahkan ke dalam skema basis data untuk mengelola sesi pertemuan kelas.

**Nama Tabel:** `sesi_pertemuan`

### Detail Kolom dan Fungsi

| Nama Kolom | Tipe Data | Atribut Tambahan | Fungsi & Tujuan |
| :--- | :--- | :--- | :--- |
| `id_sesi` | UUID | Primary Key | Mengidentifikasi setiap sesi secara unik dan aman (UUIDv4) untuk mencegah enumerasi. |
| `id_jadwal` | UUID | Foreign Key | Merelasikan sesi ke entitas `jadwal_perkuliahan`. Memiliki sifat `ON DELETE CASCADE`. |
| `pertemuan_ke` | Integer | Not Null | Menyimpan urutan pertemuan (misal: 1, 2, 3... 16). |
| `judul_sesi` | String | Max 100 Char | Memberikan penamaan atau topik bahasan pada sesi tersebut. |
| `tanggal_pelaksanaan` | Date | Not Null | Menentukan hari spesifik pelaksanaan kelas. |
| `jam_mulai` | Time | Not Null | Menandai waktu pembukaan sesi pertemuan. |
| `jam_berakhir` | Time | Not Null | Menandai waktu penutupan sesi pertemuan. |
| `metode_pertemuan` | Enum | 'synchronous', 'asynchronous' | Menentukan apakah sesi berjalan secara tatap muka virtual (real-time) atau mandiri. |
| `link_kelas_daring` | Text | Nullable | Menyimpan tautan *video conference*. Wajib diisi jika sinkron, dan boleh kosong jika asinkron. |
| `created_at` | Timestamp | - | Mencatat waktu pembuatan data sesi. |
| `updated_at` | Timestamp | - | Mencatat waktu pembaruan data sesi. |

---

## 2. Rincian Modifikasi Berkas

Berikut adalah rincian fungsionalitas per berkas yang ditambahkan/dimodifikasi dalam sistem:

### A. Berkas Migrasi Database
**Path:** `database/migrations/2026_06_08_120225_create_sesi_pertemuans_table.php`
- **Tindakan:** Dibuat Baru
- **Fungsi:** 
  - Mengeksekusi DDL (*Data Definition Language*) untuk membangun tabel `sesi_pertemuan`.
  - Membangun referensi *Foreign Key* antara `id_jadwal` ke tabel utama perkuliahan.
  - Mengamankan konsistensi struktur data melalui method `up()` dan `down()`.

### B. Berkas Model ORM
**Path:** `app/Models/SesiPertemuan.php`
- **Tindakan:** Dibuat Baru
- **Fungsi:** 
  - Mengimplementasikan `HasUuids` trait untuk menyuntikkan UUID versi 4 ke `id_sesi`.
  - Menolak increment bawaan integer dengan mematikan auto-increment.
  - Mendaftarkan atribut *mass-assignable* pada property `$fillable`.
  - Menyediakan relasi `jadwalPerkuliahan()` tipe `BelongsTo`.

### C. Berkas Validasi HTTP Request
**Path:** `app/Http/Requests/StoreSesiPertemuanRequest.php`
- **Tindakan:** Dibuat Baru
- **Fungsi Validasi Ketat:**
  - **Eksistensi Jadwal:** Memastikan `id_jadwal` valid via rules `exists:jadwal_perkuliahan,id_jadwal`.
  - **Kronologi Waktu:** Memaksa `jam_berakhir` bernilai setelah `jam_mulai`.
  - **Batas Tanggal:** Menahan input tanggal lampau via `after_or_equal:today`.
  - **Regulasi Tautan:** Validasi bersyarat `required_if:metode_pertemuan,synchronous` untuk tautan kelas.

### D. Berkas Controller API
**Path:** `app/Http/Controllers/SesiPertemuanController.php`
- **Tindakan:** Dibuat Baru
- **Fungsi Logika Bisnis:**
  - **Proteksi Duplikasi:** Memeriksa nomor `pertemuan_ke` agar tidak ganda pada jadwal yang sama.
  - **Algoritma Anti-Overlap:** Menganalisis eksistensi sesi lain di tanggal dan jadwal yang sama untuk mencegah bentrok jam.
  - **Standardisasi Response:** Mengemas *output* API menjadi struktur JSON bersih memuat `status`, `message`, dan `data`.

---

## 3. Penambahan Fitur Peserta Kelas (Update Sebelumnya)

Berikut adalah rincian file yang ditambahkan dan dimodifikasi untuk fitur pendaftaran peserta kelas:

### A. Berkas Migrasi Database
**Path:** `database/migrations/2026_06_04_145500_create_peserta_kelas_table.php`
- **Tindakan:** Dibuat Baru

### B. Berkas Model ORM
**Path:** `app/Models/PesertaKelas.php`
- **Tindakan:** Dibuat Baru

**Path:** `app/Models/JadwalPerkuliahan.php`
- **Tindakan:** Dimodifikasi (Relasi)

### C. Berkas Validasi HTTP Request
**Path:** `app/Http/Requests/EnrollKelasRequest.php`
- **Tindakan:** Dibuat Baru

### D. Berkas Controller API
**Path:** `app/Http/Controllers/Api/PesertaKelasController.php`
- **Tindakan:** Dibuat Baru

### E. Berkas Route API
**Path:** `routes/api.php`
- **Tindakan:** Dimodifikasi (Endpoint Enrollment)

### F. Dokumentasi API
**Path:** `api_documentation/API_DOCUMENTATION.md`
- **Tindakan:** Dimodifikasi (Dokumentasi Endpoint)
