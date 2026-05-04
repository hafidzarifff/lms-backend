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
    "status": "error",
    "message": "Kredensial tidak valid"
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
