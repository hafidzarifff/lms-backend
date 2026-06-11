# Catatan Pembaruan: Implementasi Fitur Presensi Mahasiswa & Materi Pembelajaran

**Tanggal:** 11 Juni 2026

## Ringkasan

Pembaruan ini menambahkan dua fitur utama ke sistem LMS:
1. **Presensi Mahasiswa** - Mencatat kehadiran mahasiswa per sesi pertemuan
2. **Materi Pembelajaran** - Upload dan kelola materi pembelajaran (file PDF, video, link) per sesi

Kedua fitur ini terhubung langsung ke **SesiPertemuan**, sehingga setiap sesi dapat memiliki:
- Data presensi mahasiswa
- Materi pembelajaran (bisa lebih dari 1 materi per sesi)
- Tugas dan pengumpulan tugas (sudah ada sebelumnya)

---

## 📁 File Baru yang Ditambahkan

### Model (Database Layer)

| File | Fungsi |
|------|--------|
| `app/Models/Presensi.php` | Model untuk data presensi mahasiswa per sesi |
| `app/Models/MateriPembelajaran.php` | Model untuk data materi pembelajaran per sesi |

### Controller (Logic Layer)

| File | Fungsi |
|------|--------|
| `app/Http/Controllers/Api/PresensiController.php` | Mengelola presensi: catat, update status, hitung persentase, rekap kehadiran |
| `app/Http/Controllers/Api/MateriPembelajaranController.php` | Mengelola materi: upload, update, hapus, generate link download |

### Database Migration

| File | Fungsi |
|------|--------|
| `database/migrations/2026_06_11_183611_create_presensi_table.php` | Membuat tabel presensi |
| `database/migrations/2026_06_11_183726_create_materi_pembelajaran_table.php` | Membuat tabel materi_pembelajaran |

---

## 📝 File yang Dimodifikasi

| File | Perubahan |
|------|-----------|
| `app/Models/SesiPertemuan.php` | Tambah relasi ke Presensi dan MateriPembelajaran |
| `routes/api.php` | Tambah 11 endpoint baru (6 presensi + 5 materi) |

---

## 1. Fitur Presensi Mahasiswa

### Apa itu Presensi?
Presensi adalah pencatatan kehadiran mahasiswa untuk setiap sesi pertemuan. Dosen dapat mencatat status kehadiran: hadir, izin, sakit, atau alpha.

### Struktur Tabel

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_presensi` | UUID PK | Primary key |
| `id_sesi` | UUID FK | Foreign key ke sesi_pertemuan |
| `id_peserta` | UUID FK | Foreign key ke peserta_kelas |
| `status_kehadiran` | ENUM | hadir / izin / sakit / alpha |
| `created_at` | TIMESTAMP | Waktu pencatatan |
| `updated_at` | TIMESTAMP | Waktu update |
| `deleted_at` | TIMESTAMP | Soft delete |

**Constraint:** Unique (id_sesi, id_peserta) - mencegah duplikasi presensi

### Endpoint API (6 endpoint)

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/presensi/catat` | Catat presensi mahasiswa |
| PUT | `/api/presensi/{id}/status` | Update status kehadiran |
| GET | `/api/presensi/sesi/{id_sesi}` | Lihat presensi per sesi |
| GET | `/api/presensi/peserta/{id_peserta}` | Lihat presensi per mahasiswa |
| POST | `/api/presensi/persentase` | Hitung persentase kehadiran |
| POST | `/api/presensi/rekap` | Rekap kehadiran per jadwal |

### Method di Controller

1. **`catat(Request $request)`**
   - Input: id_sesi, id_peserta, status_kehadiran
   - Validasi: tidak boleh duplikat presensi
   - Response: data presensi yang baru dibuat

2. **`updateStatus(Request $request, $id)`**
   - Input: status_kehadiran
   - Update status presensi yang sudah ada

3. **`getBySesi($id_sesi)`**
   - Get semua presensi untuk sesi tertentu
   - Include data mahasiswa

4. **`getByPeserta($id_peserta)`**
   - Get semua presensi untuk mahasiswa tertentu
   - Include data sesi

5. **`hitungPersentase(Request $request)`**
   - Input: id_peserta, id_jadwal
   - Hitung persentase kehadiran mahasiswa di jadwal tertentu
   - Response: persentase, jumlah hadir, total sesi

6. **`rekapKehadiran(Request $request)`**
   - Input: id_jadwal
   - Rekap kehadiran semua mahasiswa di jadwal tertentu
   - Response: daftar mahasiswa dengan status (hadir, izin, sakit, alpha) dan persentase

