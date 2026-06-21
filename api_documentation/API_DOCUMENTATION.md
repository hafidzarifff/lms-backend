# Dokumentasi API Authentication - Project LMS

Dokumentasi ini berisi informasi detail mengenai endpoint autentikasi yang telah diimplementasikan pada backend menggunakan Laravel Sanctum.

## 📌 Informasi Umum
- **Base URL:** `http://localhost:8000/api` (Sesuaikan dengan host/port development)
- **Content-Type:** `application/json`
- **Accept:** `application/json`

---

## 🔐 Endpoints

### 1. Login
Digunakan untuk mendapatkan token akses. User bisa login menggunakan salah satu dari: **Email**, **Username**, atau **Nomor Induk**.

- **URL:** `/login`
- **Method:** `POST`
- **Rate Limit:** 5 request per menit.
- **Request Body:**
```json
{
    "identifier": "admin@lms.com", 
    "password": "password123"
}
```
*Catatan: `identifier` bisa diisi Email, Username, atau Nomor Induk.*

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer",
        "user": {
            "id_user": "uuid-string-here",
            "nama_lengkap": "Administrator LMS",
            "role": "Admin"
        }
    }
}
```

- **Response Error (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Kredensial tidak valid"
}
```

- **Response Error (403 Forbidden):**
Muncul jika akun belum disetujui, ditolak, atau dinonaktifkan.
```json
{
    "success": false,
    "message": "Akun Anda sedang dalam proses verifikasi oleh Admin."
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Email, Username, atau Nomor Induk wajib diisi. (and other validation messages)",
    "errors": {
        "identifier": ["Email, Username, atau Nomor Induk wajib diisi."],
        "password": ["Password minimal 8 karakter."]
    }
}
```

---

### 2. Logout
Menghapus token akses yang sedang digunakan.

- **URL:** `/logout`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "status": "success",
    "message": "Logout berhasil"
}
```

---

### 3. Get User Profile
Mendapatkan data user yang sedang login (Middleware `auth:sanctum`).

- **URL:** `/user`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "id_user": "uuid-string-here",
    "nama_lengkap": "Administrator LMS",
    "email": "admin@lms.com",
    "username": "admin",
    "nomor_induk": "12345678",
    "role": "Admin",
    "created_at": "...",
    "updated_at": "..."
}
```

---

### 4. Registrasi Manual Dosen
Digunakan oleh Dosen untuk mendaftarkan akun baru secara mandiri. Akun yang terdaftar akan berstatus **Menunggu** dan tidak bisa login sampai disetujui oleh Admin.

- **URL:** `/register/dosen`
- **Method:** `POST`
- **Request Body:**
```json
{
    "nama_lengkap": "Dr. Budi Santoso",
    "nidn": "0012345678",
    "email": "budi@univ.ac.id",
    "password": "password123",
    "password_confirmation": "password123",
    "fakultas": "Teknik",
    "prodi": "Informatika"
}
```

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Registrasi berhasil. Akun Anda sedang menunggu verifikasi oleh Admin.",
    "data": {
        "nama_lengkap": "Dr. Budi Santoso",
        "email": "budi@univ.ac.id"
    }
}
```

---

### 5. Daftar Antrean Verifikasi Dosen (Admin)
Melihat daftar dosen yang telah melakukan registrasi. Admin dapat memfilter berdasarkan status persetujuan.

- **URL:** `/verifikasi-dosen`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params:**
    - `status` (optional): `Menunggu`, `Disetujui`, atau `Ditolak`.
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "current_page": 1,
    "data": [
        {
            "id_user": "uuid-string",
            "nama_lengkap": "Dr. Budi Santoso",
            "email": "budi@univ.ac.id",
            "role": "Dosen",
            "status_persetujuan": "Menunggu",
            "status_aktif": false,
            "created_at": "..."
        }
    ],
    "total": 1,
    "per_page": 10
}
```

---

### 6. Proses Verifikasi Dosen (Admin)
Menyetujui atau menolak registrasi akun dosen. Jika disetujui, akun otomatis menjadi aktif dan dosen bisa login.

- **URL:** `/verifikasi-dosen/{id}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "status_persetujuan": "Disetujui"
}
```
*Pilihan status: `Disetujui` atau `Ditolak`.*

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Registrasi dosen berhasil disetujui."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data dosen tidak ditemukan."
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Status persetujuan wajib diisi. (and other validation messages)",
    "errors": {
        "status_persetujuan": ["Status persetujuan wajib diisi."]
    }
}
```

---

### 7. Tambah Data Mahasiswa (Admin)
Menambahkan data mahasiswa baru secara manual. Sistem akan otomatis men-generate **Email** dan **Password default** agar mahasiswa bisa langsung login.

- **URL:** `/mahasiswa`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nama_lengkap": "Budi Rahardjo",
    "nomor_induk": "20241001",
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "angkatan": "2024"
}
```
*Catatan: Email otomatis akan berformat `{nomor_induk}@mhs.uika.ac.id` dan Password default berformat `Mhs{nomor_induk}`.*

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Data mahasiswa berhasil ditambahkan. Email dan sandi default telah dibuat."
}
```

---

### 8. Daftar Data Mahasiswa (Admin)
Mendapatkan list data seluruh mahasiswa dengan sistem pagination (50 data per halaman).

- **URL:** `/mahasiswa`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "current_page": 1,
    "data": [
        {
            "id_user": "uuid-string",
            "nama_lengkap": "Budi Rahardjo",
            "nomor_induk": "20241001",
            "email": "20241001@mhs.uika.ac.id",
            "role": "Mahasiswa",
            "fakultas": "Teknik",
            "prodi": "Informatika",
            "angkatan": "2024",
            "status_aktif": true,
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "total": 1,
    "per_page": 50
}
```

---

### 9. Detail Data Mahasiswa (Admin)
Mendapatkan detail satu data mahasiswa berdasarkan ID.

