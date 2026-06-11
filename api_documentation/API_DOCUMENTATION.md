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
    "waktu_berakhir": "10:00"
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
    "waktu_berakhir": "12:00"
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
    "link_kelas_daring": "https://meet.google.com/xyz-abcd-efg"
}
```
*Catatan: Field `id_jadwal` tidak boleh dikirim karena tidak dapat diubah. Tanggal pelaksanaan boleh tanggal lampau (berbeda dengan endpoint POST).*

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

### 36. Tambah Tugas (Dosen)
Dosen membuat tugas baru di sesi pertemuan tertentu. Dosen harus merupakan pemilik sesi (dosen pengampu jadwal perkuliahan).

- **URL:** `/sesi/{sesi_id}/tugas`
- **Method:** `POST`
- **Role:** `Dosen`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "judul": "Tugas 1: Membuat Aplikasi CRUD",
    "deskripsi": "Buatlah aplikasi CRUD sederhana menggunakan Laravel 11 sesuai materi yang telah dipelajari.",
    "deadline": "2026-06-20T23:59:00"
}
```

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Tugas berhasil dibuat.",
    "data": {
        "id_tugas": "uuid-string",
        "id_sesi": "uuid-sesi",
        "judul": "Tugas 1: Membuat Aplikasi CRUD",
        "deskripsi": "Buatlah aplikasi CRUD sederhana menggunakan Laravel 11 sesuai materi yang telah dipelajari.",
        "deadline": "2026-06-20T23:59:00.000000Z",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Sesi pertemuan tidak ditemukan."
}
```

- **Response Error (403 Forbidden - Bukan Dosen Pengampu):**
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke sesi ini."
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "message": "Judul tugas wajib diisi. (and other validation messages)",
    "errors": {
        "judul": ["Judul tugas wajib diisi."],
        "deadline": ["Deadline harus di waktu yang akan datang."]
    }
}
```

---

### 37. Daftar Tugas di Sesi (Dosen & Mahasiswa)
Mengambil daftar tugas yang ada di sesi pertemuan tertentu dengan pagination (maksimal 20 data per halaman).

- **URL:** `/sesi/{sesi_id}/tugas`
- **Method:** `GET`
- **Role:** `Dosen`, `Mahasiswa`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params (Optional):**
    - `per_page` (integer, default: 20, max: 20): Jumlah data per halaman
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Daftar tugas berhasil diambil.",
    "data": [
        {
            "id_tugas": "uuid-string",
            "id_sesi": "uuid-sesi",
            "judul": "Tugas 1: Membuat Aplikasi CRUD",
            "deskripsi": "Buatlah aplikasi CRUD sederhana...",
            "deadline": "2026-06-20T23:59:00.000000Z",
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 1
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

### 38. Detail Tugas (Dosen & Mahasiswa)
Mengambil detail satu tugas berdasarkan ID, termasuk informasi sesi pertemuan dan jadwal perkuliahan.

- **URL:** `/tugas/{id}`
- **Method:** `GET`
- **Role:** `Dosen`, `Mahasiswa`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "data": {
        "id_tugas": "uuid-string",
        "id_sesi": "uuid-sesi",
        "judul": "Tugas 1: Membuat Aplikasi CRUD",
        "deskripsi": "Buatlah aplikasi CRUD sederhana menggunakan Laravel 11 sesuai materi yang telah dipelajari.",
        "deadline": "2026-06-20T23:59:00.000000Z",
        "created_at": "...",
        "updated_at": "...",
        "sesi_pertemuan": {
            "id_sesi": "uuid-sesi",
            "id_jadwal": "uuid-jadwal",
            "pertemuan_ke": 1,
            "judul_sesi": "Pengantar Perkuliahan",
            "tanggal_pelaksanaan": "2026-06-15",
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
                "token_enrollment": "ABCXYZ"
            }
        }
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Tugas tidak ditemukan."
}
```

---

### 39. Update Tugas (Dosen)
Memperbarui data tugas (judul, deskripsi, deadline). Dosen harus merupakan pemilik sesi.

- **URL:** `/tugas/{id}`
- **Method:** `PUT`
- **Role:** `Dosen`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "judul": "Tugas 1: Membuat Aplikasi CRUD (Revisi)",
    "deskripsi": "Deskripsi yang telah diperbarui.",
    "deadline": "2026-06-25T23:59:00"
}
```
*Catatan: Semua field bersifat opsional, hanya field yang dikirim yang akan diupdate.*

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Tugas berhasil diperbarui.",
    "data": {
        "id_tugas": "uuid-string",
        "id_sesi": "uuid-sesi",
        "judul": "Tugas 1: Membuat Aplikasi CRUD (Revisi)",
        "deskripsi": "Deskripsi yang telah diperbarui.",
        "deadline": "2026-06-25T23:59:00.000000Z",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Tugas tidak ditemukan."
}
```

- **Response Error (403 Forbidden - Bukan Dosen Pengampu):**
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke tugas ini."
}
```

---

### 40. Hapus Tugas (Dosen - Soft Delete)
Menghapus tugas menggunakan soft delete (data tidak hilang permanen, hanya ditandai `deleted_at`). Dosen harus merupakan pemilik sesi.

- **URL:** `/tugas/{id}`
- **Method:** `DELETE`
- **Role:** `Dosen`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Tugas berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Tugas tidak ditemukan."
}
```

- **Response Error (403 Forbidden - Bukan Dosen Pengampu):**
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke tugas ini."
}
```

