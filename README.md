<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

# 🛒 E-Commerce API — Laravel Backend

E-Commerce API ini adalah backend RESTful API sederhana yang dibangun dengan Laravel. Proyek ini cocok untuk kebutuhan toko online seperti manajemen produk, otentikasi pengguna, keranjang belanja, dan pemrosesan pesanan. API ini dirancang untuk terhubung dengan frontend seperti Nuxt.js atau aplikasi mobile.

---

## 🚀 Fitur Utama

- 🔐 Autentikasi pengguna dengan Laravel Sanctum (Login/Register)
- 📦 CRUD Produk dan Kategori
- 🛒 Sistem Keranjang & Checkout
- 📄 JSON API yang mudah diintegrasikan
- 📊 Manajemen pesanan pengguna
- 📬 Notifikasi email
- 💳 Integrasi pembayaran (Midtrans)

---

## 🛠️ Teknologi yang Digunakan

- [Laravel](https://laravel.com/) 10+
- [PHP](https://www.php.net/) 8+
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/)
- [Sanctum](https://laravel.com/docs/sanctum)

---

## 🔐 Otentikasi API
API ini menggunakan Laravel Sanctum. Setelah login, user akan mendapatkan token Bearer untuk mengakses endpoint yang dilindungi.

---

## 📬 Contoh Endpoint

| Method | Endpoint        | Keterangan              |
| ------ | --------------- | ----------------------- |
| POST   | `/api/register` | Registrasi pengguna     |
| POST   | `/api/login`    | Login pengguna          |
| GET    | `/api/products` | Lihat semua produk      |
| POST   | `/api/products` | Tambah produk (admin)   |
| GET    | `/api/orders`   | Daftar pesanan pengguna |

---

## 🧪 Testing & Dokumentasi API
Gunakan Postman atau aplikasi sejenis untuk mencoba endpoint. Dokumentasi lengkap bisa ditambahkan melalui Postman collection atau Swagger (opsional).

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