- **URL:** `/mahasiswa/{id}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "id_user": "uuid-string",
    "nama_lengkap": "Budi Rahardjo",
    "nomor_induk": "20241001",
    "email": "20241001@mhs.uika.ac.id",
    "role": "Mahasiswa",
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "angkatan": "2024",
    "status_aktif": true,
    "created_at": "...",
    "updated_at": "..."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

### 10. Update Data Mahasiswa (Admin)
Memperbarui data profil mahasiswa. Jika `nomor_induk` diubah, maka `email` akan di-generate ulang secara otomatis. Password tidak akan berubah.

- **URL:** `/mahasiswa/{id}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nama_lengkap": "Budi Rahardjo Updated",
    "nomor_induk": "20241002",
    "fakultas": "Teknik",
    "prodi": "Sistem Informasi",
    "angkatan": "2024",
    "status_aktif": false
}
```

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Data mahasiswa berhasil diperbarui."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

### 11. Hapus Data Mahasiswa (Admin)
Menghapus data mahasiswa secara permanen dari sistem.

- **URL:** `/mahasiswa/{id}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Data mahasiswa berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

### 12. Update Data Dosen (Admin)
Memperbarui data profil dosen (nama_lengkap, nomor_induk/NIDN, email, fakultas, prodi, status_aktif, status_persetujuan). Password tidak akan berubah.

- **URL:** `/dosen/{id}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nama_lengkap": "Dr. Budi Santoso Updated",
    "nomor_induk": "0012345678",
    "email": "budi.updated@univ.ac.id",
    "fakultas": "Teknik",
    "prodi": "Sistem Informasi",
    "status_aktif": true,
    "status_persetujuan": "Disetujui"
}
```

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Data dosen berhasil diperbarui."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data dosen tidak ditemukan."
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Nama lengkap wajib diisi. (and other validation messages)",
    "errors": {
        "nama_lengkap": ["Nama lengkap wajib diisi."],
        "nomor_induk": ["Nomor induk sudah terdaftar."]
    }
}
```

---

### 13. Hapus Data Dosen (Admin)
Menghapus data dosen secara permanen dari sistem.

- **URL:** `/dosen/{id}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Data dosen berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data dosen tidak ditemukan."
}
```

---

### 14. Daftar Master Kelas (Admin)
Mengambil seluruh data master kelas dengan pagination (10 data per halaman), diurutkan dari yang terbaru.

- **URL:** `/kelas`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "current_page": 1,
    "data": [
        {
            "id_kelas": "uuid-string",
            "nama_kelas": "Kelas A",
            "kode_kelas": "KLS-A",
            "tahun_angkatan": "2024",
            "fakultas": "Teknik",
            "prodi": "Informatika",
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "total": 1,
    "per_page": 10
}
```

---

### 15. Detail Master Kelas (Admin)
Mengambil detail satu data kelas berdasarkan ID.

- **URL:** `/kelas/{id_kelas}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "id_kelas": "uuid-string",
    "nama_kelas": "Kelas A",
    "kode_kelas": "KLS-A",
    "tahun_angkatan": "2024",
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "created_at": "...",
    "updated_at": "..."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

### 16. Tambah Master Kelas (Admin)
Menambahkan data master kelas baru ke sistem.

- **URL:** `/kelas`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nama_kelas": "Kelas A",
    "kode_kelas": "KLS-A",
    "tahun_angkatan": "2024",
    "fakultas": "Teknik",
    "prodi": "Informatika"
}
```

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Master kelas berhasil ditambahkan.",
    "data": {
        "id_kelas": "uuid-string",
        "nama_kelas": "Kelas A",
        "kode_kelas": "KLS-A",
        "tahun_angkatan": "2024",
        "fakultas": "Teknik",
        "prodi": "Informatika",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "The nama kelas field is required. (and other validation messages)",
    "errors": {
        "nama_kelas": ["The nama kelas field is required."],
        "kode_kelas": ["The kode kelas has already been taken."]
    }
}
```

---

### 17. Update Master Kelas (Admin)
Memperbarui data master kelas yang sudah ada.

- **URL:** `/kelas/{id_kelas}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nama_kelas": "Kelas B Updated",
    "kode_kelas": "KLS-B",
    "tahun_angkatan": "2025",
    "fakultas": "Teknik",
    "prodi": "Sistem Informasi"
}
```

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Master kelas berhasil diperbarui."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

### 18. Hapus Master Kelas (Admin)
Menghapus data master kelas secara permanen dari sistem.

- **URL:** `/kelas/{id_kelas}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Master kelas berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

### 19. Daftar Master Mata Kuliah (Admin)
Mengambil seluruh data master mata kuliah dengan pagination (10 data per halaman), diurutkan dari yang terbaru.

