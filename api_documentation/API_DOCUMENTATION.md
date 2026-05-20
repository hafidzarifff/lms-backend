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
    "tahun_angkatan": "2024"
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
    "tahun_angkatan": "2025"
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
    "deskripsi": "Mata kuliah dasar tentang algoritma dan pemrograman."
}
```
*Catatan: Field `deskripsi` bersifat opsional (nullable).*

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
        "sks": ["The sks field must be at least 1."]
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
    "deskripsi": "Mata kuliah lanjutan tentang struktur data."
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
            "semester": "2026 - Ganjil",
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
                "deskripsi": "..."
            },
            "kelas": {
                "id_kelas": "uuid-string",
                "nama_kelas": "Kelas A",
                "kode_kelas": "KLS-A",
                "tahun_angkatan": "2024"
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
    "semester": "2026 - Ganjil",
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
        "deskripsi": "..."
    },
    "kelas": {
        "id_kelas": "uuid-string",
        "nama_kelas": "Kelas A",
        "kode_kelas": "KLS-A",
        "tahun_angkatan": "2024"
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
    "semester": "2026 - Ganjil",
    "hari": "Senin",
    "waktu_mulai": "08:00",
    "waktu_berakhir": "10:00"
}
```
*Catatan: Field `sks` dan `token_enrollment` tidak perlu dikirim. SKS otomatis diambil dari mata kuliah, token otomatis di-generate oleh sistem.*

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
        "semester": "2026 - Ganjil",
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
        "semester": ["Format semester tidak valid. Gunakan format: \"2026 - Ganjil\" atau \"2026 - Genap\"."],
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
    "semester": "2026 - Genap",
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
