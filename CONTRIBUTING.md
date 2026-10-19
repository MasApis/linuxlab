# 🤝 Panduan Berkontribusi pada LinuxLab

Terima kasih telah tertarik untuk berkontribusi pada **LinuxLab**! Platform ini adalah proyek sumber terbuka (*open-source*) yang dibangun untuk membantu siapa saja belajar Linux secara interaktif dan aman.

Kami menyambut segala bentuk kontribusi: perbaikan bug, penambahan materi kurikulum baru, perbaikan dokumentasi, maupun penyempurnaan fitur.

---

## 📋 Alur Kontribusi (Git Workflow)

1. **Fork Repositori**  
   Klik tombol **Fork** di pojok kanan atas halaman repositori GitHub ini untuk membuat salinan di akun Anda sendiri.

2. **Kloning ke Komputer Lokal**  
   ```bash
   git clone https://github.com/USERNAME-ANDA/linuxlab.git
   cd linuxlab
   ```

3. **Buat Branch Fitur Baru**  
   Gunakan nama branch yang deskriptif:
   ```bash
   git checkout -b feat/tambah-materi-networking
   # atau
   git checkout -b fix/perbaikan-timer-terminal
   ```

4. **Siapkan Lingkungan Pengembangan Lokal**  
   Ikuti panduan di [README.md](README.md) atau [DOKUMENTASI.md](DOKUMENTASI.md):
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

5. **Lakukan Perubahan & Uji Secara Menyeluruh**  
   Sebelum mengajukan perubahan, pastikan seluruh pengujian otomatis lulus:
   ```bash
   # Jalankan test PHP backend
   php artisan test

   # Jalankan pemeriksaan tipe TypeScript & build frontend
   npm run build
   ```

6. **Commit & Push**  
   Gunakan format *Conventional Commits*:
   - `feat(...)`: Fitur baru.
   - `fix(...)`: Perbaikan bug.
   - `docs(...)`: Perubahan dokumentasi.
   - `test(...)`: Penambahan atau pembaruan test.

   ```bash
   git add .
   git commit -m "feat(module): tambahkan latihan redirection di modul 3"
   git push origin feat/tambah-materi-networking
   ```

7. **Buka Pull Request (PR)**  
   - Buka halaman repositori asli di GitHub.
   - Klik tombol **Compare & pull request**.
   - Jelaskan secara singkat perubahan yang Anda lakukan, alasan perubahan, dan hasil pengujian lokal Anda.

---

## 📐 Standar Koding & Prinsip Desain

- **Backend:** Mengikuti standar PSR-12 Laravel. Letakkan logika bisnis di `app/Services/`, bukan menumpuk di `app/Http/Controllers/`.
- **Frontend:** Mengikuti Vue 3 Composition API dengan TypeScript ketat (`strict: true`). Hindari penggunaan tipe `any`.
- **Keamanan Terminal:** Jangan pernah mengekspos Docker daemon ke browser atau mengeksekusi input pengguna di server host utama. Seluruh eksekusi shell harus di dalam container terisolasi.
- **Bahasa Antarmuka:** Seluruh teks antarmuka pengguna menggunakan **Bahasa Indonesia**. Nama perintah dan istilah teknis Linux tetap dalam bentuk aslinya.

---

## 🐛 Melaporkan Masalah (Bug Report)

Jika Anda menemukan bug:
1. Periksa tab **Issues** untuk memastikan masalah tersebut belum dilaporkan oleh orang lain.
2. Jika belum ada, buat **New Issue** baru dengan menyertakan:
   - Deskripsi langkah-langkah untuk mereproduksi bug.
   - Hasil yang diharapkan vs hasil yang sebenarnya terjadi.
   - Tangkapan layar (*screenshot*) atau pesan error dari konsol jika ada.

---

*Terima kasih telah membantu menjadikan LinuxLab media belajar yang lebih baik bagi semua orang!* 🐧