- **URL:** `/mata-kuliah`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "current_page": 1,
    "data": [
        {
            "id_mk": "uuid-string",
            "kode_mk": "IF101",
            "nama_mk": "Algoritma dan Pemrograman",
            "sks": 3,
            "deskripsi": "Mata kuliah dasar tentang algoritma dan pemrograman.",
            "semester": 1,
            "fakultas": "Teknik",
            "prodi": "Informatika",
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "total": 1,
    "per_page": 10
}
```

---

### 20. Detail Master Mata Kuliah (Admin)
Mengambil detail satu data mata kuliah berdasarkan ID.

- **URL:** `/mata-kuliah/{id_mk}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "id_mk": "uuid-string",
    "kode_mk": "IF101",
    "nama_mk": "Algoritma dan Pemrograman",
    "sks": 3,
    "deskripsi": "Mata kuliah dasar tentang algoritma dan pemrograman.",
    "semester": 1,
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "created_at": "...",
    "updated_at": "..."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

### 21. Tambah Master Mata Kuliah (Admin)
Menambahkan data master mata kuliah baru ke sistem.

- **URL:** `/mata-kuliah`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "kode_mk": "IF101",
    "nama_mk": "Algoritma dan Pemrograman",
    "sks": 3,
    "deskripsi": "Mata kuliah dasar tentang algoritma dan pemrograman.",
    "semester": 1,
    "fakultas": "Teknik",
    "prodi": "Informatika"
}
```
*Catatan: Field `deskripsi`, `semester`, `fakultas`, dan `prodi` bersifat opsional (nullable). `semester` harus berupa integer antara 1-14.*

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Master mata kuliah berhasil ditambahkan.",
    "data": {
        "id_mk": "uuid-string",
        "kode_mk": "IF101",
        "nama_mk": "Algoritma dan Pemrograman",
        "sks": 3,
        "deskripsi": "Mata kuliah dasar tentang algoritma dan pemrograman.",
        "semester": 1,
        "fakultas": "Teknik",
        "prodi": "Informatika",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "The kode mk field is required. (and other validation messages)",
    "errors": {
        "kode_mk": ["The kode mk has already been taken."],
        "sks": ["The sks field must be at least 1."],
        "semester": ["The semester field must be between 1 and 14."]
    }
}
```

---

### 22. Update Master Mata Kuliah (Admin)
Memperbarui data master mata kuliah yang sudah ada.

- **URL:** `/mata-kuliah/{id_mk}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "kode_mk": "IF102",
    "nama_mk": "Struktur Data",
    "sks": 4,
    "deskripsi": "Mata kuliah lanjutan tentang struktur data.",
    "semester": 2,
    "fakultas": "Teknik",
    "prodi": "Informatika"
}
```

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Master mata kuliah berhasil diperbarui."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

### 23. Hapus Master Mata Kuliah (Admin)
Menghapus data master mata kuliah secara permanen dari sistem.

- **URL:** `/mata-kuliah/{id_mk}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Master mata kuliah berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

### 24. Daftar Jadwal Perkuliahan (Admin)
Mengambil seluruh data jadwal perkuliahan dengan Eager Loading (data mata kuliah, kelas, dan dosen ikut ditampilkan). Pagination 10 data per halaman, diurutkan dari yang terbaru.

- **URL:** `/jadwal-perkuliahan`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "current_page": 1,
    "data": [
        {
            "id_jadwal": "uuid-string",
            "id_mk": "uuid-string",
            "id_kelas": "uuid-string",
            "id_dosen": "uuid-string",
            "sks": 3,
            "fakultas": "Teknik",
            "prodi": "Informatika",
            "tahun": "2025/2026",
            "semester": 1,
            "hari": "Senin",
            "waktu_mulai": "08:00",
            "waktu_berakhir": "10:00",
            "token_enrollment": "ABCXYZ",
            "created_at": "...",
            "updated_at": "...",
            "mata_kuliah": {
                "id_mk": "uuid-string",
                "kode_mk": "IF101",
                "nama_mk": "Algoritma dan Pemrograman",
                "sks": 3,
                "deskripsi": "...",
                "semester": 1,
                "fakultas": "Teknik",
                "prodi": "Informatika"
            },
            "kelas": {
                "id_kelas": "uuid-string",
                "nama_kelas": "Kelas A",
                "kode_kelas": "KLS-A",
                "tahun_angkatan": "2024",
                "fakultas": "Teknik",
                "prodi": "Informatika"
            },
            "dosen": {
                "id_user": "uuid-string",
                "nama_lengkap": "Dr. Budi Santoso",
                "email": "budi@univ.ac.id",
                "role": "Dosen"
            }
        }
    ],
    "total": 1,
    "per_page": 10
}
```

---

### 25. Detail Jadwal Perkuliahan (Admin)
Mengambil detail satu jadwal perkuliahan berdasarkan ID, termasuk data relasi mata kuliah, kelas, dan dosen.