---

### 41. Daftar Pengumpulan Tugas (Dosen)
Melihat seluruh pengumpulan tugas yang telah dilakukan oleh mahasiswa. Dosen harus merupakan pemilik sesi.

- **URL:** `/tugas/{id}/pengumpulan`
- **Method:** `GET`
- **Role:** `Dosen`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params (Optional):**
    - `per_page` (integer, default: 20, max: 20): Jumlah data per halaman
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Daftar pengumpulan tugas berhasil diambil.",
    "data": [
        {
            "id_pengumpulan": "uuid-string",
            "id_tugas": "uuid-tugas",
            "id_mahasiswa": "uuid-mahasiswa",
            "file_url": "tugas/abc123.pdf",
            "nilai": 85,
            "catatan_dosen": "Bagus, pertahankan.",
            "created_at": "...",
            "updated_at": "...",
            "mahasiswa": {
                "id_user": "uuid-mahasiswa",
                "nama_lengkap": "Budi Rahardjo",
                "nomor_induk": "20241001",
                "email": "20241001@mhs.uika.ac.id"
            }
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 1
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Tugas tidak ditemukan."
}
```

- **Response Error (403 Forbidden - Bukan Dosen Pengampu):**
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke tugas ini."
}
```

---

### 42. Beri Nilai Pengumpulan (Dosen)
Memberikan nilai (0-100) dan catatan dosen ke pengumpulan tugas mahasiswa. Dosen harus merupakan pemilik sesi.

- **URL:** `/pengumpulan/{id}/nilai`
- **Method:** `PUT`
- **Role:** `Dosen`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nilai": 85,
    "catatan_dosen": "Pengerjaan bagus, tapi perlu perbaikan pada validasi input."
}
```

- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Nilai berhasil diberikan.",
    "data": {
        "id_pengumpulan": "uuid-string",
        "id_tugas": "uuid-tugas",
        "id_mahasiswa": "uuid-mahasiswa",
        "file_url": "tugas/abc123.pdf",
        "nilai": 85,
        "catatan_dosen": "Pengerjaan bagus, tapi perlu perbaikan pada validasi input.",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data pengumpulan tidak ditemukan."
}
```

- **Response Error (403 Forbidden - Bukan Dosen Pengampu):**
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses untuk menilai pengumpulan ini."
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "message": "Nilai wajib diisi. (and other validation messages)",
    "errors": {
        "nilai": ["Nilai wajib diisi.", "Nilai minimal 0.", "Nilai maksimal 100."]
    }
}
```

---

### 43. Kumpulkan Tugas (Mahasiswa)
Mahasiswa mengumpulkan tugas dengan mengunggah file. Hanya bisa dilakukan sebelum deadline dan hanya boleh 1 kali per tugas.

- **URL:** `/tugas/{id}/kumpul`
- **Method:** `POST`
- **Role:** `Mahasiswa`
- **Headers:**
    - `Authorization: Bearer <token>`
    - `Content-Type: multipart/form-data`
- **Request Body (Form Data):**
```
file: [binary file]
```
*Catatan: Maksimal ukuran file 10 MB.*

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Tugas berhasil dikumpulkan.",
    "data": {
        "id_pengumpulan": "uuid-string",
        "id_tugas": "uuid-tugas",
        "id_mahasiswa": "uuid-mahasiswa",
        "file_url": "tugas/abc123.pdf",
        "nilai": null,
        "catatan_dosen": null,
        "created_at": "...",
        "updated_at": "..."
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Tugas tidak ditemukan."
}
```

- **Response Error (403 Forbidden - Melewati Deadline):**
```json
{
    "success": false,
    "message": "Deadline pengumpulan sudah terlewat."
}
```

- **Response Error (422 Unprocessable Entity - Sudah Pernah Mengumpulkan):**
```json
{
    "success": false,
    "message": "Anda sudah mengumpulkan tugas ini."
}
```

- **Response Validasi Gagal (422 Unprocessable Entity):**
```json
{
    "message": "File tugas wajib diunggah. (and other validation messages)",
    "errors": {
        "file": ["File tugas wajib diunggah.", "Ukuran file maksimal 10 MB."]
    }
}
```

---

