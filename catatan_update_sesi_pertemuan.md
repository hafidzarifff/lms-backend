# Catatan Pembaruan: Implementasi Lengkap Fitur Sesi Pertemuan & Tugas

**Tanggal:** 12 Juni 2026

## Ringkasan

Pembaruan ini mencakup implementasi lengkap fitur **Sesi Pertemuan** dan **Tugas & Pengumpulan Tugas**, termasuk verifikasi kesesuaian dengan Diagram UML Class, penambahan relasi model, soft delete, serta dokumentasi API.

---

## 📊 Ringkasan Perubahan Hari Ini

### Tabel Database Baru

| No | Tabel | Migration File |
|----|-------|----------------|
| 1 | `sesi_pertemuan` | `2026_06_08_120225_create_sesi_pertemuans_table.php` |
| 2 | `tugas` | `2026_06_11_100000_create_tugas_table.php` |
| 3 | `pengumpulan_tugas` | `2026_06_11_100001_create_pengumpulan_tugas_table.php` |

### Tabel yang Dimodifikasi (Soft Delete)

| No | Tabel | Migration File | Perubahan |
|----|-------|----------------|-----------|
| 1 | `pengguna` | `2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan kolom `deleted_at` |
| 2 | `master_kelas` | `2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan kolom `deleted_at` |
| 3 | `master_mata_kuliah` | `2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan kolom `deleted_at` |
| 4 | `jadwal_perkuliahan` | `2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan kolom `deleted_at` |
| 5 | `peserta_kelas` | `2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan kolom `deleted_at` |
| 6 | `sesi_pertemuan` | `2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan kolom `deleted_at` |

### File Model Baru

| No | File | Keterangan |
|----|------|------------|
| 1 | `app/Models/SesiPertemuan.php` | Model untuk entitas sesi pertemuan |
| 2 | `app/Models/Tugas.php` | Model untuk entitas tugas |
| 3 | `app/Models/PengumpulanTugas.php` | Model untuk entitas pengumpulan tugas |

### File Model yang Dimodifikasi

| No | File | Perubahan |
|----|------|-----------|
| 1 | `app/Models/JadwalPerkuliahan.php` | Menambahkan method `sesiPertemuan()` (relasi `hasMany` ke `SesiPertemuan`) |
| 2 | `app/Models/Pengguna.php` | Menambahkan trait `SoftDeletes` |
| 3 | `app/Models/MasterKelas.php` | Menambahkan trait `SoftDeletes` |
| 4 | `app/Models/MasterMataKuliah.php` | Menambahkan trait `SoftDeletes` |
| 5 | `app/Models/PesertaKelas.php` | Menambahkan trait `SoftDeletes` |
| 6 | `app/Models/SesiPertemuan.php` | Menambahkan trait `SoftDeletes`, relasi `tugas()` |

### File Controller Baru

| No | File | Keterangan |
|----|------|------------|
| 1 | `app/Http/Controllers/SesiPertemuanController.php` | CRUD endpoint untuk sesi pertemuan |
| 2 | `app/Http/Controllers/Api/TugasController.php` | CRUD endpoint untuk tugas & pengumpulan |
| 3 | `app/Http/Controllers/Api/AdminTugasController.php` | Endpoint admin untuk manajemen tugas |

### File Request Validation Baru

| No | File | Keterangan |
|----|------|------------|
| 1 | `app/Http/Requests/StoreSesiPertemuanRequest.php` | Validasi untuk membuat sesi pertemuan |
| 2 | `app/Http/Requests/UpdateSesiPertemuanRequest.php` | Validasi untuk memperbarui sesi pertemuan |

### File Routes yang Dimodifikasi

| No | File | Perubahan |
|----|------|-----------|
| 1 | `routes/api.php` | Menambahkan endpoint sesi pertemuan (CRUD) dan tugas & pengumpulan (11 endpoint) |

### File Migrasi Baru

