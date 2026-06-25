# Dokumentasi API - Project LMS

Dokumentasi ini berisi informasi detail mengenai seluruh endpoint API yang telah diimplementasikan pada backend menggunakan Laravel Sanctum. Endpoint dikelompokkan berdasarkan role pengguna.

## Informasi Umum

- **Base URL:** `http://localhost:8000/api`
- **Content-Type:** `application/json`
- **Accept:** `application/json`
- **Autentikasi:** Menggunakan Bearer Token via Laravel Sanctum

---

## Autentikasi (Semua Role)

### 1. Login

Digunakan untuk mendapatkan token akses. User bisa login menggunakan salah satu dari: **Email**, **Username**, atau **Nomor Induk**.

- **URL:** `/login`
- **Method:** `POST`
- **Rate Limit:** 5 request per menit
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
```json
{
    "success": false,
    "message": "Akun Anda sedang dalam proses verifikasi oleh Admin."
}
```

- **Response Error (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Email, Username, atau Nomor Induk wajib diisi.",
    "errors": {
        "identifier": ["Email, Username, atau Nomor Induk wajib diisi."],
        "password": ["Password minimal 8 karakter."]
    }
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Endpoint tidak ditemukan."
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

- **Response Error (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Endpoint tidak ditemukan."
}
```

---

### 3. Get User Profile

Mendapatkan data user yang sedang login.

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
    "created_at": "2026-06-21T10:00:00.000000Z",
    "updated_at": "2026-06-21T10:00:00.000000Z"
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data user tidak ditemukan."
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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Endpoint registrasi tidak ditemukan."
}
```

---

## Role: Admin

### Manajemen Dosen

#### 5. Daftar Antrean Verifikasi Dosen

Melihat daftar dosen yang telah melakukan registrasi. Admin dapat memfilter berdasarkan status persetujuan.

- **URL:** `/verifikasi-dosen`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params:**
    - `status` (optional): `Menunggu`, `Disetujui`, atau `Ditolak`

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
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ],
    "total": 1,
    "per_page": 10
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data verifikasi dosen tidak ditemukan."
}
```

---

#### 6. Proses Verifikasi Dosen

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data dosen tidak ditemukan."
}
```

---

#### 7. Update Data Dosen

Memperbarui data profil dosen. Password tidak akan berubah.

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data dosen tidak ditemukan."
}
```

---

#### 8. Hapus Data Dosen

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data dosen tidak ditemukan."
}
```

---

### Manajemen Mahasiswa

#### 9. Tambah Data Mahasiswa

Menambahkan data mahasiswa baru secara manual. Sistem akan otomatis men-generate **Email** dan **Password default**.

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
*Catatan: Email otomatis berformat `{nomor_induk}@mhs.uika.ac.id` dan Password default berformat `Mhs{nomor_induk}`.*

- **Response Sukses (201 Created):**
```json
{
    "success": true,
    "message": "Data mahasiswa berhasil ditambahkan. Email dan sandi default telah dibuat."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Endpoint tidak ditemukan."
}
```

---

#### 10. Daftar Data Mahasiswa

Mendapatkan list data seluruh mahasiswa dengan pagination (50 data per halaman).

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
            "created_at": "2026-06-21T10:00:00.000000Z",
            "updated_at": "2026-06-21T10:00:00.000000Z"
        }
    ],
    "total": 1,
    "per_page": 50
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

#### 11. Detail Data Mahasiswa

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
    "created_at": "2026-06-21T10:00:00.000000Z",
    "updated_at": "2026-06-21T10:00:00.000000Z"
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

#### 12. Update Data Mahasiswa

Memperbarui data profil mahasiswa. Jika `nomor_induk` diubah, `email` akan di-generate ulang secara otomatis.

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

#### 13. Hapus Data Mahasiswa

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mahasiswa tidak ditemukan."
}
```

---

### Manajemen Kelas

#### 14. Daftar Master Kelas

Mengambil seluruh data master kelas dengan pagination (10 data per halaman).

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
            "created_at": "2026-06-21T10:00:00.000000Z",
            "updated_at": "2026-06-21T10:00:00.000000Z"
        }
    ],
    "total": 1,
    "per_page": 10
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

#### 15. Detail Master Kelas

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
    "created_at": "2026-06-21T10:00:00.000000Z",
    "updated_at": "2026-06-21T10:00:00.000000Z"
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

#### 16. Tambah Master Kelas

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
        "created_at": "2026-06-21T10:00:00.000000Z",
        "updated_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

#### 17. Update Master Kelas

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

#### 18. Hapus Master Kelas

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data kelas tidak ditemukan."
}
```

---

### Manajemen Mata Kuliah

#### 19. Daftar Master Mata Kuliah

Mengambil seluruh data master mata kuliah dengan pagination (10 data per halaman).

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
            "created_at": "2026-06-21T10:00:00.000000Z",
            "updated_at": "2026-06-21T10:00:00.000000Z"
        }
    ],
    "total": 1,
    "per_page": 10
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

#### 20. Detail Master Mata Kuliah

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
    "created_at": "2026-06-21T10:00:00.000000Z",
    "updated_at": "2026-06-21T10:00:00.000000Z"
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

