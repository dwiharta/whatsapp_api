# Backend PHP - API WhatsApp

Upload folder `backend` ke hosting yang mendukung PHP + MySQL + HTTPS.

1. Import `database.sql` ke MySQL.
2. Edit `config/database.php` sesuai database hosting.
3. Edit `config/fonnte.php` dan masukkan token Fonnte.
4. Edit `config/cors.php` dan ganti `https://USERNAME.github.io` dengan URL GitHub Pages kamu.
5. Isi URL backend di `frontend/config.js`.

Backend wajib HTTPS karena session cookie menggunakan `SameSite=None; Secure`.