| No | File | Keterangan |
|----|------|------------|
| 1 | `database/migrations/2026_06_08_120225_create_sesi_pertemuans_table.php` | Membuat tabel `sesi_pertemuan` |
| 2 | `database/migrations/2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Menambahkan `deleted_at` ke 6 tabel |
| 3 | `database/migrations/2026_06_11_100000_create_tugas_table.php` | Membuat tabel `tugas` |
| 4 | `database/migrations/2026_06_11_100001_create_pengumpulan_tugas_table.php` | Membuat tabel `pengumpulan_tugas` |

### File Dokumentasi

| No | File | Keterangan |
|----|------|------------|
| 1 | `api_documentation/API_DOCUMENTATION.md` | Menambahkan dokumentasi endpoint sesi pertemuan (#31-35) dan tugas (#36-46) |

### File Catatan Lama yang Dihapus

| No | File | Alasan |
|----|------|--------|
| 1 | `catatan_update_sesi_pertemuan.md` | Digabung ke catatan ini |
| 2 | `catatan_update_rud_sesi_pertemuan.md` | Digabung ke catatan ini |
| 3 | `CHANGELOG-soft-delete.md` | Informasi sudah ada di catatan ini |

---

## 1. Fitur Sesi Pertemuan

### 1.1 Struktur Tabel `sesi_pertemuan`

Tabel ini menyimpan data sesi pertemuan untuk setiap jadwal perkuliahan.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_sesi` | UUID | Primary Key |
| `id_jadwal` | UUID | Foreign Key → `jadwal_perkuliahan(id_jadwal)` ON DELETE CASCADE |
| `pertemuan_ke` | INT | Nomor pertemuan (1, 2, 3, ...) |
| `judul_sesi` | VARCHAR(100) | Topik/judul sesi |
| `tanggal_pelaksanaan` | DATE | Tanggal pelaksanaan |
| `jam_mulai` | TIME | Waktu mulai |
| `jam_berakhir` | TIME | Waktu berakhir |
| `metode_pertemuan` | ENUM | `synchronous` atau `asynchronous` |
| `link_kelas_daring` | TEXT | Link video conference (nullable, required if synchronous) |
| `created_at` | TIMESTAMP | Waktu pembuatan |
| `updated_at` | TIMESTAMP | Waktu pembaruan |
| `deleted_at` | TIMESTAMP | Waktu penghapusan (soft delete) |

### 1.2 Endpoint API Sesi Pertemuan

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `POST` | `/api/sesi-pertemuan` | Membuat sesi pertemuan baru |
| `GET` | `/api/sesi-pertemuan` | Menampilkan daftar sesi dengan filter |
| `GET` | `/api/sesi-pertemuan/{id_sesi}` | Menampilkan detail sesi |
| `PUT` | `/api/sesi-pertemuan/{id_sesi}` | Memperbarui data sesi |
| `DELETE` | `/api/sesi-pertemuan/{id_sesi}` | Menghapus sesi (soft delete) |

**Query Parameters (GET list):**
- `id_jadwal` - Filter berdasarkan jadwal
- `tanggal` - Filter berdasarkan tanggal
- `metode_pertemuan` - Filter berdasarkan metode
- `per_page` - Jumlah data per halaman (default: 10)

### 1.3 Validasi Bisnis Sesi Pertemuan

1. **Anti-Duplikasi Pertemuan Ke**
   Tidak boleh ada dua sesi dengan `pertemuan_ke` yang sama untuk `id_jadwal` yang sama.

2. **Anti-Overlap Waktu**
   Tidak boleh ada dua sesi yang waktunya beririsan untuk `id_jadwal` dan `tanggal_pelaksanaan` yang sama.

3. **Link Kelas Daring**
   Field `link_kelas_daring` wajib diisi jika `metode_pertemuan = synchronous`.

4. **ID Jadwal Tidak Dapat Diubah**
   Pada endpoint UPDATE, field `id_jadwal` bersifat `prohibited` untuk mencegah perpindahan sesi ke jadwal lain.

5. **Tanggal Masa Lalu**
   Pada endpoint CREATE, `tanggal_pelaksanaan` harus ≥ hari ini. Pada UPDATE, tanggal lampau diperbolehkan.

---

## 2. Fitur Tugas & Pengumpulan Tugas

### 2.1 Struktur Tabel `tugas`