#### 21. Tambah Master Mata Kuliah

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
*Catatan: Field `deskripsi`, `semester`, `fakultas`, dan `prodi` bersifat opsional (nullable). `semester` harus integer antara 1-14.*

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
        "created_at": "2026-06-21T10:00:00.000000Z",
        "updated_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

#### 22. Update Master Mata Kuliah

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

#### 23. Hapus Master Mata Kuliah

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah tidak ditemukan."
}
```

---

### Manajemen Jadwal Perkuliahan

#### 24. Daftar Jadwal Perkuliahan

Mengambil seluruh data jadwal perkuliahan dengan Eager Loading. Pagination 10 data per halaman.

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
            "created_at": "2026-06-21T10:00:00.000000Z",
            "updated_at": "2026-06-21T10:00:00.000000Z",
            "mata_kuliah": {
                "id_mk": "uuid-string",
                "kode_mk": "IF101",
                "nama_mk": "Algoritma dan Pemrograman",
                "sks": 3
            },
            "kelas": {
                "id_kelas": "uuid-string",
                "nama_kelas": "Kelas A",
                "kode_kelas": "KLS-A"
            },
            "dosen": {
                "id_user": "uuid-string",
                "nama_lengkap": "Dr. Budi Santoso",
                "email": "budi@univ.ac.id"
            }
        }
    ],
    "total": 1,
    "per_page": 10
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

#### 25. Detail Jadwal Perkuliahan

Mengambil detail satu jadwal perkuliahan berdasarkan ID.

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
    "created_at": "2026-06-21T10:00:00.000000Z",
    "updated_at": "2026-06-21T10:00:00.000000Z",
    "mata_kuliah": {
        "id_mk": "uuid-string",
        "kode_mk": "IF101",
        "nama_mk": "Algoritma dan Pemrograman"
    },
    "kelas": {
        "id_kelas": "uuid-string",
        "nama_kelas": "Kelas A",
        "kode_kelas": "KLS-A"
    },
    "dosen": {
        "id_user": "uuid-string",
        "nama_lengkap": "Dr. Budi Santoso",
        "email": "budi@univ.ac.id"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

#### 26. Tambah Jadwal Perkuliahan

Menambahkan data jadwal perkuliahan baru. SKS diambil otomatis dari master mata kuliah. Token enrollment di-generate otomatis 6 karakter huruf kapital unik.

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
*Catatan: Field `sks` dan `token_enrollment` tidak perlu dikirim. `semester` berupa integer (1-14).*

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
        "created_at": "2026-06-21T10:00:00.000000Z",
        "updated_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data mata kuliah, kelas, atau dosen tidak ditemukan."
}
```

---

#### 27. Update Jadwal Perkuliahan

Memperbarui data jadwal perkuliahan yang sudah ada. Token enrollment tidak berubah.

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

#### 28. Hapus Jadwal Perkuliahan

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

### Manajemen Peserta Kelas

#### 29. Daftar Peserta Kelas by Jadwal

Mengambil daftar seluruh peserta yang terdaftar pada jadwal tertentu.

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
            "created_at": "2026-06-21T10:00:00.000000Z",
            "updated_at": "2026-06-21T10:00:00.000000Z",
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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Data jadwal perkuliahan tidak ditemukan.",
    "data": null
}
```

---

### Manajemen Tugas (Admin)

#### 30. Daftar Tugas

Mengambil semua data tugas yang ada di sistem.

- **URL:** `/admin/tugas`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar tugas berhasil diambil.",
    "data": [
        {
            "id": "uuid-string",
            "judul_tugas": "Tugas Pertemuan 1",
            "deskripsi_tugas": "Kerjakan soal algoritma berikut.",
            "batas_waktu": "2026-07-01 23:59:59",
            "link_cbt": "https://cbt.uika.ac.id/exam/123",
            "token_cbt": "ABC123"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

### Manajemen Pertanyaan Evaluasi

#### 31. Daftar Pertanyaan Evaluasi

Mengambil seluruh data pertanyaan evaluasi.

- **URL:** `/pertanyaan-evaluasi`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar pertanyaan berhasil diambil.",
    "data": [
        {
            "id_pertanyaan": "uuid-string",
            "kategori": "Pengajaran",
            "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
            "urutan": 1,
            "is_aktif": true
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan evaluasi tidak ditemukan."
}
```

---

#### 32. Daftar Pertanyaan Aktif

Mengambil daftar pertanyaan evaluasi yang berstatus aktif.

