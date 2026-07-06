**1. Pengguna**
Tabel master yang berfungsi untuk menyimpan data seluruh civitas akademika (Mahasiswa, Dosen, dan Admin) yang menggunakan sistem LMS kampus, mencakup informasi profil, kredensial login, dan atribut akademik.

<center>Tabel 1. Tabel Pengguna</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_user | uuid | PRIMARY KEY, identitas unik pengguna |
| nama_lengkap | varchar (255) | Nama lengkap sesuai identitas resmi |
| role | varchar (255) | Peran pengguna (Mahasiswa/Dosen/Admin) |
| email | varchar (255) | Email sebagai identitas login |
| nomor_induk | varchar (255) | Nomor identitas resmi (NIM/NIDN) |
| password | varchar (255) | Kata sandi terenkripsi untuk keamanan |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| fakultas | varchar (255) | Nama fakultas |
| prodi | varchar (255) | Nama Prodi |
| status_aktif | boolean | Status keaktifan akun dalam sistem, berbentuk True atau False |
| status_persetujuan | varchar (255) | Status persetujuan |
| angkatan | varchar (255) | Tahun angkatan masuk |
| foto_profil | varchar (255) | Lokasi atau nama file foto profil pengguna |
| login_terakhir | timestamp | Login terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| nomor_telepon | varchar (255) | Nomor telepon |
| tanggal_lahir | date | Tanggal lahir |
| alamat | text | Alamat |
| username | varchar (255) | Username |
| is_first_login | boolean | Status penanda boolean untuk first_login |

**2. Master Mata Kuliah**
Tabel untuk menyimpan data referensi mata kuliah beserta jumlah SKS dan deskripsinya.

<center>Tabel 2. Tabel Master Mata Kuliah</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_mk | uuid | PRIMARY KEY, identitas unik master mata kuliah |
| kode_mk | varchar (10) | Kode mk |
| nama_mk | varchar (100) | Nama mk |
| sks | integer | Sks |
| deskripsi | text | Deskripsi |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| semester | integer | Semester |
| fakultas | varchar (255) | Fakultas |
| prodi | varchar (255) | Prodi |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |

**3. Master Kelas**
Tabel untuk menyimpan data referensi kelas yang tersedia pada institusi pendidikan.

<center>Tabel 3. Tabel Master Kelas</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_kelas | uuid | FOREIGN KEY, referensi ke data kelas |
| nama_kelas | varchar (50) | Nama kelas |
| kode_kelas | varchar (10) | Kode kelas |
| tahun_angkatan | varchar (255) | Tahun angkatan |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| fakultas | varchar (255) | Fakultas |
| prodi | varchar (255) | Prodi |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |

**4. Jadwal Perkuliahan**
Tabel untuk menyimpan data jadwal perkuliahan yang mengaitkan mata kuliah, kelas, dosen, dan waktu pelaksanaannya.

<center>Tabel 4. Tabel Jadwal Perkuliahan</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_jadwal | uuid | PRIMARY KEY, identitas unik jadwal perkuliahan |
| id_mk | uuid | FOREIGN KEY, referensi ke data mk |
| id_kelas | uuid | FOREIGN KEY, referensi ke data kelas |
| id_dosen | uuid | FOREIGN KEY, referensi ke data dosen |
| sks | integer | Sks |
| semester | integer | Semester |
| hari | varchar (10) | Hari |
| waktu_mulai | time | Waktu mulai |
| waktu_berakhir | time | Waktu berakhir |
| token_enrollment | varchar (10) | Token enrollment |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| fakultas | varchar (255) | Fakultas |
| prodi | varchar (255) | Prodi |
| tahun | varchar (9) | Tahun |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| tanggal_mulai | date | Tanggal mulai |

**5. Peserta Kelas**
Tabel untuk menyimpan data mahasiswa yang terdaftar sebagai peserta dalam suatu kelas perkuliahan tertentu.