- **URL:** `/jadwal-perkuliahan/{id_jadwal}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "id_jadwal": "uuid-string",
    "id_mk": "uuid-string",
    "id_kelas": "uuid-string",
    "id_dosen": "uuid-string",
    "sks": 3,
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "tahun": "2025/2026",
    "semester": 1,
    "hari": "Senin",
    "waktu_mulai": "08:00",
    "waktu_berakhir": "10:00",
    "token_enrollment": "ABCXYZ",
    "created_at": "...",
    "updated_at": "...",
    "mata_kuliah": {
        "id_mk": "uuid-string",
        "kode_mk": "IF101",
        "nama_mk": "Algoritma dan Pemrograman",
        "sks": 3,
        "deskripsi": "...",
        "semester": 1,
        "fakultas": "Teknik",
        "prodi": "Informatika"
    },
    "kelas": {
        "id_kelas": "uuid-string",
        "nama_kelas": "Kelas A",
        "kode_kelas": "KLS-A",
        "tahun_angkatan": "2024",
        "fakultas": "Teknik",
        "prodi": "Informatika"
    },
    "dosen": {
        "id_user": "uuid-string",
        "nama_lengkap": "Dr. Budi Santoso",
        "email": "budi@univ.ac.id",
        "role": "Dosen"
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

### 26. Tambah Jadwal Perkuliahan (Admin)
Menambahkan data jadwal perkuliahan baru. SKS diambil otomatis dari master mata kuliah yang dipilih. Token enrollment di-generate otomatis sebagai 6 karakter huruf kapital acak dan unik.

- **URL:** `/jadwal-perkuliahan`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_mk": "uuid-mata-kuliah",
    "id_kelas": "uuid-kelas",
    "id_dosen": "uuid-dosen",
    "semester": 1,
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "tahun": "2025/2026",
    "hari": "Senin",
    "waktu_mulai": "08:00",
    "waktu_berakhir": "10:00",
    "tanggal_mulai": "2026-07-01"
}
```
*Catatan: Field `sks` dan `token_enrollment` tidak perlu dikirim. SKS otomatis diambil dari mata kuliah, token otomatis di-generate oleh sistem. `semester` berupa integer (1-14).*

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Jadwal perkuliahan berhasil ditambahkan.",
    "data": {
        "id_jadwal": "uuid-string",
        "id_mk": "uuid-mata-kuliah",
        "id_kelas": "uuid-kelas",
        "id_dosen": "uuid-dosen",
        "sks": 3,
        "fakultas": "Teknik",
        "prodi": "Informatika",
        "tahun": "2025/2026",
        "semester": 1,
        "hari": "Senin",
        "waktu_mulai": "08:00",
        "waktu_berakhir": "10:00",
        "tanggal_mulai": "2026-07-01",
        "token_enrollment": "ABCXYZ",
        "created_at": "...",
        "updated_at": "...",
        "mata_kuliah": { "..." : "..." },
        "kelas": { "..." : "..." },
        "dosen": { "..." : "..." }
    }
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Mata kuliah wajib dipilih. (and other validation messages)",
    "errors": {
        "id_mk": ["Mata kuliah wajib dipilih."],
        "id_dosen": ["Pengguna yang dipilih bukan berstatus Dosen."],
        "semester": ["Semester harus berupa angka.", "Semester minimal bernilai 1.", "Semester maksimal bernilai 14."],
        "fakultas": ["Fakultas wajib diisi."],
        "prodi": ["Program studi wajib diisi."],
        "tahun": ["Tahun ajaran wajib diisi."],
        "waktu_berakhir": ["Waktu berakhir harus setelah waktu mulai."]
    }
}
```

---

### 27. Update Jadwal Perkuliahan (Admin)
Memperbarui data jadwal perkuliahan yang sudah ada. Token enrollment tidak berubah. Jika mata kuliah (`id_mk`) diubah, maka SKS otomatis menyesuaikan dari mata kuliah baru.

- **URL:** `/jadwal-perkuliahan/{id_jadwal}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_mk": "uuid-mata-kuliah",
    "id_kelas": "uuid-kelas",
    "id_dosen": "uuid-dosen",
    "semester": 2,
    "fakultas": "Teknik",
    "prodi": "Informatika",
    "tahun": "2025/2026",
    "hari": "Selasa",
    "waktu_mulai": "10:00",
    "waktu_berakhir": "12:00",
    "tanggal_mulai": "2026-07-01"
}
```

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Jadwal perkuliahan berhasil diperbarui."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

### 28. Hapus Jadwal Perkuliahan (Admin)
Menghapus data jadwal perkuliahan secara permanen dari sistem.

- **URL:** `/jadwal-perkuliahan/{id_jadwal}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Jadwal perkuliahan berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

### 29. Pendaftaran Peserta Kelas (Mahasiswa)
Mahasiswa mendaftar (enroll) ke jadwal perkuliahan menggunakan token (6 karakter uppercase).

- **URL:** `/peserta-kelas/enroll`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "token_enrollment": "ABCXYZ"
}
```

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Berhasil mendaftar ke kelas.",
    "data": {
        "id_peserta": "uuid-string",
        "id_jadwal": "uuid-jadwal",
        "id_mahasiswa": "uuid-mahasiswa",
        "tanggal_daftar": "2026-06-04T07:55:00.000000Z",
        "evaluasi_selesai": false,
        "kehadiran": "0/0",
        "nilai_akhir": 0.00,
        "status_kelayakan": "Belum Ditentukan",
        "created_at": "...",
        "updated_at": "...",
        "jadwal": { "..." : "..." },
        "mahasiswa": { "..." : "..." }
    }
}
```

- **Response Error (403 Forbidden):**
```json
{
    "success": false,
    "message": "Hanya pengguna dengan role Mahasiswa yang dapat melakukan enrollment.",
    "data": null
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Token enrollment tidak valid atau jadwal tidak ditemukan.",
    "data": null
}
```

- **Response Error (409 Conflict):**
```json
{
    "success": false,
    "message": "Anda sudah terdaftar di kelas ini.",
    "data": null
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validasi gagal.",
    "data": {
        "token_enrollment": [
            "Token enrollment wajib diisi."
        ]
    }
}
```

---

### 30. Daftar Peserta Kelas by Jadwal
Mengambil daftar seluruh peserta yang terdaftar pada jadwal tertentu. Data mahasiswa di-eager load untuk mencegah N+1 query.

- **URL:** `/jadwal/{id_jadwal}/peserta`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Daftar peserta kelas berhasil diambil.",
    "data": [
        {
            "id_peserta": "uuid-string",
            "id_jadwal": "uuid-jadwal",
            "id_mahasiswa": "uuid-mahasiswa",
            "tanggal_daftar": "2026-06-04T07:55:00.000000Z",
            "evaluasi_selesai": false,
            "kehadiran": "0/0",
            "nilai_akhir": 0.00,
            "status_kelayakan": "Belum Ditentukan",
            "created_at": "...",
            "updated_at": "...",
            "mahasiswa": {
                "id_user": "uuid-string",
                "nama_lengkap": "Budi Rahardjo",
                "nomor_induk": "20241001",
                "email": "20241001@mhs.uika.ac.id"
            }
        }
    ]
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan.",
    "data": null
}
}
```

---

### 31. Tambah Sesi Pertemuan
Menambahkan data sesi pertemuan untuk suatu jadwal perkuliahan. Sesi ini akan diperiksa apakah berbenturan waktunya atau apakah pertemuannya duplikat.

- **URL:** `/sesi-pertemuan`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_jadwal": "550e8400-e29b-41d4-a716-446655440000",
    "pertemuan_ke": 1,
    "judul_sesi": "Pengantar Perkuliahan",
    "tanggal_pelaksanaan": "2026-06-15",
    "jam_mulai": "08:00",
    "jam_berakhir": "10:00",
    "metode_pertemuan": "synchronous",
    "link_kelas_daring": "https://meet.google.com/abc-defg-hij"
}
```
*Catatan: Nilai `id_jadwal` di atas hanya contoh format UUID. Pastikan Anda menggunakan `id_jadwal` asli yang sudah terdaftar di database (ambil melalui endpoint `GET /jadwal-perkuliahan`).*

