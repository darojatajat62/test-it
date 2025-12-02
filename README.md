# 🔐 Laporan Mahasiswa Terintegrasi Whastapp
PHP NATIVE MVC DAN NODE JS**.

---

### 🛡️ Fitur
- Export Excel Mahasiswa
- Kirim Whastapp



## 📐 Wireframe

### Tampilan tidak terkoneksi whastapp
![Warframe](https://raw.githubusercontent.com/darojatajat62/test-it/main/images_github/disconnect.jpeg)

### Sudah terkoneksi whastapp
![Warframe](https://raw.githubusercontent.com/darojatajat62/test-it/main/images_github/connect.jpeg)

### Server Whastapp
![Warframe](https://raw.githubusercontent.com/darojatajat62/test-it/main/images_github/server-whastapp.jpeg)

---
.
🛠️ Prasyarat
PHP 8+
MySQL / MariaDB
Composer
Node.js 18+
npm

# 📦 Instalasi

## 1. Clone Project
```bash
cd htdocs
git clone https://github.com/username-anda/nama-repo.git
cd nama-repo
2. Konfigurasi .env
Salin file contoh:

bash
Copy code
cp .env.example .env
Isi konfigurasi:

ini
Copy code
DB_HOST=localhost
DB_NAME=nama_database
DB_USER=root
DB_PASS=
BASE_URL=http://localhost/nama-repo/public/
APP_DEBUG=true

WHATSAPP_API_URL=http://localhost:3000/

SESSION_LIFETIME=1440
SESSION_SECURE=false
SESSION_HTTPONLY=true


3. Migration & Seeder
bash
Copy code
php run_migration.php


4. Instalasi Node.js WhatsApp Gateway
bash
Copy code
cd node_whatsapp
npm install
npm start
WhatsApp Server:
http://localhost:3000


🚀 Menjalankan Aplikasi
Jalankan WhatsApp Gateway
bash
Copy code
npm start
Akses aplikasi PHP
http://localhost/nama-repo/public/


🎯 Fitur
Export Excel
Kirim Excel via WhatsApp
QR Code WhatsApp
Status WhatsApp (Connected/Disconnected)
PHP Native MVC + Node.js