<center>Tabel 5. Tabel Peserta Kelas</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_peserta | uuid | FOREIGN KEY, referensi ke data peserta |
| id_jadwal | uuid | FOREIGN KEY, referensi ke data jadwal |
| id_mahasiswa | uuid | FOREIGN KEY, referensi ke data mahasiswa |
| tanggal_daftar | timestamp | Tanggal daftar |
| evaluasi_selesai | boolean | Evaluasi selesai |
| kehadiran | varchar (10) | Kehadiran |
| nilai_akhir | numeric | Nilai akhir |
| status_kelayakan | varchar (255) | Status kelayakan |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |

**6. Sesi Pertemuan**
Tabel untuk menyimpan rencana dan pelaksanaan setiap sesi pertemuan/perkuliahan dari suatu jadwal kelas.

<center>Tabel 6. Tabel Sesi Pertemuan</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_sesi | uuid | FOREIGN KEY, referensi ke data sesi |
| id_jadwal | uuid | FOREIGN KEY, referensi ke data jadwal |
| pertemuan_ke | integer | Pertemuan ke |
| judul_sesi | varchar (100) | Judul sesi |
| tanggal_pelaksanaan | date | Tanggal pelaksanaan |
| jam_mulai | time | Jam mulai |
| jam_berakhir | time | Jam berakhir |
| metode_pertemuan | varchar (255) | Metode pertemuan |
| link_kelas_daring | text | Link kelas daring |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| materi | text | Materi |
| status | varchar (50) | Status |
| url_cbt | text | Url cbt |

**7. Materi Pembelajaran**
Tabel untuk menyimpan data materi-materi yang diberikan pada tiap sesi pertemuan, termasuk file atau link video.

<center>Tabel 7. Tabel Materi Pembelajaran</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_materi | uuid | PRIMARY KEY, identitas unik materi pembelajaran |
| id_sesi | uuid | FOREIGN KEY, referensi ke data sesi |
| judul_materi | varchar (200) | Judul materi |
| file_materi | text | File materi |
| link_video_pembelajaran | varchar (500) | Link video pembelajaran |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| deskripsi | text | Deskripsi |

**8. Presensi**
Tabel untuk mencatat kehadiran peserta kelas pada setiap sesi pertemuan.

<center>Tabel 8. Tabel Presensi</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_presensi | uuid | PRIMARY KEY, identitas unik presensi |
| id_sesi | uuid | FOREIGN KEY, referensi ke data sesi |
| id_peserta | uuid | FOREIGN KEY, referensi ke data peserta |
| status_kehadiran | varchar (255) | Status kehadiran |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |

**9. Forum Diskusi**
Tabel untuk menampung pesan dan diskusi antar pengguna di dalam forum diskusi.

<center>Tabel 9. Tabel Forum Diskusi</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_pesan | uuid | FOREIGN KEY, referensi ke data pesan |
| id_sesi | uuid | FOREIGN KEY, referensi ke data sesi |
| id_pengirim | uuid | FOREIGN KEY, referensi ke data pengirim |
| isi_pesan | text | Isi pesan |
| waktu_kirim | timestamp | Waktu kirim |
| id_parent_pesan | uuid | FOREIGN KEY, referensi ke data parent_pesan |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |

**10. Tugas**
Tabel untuk menyimpan data tugas atau penugasan yang diberikan pada suatu sesi pertemuan tertentu.

<center>Tabel 10. Tabel Tugas</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_tugas | uuid | PRIMARY KEY, identitas unik tugas |
| id_sesi | uuid | FOREIGN KEY, referensi ke data sesi |
| judul_tugas | varchar (200) | Judul tugas |
| deskripsi_tugas | text | Deskripsi tugas |
| batas_waktu | timestamp | Batas waktu |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| link_cbt | varchar (255) | Link cbt |
| token_cbt | varchar (10) | Token cbt |

**11. Nilai Cbt**
Tabel untuk menyimpan hasil nilai Computer Based Test (CBT) yang diperoleh mahasiswa pada suatu tugas/ujian.

<center>Tabel 11. Tabel Nilai Cbt</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_nilai | uuid | PRIMARY KEY, identitas unik nilai cbt |
| id_tugas | uuid | FOREIGN KEY, referensi ke data tugas |
| id_peserta | uuid | FOREIGN KEY, referensi ke data peserta |
| nilai | numeric | Nilai |
| waktu_sinkron | timestamp | Waktu sinkron |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |

**12. Pertanyaan Evaluasi**
Tabel untuk menyimpan data pertanyaan kuesioner evaluasi yang ditujukan kepada peserta kelas.

<center>Tabel 12. Tabel Pertanyaan Evaluasi</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_pertanyaan | uuid | FOREIGN KEY, referensi ke data pertanyaan |
| kategori | varchar (50) | Kategori |
| teks_pertanyaan | text | Teks pertanyaan |
| urutan | integer | Urutan |
| is_aktif | boolean | Status penanda boolean untuk aktif |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| tipe_pertanyaan | varchar (255) | Tipe pertanyaan |

**13. Jawaban Evaluasi**
Tabel untuk menyimpan jawaban kuesioner evaluasi yang telah disubmit oleh peserta kelas.

<center>Tabel 13. Tabel Jawaban Evaluasi</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_evaluasi | uuid | PRIMARY KEY, identitas unik jawaban evaluasi |
| id_pertanyaan | uuid | FOREIGN KEY, referensi ke data pertanyaan |
| id_peserta | uuid | FOREIGN KEY, referensi ke data peserta |
| skor | integer | Skor |
| waktu_submit | timestamp | Waktu submit |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| jawaban_teks | text | Jawaban teks |
| id_jadwal | uuid | FOREIGN KEY, referensi ke data jadwal |

**14. Template Sertifikat**
Tabel untuk menyimpan desain template background serta konfigurasi letak teks untuk pencetakan sertifikat.

<center>Tabel 14. Tabel Template Sertifikat</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_template | uuid | FOREIGN KEY, referensi ke data template |
| nama_template | varchar (100) | Nama template |
| file_background | varchar (300) | File background |
| is_aktif | boolean | Status penanda boolean untuk aktif |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| layout_data | json | Layout data |
| tipe_sertifikat | varchar (50) | Tipe sertifikat |

**15. Sertifikat**
Tabel untuk menyimpan data sertifikat kelulusan atau keikutsertaan yang diterbitkan untuk pengguna.

<center>Tabel 15. Tabel Sertifikat</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_sertifikat | uuid | PRIMARY KEY, identitas unik sertifikat |
| id_peserta | uuid | FOREIGN KEY, referensi ke data peserta |
| id_template | uuid | FOREIGN KEY, referensi ke data template |
| nomor_sertifikat | varchar (50) | Nomor sertifikat |
| tanggal_terbit | date | Tanggal terbit |
| file_url | varchar (255) | File url |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |
| deleted_at | timestamp | Waktu penghapusan data (soft delete) |
| tipe_sertifikat | varchar (50) | Tipe sertifikat |

**16. Forum Diskusi Reads**
Tabel untuk melacak status baca pesan di dalam forum diskusi oleh masing-masing pengguna.

<center>Tabel 16. Tabel Forum Diskusi Reads</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | uuid | PRIMARY KEY, identitas unik forum diskusi reads |
| id_pesan | uuid | FOREIGN KEY, referensi ke data pesan |
| id_user | uuid | FOREIGN KEY, referensi ke data user |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |

**17. Notifikasi**
Tabel untuk menyimpan log notifikasi sistem yang dikirimkan kepada pengguna tertentu.

<center>Tabel 17. Tabel Notifikasi</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id_notifikasi | uuid | PRIMARY KEY, identitas unik notifikasi |
| id_user | uuid | FOREIGN KEY, referensi ke data user |
| judul | varchar (255) | Judul |
| pesan | text | Pesan |
| tipe | varchar (255) | Tipe |
| id_referensi | uuid | FOREIGN KEY, referensi ke data referensi |
| is_read | boolean | Status penanda boolean untuk read |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |

**18. Users**
Tabel default sistem untuk mengelola autentikasi dasar.

<center>Tabel 18. Tabel Users</center>

| Field | Tipe Data | Keterangan |
|---|---|---|
| id | bigint | PRIMARY KEY, identitas unik users |
| name | varchar (255) | Name |
| email | varchar (255) | Email |
| email_verified_at | timestamp | Email verified at |
| password | varchar (255) | Password |
| remember_token | varchar (100) | Remember token |
| created_at | timestamp | Waktu pencatatan data pertama kali |
| updated_at | timestamp | Waktu pembaruan data terakhir |

