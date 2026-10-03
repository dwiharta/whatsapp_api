# Versi Siap GitHub Pages

Frontend dan backend sudah dipisahkan.

- `frontend/` -> GitHub Pages
- `backend/` -> hosting PHP + MySQL + HTTPS

Alur: GitHub Pages -> fetch() -> PHP API -> MySQL / Fonnte

GitHub Pages tidak menjalankan PHP, sehingga backend tetap membutuhkan hosting PHP.

## Instalasi
1. Upload `backend` ke hosting PHP + MySQL.
2. Import `backend/database.sql`.
3. Atur `backend/config/database.php`.
4. Isi token di `backend/config/fonnte.php`.
5. Atur URL GitHub Pages di `backend/config/cors.php`.
6. Atur URL backend di `frontend/config.js`.
7. Upload isi `frontend` ke repository GitHub Pages.

Jangan taruh token Fonnte di frontend/GitHub Pages.