### 44. Status Pengumpulan Tugas Saya (Mahasiswa)
Mahasiswa melihat status pengumpulan tugas miliknya sendiri. Response berisi status pengumpulan (`belum_dikumpulkan`, `menunggu_penilaian`, atau `sudah_dinilai`).

- **URL:** `/tugas/{id}/pengumpulan/saya`
- **Method:** `GET`
- **Role:** `Mahasiswa`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses - Belum Dikumpulkan (200 OK):**
```json
{
    "success": true,
    "message": "Anda belum mengumpulkan tugas ini.",
    "data": {
        "status": "belum_dikumpulkan",
        "pengumpulan": null
    }
}
```

- **Response Sukses - Menunggu Penilaian (200 OK):**
```json
{
    "success": true,
    "message": "Status pengumpulan berhasil diambil.",
    "data": {
        "status": "menunggu_penilaian",
        "pengumpulan": {
            "id_pengumpulan": "uuid-string",
            "id_tugas": "uuid-tugas",
            "id_mahasiswa": "uuid-mahasiswa",
            "file_url": "tugas/abc123.pdf",
            "nilai": null,
            "catatan_dosen": null,
            "created_at": "...",
            "updated_at": "..."
        }
    }
}
```

- **Response Sukses - Sudah Dinilai (200 OK):**
```json
{
    "success": true,
    "message": "Status pengumpulan berhasil diambil.",
    "data": {
        "status": "sudah_dinilai",
        "pengumpulan": {
            "id_pengumpulan": "uuid-string",
            "id_tugas": "uuid-tugas",
            "id_mahasiswa": "uuid-mahasiswa",
            "file_url": "tugas/abc123.pdf",
            "nilai": 85,
            "catatan_dosen": "Pengerjaan bagus, tapi perlu perbaikan pada validasi input.",
            "created_at": "...",
            "updated_at": "..."
        }
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Tugas tidak ditemukan."
}
```

---

### 45. Daftar Semua Tugas (Admin)
Mengambil seluruh tugas lintas sesi dengan pagination (maksimal 20 data per halaman).

- **URL:** `/admin/tugas`
- **Method:** `GET`
- **Role:** `Admin`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params (Optional):**
    - `per_page` (integer, default: 20, max: 20): Jumlah data per halaman
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Daftar semua tugas berhasil diambil.",
    "data": [
        {
            "id_tugas": "uuid-string",
            "id_sesi": "uuid-sesi",
            "judul": "Tugas 1: Membuat Aplikasi CRUD",
            "deskripsi": "Buatlah aplikasi CRUD sederhana...",
            "deadline": "2026-06-20T23:59:00.000000Z",
            "created_at": "...",
            "updated_at": "...",
            "sesi_pertemuan": {
                "id_sesi": "uuid-sesi",
                "id_jadwal": "uuid-jadwal",
                "pertemuan_ke": 1,
                "judul_sesi": "Pengantar Perkuliahan",
                "tanggal_pelaksanaan": "2026-06-15",
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
                    "token_enrollment": "ABCXYZ"
                }
            }
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 1
    }
}
```

---

### 46. Hapus Pengumpulan Tugas (Admin - Soft Delete)
Admin menghapus data pengumpulan tugas menggunakan soft delete.

- **URL:** `/admin/pengumpulan/{id}`
- **Method:** `DELETE`
- **Role:** `Admin`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Data pengumpulan berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data pengumpulan tidak ditemukan."
}
```

---

## 🎭 Roles & Permissions (Abilities)
Setiap token yang dihasilkan memiliki **Abilities** sesuai dengan role user:
- **Admin:** `admin:*`
- **Dosen:** `dosen:*`
- **Mahasiswa:** `mahasiswa:*`

FE dapat mengecek scope token ini jika diperlukan untuk permission-based UI.

### Middleware Role-Based
Beberapa endpoint dilindungi dengan middleware role yang memastikan hanya user dengan role tertentu yang dapat mengakses:
- `role:Admin` — Hanya Admin yang dapat mengakses
- `role:Dosen` — Hanya Dosen yang dapat mengakses
- `role:Mahasiswa` — Hanya Mahasiswa yang dapat mengakses
- `role:Dosen,Mahasiswa` — Dosen dan Mahasiswa dapat mengakses

Jika user tidak memiliki akses, API akan mengembalikan response:
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke fitur ini."
}
```
dengan status code `403 Forbidden`.

## 🛠️ Tips untuk Frontend (FE)
1. **Header:** Pastikan selalu mengirim header `Accept: application/json` agar Laravel mengembalikan response dalam format JSON (terutama saat error validasi).
2. **Storage:** Simpan `token` di LocalStorage atau Cookie yang aman (HttpOnly lebih disarankan jika di production).
3. **Interceptor:** Gunakan Axios Interceptor untuk otomatis menyisipkan header `Authorization: Bearer <token>` pada setiap request ke endpoint yang diproteksi.
