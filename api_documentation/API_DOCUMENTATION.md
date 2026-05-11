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
    "message": "Data mahasiswa berhasil diperbarui."
}
```

- **Response Error (404 Not Found):**
```json
{
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
    "message": "Data mahasiswa berhasil dihapus."
}
```

- **Response Error (404 Not Found):**
```json
{
    "message": "Data mahasiswa tidak ditemukan."
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