- **Response Sukses (201 Created):**
```json
{
    "status": "success",
    "message": "Sesi pertemuan berhasil dibuat.",
    "data": {
        "id_sesi": "uuid-string",
        "id_jadwal": "550e8400-e29b-41d4-a716-446655440000",
        "pertemuan_ke": 1,
        "judul_sesi": "Pengantar Perkuliahan",
        "tanggal_pelaksanaan": "2026-06-15",
        "jam_mulai": "08:00",
        "jam_berakhir": "10:00",
        "metode_pertemuan": "synchronous",
        "link_kelas_daring": "https://meet.google.com/abc-defg-hij",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Error (422 Unprocessable Entity - Bentrok Waktu):**
```json
{
    "status": "error",
    "message": "Waktu sesi bentrok dengan sesi lain pada tanggal yang sama."
}
```

- **Response Error (422 Unprocessable Entity - Duplikasi Pertemuan Ke):**
```json
{
    "status": "error",
    "message": "Pertemuan ke-1 sudah ada untuk jadwal ini."
}
```

---

### 32. Daftar Sesi Pertemuan
Mengambil seluruh data sesi pertemuan dengan pagination (10 data per halaman), diurutkan berdasarkan tanggal pelaksanaan (terbaru) dan nomor pertemuan. Mendukung filter berdasarkan `id_jadwal`, `tanggal`, dan `metode_pertemuan`.

- **URL:** `/sesi-pertemuan`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params (Optional):**
    - `per_page` (integer, default: 10): Jumlah data per halaman
    - `id_jadwal` (uuid): Filter berdasarkan ID jadwal perkuliahan
    - `tanggal` (date, format: YYYY-MM-DD): Filter berdasarkan tanggal pelaksanaan
    - `metode_pertemuan` (string): Filter berdasarkan metode (`synchronous` atau `asynchronous`)
- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id_sesi": "uuid-string",
                "id_jadwal": "uuid-jadwal",
                "pertemuan_ke": 1,
                "judul_sesi": "Pengantar Perkuliahan",
                "tanggal_pelaksanaan": "2026-06-15",
                "jam_mulai": "08:00",
                "jam_berakhir": "10:00",
                "metode_pertemuan": "synchronous",
                "link_kelas_daring": "https://meet.google.com/abc-defg-hij",
                "created_at": "...",
                "updated_at": "...",
                "jadwal_perkuliahan": {
                    "id_jadwal": "uuid-jadwal",
                    "id_mk": "uuid-mk",
                    "id_kelas": "uuid-kelas",
                    "id_dosen": "uuid-dosen",
                    "sks": 3,
                    "fakultas": "Teknik",
                    "prodi": "Informatika",
                    "tahun": "2025/2026",
                    "semester": 1,
                    "hari": "Senin",
                    "waktu_mulai": "08:00",
                    "waktu_berakhir": "10:00",
                    "token_enrollment": "ABCXYZ",
                    "created_at": "...",
                    "updated_at": "..."
                }
            }
        ],
        "total": 1,
        "per_page": 10
    }
}
```

---

### 33. Detail Sesi Pertemuan
Mengambil detail satu data sesi pertemuan berdasarkan ID, termasuk data relasi jadwal perkuliahan.

- **URL:** `/sesi-pertemuan/{id_sesi}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": {
        "id_sesi": "uuid-string",
        "id_jadwal": "uuid-jadwal",
        "pertemuan_ke": 1,
        "judul_sesi": "Pengantar Perkuliahan",
        "tanggal_pelaksanaan": "2026-06-15",
        "jam_mulai": "08:00",
        "jam_berakhir": "10:00",
        "metode_pertemuan": "synchronous",
        "link_kelas_daring": "https://meet.google.com/abc-defg-hij",
        "created_at": "...",
        "updated_at": "...",
        "jadwal_perkuliahan": {
            "id_jadwal": "uuid-jadwal",
            "id_mk": "uuid-mk",
            "id_kelas": "uuid-kelas",
            "id_dosen": "uuid-dosen",
            "sks": 3,
            "fakultas": "Teknik",
            "prodi": "Informatika",
            "tahun": "2025/2026",
            "semester": 1,
            "hari": "Senin",
            "waktu_mulai": "08:00",
            "waktu_berakhir": "10:00",
            "token_enrollment": "ABCXYZ",
            "created_at": "...",
            "updated_at": "..."
        }
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

### 34. Update Sesi Pertemuan
Memperbarui data sesi pertemuan yang sudah ada. Field `id_jadwal` tidak dapat diubah. Validasi duplikasi pertemuan ke dan bentrok waktu tetap berlaku untuk record lain.

- **URL:** `/sesi-pertemuan/{id_sesi}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "pertemuan_ke": 1,
    "judul_sesi": "Pengantar Perkuliahan (Updated)",
    "tanggal_pelaksanaan": "2026-06-16",
    "jam_mulai": "09:00",
    "jam_berakhir": "11:00",
    "metode_pertemuan": "synchronous",
    "status": "TERJADWAL",
    "materi": "Materi PDF tentang pengenalan perkuliahan",
    "url_cbt": "https://cbt.uika.ac.id/exam/123",
    "link_kelas_daring": "https://meet.google.com/xyz-abcd-efg"
}
```
*Catatan: Field `id_jadwal` tidak boleh dikirim karena tidak dapat diubah. Tanggal pelaksanaan boleh tanggal lampau (berbeda dengan endpoint POST). `status` dapat berupa TERJADWAL, BERJALAN, atau SELESAI.*

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Sesi pertemuan berhasil diperbarui.",
    "data": {
        "id_sesi": "uuid-string",
        "id_jadwal": "uuid-jadwal",
        "pertemuan_ke": 1,
        "judul_sesi": "Pengantar Perkuliahan (Updated)",
        "tanggal_pelaksanaan": "2026-06-16",
        "jam_mulai": "09:00",
        "jam_berakhir": "11:00",
        "metode_pertemuan": "synchronous",
        "link_kelas_daring": "https://meet.google.com/xyz-abcd-efg",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

- **Response Error (422 Unprocessable Entity - ID Jadwal Diubah):**
```json
{
    "message": "ID Jadwal tidak boleh diubah.",
    "errors": {
        "id_jadwal": ["ID Jadwal tidak boleh diubah."]
    }
}
```

- **Response Error (422 Unprocessable Entity - Bentrok Waktu):**
```json
{
    "status": "error",
    "message": "Waktu sesi bentrok dengan sesi lain pada tanggal yang sama."
}
```

- **Response Error (422 Unprocessable Entity - Duplikasi Pertemuan Ke):**
```json
{
    "status": "error",
    "message": "Pertemuan ke-1 sudah ada untuk jadwal ini."
}
```

---

### 35. Hapus Sesi Pertemuan
Menghapus data sesi pertemuan secara permanen dari sistem.

- **URL:** `/sesi-pertemuan/{id_sesi}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Sesi pertemuan berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