- **URL:** `/pertanyaan-evaluasi/aktif`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar pertanyaan aktif berhasil diambil.",
    "data": [
        {
            "id_pertanyaan": "uuid-string",
            "kategori": "Pengajaran",
            "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
            "urutan": 1,
            "is_aktif": true
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan aktif tidak ditemukan."
}
```

---

#### 33. Daftar Kategori Pertanyaan

Mengambil daftar kategori pertanyaan evaluasi yang tersedia.

- **URL:** `/pertanyaan-evaluasi/kategori`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar kategori berhasil diambil.",
    "data": ["Pengajaran", "Kedisiplinan", "Materi"]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data kategori tidak ditemukan."
}
```

---

#### 34. Detail Pertanyaan

Mengambil detail satu pertanyaan evaluasi berdasarkan ID.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail pertanyaan berhasil diambil.",
    "data": {
        "id_pertanyaan": "uuid-string",
        "kategori": "Pengajaran",
        "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
        "urutan": 1,
        "is_aktif": true
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan tidak ditemukan."
}
```

---

#### 35. Buat Pertanyaan

Menambahkan pertanyaan evaluasi baru.

- **URL:** `/pertanyaan-evaluasi`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "kategori": "Pengajaran",
    "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
    "urutan": 1,
    "is_aktif": true
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Pertanyaan evaluasi berhasil dibuat.",
    "data": {
        "id_pertanyaan": "uuid-string",
        "kategori": "Pengajaran",
        "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
        "urutan": 1,
        "is_aktif": true
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Endpoint tidak ditemukan."
}
```

---

#### 36. Update Pertanyaan

Memperbarui data pertanyaan evaluasi yang sudah ada.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "kategori": "Kedisiplinan",
    "teks_pertanyaan": "Bagaimana kedisiplinan dosen dalam mengajar?",
    "urutan": 2,
    "is_aktif": true
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Pertanyaan evaluasi berhasil diperbarui.",
    "data": {
        "id_pertanyaan": "uuid-string",
        "kategori": "Kedisiplinan",
        "teks_pertanyaan": "Bagaimana kedisiplinan dosen dalam mengajar?",
        "urutan": 2,
        "is_aktif": true
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan tidak ditemukan."
}
```

---

#### 37. Hapus Pertanyaan

Menghapus pertanyaan evaluasi secara permanen.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Pertanyaan evaluasi berhasil dihapus."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan tidak ditemukan."
}
```

---

#### 38. Toggle Status Aktif Pertanyaan

Mengubah status aktif pertanyaan evaluasi menjadi aktif atau nonaktif.

- **URL:** `/pertanyaan-evaluasi/{id_pertanyaan}/toggle`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Status pertanyaan berhasil diubah.",
    "data": {
        "id_pertanyaan": "uuid-string",
        "is_aktif": false
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan tidak ditemukan."
}
```

---

#### 39. Update Urutan Bulk

Memperbarui urutan beberapa pertanyaan sekaligus.

- **URL:** `/pertanyaan-evaluasi/bulk-urutan`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "urutan": [
        {
            "id_pertanyaan": "uuid-pertanyaan-1",
            "urutan": 1
        },
        {
            "id_pertanyaan": "uuid-pertanyaan-2",
            "urutan": 2
        }
    ]
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Urutan pertanyaan berhasil diperbarui."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan tidak ditemukan."
}
```

---

### Manajemen Template Sertifikat

#### 40. Daftar Template Sertifikat

Mengambil seluruh data template sertifikat.

- **URL:** `/template-sertifikat`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar template berhasil diambil.",
    "data": [
        {
            "id_template": "uuid-string",
            "nama_template": "Sertifikat Kelulusan",
            "file_background": "backgrounds/template1.png",
            "is_aktif": true,
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template tidak ditemukan."
}
```

---

#### 41. Daftar Template Aktif

Mengambil daftar template sertifikat yang berstatus aktif.

- **URL:** `/template-sertifikat/aktif`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar template aktif berhasil diambil.",
    "data": [
        {
            "id_template": "uuid-string",
            "nama_template": "Sertifikat Kelulusan",
            "is_aktif": true
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template aktif tidak ditemukan."
}
```

---

#### 42. Detail Template Sertifikat

Mengambil detail satu data template sertifikat berdasarkan ID.

- **URL:** `/template-sertifikat/{id_template}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail template berhasil diambil.",
    "data": {
        "id_template": "uuid-string",
        "nama_template": "Sertifikat Kelulusan",
        "file_background": "backgrounds/template1.png",
        "is_aktif": true,
        "created_at": "2026-06-21T10:00:00.000000Z",
        "updated_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template tidak ditemukan."
}
```

---

#### 43. Buat Template Sertifikat

Membuat template sertifikat baru.

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
    "message": "Template sertifikat berhasil dibuat.",
    "data": {
        "id_template": "uuid-string",
        "nama_template": "Sertifikat Kelulusan",
        "is_aktif": true
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Endpoint tidak ditemukan."
}
```

---

#### 44. Update Template Sertifikat

Memperbarui data template sertifikat yang sudah ada.

- **URL:** `/template-sertifikat/{id_template}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nama_template": "Sertifikat Kelulusan Updated",
    "is_aktif": false
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Template sertifikat berhasil diperbarui.",
    "data": {
        "id_template": "uuid-string",
        "nama_template": "Sertifikat Kelulusan Updated",
        "is_aktif": false
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template tidak ditemukan."
}
```

---

#### 45. Hapus Template Sertifikat

Menghapus template sertifikat secara permanen.

- **URL:** `/template-sertifikat/{id_template}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Template sertifikat berhasil dihapus."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template tidak ditemukan."
}
```

---

#### 46. Toggle Status Aktif Template

Mengubah status aktif template sertifikat.

- **URL:** `/template-sertifikat/{id_template}/toggle`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Status template berhasil diubah.",
    "data": {
        "id_template": "uuid-string",
        "is_aktif": false
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template tidak ditemukan."
}
```

---

#### 47. Upload Background Template

Mengunggah gambar background untuk template sertifikat.

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
    "message": "Background template berhasil diupload.",
    "data": {
        "id_template": "uuid-string",
        "file_background": "backgrounds/template1.png"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template tidak ditemukan."
}
```

---

#### 48. Download Background Template

Mengunduh file background dari template sertifikat.

- **URL:** `/template-sertifikat/{id_template}/download-background`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Background template berhasil diunduh.",
    "data": {
        "url": "/storage/backgrounds/template1.png"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template atau file background tidak ditemukan."
}
```

---

### Manajemen Sertifikat

#### 49. Daftar Sertifikat

Mengambil semua sertifikat dengan dukungan filter dan pagination (15 data per halaman).

- **URL:** `/sertifikat`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params:**
    - `id_peserta` (opsional): Filter berdasarkan ID peserta
    - `id_template` (opsional): Filter berdasarkan ID template
    - `dari_tanggal` (opsional): Filter awal rentang tanggal (YYYY-MM-DD)
    - `sampai_tanggal` (opsional): Filter akhir rentang tanggal (YYYY-MM-DD)
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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data sertifikat tidak ditemukan."
}
```

---

#### 50. Terbitkan Sertifikat

Menerbitkan sertifikat baru untuk satu peserta. Nomor sertifikat digenerate otomatis.

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
    "data": {
        "id_sertifikat": "uuid-string",
        "nomor_sertifikat": "SERT/2026/06/0002"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data peserta atau template tidak ditemukan."
}
```

