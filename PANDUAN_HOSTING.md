# 🚀 Panduan Lengkap Hosting & Deployment LinuxLab

> **Buku Panduan untuk Pemula:**  
> Panduan ini disusun langkah demi langkah dari nol untuk memandu Anda menyewa server, memasang seluruh perangkat lunak, menghubungkan domain, memasang sertifikat keamanan SSL gratis (HTTPS), hingga website **LinuxLab** siap digunakan oleh masyarakat umum di internet.

---

## 📑 Daftar Isi
1. [Penting: Jenis Hosting yang Dibutuhkan (Jangan Salah Pilih!)](#1-penting-jenis-hosting-yang-dibutuhkan-jangan-salah-pilih)
2. [Spesifikasi Server VPS yang Direkomendasikan](#2-spesifikasi-server-vps-yang-direkomendasikan)
3. [Langkah 1: Menyiapkan Server VPS Baru](#3-langkah-1-menyiapkan-server-vps-baru)
4. [Langkah 2: Memasang Software yang Dibutuhkan di Server](#4-langkah-2-memasang-software-yang-dibutuhkan-di-server)
5. [Langkah 3: Mengunggah Kode & Konfigurasi LinuxLab](#5-langkah-3-mengunggah-kode--konfigurasi-linuxlab)
6. [Langkah 4: Menjalankan WebSocket Gateway dengan PM2](#6-langkah-4-menjalankan-websocket-gateway-dengan-pm2)
7. [Langkah 5: Konfigurasi Web Server Nginx (Web + WebSocket)](#7-langkah-5-konfigurasi-web-server-nginx-web--websocket)
8. [Langkah 6: Memasang SSL Gratis (HTTPS & WSS) dengan Certbot](#8-langkah-6-memasang-ssl-gratis-https--wss-dengan-certbot)
9. [Langkah 7: Menyiapkan Pembersih Sesi Otomatis (Cron Job)](#9-langkah-7-menyiapkan-pembersih-sesi-otomatis-cron-job)
10. [Checklist Final Sebelum Publikasi](#10-checklist-final-sebelum-publikasi)
11. [Panduan Update & Pemeliharaan di Masa Depan](#11-panduan-update--pemeliharaan-di-masa-depan)

---

## 1. Penting: Jenis Hosting yang Dibutuhkan (Jangan Salah Pilih!)

### ❌ TIDAK BISA Menggunakan Shared Hosting Biasa (cPanel Murah)
Banyak orang pemula membeli "Shared Hosting" murah (seperti cPanel biasa). **LinuxLab tidak bisa berjalan di Shared Hosting biasa** karena:
1. LinuxLab membutuhkan izin untuk menjalankan **Docker** (membuat kontainer mini Linux untuk murid latihan). Shared hosting tidak mengizinkan Docker demi alasan keamanan server bersama.
2. LinuxLab membutuhkan proses background **Node.js WebSocket Gateway** yang harus hidup terus-menerus 24 jam.

### ✅ HARUS Menggunakan VPS (Virtual Private Server) / Cloud Server
Anda harus menyewa sebuah **VPS**. VPS adalah komputer virtual utuh milik Anda sendiri di mana Anda memiliki akses penuh tertinggi (`root`) untuk memasang software apa pun.

**Penyedia VPS Populer yang Murah & Bagus:**
- **Luar Negeri:** DigitalOcean, Linode / Akamai, Hetzner, Vultr, Contabo, AWS Lightsail.
- **Indonesia (Bisa bayar pakai QRIS/Transfer Bank):** IDCloudHost, DomaiNesia (pilih paket Cloud VPS), Biznet Gio, Niagahoster (pilih paket VPS, bukan web hosting biasa).

---

## 2. Spesifikasi Server VPS yang Direkomendasikan

Saat menyewa VPS, pilih sistem operasi **Ubuntu 24.04 LTS** atau **Ubuntu 22.04 LTS**.

| Keperluan | RAM | CPU Core | Kapasitas Disk (SSD) | Estimasi Biaya |
|---|:---:|:---:|:---:|:---:|
| **Uji Coba / Skala Kecil** (1–5 murid aktif bersamaan) | **2 GB** | 1–2 Core | 25 – 40 GB SSD | Rp 80.000 – Rp 120.000 / bln ($5–$7) |
| **Produksi Nyaman** (5–15 murid aktif bersamaan) | **4 GB** | 2 Core | 50 – 80 GB SSD | Rp 150.000 – Rp 250.000 / bln ($10–$15) |

*(Setiap container latihan murid memakan RAM sekitar 50–100MB dari batas maksimal 256MB, jadi RAM 2–4 GB sangat ideal).*

---

## 3. Langkah 1: Menyiapkan Server VPS Baru

Setelah menyewa VPS, Anda akan mendapatkan alamat **IP Server** (contoh: `103.180.20.15`) dan kata sandi root dari penyedia hosting.

### A. Masuk ke Server via SSH
Buka terminal di laptop Anda (Linux/macOS) atau aplikasi *Command Prompt / PowerShell / PuTTY* di Windows, lalu ketik:
```bash
ssh root@IP_SERVER_ANDA
```
*(Masukkan kata sandi saat diminta).*

### B. Perbarui Seluruh Sistem
Jalankan perintah ini agar semua program dasar server dalam kondisi terbaru:
```bash
apt update && apt upgrade -y
```

### C. Pasang Firewall Dasar (UFW)
Lindungi server Anda dari serangan luar dengan hanya membuka port penting:
```bash
# Izinkan akses SSH (agar Anda tidak terkunci), Web biasa, dan Web aman
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp

# Aktifkan firewall
ufw enable
```
*(Ketik `y` dan tekan Enter).*

---

## 4. Langkah 2: Memasang Software yang Dibutuhkan di Server

Jalankan perintah berikut satu per satu di server VPS Anda:

### A. Pasang Nginx (Web Server) & Git
```bash
apt install -y nginx git curl unzip
```

### B. Pasang PHP 8.2/8.3 Beserta Ekstensi yang Dibutuhkan
```bash
apt install -y php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-gd php-cli
```

### C. Pasang Composer (Manajer Paket PHP)
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### D. Pasang Node.js & NPM (Versi 20 LTS)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

### E. Pasang PM2 (Pengelola Proses Background)
PM2 berfungsi menjaga WebSocket Gateway tetap hidup dan otomatis menyala kembali jika server restart:
```bash
npm install -g pm2
```

### F. Pasang Database MySQL Server
```bash
apt install -y mysql-server
```
Masuk ke MySQL untuk membuat database dan akun user khusus LinuxLab:
```bash
mysql -u root
```
Di dalam layar MySQL (`mysql>`), ketik perintah berikut (ganti `PasswordKuatAnda123!` dengan kata sandi rahasia Anda):
```sql
CREATE DATABASE linuxlab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'linuxlab_user'@'localhost' IDENTIFIED BY 'PasswordKuatAnda123!';
GRANT ALL PRIVILEGES ON linuxlab.* TO 'linuxlab_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### G. Pasang Docker & Berikan Izin ke Web Server
```bash
apt install -y docker.io
systemctl enable --now docker

# Berikan izin ke user web server (www-data) agar Laravel bisa memerintah Docker
usermod -aG docker www-data
```

---

## 5. Langkah 3: Mengunggah Kode & Konfigurasi LinuxLab

### A. Kloning Proyek ke Folder Web Server
```bash
cd /var/www
git clone <URL_REPOSITORI_GIT_ANDA> linuxlab
cd /var/www/linuxlab
```

### B. Pasang Dependensi Backend & Frontend
```bash
# Pasang paket PHP (mode produksi: lebih cepat & tanpa paket dev)
composer install --no-dev --optimize-autoloader

# Pasang paket JavaScript & bangun tampilan
npm install
npm run build
```

### C. Konfigurasi File Lingkungan (`.env`)
Salin berkas contoh:
```bash
cp .env.example .env
```
Buka dan edit file `.env`:
```bash
nano .env
```
Ubah bagian-bagian penting berikut:
```ini
APP_NAME=LinuxLab
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

# Database (sesuaikan dengan yang dibuat di Langkah 2F)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=linuxlab
DB_USERNAME=linuxlab_user
DB_PASSWORD=PasswordKuatAnda123!

# Konfigurasi Sesi Terminal
TERMINAL_IMAGE=linuxlab-terminal:latest
TERMINAL_CPU_LIMIT=0.5
TERMINAL_MEMORY_LIMIT=256m
TERMINAL_SESSION_TTL=30
TERMINAL_MAX_SESSIONS=10
TERMINAL_WS_PORT=3001

# Penanda Tangan Sertifikat
CERTIFICATE_SIGNATORY_NAME="Dosen Pengampu / Tim LinuxLab"
CERTIFICATE_SIGNATORY_TITLE="Kepala Laboratorium Komputer"
```
*(Tekan `Ctrl + O` lalu `Enter` untuk menyimpan, lalu `Ctrl + X` untuk keluar dari nano).*

### D. Generate Key & Persiapan Database
```bash
# Buat kunci enkripsi aplikasi
php artisan key:generate

# Bangun image Docker untuk ruang latihan murid
docker build -t linuxlab-terminal:latest docker/terminal/

# Jalankan migrasi tabel database dan masukkan materi kurikulum awal
php artisan migrate --force --seed

# Optimasi performa Laravel untuk production (cache config & routes)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### E. Atur Kepemilikan & Izin File Folder
Pastikan web server (`www-data`) memiliki hak penuh untuk menulis file cache, log, dan sesi:
```bash
chown -R www-data:www-data /var/www/linuxlab
chmod -R 775 /var/www/linuxlab/storage /var/www/linuxlab/bootstrap/cache
```

---

## 6. Langkah 4: Menjalankan WebSocket Gateway dengan PM2

WebSocket Terminal Gateway adalah jembatan yang menghubungkan ketikan browser murid ke dalam Docker. Kita jalankan menggunakan PM2 agar hidup terus-menerus:

```bash
cd /var/www/linuxlab

# Jalankan gateway dengan PM2
pm2 start bin/terminal-gateway.mjs --name "linuxlab-gateway"

# Simpan agar otomatis hidup saat server di-reboot
pm2 save
pm2 startup
```
*(Jika muncul perintah yang disarankan oleh PM2, salin dan jalankan perintah tersebut).*

Cek statusnya:
```bash
pm2 status
```
*(Pastikan status `linuxlab-gateway` berwarna hijau / **online**).*

---

## 7. Langkah 5: Konfigurasi Web Server Nginx (Web + WebSocket)

Nginx bertindak sebagai gerbang terdepan yang menerima permintaan pengunjung internet, melayani file web, mengarahkan PHP ke PHP-FPM, dan menyalurkan koneksi WebSocket terminal ke port 3001.

Buat file konfigurasi Nginx baru:
```bash
nano /etc/nginx/sites-available/linuxlab
```
Tempelkan konfigurasi berikut (ganti `domain-anda.com` dengan nama domain asli Anda):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/linuxlab/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;

    # 1. Jalur Utama Web Laravel (Inertia + Vue)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # 2. Penanganan Eksekusi Script PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # 3. Jalur Proxy WebSocket Terminal (PENTING!)
    location /ws/terminal {
        proxy_pass http://127.0.0.1:3001/ws/terminal;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
    }

    # Blokir akses ke file tersembunyi seperti .env atau .git
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
*(Catatan: jika versi PHP Anda adalah PHP 8.3, ganti baris fastcgi_pass menjadi `unix:/var/run/php/php8.3-fpm.sock` jika diperlukan).*

Aktifkan konfigurasi Nginx tersebut:
```bash
# Buat tautan simbolik
ln -s /etc/nginx/sites-available/linuxlab /etc/nginx/sites-enabled/

# Hapus situs default bawaan Nginx agar tidak bentrok
rm /etc/nginx/sites-enabled/default

# Periksa apakah konfigurasi sudah benar (harus muncul syntax is ok)
nginx -t

# Muat ulang Nginx
systemctl reload nginx
```

---

## 8. Langkah 6: Memasang SSL Gratis (HTTPS & WSS) dengan Certbot

Sertifikat SSL wajib digunakan agar website dapat dibuka dengan aman menggunakan **HTTPS** (gembok hijau), dan agar koneksi terminal WebSocket terenkripsi menggunakan **WSS (WebSocket Secure)**.

### A. Arahkan Domain ke IP Server Anda
Sebelum memasang SSL, pastikan di panel domain Anda (misal Rumahweb, Niagahoster, Cloudflare, Namecheap), Anda sudah menambahkan DNS Record:
- **Tipe:** `A`
- **Nama/Host:** `@` (dan `www`)
- **Nilai/Target:** `IP_SERVER_VPS_ANDA`
*(Tunggu 5-15 menit agar DNS terhubung).*

### B. Pasang Certbot & Dapatkan Sertifikat Otomatis
Jalankan perintah ini di server:
```bash
apt install -y certbot python3-certbot-nginx

# Minta sertifikat SSL otomatis dari Let's Encrypt
certbot --nginx -d domain-anda.com -d www.domain-anda.com
```
- Masukkan alamat email Anda.
- Ketik `Y` untuk menyetujui persyaratan.
- Certbot akan otomatis mengubah file Nginx Anda dan mengaktifkan HTTPS serta perpanjangan otomatis (*auto-renewal*).

Selesai! Sekarang buka `https://domain-anda.com` di browser Anda! 🎉

---

## 9. Langkah 7: Menyiapkan Pembersih Sesi Otomatis (Cron Job)

Di LinuxLab, kontainer murid yang sudah lewat dari 30 menit harus dibersihkan agar kapasitas server tidak penuh. Kita atur jadwal otomatis di server:

Buka crontab:
```bash
crontab -e
```
*(Jika diminta memilih editor, pilih `1` untuk nano).*

Gulir ke baris paling bawah, lalu tempelkan baris ini:
```bash
# Jalankan pembersihan sesi kedaluwarsa LinuxLab setiap 10 menit
*/10 * * * * cd /var/www/linuxlab && php artisan terminal:clean-expired >> /dev/null 2>&1
```
*(Simpan dengan `Ctrl + O`, `Enter`, lalu `Ctrl + X`).*

---

## 10. Checklist Final Sebelum Publikasi

Sebelum mengumumkan website ke teman-teman, dosen, atau murid, pastikan hal berikut:

- [ ] **Mode Debug Mati**: Buka `.env`, pastikan `APP_DEBUG=false` agar error teknis tidak terlihat oleh publik.
- [ ] **Uji Pendaftaran Akun**: Coba daftar akun baru di halaman `/register`.
- [ ] **Uji Buka Terminal**: Buka salah satu bab pelajaran, klik *Mulai Praktik*, dan pastikan terminal hitam xterm.js terbuka serta merespons ketikan perintah (seperti `pwd` dan `ls`).
- [ ] **Uji Penilaian**: Coba kerjakan salah satu latihan dan klik *Periksa Jawaban*.
- [ ] **Uji Sertifikat**: Selesaikan seluruh modul atau buat akun uji untuk mencoba unduh sertifikat PDF dan periksa di menu *Verifikasi Sertifikat*.
- [ ] **Cek Akun Admin**: Pastikan Anda bisa login ke `/login` menggunakan akun administrator dan menu `/admin` terbuka normal.

---

## 11. Panduan Update & Pemeliharaan di Masa Depan

Jika di kemudian hari Anda menambahkan materi baru, mengubah desain, atau memperbaiki kode di komputer lokal dan mengunggahnya ke GitHub/GitLab, berikut cara memperbarui website di server VPS Anda:

```bash
# 1. Masuk ke folder proyek
cd /var/www/linuxlab

# 2. Tarik kode terbaru dari Git
git pull origin main

# 3. Perbarui paket (jika ada paket baru)
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 4. Jalankan migrasi database (jika ada tabel/kolom baru)
php artisan migrate --force

# 5. Bersihkan dan perbarui cache Laravel
php artisan optimize:clear
php artisan optimize

# 6. Restart WebSocket Gateway
pm2 restart linuxlab-gateway

# 7. Pastikan hak akses file tetap milik www-data
chown -R www-data:www-data /var/www/linuxlab
```

### Memeriksa Status Kontainer di Server
Kapan pun Anda ingin melihat ada berapa murid yang sedang latihan saat ini:
```bash
docker ps
```
Anda akan melihat daftar kontainer aktif yang sedang berjalan beserta sisa memorinya.

---

*Selamat! Aplikasi LinuxLab Anda kini sudah resmi online di internet dan siap menjadi media pembelajaran Linux yang interaktif bagi siapa saja! 🚀*