---

## 2. Fitur Materi Pembelajaran

### Apa itu Materi Pembelajaran?
Materi pembelajaran adalah file atau link yang diupload oleh dosen untuk setiap sesi pertemuan. Satu sesi bisa memiliki beberapa materi.

### Struktur Tabel

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_materi` | UUID PK | Primary key |
| `id_sesi` | UUID FK | Foreign key ke sesi_pertemuan |
| `judul_materi` | VARCHAR(200) | Judul materi |
| `file_materi` | VARCHAR(500) | Path file (nullable) |
| `link_video_pembelajaran` | VARCHAR(500) | Link video (nullable) |
| `created_at` | TIMESTAMP | Waktu upload |
| `updated_at` | TIMESTAMP | Waktu update |
| `deleted_at` | TIMESTAMP | Soft delete |

### Endpoint API (5 endpoint)

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/materi/upload` | Upload materi baru |
| PUT | `/api/materi/{id}` | Update materi |
| DELETE | `/api/materi/{id}` | Hapus materi (soft delete) |
| GET | `/api/materi/sesi/{id_sesi}` | Lihat materi per sesi |
| GET | `/api/materi/{id}/download` | Generate link download |

### Method di Controller

1. **`upload(Request $request)`**
   - Input: id_sesi, judul_materi, file_materi (optional), link_video_pembelajaran (optional)
   - Upload file ke storage dan simpan path-nya
   - Max file size: 50MB
   - Response: data materi yang baru diupload

2. **`update(Request $request, $id)`**
   - Input: judul_materi (optional), file_materi (optional), link_video_pembelajaran (optional)
   - Update data materi
   - Jika ada file baru, file lama akan dihapus

3. **`hapus($id)`**
   - Soft delete materi
   - File di storage juga akan dihapus

4. **`getBySesi($id_sesi)`**
   - Get semua materi untuk sesi tertentu

5. **`generateLinkDownload($id)`**
   - Generate temporary download URL (valid 1 jam)
   - Response: download_url dan expired_at

---

## 3. Relasi Antar Tabel

```
SesiPertemuan
├── presensi() - hasMany Presensi
├── materiPembelajaran() - hasMany MateriPembelajaran
└── tugas() - hasMany Tugas (sudah ada)
```

### Detail Relasi

- **SesiPertemuan → Presensi**: 1 sesi bisa memiliki banyak presensi (1 per mahasiswa)
- **SesiPertemuan → MateriPembelajaran**: 1 sesi bisa memiliki banyak materi
- **SesiPertemuan → Tugas**: 1 sesi bisa memiliki banyak tugas (sudah ada sebelumnya)

---

## 4. Struktur Lengkap per Sesi

Setiap **SesiPertemuan** sekarang dapat memiliki:

1. **Presensi Mahasiswa**
   - Daftar mahasiswa yang hadir/izin/sakit/alpha
   - Perhitungan persentase kehadiran

2. **Materi Pembelajaran**
   - File PDF/DOC (upload)
   - Video (upload atau link)
   - Link pembelajaran

3. **Tugas**
   - Tugas dari dosen
   - Pengumpulan tugas dari mahasiswa
   - Penilaian

---

## 5. Endpoint API Lengkap (Update 11 Juni 2026)

### Presensi Mahasiswa (6 endpoint)
- POST `/api/presensi/catat`
- PUT `/api/presensi/{id}/status`
- GET `/api/presensi/sesi/{id_sesi}`
- GET `/api/presensi/peserta/{id_peserta}`
- POST `/api/presensi/persentase`
- POST `/api/presensi/rekap`

### Materi Pembelajaran (5 endpoint)
- POST `/api/materi/upload`
- PUT `/api/materi/{id}`
- DELETE `/api/materi/{id}`
- GET `/api/materi/sesi/{id_sesi}`
- GET `/api/materi/{id}/download`

---

## 6. Kesimpulan

Pembaruan ini melengkapi fitur **SesiPertemuan** dengan:
- ✅ **Presensi Mahasiswa** - Pencatatan kehadiran lengkap dengan rekap
- ✅ **Materi Pembelajaran** - Upload dan kelola materi (file & video)
- ✅ **Tugas** - Sudah ada sebelumnya (tugas & pengumpulan)

Sekarang setiap sesi pertemuan memiliki semua komponen yang dibutuhkan untuk pembelajaran online/offline.

**Total endpoint baru:** 11 endpoint (6 presensi + 5 materi)
