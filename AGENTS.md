# AGENTS.md: LinuxLab

Sebelum mengerjakan apa pun, baca `SPEC.md` di root proyek (sumber kebenaran fitur, skema data, dan keamanan). Rancangan tampilan ada di folder `docs/design/` (wireframe dari laporan proposal). Jangan mengerjakan di luar SPEC.

LinuxLab adalah platform belajar Linux interaktif berbasis web untuk pemula: materi, terminal Linux nyata di dalam container terisolasi, latihan dengan penilaian otomatis, progres, lencana, leaderboard, sertifikat, forum, dan panel admin. `SPEC.md` adalah sumber kebenaran; file ini adalah aturan cara bekerja.

## Tentang Pengguna
Pemula. Jelaskan dengan bahasa Indonesia sederhana, singkat, tanpa istilah yang tidak dijelaskan. Lingkungan pengguna: Linux, aplikasi diuji di browser pada laptop/server pengembangan yang sama (satu server). Sumber daya mesin terbatas: jalankan container terminal seperlunya dan selalu bersihkan sesi yang selesai.

## Aturan Kerja
1. **Satu fase per sesi.** Kerjakan hanya fase/fitur yang diminta di prompt (rencana fase ada di SPEC bagian 15).
2. **Jangan menambah fitur di luar `SPEC.md`.** Perhatikan juga daftar *out of scope* di SPEC bagian 17. Jika ragu atau ada ide, tanya dulu.
3. **Rencanakan dulu** untuk perubahan besar (struktur folder, paket/library baru, skema database, apa pun yang menyangkut Docker/container terminal): tampilkan rencana singkat dan tunggu persetujuan.
4. **Langkah kecil.** Ubah sedikit, jalankan, verifikasi, lanjut.
5. **Sebelum menyatakan selesai**, jalankan `php artisan test` dan `npm run build` (serta pemeriksaan tipe TypeScript bila tersedia di `package.json`), lalu laporkan hasilnya apa adanya. Jangan bilang berhasil jika belum dijalankan atau gagal.
6. **Cek setiap acceptance criteria** dari kode kebutuhan yang dikerjakan (mis. `FR-TERM-01`, `SEC-TERM-01`) dan laporkan satu per satu: terpenuhi atau belum, beserta buktinya.
7. **Tulis test** (Pest/PHPUnit) untuk logika di `app/Services/` dan perilaku penting lainnya: penilaian latihan, progres, lencana, sertifikat, perhitungan leaderboard, upvote, dan RBAC.
8. **Jangan menjalankan `git commit`, `git push`, atau perintah yang menghapus data/database.** Pengguna yang commit sendiri. Cukup sarankan pesan commit.
9. **Jangan memasang paket baru** (Composer/npm) di luar yang disebut SPEC tanpa menjelaskan alasannya dan meminta persetujuan. Jangan mengganti stack: Laravel, Vue 3 + TypeScript, Inertia.js, MySQL.
10. Jika perintah gagal, tunjukkan pesan error, jelaskan penyebabnya dengan sederhana, lalu perbaiki. Jangan menyembunyikan error.
11. Jika mengubah skema data, aturan penilaian, atau aturan fitur lain yang tertulis di SPEC, perbarui `SPEC.md` (naikkan versi dan catat di riwayat perubahan SPEC bagian 18) dan sebutkan di ringkasan.
12. Setelah selesai, tulis ringkasan: file yang diubah, perintah yang perlu dijalankan pengguna, dan apa yang perlu dicek pengguna di browser.

## UI/UX (rancangan di folder `docs/design/`)
- `docs/design/` berisi wireframe halaman (file PNG dari laporan proposal: halaman utama, login/registrasi, dashboard, materi, praktik terminal, progres & pencapaian, forum, panel admin, leaderboard & sertifikat). Baca wireframe yang cocok **sebelum** membuat sebuah halaman.
- Wireframe adalah **rancangan**, bukan desain final dan bukan screenshot aplikasi jadi. Ambil susunan, komponen, dan teks penjelasnya; gaya visual (warna, font, jarak) tetapkan sekali di komponen/layout bersama dan file CSS utama, bukan ditulis ulang di tiap halaman.
- Data contoh di wireframe dan laporan (nama pengguna, angka progres, skor, nama lencana) hanyalah contoh. **Jangan di-hardcode**; ambil dari database.
- Elemen pada wireframe yang fiturnya tidak ada di SPEC **dihilangkan dari halaman** (bukan tombol mati atau placeholder), lalu susunan dirapikan agar tetap seimbang.
- Jangan menampilkan klaim yang tidak benar untuk aplikasi ini (mis. "sertifikat resmi berlisensi", "dijamin aman 100%", statistik pengguna yang tidak ada).
- Jika halaman yang diminta tidak ada wireframe-nya, atau wireframe berbeda dengan SPEC, berhenti, laporkan perbedaannya, dan tanya mana yang dipakai. Jangan menebak.
- Seluruh teks antarmuka dalam **bahasa Indonesia**; istilah Linux (nama perintah, path, permission) tetap dalam bentuk aslinya. Sediakan keadaan kosong (*empty state*) sesuai SPEC UI-02.