---

#### 51. Terbitkan Sertifikat Bulk

Menerbitkan sertifikat untuk banyak peserta sekaligus.

- **URL:** `/sertifikat/bulk`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
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
    "data": [
        {
            "id_sertifikat": "uuid-string-1",
            "nomor_sertifikat": "SERT/2026/06/0002"
        },
        {
            "id_sertifikat": "uuid-string-2",
            "nomor_sertifikat": "SERT/2026/06/0003"
        }
    ],
    "total": 2
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data template atau peserta tidak ditemukan."
}
```

---

#### 52. Update Sertifikat

Memperbarui data tanggal terbit sertifikat.

- **URL:** `/sertifikat/{id_sertifikat}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
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
    "data": {
        "id_sertifikat": "uuid-string",
        "nomor_sertifikat": "SERT/2026/06/0002"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data sertifikat tidak ditemukan."
}
```

---

#### 53. Upload File Sertifikat

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
    "data": {
        "id_sertifikat": "uuid-string",
        "nomor_sertifikat": "SERT/2026/06/0002"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data sertifikat tidak ditemukan."
}
```

---

#### 54. Statistik Sertifikat

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data statistik sertifikat tidak ditemukan."
}
```

---

## Role: Dosen

### Manajemen Sesi Pertemuan

#### 55. Tambah Sesi Pertemuan

Menambahkan data sesi pertemuan untuk suatu jadwal perkuliahan. Sesi diperiksa terhadap bentrok waktu dan duplikasi nomor pertemuan.

- **URL:** `/sesi-pertemuan`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_jadwal": "uuid-jadwal",
    "pertemuan_ke": 1,
    "judul_sesi": "Pengantar Perkuliahan",
    "tanggal_pelaksanaan": "2026-06-15",
    "jam_mulai": "08:00",
    "jam_berakhir": "10:00",
    "metode_pertemuan": "synchronous",
    "link_kelas_daring": "https://meet.google.com/abc-defg-hij"
}
```

- **Response Sukses (201 Created):**
```json
{
    "status": "success",
    "message": "Sesi pertemuan berhasil dibuat.",
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
        "created_at": "2026-06-21T10:00:00.000000Z",
        "updated_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data jadwal perkuliahan tidak ditemukan."
}
```

---

#### 56. Daftar Sesi Pertemuan

Mengambil seluruh data sesi pertemuan dengan pagination dan filter opsional.

