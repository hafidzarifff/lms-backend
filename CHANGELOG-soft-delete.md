# Changelog — Implementasi Soft Delete

**Tanggal:** 11 Juni 2026

## Ringkasan

Menambahkan fitur **soft delete** pada seluruh model agar data yang dihapus tidak hilang permanen dari database, melainkan hanya ditandai dengan timestamp pada kolom `deleted_at`.

---

## Fitur Baru

- **Soft Delete** — Semua data yang dihapus melalui endpoint `DELETE` kini tetap tersimpan di database dengan kolom `deleted_at` terisi timestamp penghapusan. Data dapat dipulihkan kapan saja.

## File Baru

| File | Keterangan |
|------|-----------|
| `database/migrations/2026_06_11_000000_add_soft_deletes_to_all_tables.php` | Migration untuk menambah kolom `deleted_at` pada 6 tabel |

## File yang Diperbarui

| File | Perubahan |
|------|----------|
| `app/Models/Pengguna.php` | Menambahkan trait `SoftDeletes` |
| `app/Models/MasterKelas.php` | Menambahkan trait `SoftDeletes` |
| `app/Models/MasterMataKuliah.php` | Menambahkan trait `SoftDeletes` |
| `app/Models/JadwalPerkuliahan.php` | Menambahkan trait `SoftDeletes` |
| `app/Models/PesertaKelas.php` | Menambahkan trait `SoftDeletes` |
| `app/Models/SesiPertemuan.php` | Menambahkan trait `SoftDeletes` |

## Tabel yang Terdampak

- `pengguna`
- `master_kelas`
- `master_mata_kuliah`
- `jadwal_perkuliahan`
- `peserta_kelas`
- `sesi_pertemuan`

## Cara Kerja

- **Menghapus data:** Panggil endpoint `DELETE` seperti biasa → record tetap ada di database, kolom `deleted_at` terisi.
- **Melihat data:** Endpoint `GET` (index/show) otomatis menyembunyikan data yang sudah soft-deleted.
- **Memulihkan data:** Gunakan `$record->restore()` untuk membatalkan penghapusan.
- **Hapus permanen:** Gunakan `$record->forceDelete()` untuk menghapus record sepenuhnya dari database.
