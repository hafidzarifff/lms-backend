# Catatan Update Sesi Pertemuan

**Tanggal:** 12 Juni 2026  
**Update Terakhir:** 12 Juni 2026

---

## Ringkasan

Update ini menambahkan 3 fitur utama ke Sesi Pertemuan:

1. **Presensi Mahasiswa** - Pencatatan kehadiran digital
2. **Materi Pembelajaran** - Upload dan distribusi materi
3. **Tugas dengan CBT** - Manajemen tugas dengan dukungan ujian online

**Total:** 26 endpoint API, 4 tabel database

---

## Sesi Pertemuan (7 endpoint)

Sesi Pertemuan adalah pusat dari semua aktivitas pembelajaran. Setiap mata kuliah memiliki beberapa sesi pertemuan.

**Endpoint:**
- GET `/api/sesi-pertemuan` - List semua sesi
- POST `/api/sesi-pertemuan` - Buat sesi baru
- GET `/api/sesi-pertemuan/{id}` - Detail sesi
- PUT `/api/sesi-pertemuan/{id}` - Update sesi
- DELETE `/api/sesi-pertemuan/{id}` - Hapus sesi
- GET `/api/sesi-pertemuan/jadwal/{id}` - Sesi per jadwal
- GET `/api/sesi-pertemuan/{id}/aktif` - Cek sesi aktif

**Contoh:**
```
1. Dosen pilih mata kuliah
2. Klik "Tambah Sesi Pertemuan"
3. Isi: Pertemuan ke-1, judul, tanggal, waktu, metode, link
4. Simpan
5. Dosen bisa langsung menambah Presensi, Materi, dan Tugas
```

---

## Fitur 1: Presensi Mahasiswa (6 endpoint)

Sistem pencatatan kehadiran mahasiswa secara digital untuk setiap sesi pertemuan.

**Endpoint:**
- POST `/api/presensi/catat` - Catat kehadiran
- PUT `/api/presensi/{id}/status` - Update status
- GET `/api/presensi/sesi/{id}` - Lihat per sesi
- GET `/api/presensi/peserta/{id}` - Lihat per mahasiswa
- POST `/api/presensi/persentase` - Hitung persentase
- POST `/api/presensi/rekap` - Rekap kehadiran

**Dosen mencatat kehadiran:**
```
1. Buka sesi pertemuan
2. Klik tab "Presensi"
3. Sistem tampilkan daftar mahasiswa
4. Tandai: hadir / izin / sakit / alpha
5. Simpan
```

**Mahasiswa cek kehadiran:**
```
1. Buka sesi pertemuan
2. Klik tab "Presensi"
3. Lihat status kehadiran sendiri
4. Lihat persentase kehadiran total
```

---

## Fitur 2: Materi Pembelajaran (5 endpoint)

Platform digital untuk dosen mengupload dan mendistribusikan materi pembelajaran kepada mahasiswa.

**Endpoint:**
- POST `/api/materi/upload` - Upload materi
- PUT `/api/materi/{id}` - Update materi
- DELETE `/api/materi/{id}` - Hapus materi
- GET `/api/materi/sesi/{id}` - Lihat per sesi
- GET `/api/materi/{id}/download` - Download materi

**Dosen upload materi:**
```
1. Buka sesi pertemuan
2. Klik tab "Materi"
3. Klik "Upload Materi"
4. Isi: judul, deskripsi, tipe
5. Upload file
6. Mahasiswa bisa download
```

**Mahasiswa download materi:**
```
1. Buka sesi pertemuan
2. Klik tab "Materi"
3. Klik "Download" pada materi
4. File terdownload
```

---

## Fitur 3: Tugas dengan CBT (8 endpoint)

Sistem manajemen tugas dengan dukungan Computer Based Test (CBT). Dosen bisa membuat tugas biasa atau ujian online.

**Endpoint:**
- GET `/api/sesi/{id}/tugas` - List tugas
- POST `/api/sesi/{id}/tugas` - Buat tugas
- GET `/api/tugas/{id}` - Detail tugas
- PUT `/api/tugas/{id}` - Update tugas
- DELETE `/api/tugas/{id}` - Hapus tugas
- GET `/api/tugas/{id}/deadline` - Cek deadline
- GET `/api/tugas/{id}/launch/{id_peserta}` - Launch CBT
- GET `/api/admin/tugas` - List semua tugas

**Fitur CBT:**

Integrasi dengan sistem ujian online eksternal. Mahasiswa mengerjakan ujian melalui link dengan token akses.

**Dosen buat tugas CBT:**
```
1. Buka sesi pertemuan
2. Klik tab "Tugas"
3. Klik "Buat Tugas"
4. Isi: judul, deskripsi, batas waktu
5. Masukkan link CBT eksternal
6. Sistem auto-generate token
7. Simpan
```

**Mahasiswa kerjakan CBT:**
```
1. Buka sesi pertemuan
2. Klik tab "Tugas"
3. Klik "Mulai Ujian"
4. Sistem generate URL launch
5. Mahasiswa diarahkan ke CBT
6. Kerjakan ujian
```

---

## Statistik

| Fitur | Endpoint |
|-------|----------|
| Sesi Pertemuan | 7 |
| Presensi | 6 |
| Materi | 5 |
| Tugas | 8 |
| **TOTAL** | **26** |

---

## Tips Presentasi (15 menit)

**1. Buka dengan Konsep (2 menit)**
```
"Sistem kita punya Sesi Pertemuan.
Setiap mata kuliah punya beberapa sesi.
Setiap sesi punya 3 fitur:
- Presensi (catat kehadiran)
- Materi (upload materi)
- Tugas (buat tugas)"
```

**2. Jelaskan Sesi Pertemuan (2 menit)**
```
"Sesi Pertemuan adalah pusat aktivitas.
Dosen bisa buat, edit, hapus sesi.
Contoh: Pertemuan ke-1 'Pengantar HTML'"
```

**3. Jelaskan Presensi (3 menit)**
```
"Setiap sesi punya presensi.
Dosen catat kehadiran.
Mahasiswa cek kehadiran.
Sistem hitung persentase otomatis."
```

**4. Jelaskan Materi (3 menit)**
```
"Setiap sesi punya materi.
Dosen upload PDF, video, dll.
Mahasiswa download kapan saja."
```

**5. Jelaskan Tugas (3 menit)**
```
"Setiap sesi punya tugas.
Dosen buat tugas biasa atau CBT.
CBT terintegrasi sistem ujian eksternal.
Token otomatis."
```

**6. Tutup dengan Impact (2 menit)**
```
"Dengan sistem ini:
- Dosen lebih efisien
- Mahasiswa lebih mudah
- Data lebih akurat
- Semua terintegrasi"
```

---

**Selesai.**