Tabel ini menyimpan data tugas yang diberikan oleh dosen untuk setiap sesi pertemuan.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_tugas` | UUID | Primary Key |
| `id_sesi` | UUID | Foreign Key → `sesi_pertemuan(id_sesi)` ON DELETE CASCADE |
| `judul` | VARCHAR(200) | Judul tugas |
| `deskripsi` | TEXT | Deskripsi/detail tugas (nullable) |
| `deadline` | DATETIME | Batas waktu pengumpulan |
| `created_at` | TIMESTAMP | Waktu pembuatan |
| `updated_at` | TIMESTAMP | Waktu pembaruan |
| `deleted_at` | TIMESTAMP | Waktu penghapusan (soft delete) |

### 2.2 Struktur Tabel `pengumpulan_tugas`

Tabel ini menyimpan data pengumpulan tugas oleh mahasiswa.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_pengumpulan` | UUID | Primary Key |
| `id_tugas` | UUID | Foreign Key → `tugas(id_tugas)` ON DELETE CASCADE |
| `id_mahasiswa` | UUID | Foreign Key → `pengguna(id_user)` ON DELETE CASCADE |
| `file_url` | VARCHAR(500) | Path file tugas yang diunggah |
| `nilai` | INT | Nilai dari dosen (0-100, nullable) |
| `catatan_dosen` | TEXT | Catatan dari dosen (nullable) |
| `created_at` | TIMESTAMP | Waktu pengumpulan |
| `updated_at` | TIMESTAMP | Waktu pembaruan |
| `deleted_at` | TIMESTAMP | Waktu penghapusan (soft delete) |

**Constraint:** Unique pada kombinasi `[id_tugas, id_mahasiswa]` — mahasiswa hanya bisa mengumpulkan 1 kali per tugas.

### 2.3 Endpoint API Tugas & Pengumpulan

**Dosen:**

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `POST` | `/api/sesi/{sesi_id}/tugas` | Membuat tugas baru di sesi |
| `PUT` | `/api/tugas/{id}` | Memperbarui tugas |
| `DELETE` | `/api/tugas/{id}` | Menghapus tugas (soft delete) |
| `GET` | `/api/tugas/{id}/pengumpulan` | Daftar pengumpulan tugas |
| `PUT` | `/api/pengumpulan/{id}/nilai` | Memberi nilai pengumpulan |

**Dosen & Mahasiswa:**

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/api/sesi/{sesi_id}/tugas` | Daftar tugas di sesi |
| `GET` | `/api/tugas/{id}` | Detail tugas |

**Mahasiswa:**

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `POST` | `/api/tugas/{id}/kumpul` | Mengumpulkan tugas (upload file) |
| `GET` | `/api/tugas/{id}/pengumpulan/saya` | Status pengumpulan sendiri |

**Admin:**

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/api/admin/tugas` | Daftar semua tugas lintas sesi |
| `DELETE` | `/api/admin/pengumpulan/{id}` | Hapus pengumpulan (admin) |

### 2.4 Validasi Bisnis Tugas

1. **Hanya Dosen Pengampu** — Hanya dosen yang mengampu jadwal perkuliahan terkait yang dapat membuat, mengedit, atau menghapus tugas.
2. **Deadline Validasi** — Mahasiswa tidak dapat mengumpulkan tugas setelah deadline terlewat.
3. **Single Submission** — Mahasiswa hanya dapat mengumpulkan tugas 1 kali (unique constraint).
4. **File Upload** — Maksimal ukuran file 10 MB.
5. **Nilai Range** — Nilai yang diberikan harus antara 0-100.
6. **Soft Delete** — Penghapusan tugas dan pengumpulan menggunakan soft delete.

---

## 3. Fitur Soft Delete

Soft delete telah ditambahkan ke semua tabel utama untuk mencegah kehilangan data permanen.

| Tabel | Keterangan |
|-------|------------|
| `pengguna` | Data user yang dihapus bisa dipulihkan |
| `master_kelas` | Data kelas yang dihapus bisa dipulihkan |
| `master_mata_kuliah` | Data mata kuliah yang dihapus bisa dipulihkan |
| `jadwal_perkuliahan` | Data jadwal yang dihapus bisa dipulihkan |
| `peserta_kelas` | Data peserta yang dihapus bisa dipulihkan |
| `sesi_pertemuan` | Data sesi yang dihapus bisa dipulihkan |
| `tugas` | Data tugas yang dihapus bisa dipulihkan |
| `pengumpulan_tugas` | Data pengumpulan yang dihapus bisa dipulihkan |