## Tech Stack
Laravel (PHP), Vue 3 + TypeScript (Composition API), Inertia.js, MySQL (Eloquent + migration), xterm.js untuk tampilan terminal di browser, container Linux terisolasi untuk eksekusi terminal (hanya lewat backend), autentikasi **sesi Laravel** (bukan JWT, bukan OAuth untuk v1), Pest/PHPUnit untuk test backend, dompdf (atau pustaka PDF Laravel yang disetujui) untuk sertifikat.

## Struktur
```
app/Http/Controllers/    # controller per fitur (Auth, Module, Lesson, Exercise, Forum, Admin, ...)
app/Http/Middleware/     # middleware RBAC (role admin)
app/Models/              # satu model Eloquent per tabel SPEC bagian 8
app/Services/            # TerminalService, ValidationService, ProgressService, BadgeService, CertificateService
database/migrations/     # satu migration per tabel, urutan mengikuti SPEC bagian 8
database/seeders/        # konten awal SPEC bagian 7, lencana awal bagian 12, akun admin awal
resources/js/Pages/      # halaman Inertia (SPEC bagian 9 & 10)
resources/js/Components/ # komponen bersama (Terminal, ProgressBar, BadgeCard, ...)
resources/js/Layouts/    # layout publik, pengguna, admin
routes/web.php
SPEC.md                  # sumber kebenaran (jangan dipindah)
```
- Browser **tidak pernah** berkomunikasi langsung dengan container atau Docker. Satu-satunya komponen yang berbicara ke layanan terminal/Docker adalah `TerminalService`.
- Logika aturan hanya di tempatnya: penilaian di `ValidationService`, progres di `ProgressService`, lencana di `BadgeService`, sertifikat di `CertificateService`. Controller dan komponen Vue tidak menggandakan aturan itu.

## Konvensi Kode
- Ikuti konvensi Laravel (PSR-12) dan Vue 3 + TypeScript. TypeScript ketat: hindari `any` tanpa alasan.
- Nama kode (kelas, fungsi, variabel, route, tabel, kolom) dalam bahasa Inggris mengikuti SPEC persis (mis. `terminal_sessions`, `validation_config`, `certificate_code`). Teks antarmuka dan komentar singkat dalam bahasa Indonesia.
- Seluruh akses data lewat Eloquent/model; jangan menulis SQL mentah kecuali untuk agregasi leaderboard yang memang memerlukan query agregat — dan itu pun lewat query builder dengan parameter terikat (bukan gabungan string dari input pengguna).
- Validasi semua input di server (Form Request / validator Laravel). Keluaran dari pengguna (pertanyaan, jawaban, nama) di-escape pada tampilan untuk mencegah XSS.
- Format tanggal dan angka untuk pengguna memakai locale Indonesia (`id_ID`).

## Perintah
| Perintah | Fungsi |
|----------|--------|
| `php artisan serve` | Jalankan server Laravel (http://127.0.0.1:8000) |
| `npm run dev` | Jalankan Vite untuk frontend saat mengembangkan |
| `npm run build` | Bangun frontend (sekaligus memeriksa error TypeScript sesuai konfigurasi proyek) |
| `php artisan test` | Jalankan test backend |
| `php artisan migrate` | Jalankan migration yang belum jalan |
| `php artisan migrate:fresh --seed` | **Hanya pengembangan:** bangun ulang database + isi data awal |
| `php artisan route:list` | Lihat daftar route |
| `docker ps` | Lihat container yang sedang jalan (untuk memeriksa sesi terminal) |

## Keamanan (wajib — dari SPEC bagian 13, tidak bisa ditawar)
- **Docker daemon (socket/API Docker) tidak pernah diekspos** ke browser, frontend, atau jaringan publik. Hanya `TerminalService` di backend yang berkomunikasi dengan layanan terminal lewat jaringan internal.
- Satu container = satu pengguna = satu sesi. Container dibatasi CPU, memori, dan jumlah proses; berjalan sebagai pengguna non-root; bawaan **tanpa jaringan keluar**; sesi dibatasi waktu (bawaan 30 menit) lalu container **dihancurkan** dan filesystem-nya dibuang.
- Perintah pengguna dieksekusi **hanya di dalam container sesinya sendiri**. Jangan pernah menjalankan perintah pengguna di host/server utama, dan jangan membangun perintah shell di host dari gabungan string input pengguna.
- Kepemilikan sesi diperiksa di setiap permintaan terminal dan penilaian: pengguna hanya boleh mengakses sesi, percobaan, progres, dan sertifikat miliknya.
- Jangan membaca, mencetak, atau meng-commit file rahasia (`.env` dan isinya, kunci, kredensial database/Docker). Password selalu hash bawaan Laravel; perlindungan CSRF bawaan Laravel jangan dimatikan.
- Jangan mengklaim sistem "sudah aman" atau "sudah teruji" — laporkan hanya pemeriksaan yang benar-benar dijalankan.

## Definisi Selesai
Fase/fitur selesai jika: seluruh acceptance criteria kode kebutuhan terkait di SPEC terpenuhi dan dilaporkan satu per satu, `php artisan test` lulus, `npm run build` bersih, dan pengguna sudah mengecek hasilnya sendiri di browser.