## 📜 Fitur Tugas

### 36. Daftar Tugas (Admin)
Endpoint untuk daftar tugas (admin).

- **URL:** `/admin/tugas`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 37. Buat Tugas
Endpoint untuk buat tugas.

- **URL:** `/sesi/{sesi_id}/tugas`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "judul_tugas": "string, max 200, required",
    "deskripsi_tugas": "string, optional",
    "batas_waktu": "YYYY-MM-DD HH:MM:SS, required",
    "link_cbt": "url, optional",
    "token_cbt": "string, max 10, optional"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 38. Update Tugas
Endpoint untuk update tugas.

- **URL:** `/tugas/{id}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "judul_tugas": "string, max 200, optional",
    "deskripsi_tugas": "string, optional",
    "batas_waktu": "YYYY-MM-DD HH:MM:SS, optional",
    "link_cbt": "url, optional",
    "token_cbt": "string, max 10, optional"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 39. Hapus Tugas
Endpoint untuk hapus tugas.

- **URL:** `/tugas/{id}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 40. Daftar Tugas di Sesi
Endpoint untuk daftar tugas di sesi.

- **URL:** `/sesi/{sesi_id}/tugas`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 41. Detail Tugas
Endpoint untuk detail tugas.

- **URL:** `/tugas/{id}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 42. Cek Deadline Tugas
Endpoint untuk cek deadline tugas.

- **URL:** `/tugas/{id}/deadline`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 43. Get Launch URL Tugas
Endpoint untuk get launch url tugas.

- **URL:** `/tugas/{id}/launch/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

## Fitur Forum Diskusi

### 44. Daftar Forum Diskusi Sesi
Endpoint untuk daftar forum diskusi sesi.

- **URL:** `/sesi/{idSesi}/forum`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 45. Buat Post Forum
Endpoint untuk buat post forum.

- **URL:** `/forum`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "id_sesi": "uuid, required",
    "isi_pesan": "string, max 5000, required",
    "id_parent_pesan": "uuid, optional"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 46. Detail Post Forum
Endpoint untuk detail post forum.

- **URL:** `/forum/{idPesan}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 47. Balasan Forum
Endpoint untuk balasan forum.

- **URL:** `/forum/{idPesan}/replies`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 48. Update Post
Endpoint untuk update post.

- **URL:** `/forum/{idPesan}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "isi_pesan": "string, max 5000, required"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 49. Hapus Post
Endpoint untuk hapus post.

- **URL:** `/forum/{idPesan}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 50. Cari Forum
Endpoint untuk cari forum.

- **URL:** `/sesi/{idSesi}/forum/search`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

## 📜 Fitur Nilai CBT

### 51. Simpan Nilai CBT
Endpoint untuk simpan nilai cbt.

- **URL:** `/nilai-cbt`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "nilai": [
        {
            "id_tugas": "uuid, required",
            "id_peserta": "uuid, required",
            "nilai": "numeric 0-100, required"
        }
    ]
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 52. Nilai CBT per Tugas
Endpoint untuk nilai cbt per tugas.

- **URL:** `/nilai-cbt/tugas/{id_tugas}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 53. Nilai CBT per Peserta
Endpoint untuk nilai cbt per peserta.

- **URL:** `/nilai-cbt/peserta/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 54. Detail Nilai CBT
Endpoint untuk detail nilai cbt.

- **URL:** `/nilai-cbt/{id_tugas}/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 55. Update Nilai CBT
Endpoint untuk update nilai cbt.

- **URL:** `/nilai-cbt/{id_nilai}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "nilai": "numeric 0-100, required"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 56. Hapus Nilai CBT
Endpoint untuk hapus nilai cbt.

- **URL:** `/nilai-cbt/{id_nilai}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 57. Statistik Nilai Tugas
Endpoint untuk statistik nilai tugas.

- **URL:** `/nilai-cbt/tugas/{id_tugas}/statistik`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 58. Ranking Nilai Tugas
Endpoint untuk ranking nilai tugas.