**Cara Kerja:**
- **Menghapus:** Endpoint `DELETE` akan mengisi kolom `deleted_at` dengan timestamp.
- **Query:** Eloquent otomatis menyembunyikan data yang sudah soft-deleted.
- **Restore:** Gunakan `$model->restore()` untuk memulihkan data.
- **Force Delete:** Gunakan `$model->forceDelete()` untuk menghapus permanen.

---

## 4. Relasi Model (Lengkap)

### SesiPertemuan
- `belongsTo` → `JadwalPerkuliahan` (via `id_jadwal`)
- `hasMany` → `Tugas` (via `id_sesi`)

### JadwalPerkuliahan
- `hasMany` → `SesiPertemuan` (via `id_jadwal`) ✅ **DIPERBAIKI HARI INI**
- `hasMany` → `PesertaKelas` (via `id_jadwal`)
- `belongsTo` → `MasterMataKuliah` (via `id_mk`)
- `belongsTo` → `MasterKelas` (via `id_kelas`)
- `belongsTo` → `Pengguna/Dosen` (via `id_dosen`)

### Tugas
- `belongsTo` → `SesiPertemuan` (via `id_sesi`)
- `hasMany` → `PengumpulanTugas` (via `id_tugas`)

### PengumpulanTugas
- `belongsTo` → `Tugas` (via `id_tugas`)
- `belongsTo` → `Pengguna/Mahasiswa` (via `id_mahasiswa`)

---

## 5. Verifikasi UML vs Implementasi

| Aspek | Status |
|-------|--------|
| Struktur tabel `sesi_pertemuan` (12 kolom termasuk `deleted_at`) | ✅ Sesuai |
| Primary Key `id_sesi` (UUID) | ✅ Sesuai |
| Foreign Key `id_jadwal` ke `jadwal_perkuliahan` (CASCADE) | ✅ Sesuai |
| Field `pertemuan_ke`, `judul_sesi`, `tanggal_pelaksanaan` | ✅ Sesuai |
| Field `jam_mulai`, `jam_berakhir` (time) | ✅ Sesuai |
| Enum `metode_pertemuan` (synchronous/asynchronous) | ✅ Sesuai |
| Field `link_kelas_daring` (nullable, required if synchronous) | ✅ Sesuai |
| Timestamps `created_at`, `updated_at` | ✅ Sesuai |
| Soft Delete `deleted_at` | ✅ Sesuai |
| Method CRUD (create, read, update, delete) | ✅ Sesuai |
| Method utility (markAsSynchronous, markAsAsynchronous, setSesiLink) | ✅ Sesuai |
| Relasi `SesiPertemuan → JadwalPerkuliahan` (belongsTo) | ✅ Sesuai |
| Relasi `SesiPertemuan → Tugas` (hasMany) | ✅ Sesuai |
| Relasi `JadwalPerkuliahan → SesiPertemuan` (hasMany) | ✅ Sesuai (diperbaiki hari ini) |
| Validasi anti-duplikasi `pertemuan_ke` per jadwal | ✅ Sesuai |
| Validasi anti-overlap waktu | ✅ Sesuai |

✅ **Fitur Sesi Pertemuan telah 100% sesuai dengan Diagram UML Class.**

---

## 6. Kesimpulan

**Total Implementasi:**
- 3 tabel baru (`sesi_pertemuan`, `tugas`, `pengumpulan_tugas`)
- 6 tabel dimodifikasi (soft delete)
- 3 model baru (`SesiPertemuan`, `Tugas`, `PengumpulanTugas`)
- 6 model dimodifikasi (soft delete + relasi)
- 3 controller baru (`SesiPertemuanController`, `TugasController`, `AdminTugasController`)
- 2 request validation baru (`StoreSesiPertemuanRequest`, `UpdateSesiPertemuanRequest`)
- 16 endpoint API baru (5 sesi pertemuan + 11 tugas & pengumpulan)
- 1 relasi model diperbaiki (`JadwalPerkuliahan → SesiPertemuan`)
- 1 dokumentasi API diperbarui (`API_DOCUMENTATION.md`)