- **URL:** `/sesi-pertemuan`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params (Optional):**
    - `per_page` (integer, default: 10)
    - `id_jadwal` (uuid)
    - `tanggal` (YYYY-MM-DD)
    - `metode_pertemuan` (`synchronous` atau `asynchronous`)

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
                "jadwal_perkuliahan": {
                    "id_jadwal": "uuid-jadwal",
                    "hari": "Senin",
                    "waktu_mulai": "08:00",
                    "waktu_berakhir": "10:00"
                }
            }
        ],
        "total": 1,
        "per_page": 10
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data sesi pertemuan tidak ditemukan."
}
```

---

#### 57. Detail Sesi Pertemuan

Mengambil detail satu data sesi pertemuan berdasarkan ID.

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
        "jadwal_perkuliahan": {
            "id_jadwal": "uuid-jadwal",
            "semester": 1,
            "hari": "Senin",
            "waktu_mulai": "08:00",
            "waktu_berakhir": "10:00"
        }
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 58. Update Sesi Pertemuan

Memperbarui data sesi pertemuan. Field `id_jadwal` tidak dapat diubah.

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
*Catatan: `status` dapat berupa TERJADWAL, BERJALAN, atau SELESAI.*

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
        "link_kelas_daring": "https://meet.google.com/xyz-abcd-efg"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 59. Hapus Sesi Pertemuan

Menghapus data sesi pertemuan secara permanen.

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

### Manajemen Tugas

#### 60. Buat Tugas

Menambahkan tugas baru untuk satu sesi pertemuan.

- **URL:** `/sesi/{sesi_id}/tugas`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "judul_tugas": "Tugas Pertemuan 1",
    "deskripsi_tugas": "Kerjakan soal algoritma berikut.",
    "batas_waktu": "2026-07-01 23:59:59",
    "link_cbt": "https://cbt.uika.ac.id/exam/123",
    "token_cbt": "ABC123"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Tugas berhasil dibuat.",
    "data": {
        "id": "uuid-string",
        "judul_tugas": "Tugas Pertemuan 1",
        "batas_waktu": "2026-07-01 23:59:59"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 61. Daftar Tugas di Sesi

Mengambil semua tugas yang terdaftar pada satu sesi pertemuan.

- **URL:** `/sesi/{sesi_id}/tugas`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar tugas berhasil diambil.",
    "data": [
        {
            "id": "uuid-string",
            "judul_tugas": "Tugas Pertemuan 1",
            "deskripsi_tugas": "Kerjakan soal algoritma berikut.",
            "batas_waktu": "2026-07-01 23:59:59",
            "link_cbt": "https://cbt.uika.ac.id/exam/123"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 62. Detail Tugas

Mengambil detail satu data tugas berdasarkan ID.

- **URL:** `/tugas/{id}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail tugas berhasil diambil.",
    "data": {
        "id": "uuid-string",
        "judul_tugas": "Tugas Pertemuan 1",
        "deskripsi_tugas": "Kerjakan soal algoritma berikut.",
        "batas_waktu": "2026-07-01 23:59:59",
        "link_cbt": "https://cbt.uika.ac.id/exam/123",
        "token_cbt": "ABC123"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

#### 63. Update Tugas

Memperbarui data tugas yang sudah ada.

- **URL:** `/tugas/{id}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "judul_tugas": "Tugas Pertemuan 1 Updated",
    "deskripsi_tugas": "Kerjakan soal algoritma yang diperbarui.",
    "batas_waktu": "2026-07-05 23:59:59",
    "link_cbt": "https://cbt.uika.ac.id/exam/456",
    "token_cbt": "DEF456"
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Tugas berhasil diperbarui.",
    "data": {
        "id": "uuid-string",
        "judul_tugas": "Tugas Pertemuan 1 Updated"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

#### 64. Hapus Tugas

Menghapus data tugas secara permanen.

- **URL:** `/tugas/{id}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Tugas berhasil dihapus."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

#### 65. Cek Deadline Tugas

Memeriksa apakah deadline tugas sudah lewat atau belum.

- **URL:** `/tugas/{id}/deadline`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Informasi deadline berhasil diambil.",
    "data": {
        "id": "uuid-string",
        "batas_waktu": "2026-07-01 23:59:59",
        "is_expired": false,
        "sisa_waktu": "8 hari lagi"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

#### 66. Get Launch URL Tugas

Mendapatkan URL untuk membuka tugas CBT peserta tertentu.

- **URL:** `/tugas/{id}/launch/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "URL tugas berhasil digenerate.",
    "data": {
        "launch_url": "https://cbt.uika.ac.id/exam/123?token=ABC123&peserta=uuid-peserta"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas atau peserta tidak ditemukan."
}
```

---

### Manajemen Nilai CBT

#### 67. Simpan Nilai CBT

Menyimpan nilai CBT untuk satu atau lebih peserta.

- **URL:** `/nilai-cbt`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nilai": [
        {
            "id_tugas": "uuid-tugas",
            "id_peserta": "uuid-peserta",
            "nilai": 85
        }
    ]
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Nilai CBT berhasil disimpan.",
    "data": {
        "total_disimpan": 1
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas atau peserta tidak ditemukan."
}
```

---

#### 68. Nilai CBT per Tugas

Mengambil semua nilai CBT untuk satu tugas tertentu.

- **URL:** `/   `
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Nilai CBT per tugas berhasil diambil.",
    "data": [
        {
            "id_nilai": "uuid-string",
            "id_tugas": "uuid-tugas",
            "id_peserta": "uuid-peserta",
            "nilai": 85,
            "peserta": {
                "nama_lengkap": "Budi Rahardjo",
                "nomor_induk": "20241001"
            }
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

#### 69. Detail Nilai CBT

Mengambil detail nilai CBT satu peserta untuk satu tugas.

- **URL:** `/nilai-cbt/{id_tugas}/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail nilai CBT berhasil diambil.",
    "data": {
        "id_nilai": "uuid-string",
        "id_tugas": "uuid-tugas",
        "id_peserta": "uuid-peserta",
        "nilai": 85
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data nilai CBT tidak ditemukan."
}
```

---

#### 70. Update Nilai CBT

Memperbarui nilai CBT yang sudah ada.

- **URL:** `/nilai-cbt/{id_nilai}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "nilai": 90
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Nilai CBT berhasil diperbarui.",
    "data": {
        "id_nilai": "uuid-string",
        "nilai": 90
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data nilai CBT tidak ditemukan."
}
```

---

#### 71. Hapus Nilai CBT

Menghapus nilai CBT secara permanen.

- **URL:** `/nilai-cbt/{id_nilai}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Nilai CBT berhasil dihapus."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data nilai CBT tidak ditemukan."
}
```

---

#### 72. Statistik Nilai Tugas

Mengambil statistik nilai CBT untuk satu tugas tertentu.

- **URL:** `/nilai-cbt/tugas/{id_tugas}/statistik`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Statistik nilai tugas berhasil diambil.",
    "data": {
        "id_tugas": "uuid-tugas",
        "jumlah_peserta": 30,
        "rata_rata": 78.5,
        "nilai_tertinggi": 100,
        "nilai_terendah": 45
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

#### 73. Ranking Nilai Tugas

Mengambil ranking nilai CBT peserta untuk satu tugas.

- **URL:** `/nilai-cbt/tugas/{id_tugas}/ranking/{limit?}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Ranking nilai tugas berhasil diambil.",
    "data": [
        {
            "peringkat": 1,
            "nama_lengkap": "Budi Rahardjo",
            "nomor_induk": "20241001",
            "nilai": 100
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data tugas tidak ditemukan."
}
```

---

### Forum Diskusi (Dosen)

#### 74. Daftar Forum Diskusi Sesi

Mengambil semua post forum diskusi dalam satu sesi pertemuan.

- **URL:** `/sesi/{idSesi}/forum`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar forum diskusi berhasil diambil.",
    "data": [
        {
            "id_pesan": "uuid-string",
            "id_sesi": "uuid-sesi",
            "id_pengirim": "uuid-user",
            "isi_pesan": "Selamat datang di forum diskusi pertemuan 1.",
            "id_parent_pesan": null,
            "pengirim": {
                "nama_lengkap": "Dr. Budi Santoso",
                "role": "Dosen"
            },
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 75. Buat Post Forum

Membuat post baru di forum diskusi sesi.

- **URL:** `/forum`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_sesi": "uuid-sesi",
    "isi_pesan": "Ini adalah pesan forum diskusi.",
    "id_parent_pesan": null
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Post forum berhasil dibuat.",
    "data": {
        "id_pesan": "uuid-string",
        "id_sesi": "uuid-sesi",
        "isi_pesan": "Ini adalah pesan forum diskusi.",
        "created_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 76. Detail Post Forum

Mengambil detail satu post forum berdasarkan ID.

- **URL:** `/forum/{idPesan}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail post forum berhasil diambil.",
    "data": {
        "id_pesan": "uuid-string",
        "id_sesi": "uuid-sesi",
        "id_pengirim": "uuid-user",
        "isi_pesan": "Ini adalah pesan forum diskusi.",
        "id_parent_pesan": null,
        "pengirim": {
            "nama_lengkap": "Dr. Budi Santoso",
            "role": "Dosen"
        },
        "created_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Post forum tidak ditemukan."
}
```

---

#### 77. Balasan Forum

Mengambil semua balasan dari satu post forum.

- **URL:** `/forum/{idPesan}/replies`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Balasan forum berhasil diambil.",
    "data": [
        {
            "id_pesan": "uuid-string",
            "id_parent_pesan": "uuid-parent",
            "isi_pesan": "Ini balasan dari dosen.",
            "pengirim": {
                "nama_lengkap": "Dr. Budi Santoso",
                "role": "Dosen"
            },
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Post forum tidak ditemukan."
}
```

---

#### 78. Update Post Forum

Memperbarui isi post forum yang sudah ada.

- **URL:** `/forum/{idPesan}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "isi_pesan": "Isi pesan yang telah diperbarui."
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Post forum berhasil diperbarui.",
    "data": {
        "id_pesan": "uuid-string",
        "isi_pesan": "Isi pesan yang telah diperbarui.",
        "updated_at": "2026-06-21T11:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Post forum tidak ditemukan."
}
```

---

#### 79. Hapus Post Forum

Menghapus post forum secara permanen.

- **URL:** `/forum/{idPesan}`
- **Method:** `DELETE`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Post forum berhasil dihapus."
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Post forum tidak ditemukan."
}
```

---

#### 80. Cari Forum

Mencari post forum berdasarkan kata kunci di dalam satu sesi.

- **URL:** `/sesi/{idSesi}/forum/search`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params:**
    - `q`: Kata kunci pencarian (required)

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Hasil pencarian forum berhasil diambil.",
    "data": [
        {
            "id_pesan": "uuid-string",
            "isi_pesan": "Ini adalah pesan yang cocok dengan pencarian.",
            "pengirim": {
                "nama_lengkap": "Budi Rahardjo"
            },
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

### Rekap Evaluasi (Dosen)

#### 81. Statistik Jawaban Pertanyaan

Mengambil statistik jawaban evaluasi untuk satu pertanyaan.

- **URL:** `/jawaban-evaluasi/pertanyaan/{id_pertanyaan}/statistik`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Statistik jawaban berhasil diambil.",
    "data": {
        "id_pertanyaan": "uuid-string",
        "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
        "rata_rata_skor": 4.2,
        "total_jawaban": 30
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data pertanyaan tidak ditemukan."
}
```