- **URL:** `/nilai-cbt/tugas/{id_tugas}/ranking/{limit?}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

## 📜 Fitur Pertanyaan Evaluasi

### 59. Daftar Pertanyaan Evaluasi
Endpoint untuk daftar pertanyaan evaluasi.

- **URL:** `/pertanyaan-evaluasi`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 60. Daftar Pertanyaan Aktif
Endpoint untuk daftar pertanyaan aktif.

- **URL:** `/pertanyaan-evaluasi/aktif`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 61. Daftar Kategori Pertanyaan
Endpoint untuk daftar kategori pertanyaan.

- **URL:** `/pertanyaan-evaluasi/kategori`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 62. Detail Pertanyaan
Endpoint untuk detail pertanyaan.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 63. Buat Pertanyaan
Endpoint untuk buat pertanyaan.

- **URL:** `/pertanyaan-evaluasi`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "kategori": "string, max 50, required",
    "teks_pertanyaan": "string, required",
    "urutan": "integer, min 1, required",
    "is_aktif": "boolean, optional"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 64. Update Pertanyaan
Endpoint untuk update pertanyaan.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "kategori": "string, max 50, optional",
    "teks_pertanyaan": "string, optional",
    "urutan": "integer, optional",
    "is_aktif": "boolean, optional"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 65. Hapus Pertanyaan
Endpoint untuk hapus pertanyaan.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 66. Toggle Status Aktif Pertanyaan
Endpoint untuk toggle status aktif pertanyaan.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}/toggle`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 67. Update Urutan Bulk
Endpoint untuk update urutan bulk.

- **URL:** `/pertanyaan-evaluasi/bulk-urutan`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "urutan": [
        {
            "id_pertanyaan": "uuid, required",
            "urutan": "integer, required"
        }
    ]
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

## 📜 Fitur Jawaban Evaluasi

### 68. Simpan Jawaban Evaluasi
Endpoint untuk simpan jawaban evaluasi.

- **URL:** `/jawaban-evaluasi`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "id_peserta": "uuid, required",
    "jawaban": [
        {
            "id_pertanyaan": "uuid, required",
            "skor": "integer 1-5, required"
        }
    ]
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 69. Jawaban per Peserta
Endpoint untuk jawaban per peserta.

- **URL:** `/jawaban-evaluasi/peserta/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 70. Jawaban per Pertanyaan
Endpoint untuk jawaban per pertanyaan.

- **URL:** `/jawaban-evaluasi/pertanyaan/{id_pertanyaan}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 71. Detail Jawaban
Endpoint untuk detail jawaban.

- **URL:** `/jawaban-evaluasi/{id_pertanyaan}/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 72. Update Jawaban
Endpoint untuk update jawaban.

- **URL:** `/jawaban-evaluasi/{id_evaluasi}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "skor": "integer 1-5, required"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 73. Hapus Jawaban
Endpoint untuk hapus jawaban.

- **URL:** `/jawaban-evaluasi/{id_evaluasi}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 74. Statistik Jawaban Pertanyaan
Endpoint untuk statistik jawaban pertanyaan.

- **URL:** `/jawaban-evaluasi/pertanyaan/{id_pertanyaan}/statistik`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 75. Statistik Kategori Evaluasi
Endpoint untuk statistik kategori evaluasi.

- **URL:** `/jawaban-evaluasi/statistik-kategori`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 76. Cek Status Evaluasi Peserta
Endpoint untuk cek status evaluasi peserta.

- **URL:** `/jawaban-evaluasi/peserta/{id_peserta}/status`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 77. Rekap Evaluasi
Endpoint untuk rekap evaluasi.

- **URL:** `/jawaban-evaluasi/rekap`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

## Fitur Template Sertifikat

### 78. Daftar Template
Endpoint untuk daftar template.

- **URL:** `/template-sertifikat`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 79. Daftar Template Aktif
Endpoint untuk daftar template aktif.

- **URL:** `/template-sertifikat/aktif`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 80. Detail Template
Endpoint untuk detail template.

- **URL:** `/template-sertifikat/{id_template}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 81. Buat Template
Endpoint untuk buat template.

- **URL:** `/template-sertifikat`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (Multipart/form-data):**
    - `nama_template`: string, max 100, required
    - `file_background`: image (jpeg, jpg, png), max 5MB, optional
    - `is_aktif`: boolean, optional

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 82. Update Template
Endpoint untuk update template.

- **URL:** `/template-sertifikat/{id_template}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "nama_template": "string, max 100, optional",
    "is_aktif": "boolean, optional"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 83. Hapus Template
Endpoint untuk hapus template.

- **URL:** `/template-sertifikat/{id_template}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 84. Toggle Status Aktif Template
Endpoint untuk toggle status aktif template.

- **URL:** `/template-sertifikat/{id_template}/toggle`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 85. Upload Background Template
Endpoint untuk upload background template.

- **URL:** `/template-sertifikat/{id_template}/background`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (Multipart/form-data):**
    - `file_background`: image (jpeg, jpg, png), max 5MB, required

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

### 86. Download Background Template
Endpoint untuk download background template.

- **URL:** `/template-sertifikat/{id_template}/download-background`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Berhasil",
    "data": {}
}
```

---

## 📜 Fitur Sertifikat

### 87. Daftar Sertifikat
Mengambil semua sertifikat dengan dukungan filter dan pagination (15 data per halaman).

- **URL:** `/sertifikat`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params:**
    - `id_peserta` (opsional): Filter berdasarkan ID peserta (UUID)
    - `id_template` (opsional): Filter berdasarkan ID template (UUID)
    - `dari_tanggal` (opsional): Filter rentang tanggal terbit awal (YYYY-MM-DD)
    - `sampai_tanggal` (opsional): Filter rentang tanggal terbit akhir (YYYY-MM-DD)
    - `per_page` (opsional): Jumlah item per halaman (default 15)

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id_sertifikat": "uuid-string",
                "id_peserta": "uuid-string",
                "id_template": "uuid-string",
                "nomor_sertifikat": "SERT/2026/06/0001",
                "tanggal_terbit": "2026-06-17",
                "file_url": "sertifikats/filename.pdf",
                "created_at": "...",
                "updated_at": "...",
                "peserta": {
                    "id_user": "uuid-string",
                    "nama_lengkap": "Budi Santoso",
                    "nim": "12345678",
                    "email": "budi@example.com"
                },
                "template": {
                    "id_template": "uuid-string",
                    "nama_template": "Sertifikat Kelulusan"
                }
            }
        ],
        "total": 1,
        "per_page": 15
    }
}
```

