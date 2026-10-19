# 🐧 LinuxLab — Platform Belajar Linux Interaktif Berbasis Web

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v1-9553E9?style=flat&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Docker](https://img.shields.io/badge/Docker-Terminal%20Isolation-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com)
[![Tests](https://img.shields.io/badge/Pest%20Tests-94%20passed-brightgreen?style=flat)](tests)

**LinuxLab** adalah platform pembelajaran sistem operasi Linux interaktif berbasis web yang dirancang khusus untuk pemula. Platform ini menyediakan materi terstruktur, terminal Linux nyata di dalam container terisolasi, latihan praktik dengan mesin penilaian otomatis (*auto-grading*), pelacakan progres, lencana penghargaan (*badges*), papan peringkat (*leaderboard*), forum diskusi terikat pelajaran, penerbitan & verifikasi sertifikat PDF, serta panel administrator lengkap.

> 📖 **Panduan Ramah Pemula:** Untuk penjelasan lengkap yang mudah dipahami orang awam (dilengkapi analogi, alur detail, penjelasan kode, dan FAQ), silakan baca **[DOKUMENTASI.md](DOKUMENTASI.md)**.  
> 🛠️ **Panduan Arsitektur & Kode (Teknis):** Penjelasan mendalam struktur backend, frontend, service layer, integrasi terminal Docker, dan aliran data dapat dibaca di **[DOKUMENTASI_TEKNIS.md](DOKUMENTASI_TEKNIS.md)**.  
> 🌐 **Panduan Hosting & Deployment:** Panduan langkah demi langkah menyewa VPS, setup domain, SSL HTTPS, Nginx, Docker, dan PM2 untuk publikasi dapat dibaca di **[PANDUAN_HOSTING.md](PANDUAN_HOSTING.md)**.  
> 🎯 **Spesifikasi Kebutuhan Teknis:** Sumber kebenaran fitur (*Single Source of Truth*) dan aturan arsitektur tersedia di **[SPEC.md](SPEC.md)**.

---

## 🚀 Fitur Utama

- **Terminal Linux Nyata di Browser**: Berinteraksi langsung dengan shell Linux asli (bukan simulasi teks) melalui emulator xterm.js dan WebSocket gateway internal.
- **Isolasi Penuh & Aman**: Setiap sesi terminal berjalan di dalam container Docker tersendiri yang dibatasi CPU, memori (256MB), proses, non-root (`student`), dan tanpa akses jaringan keluar (`--network none`).
- **Penilaian Otomatis (Auto-Grading)**: Memeriksa hasil latihan secara riil di dalam container (`system_state`, `output`, atau `combined`) dengan umpan balik langsung.
- **Gamifikasi & Capaian**: Sistem lencana otomatis (*Badges*), pencatatan progres per modul & pelajaran, serta papan peringkat (*Leaderboard*) publik sepanjang waktu.
- **Sertifikat Kelulusan Resmi**: Terbit otomatis satu kali setelah menyelesaikan 100% seluruh modul terbitan, berformat PDF, dan dapat diverifikasi secara publik via kode unik.
- **Forum Diskusi Terintegrasi**: Diskusi tanya-jawab yang terikat langsung pada bab materi pelajaran dengan dukungan upvote dan penanda jawaban terbaik.
- **Panel Administrator**: Manajemen penuh modul, pelajaran, latihan, hint, validator JSON, moderasi forum, kelola pengguna, dan statistik sistem.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+), Eloquent ORM, Sesi Laravel
- **Frontend**: Vue 3 (Composition API), TypeScript, Inertia.js, Tailwind CSS
- **Terminal Emulator**: xterm.js + fit-addon
- **Terminal Transport**: WebSocket Gateway (Node.js daemon di port 3001)
- **Containerization**: Docker (Linux Container)
- **Database**: MySQL (15 tabel terstruktur)
- **PDF Generation**: dompdf (Sertifikat kelulusan)
- **Testing**: Pest PHP (94 feature & unit tests)

---

## ⚡ Panduan Instalasi Cepat (NFR-05)

### Prasyarat
- PHP 8.2+ dengan ekstensi `pdo_mysql`, `mbstring`, `gd`, `curl`
- Composer
- Node.js 18+ & npm
- MySQL 8.0+
- Docker daemon berjalan

### Langkah Pemasangan:

```bash
# 1. Kloning repositori & masuk ke direktori
git clone <url-repo-anda> linuxlab
cd linuxlab

# 2. Pasang dependensi PHP dan JavaScript
composer install
npm install

# 3. Siapkan file konfigurasi lingkungan (.env)
cp .env.example .env
php artisan key:generate

# 4. Sesuaikan konfigurasi database dan kredensial di file .env:
# DB_DATABASE=linuxlab, DB_USERNAME=..., DB_PASSWORD=...

# 5. Bangun image Docker untuk container praktik terminal
docker build -t linuxlab-terminal:latest docker/terminal/

# 6. Jalankan migrasi tabel database dan masukkan konten awal (seeders)
php artisan migrate --seed

# 7. Bangun aset frontend
npm run build
```

---

## 🖥️ Menjalankan Layanan (Development)

Buka 3 jendela terminal terpisah untuk menjalankan seluruh komponen:

```bash
# Terminal 1: Jalankan Web Server Laravel
php artisan serve

# Terminal 2: Jalankan WebSocket Terminal Gateway (Port 3001)
node bin/terminal-gateway.mjs

# Terminal 3 (Opsional): Jalankan Vite untuk hot-reload frontend saat koding
npm run dev
```

Aplikasi dapat langsung diakses di peramban Anda melalui:
👉 **`http://127.0.0.1:8000`**

---

## 🧪 Menjalankan Pengujian

```bash
# Menjalankan seluruh test backend (Pest PHP)
php artisan test

# Memeriksa kompilasi TypeScript dan build frontend
npm run build
```

---

## 📄 Lisensi & Hak Cipta

Proyek ini dibangun untuk tujuan edukasi platform pembelajaran Linux interaktif.  
Dokumentasi lengkap: **[DOKUMENTASI.md](DOKUMENTASI.md)**.