---

#### 82. Statistik Kategori Evaluasi

Mengambil statistik evaluasi dikelompokkan per kategori pertanyaan.

- **URL:** `/jawaban-evaluasi/statistik-kategori`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Statistik kategori evaluasi berhasil diambil.",
    "data": [
        {
            "kategori": "Pengajaran",
            "rata_rata_skor": 4.2,
            "total_jawaban": 90
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data evaluasi tidak ditemukan."
}
```

---

#### 83. Rekap Evaluasi

Mengambil rekap keseluruhan hasil evaluasi.

- **URL:** `/jawaban-evaluasi/rekap`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Rekap evaluasi berhasil diambil.",
    "data": {
        "total_peserta_mengisi": 120,
        "total_peserta_belum_mengisi": 30,
        "rata_rata_keseluruhan": 4.1
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data rekap evaluasi tidak ditemukan."
}
```

---

## Role: Mahasiswa

### Pendaftaran Kelas

#### 84. Pendaftaran Peserta Kelas (Enroll)

Mahasiswa mendaftar ke jadwal perkuliahan menggunakan token enrollment (6 karakter uppercase).

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
        "jadwal": {
            "id_jadwal": "uuid-jadwal",
            "hari": "Senin",
            "waktu_mulai": "08:00"
        },
        "mahasiswa": {
            "id_user": "uuid-mahasiswa",
            "nama_lengkap": "Budi Rahardjo"
        }
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Token enrollment tidak valid atau jadwal tidak ditemukan.",
    "data": null
}
```

---

### Nilai CBT (Mahasiswa)

#### 85. Nilai CBT per Peserta

Mengambil semua nilai CBT milik mahasiswa yang sedang login.

- **URL:** `/nilai-cbt/peserta/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Nilai CBT berhasil diambil.",
    "data": [
        {
            "id_nilai": "uuid-string",
            "id_tugas": "uuid-tugas",
            "nilai": 85,
            "tugas": {
                "judul_tugas": "Tugas Pertemuan 1",
                "batas_waktu": "2026-07-01 23:59:59"
            }
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data peserta tidak ditemukan."
}
```

---

### Forum Diskusi (Mahasiswa)

#### 86. Daftar Forum Diskusi Sesi (Mahasiswa)

Mengambil semua post forum diskusi dalam satu sesi pertemuan.

- **URL:** `/sesi/{idSesi}/forum`
- **Method:** `G`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Daftar forum diskusi berhasil diambil.",
    "data": [
        {
            "id_pesan": "uuid-string",
            "id_sesi": "uuid-sesi",
            "isi_pesan": "Selamat datang di forum diskusi.",
            "pengirim": {
                "nama_lengkap": "Dr. Budi Santoso",
                "role": "Dosen"
            },
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 87. Buat Post Forum (Mahasiswa)

Membuat post atau balasan baru di forum diskusi sesi.

- **URL:** `/forum`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_sesi": "uuid-sesi",
    "isi_pesan": "Saya ingin bertanya tentang materi pertemuan ini.",
    "id_parent_pesan": null
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Post forum berhasil dibuat.",
    "data": {
        "id_pesan": "uuid-string",
        "id_sesi": "uuid-sesi",
        "isi_pesan": "Saya ingin bertanya tentang materi pertemuan ini.",
        "created_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

#### 88. Detail Post Forum (Mahasiswa)

Mengambil detail satu post forum berdasarkan ID.

- **URL:** `/forum/{idPesan}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail post forum berhasil diambil.",
    "data": {
        "id_pesan": "uuid-string",
        "isi_pesan": "Saya ingin bertanya tentang materi pertemuan ini.",
        "pengirim": {
            "nama_lengkap": "Budi Rahardjo",
            "role": "Mahasiswa"
        },
        "created_at": "2026-06-21T10:00:00.000000Z"
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Post forum tidak ditemukan."
}
```

---

#### 89. Balasan Forum (Mahasiswa)

Mengambil semua balasan dari satu post forum.

- **URL:** `/forum/{idPesan}/replies`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Balasan forum berhasil diambil.",
    "data": [
        {
            "id_pesan": "uuid-string",
            "id_parent_pesan": "uuid-parent",
            "isi_pesan": "Ini jawaban dari dosen.",
            "pengirim": {
                "nama_lengkap": "Dr. Budi Santoso",
                "role": "Dosen"
            },
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Post forum tidak ditemukan."
}
```

---

#### 90. Cari Forum (Mahasiswa)

Mencari post forum berdasarkan kata kunci di dalam satu sesi.

- **URL:** `/sesi/{idSesi}/forum/search`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Query Params:**
    - `q`: Kata kunci pencarian (required)

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Hasil pencarian forum berhasil diambil.",
    "data": [
        {
            "id_pesan": "uuid-string",
            "isi_pesan": "Pesan yang cocok dengan pencarian.",
            "pengirim": {
                "nama_lengkap": "Dr. Budi Santoso"
            },
            "created_at": "2026-06-21T10:00:00.000000Z"
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sesi pertemuan tidak ditemukan."
}
```

---

### Evaluasi (Mahasiswa)

#### 91. Simpan Jawaban Evaluasi

Mahasiswa menyimpan jawaban evaluasi untuk satu peserta kelas.

- **URL:** `/jawaban-evaluasi`
- **Method:** `POST`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "id_peserta": "uuid-peserta",
    "jawaban": [
        {
            "id_pertanyaan": "uuid-pertanyaan",
            "skor": 4
        }
    ]
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Jawaban evaluasi berhasil disimpan.",
    "data": {
        "total_jawaban_disimpan": 5
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data peserta atau pertanyaan tidak ditemukan."
}
```

---

#### 92. Jawaban Evaluasi per Peserta

Mengambil semua jawaban evaluasi yang telah diisi oleh satu peserta.

- **URL:** `/jawaban-evaluasi/peserta/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Jawaban evaluasi berhasil diambil.",
    "data": [
        {
            "id_evaluasi": "uuid-string",
            "id_pertanyaan": "uuid-pertanyaan",
            "skor": 4,
            "pertanyaan": {
                "teks_pertanyaan": "Bagaimana kualitas pengajaran dosen?",
                "kategori": "Pengajaran"
            }
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data peserta tidak ditemukan."
}
```

---

#### 93. Detail Jawaban Evaluasi

Mengambil detail jawaban evaluasi peserta untuk satu pertanyaan.

- **URL:** `/jawaban-evaluasi/{id_pertanyaan}/{id_peserta}`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Detail jawaban evaluasi berhasil diambil.",
    "data": {
        "id_evaluasi": "uuid-string",
        "id_pertanyaan": "uuid-pertanyaan",
        "id_peserta": "uuid-peserta",
        "skor": 4
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data jawaban evaluasi tidak ditemukan."
}
```

---

#### 94. Update Jawaban Evaluasi

Memperbarui jawaban evaluasi yang sudah dikirim.

- **URL:** `/jawaban-evaluasi/{id_evaluasi}`
- **Method:** `PUT`
- **Headers:**
    - `Authorization: Bearer <token>`
- **Request Body:**
```json
{
    "skor": 5
}
```

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Jawaban evaluasi berhasil diperbarui.",
    "data": {
        "id_evaluasi": "uuid-string",
        "skor": 5
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data jawaban evaluasi tidak ditemukan."
}
```

---

#### 95. Cek Status Evaluasi Peserta

Memeriksa apakah peserta sudah mengisi evaluasi atau belum.

- **URL:** `/jawaban-evaluasi/peserta/{id_peserta}/status`
- **Method:** `GET`
- **Headers:**
    - `Authorization: Bearer <token>`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "message": "Status evaluasi berhasil diambil.",
    "data": {
        "id_peserta": "uuid-peserta",
        "sudah_mengisi": true,
        "jumlah_jawaban": 5
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data peserta tidak ditemukan."
}
```

---

### Sertifikat (Mahasiswa)

#### 96. Daftar Sertifikat per Peserta

Mengambil semua sertifikat yang dimiliki oleh mahasiswa.

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
            "peserta": {
                "id_peserta": "uuid-peserta",
                "nama_lengkap": "Budi Rahardjo"
            },
            "template": {
                "id_template": "uuid-template",
                "nama_template": "Sertifikat Kelulusan"
            }
        }
    ]
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Data peserta tidak ditemukan."
}
```

---

#### 97. Detail Sertifikat (Mahasiswa)

Mengambil detail satu data sertifikat berdasarkan ID.

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
        "peserta": {
            "id_peserta": "uuid-peserta",
            "nama_lengkap": "Budi Rahardjo"
        },
        "template": {
            "id_template": "uuid-template",
            "nama_template": "Sertifikat Kelulusan"
        }
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sertifikat tidak ditemukan."
}
```

---

#### 98. Download File Sertifikat (Mahasiswa)

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

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sertifikat tidak ditemukan."
}
```

---

#### 99. Verifikasi Sertifikat (Publik)

Verifikasi keaslian sertifikat berdasarkan nomor sertifikat. Endpoint ini dapat diakses tanpa token autentikasi.

- **URL:** `/sertifikat/verify/{nomor_sertifikat}`
- **Method:** `GET`

- **Response Sukses (200 OK):**
```json
{
    "status": "success",
    "valid": true,
    "message": "Sertifikat valid",
    "data": {
        "nomor_sertifikat": "SERT/2026/06/0001",
        "tanggal_terbit": "17 June 2026",
        "peserta": {
            "id_peserta": "uuid-peserta",
            "nama_lengkap": "Budi Rahardjo"
        },
        "template": {
            "id_template": "uuid-template",
            "nama_template": "Sertifikat Kelulusan"
        }
    }
}
```

- **Response Error (401 Unauthorized):**
`json
{
    "success": false,
    "message": "Unauthenticated."
}
`

- **Response Error (404 Not Found):**
```json
{
    "status": "error",
    "message": "Sertifikat tidak valid atau tidak ditemukan.",
    "valid": false
}
```

---

## Roles dan Permissions (Abilities)

Setiap token yang dihasilkan memiliki Abilities sesuai dengan role user:

| Role | Ability Token | Akses |
|---|---|---|
| Admin | `admin:*` | Seluruh endpoint manajemen sistem |
| Dosen | `dosen:*` | Sesi pertemuan, tugas, nilai CBT, forum diskusi |
| Mahasiswa | `mahasiswa:*` | Enroll kelas, forum diskusi, evaluasi, sertifikat |

## Tips untuk Frontend

1. **Header:** Pastikan selalu mengirim header `Accept: application/json` agar Laravel mengembalikan response dalam format JSON.
2. **Storage:** Simpan `token` di LocalStorage atau Cookie yang aman (HttpOnly lebih disarankan di production).
3. **Interceptor:** Gunakan Axios Interceptor untuk otomatis menyisipkan header `Authorization: Bearer <token>` pada setiap request ke endpoint yang diproteksi.
4. **Role Check:** Gunakan field `role` pada response login untuk menentukan halaman awal yang sesuai per role.