---

### 88. Daftar Sertifikat per Peserta
Mengambil semua sertifikat untuk satu peserta tertentu (tanpa pagination).

- **URL:** `/sertifikat/peserta/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": [
        {
            "id_sertifikat": "uuid-string",
            "nomor_sertifikat": "SERT/2026/06/0001",
            "tanggal_terbit": "2026-06-17",
            "peserta": { ... },
            "template": { ... }
        }
    ]
}
```

---

### 89. Detail Sertifikat
Mengambil detail satu data sertifikat.

- **URL:** `/sertifikat/{id_sertifikat}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": {
        "id_sertifikat": "uuid-string",
        "nomor_sertifikat": "SERT/2026/06/0001",
        "tanggal_terbit": "2026-06-17",
        "file_url": "sertifikats/filename.pdf",
        "peserta": { ... },
        "template": { ... }
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sertifikat tidak ditemukan"
}
```

---

### 90. Terbitkan Sertifikat
Menerbitkan sertifikat baru untuk satu peserta. Nomor sertifikat akan digenerate otomatis.

- **URL:** `/sertifikat`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (Multipart/form-data jika ada file):**
    - `id_peserta`: UUID (Wajib)
    - `id_template`: UUID (Wajib, harus aktif)
    - `tanggal_terbit`: YYYY-MM-DD (Opsional, default sekarang)
    - `file_sertifikat`: File PDF (Opsional, max 10MB)

- **Response Sukses (201 Created):**
```json
{
    "status": "success",
    "message": "Sertifikat berhasil diterbitkan",
    "data": { ... }
}
```

---

### 91. Terbitkan Sertifikat Bulk
Menerbitkan sertifikat untuk banyak peserta sekaligus.

- **URL:** `/sertifikat/bulk`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "id_template": "uuid-string",
    "tanggal_terbit": "2026-06-17",
    "peserta": [
        { "id_peserta": "uuid-1" },
        { "id_peserta": "uuid-2" }
    ]
}
```

- **Response Sukses (201 Created):**
```json
{
    "status": "success",
    "message": "2 sertifikat berhasil diterbitkan",
    "data": [ { ... }, { ... } ],
    "total": 2
}
```

---

### 92. Update Sertifikat
Memperbarui data tanggal terbit sertifikat.

- **URL:** `/sertifikat/{id_sertifikat}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (JSON):**
```json
{
    "tanggal_terbit": "2026-06-18"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Sertifikat berhasil diupdate",
    "data": { ... }
}
```

---

### 93. Upload File Sertifikat
Upload atau timpa file PDF sertifikat yang sudah ada.

- **URL:** `/sertifikat/{id_sertifikat}/upload`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body (Multipart/form-data):**
    - `file_sertifikat`: File PDF (Wajib, max 10MB)

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "File sertifikat berhasil diupload",
    "data": { ... }
}
```

---

### 94. Download File Sertifikat
Mendapatkan URL untuk mengunduh file sertifikat.

- **URL:** `/sertifikat/{id_sertifikat}/download`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": {
        "url": "/storage/sertifikats/uuid_filename.pdf",
        "nomor_sertifikat": "SERT/2026/06/0001",
        "nama_file": "uuid_filename.pdf"
    }
}
```

---

### 95. Verifikasi Sertifikat
Verifikasi keaslian sertifikat berdasarkan nomor sertifikat. Endpoint ini bisa diakses secara publik (tidak perlu token jika diatur di routing).

- **URL:** `/sertifikat/verify/{nomor_sertifikat}`
- **Method:** `GET`

- **Response Sukses (200 OK - Valid):**
```json
{
    "status": "success",
    "valid": true,
    "message": "Sertifikat valid",
    "data": {
        "nomor_sertifikat": "SERT/2026/06/0001",
        "tanggal_terbit": "17 June 2026",
        "peserta": { ... },
        "template": { ... }
    }
}
```

- **Response Error (404 Not Found - Tidak Valid):**
```json
{
    "status": "error",
    "message": "Sertifikat tidak valid atau tidak ditemukan",
    "valid": false
}
```

---

### 96. Statistik Sertifikat
Mendapatkan statistik penggunaan sertifikat secara keseluruhan.

- **URL:** `/sertifikat/statistik`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "data": {
        "summary": {
            "total_sertifikat": 150,
            "total_penerima": 120,
            "total_template_digunakan": 5,
            "terbit_pertama": "2026-01-01",
            "terbit_terakhir": "2026-06-17"
        },
        "per_bulan": [
            {
                "bulan": "2026-06-01 00:00:00",
                "jumlah": 45
            }
        ]
    }
}
```

## 🎭 Roles & Permissions (Abilities)
Setiap token yang dihasilkan memiliki **Abilities** sesuai dengan role user:
- **Admin:** `admin:*`
- **Dosen:** `dosen:*`
- **Mahasiswa:** `mahasiswa:*`

FE dapat mengecek scope token ini jika diperlukan untuk permission-based UI.

## 🛠️ Tips untuk Frontend (FE)
1. **Header:** Pastikan selalu mengirim header `Accept: application/json` agar Laravel mengembalikan response dalam format JSON (terutama saat error validasi).
2. **Storage:** Simpan `token` di LocalStorage atau Cookie yang aman (HttpOnly lebih disarankan jika di production).
3. **Interceptor:** Gunakan Axios Interceptor untuk otomatis menyisipkan header `Authorization: Bearer <token>` pada setiap request ke endpoint yang diproteksi.
