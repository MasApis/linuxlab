# Duolingo Light Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Samakan seluruh UI LinuxLab dengan desain Pencil `linuxlab.pen` (tema terang gaya Duolingo).

**Architecture:** Satu kali ganti nilai token Tailwind ke nilai Pencil (`GetVariables`), lalu sesuaikan komponen bersama, layout, dan halaman per batch. Tanpa ubah logika Services, route, RBAC, atau skema DB.

**Tech Stack:** Laravel + Vue 3 + TS + Inertia, Tailwind 3 (token di `tailwind.config.js`), font Nunito + Baloo 2 via bunny.net (pola sama seperti Inter saat ini).

**Spec:** `SPEC.md` v1.1 + frame Pencil di `linuxlab.pen` (token: `brand-green #58CC02`/`dark #46A302`, `ink #100F3E`, `text #4B4B4B/#6B6B6B/#767676`, `border #E5E5E5`, `surface #F7F7F7`, `surface-raised #FFFFFF`, radius 8/16/24).

## Global Constraints

- Teks antarmuka Bahasa Indonesia; istilah Linux tetap Inggris.
- Tidak tambah dependensi Composer/npm tanpa persetujuan.
- Satu container = satu pengguna = satu sesi; `TerminalService` satu-satunya yang bicara ke Docker (tidak tersentuh).
- RBAC server-side tidak berubah; hanya tampilan.
- Setiap halaman: ada empty/loading/error state; lolos mobile 320px; kontras AA; keyboard + focus ring.
- Tanpa angka/testimoni/klaim palsu; tanpa dead control.

## Review Focus

- Halaman praktik terminal di layar sempit (frame `Ruang Praktik — Laptop Sempit`): terminal + panel latihan harus tetap bisa dipakai, bukan sekadar muat.
- Verifikasi sertifikat publik + pratinjau PDF: kode salah/valid harus jelas bedanya.
- State admin kosong (modul/belum ada data): empty state harus beri aksi, bukan "No data".
- Error validasi auth (frame `States`): pesan harus di samping field yang salah.
- `prefers-reduced-motion`: animasi pulse/scale mati total.

---

### Task 0: Token fondasi + font

**Files:**
- Modify: `tailwind.config.js` (palet Pencil, font `display: Baloo 2`, `sans: Nunito`)
- Modify: `resources/css/app.css` (base terang, scrollbar terang)
- Modify: `resources/views/app.blade.php` (font bunny.net Nunito + Baloo 2)

**Interfaces:**
- Consumes: nilai `GetVariables` Pencil (di Spec atas).
- Produces: nama token tetap (`surface-*`, `text-*`, `brand-*`, `border`, `rounded-card/button/panel`) supaya pemakaian kelas di halaman tidak berubah arti struktur.

- [ ] **Step 1:** Tulis ulang blok `colors`, `fontFamily`, `borderRadius` di `tailwind.config.js` ke nilai Pencil (surface base `#F7F7F7`, card/elevated `#FFFFFF`, border `#E5E5E5`, text `#4B4B4B/#6B6B6B/#767676`, brand-primary `#58CC02` + hover `#46A302` + light + dark `#003824`-ish → pakai `brand-green-dark #46A302` sebagai hover dan teks-di-atas-hijau `#FFFFFF`).
- [ ] **Step 2:** Sesuaikan `app.css` (color-scheme light, background `#F7F7F7`, scrollbar terang) dan `app.blade.php` (ganti Inter → Nunito + Baloo 2).
- [ ] **Step 3:** Jalankan `npm run build`. Expected: PASS tanpa error TS.
- [ ] **Step 4:** Buka `/` di browser, bandingkan sekilas dengan screenshot frame `Landing Page`. Catat yang masih gelap (hardcode `text-white`, `bg-slate-*`, gradient) sebagai input Task 2-4.

### Task 1: Komponen bersama gaya Duolingo

**Files:**
- Modify: `AppButton.vue` (tombol 3D: `shadow-[0_4px_0_<dark>]`, active:translate-y), `AppCard.vue`, `AppLogo.vue`, `ProgressBar.vue`, `BadgeCard.vue`, `EmptyState.vue`, `Modal.vue`, `TextInput.vue`, `FormInput.vue`, `InputLabel.vue`, `InputError.vue`, `Checkbox.vue`, `Dropdown.vue`, `DropdownLink.vue`, `NavLink.vue`, `ResponsiveNavLink.vue`, `SecondaryButton.vue`, `PrimaryButton.vue`, `DangerButton.vue`, `Terminal.vue`, `AppButton` ghost variant.

**Interfaces:**
- Consumes: token Task 0.
- Produces: API props tidak berubah (variant/size sama) supaya halaman pemakai tidak disentuh.

- [ ] **Step 1:** Terapkan gaya Pencil per komponen (kartu putih border `#E5E5E5` radius 16; tombol 3D; input terang fokus hijau).
- [ ] **Step 2:** Hapus sisa `gray-*/slate-*` gelap di komponen legacy (Breeze) atau samakan ke token.
- [ ] **Step 3:** `npm run build`. Expected: PASS.
- [ ] **Step 4:** Cek halaman `DesignSystemTest` di browser + keyboard Tab tiap kontrol.

### Task 2: Layout (Public, Auth, User, Admin)

**Files:**
- Modify: `Layouts/PublicLayout.vue`, `Layouts/AuthLayout.vue`, `Layouts/UserLayout.vue`, `Layouts/AdminLayout.vue`, dan halaman auth yang masih pakai `GuestLayout.vue` (`Login` sudah Auth? cek: `VerifyEmail`, `ForgotPassword`, `ConfirmPassword`, `ResetPassword` → pindah ke `AuthLayout`).

**Interfaces:**
- Consumes: komponen Task 1.
- Produces: header putih + nav + footer konsisten (frame `Header`/`Footer` tiap halaman Pencil).

- [ ] **Step 1:** Samakan header/nav/footer terang per screenshot Pencil (logo hijau, link ink, tombol MASUK ghost + DAFTAR hijau 3D).
- [ ] **Step 2:** Migrasi 4 halaman auth sisa ke `AuthLayout` (split-screen terang sesuai frame `Login`).
- [ ] **Step 3:** `npm run build` PASS + cek mobile menu + dropdown user via keyboard.

### Task 3: Halaman publik + pengguna inti

**Files:**
- Modify: `Pages/Welcome.vue`, `Pages/Dashboard.vue`, `Pages/Modules/Index.vue`, `Pages/Modules/Show.vue`, `Pages/Lessons/Show.vue`, `Pages/Lessons/Practice.vue`, `Pages/Progress/Index.vue`, `Pages/Achievements/Index.vue`, `Pages/Leaderboard/Index.vue`, `Pages/Certificates/Verify.vue`.

**Interfaces:** Consumes Task 0-2. Perbandingan visual: screenshot Pencil `Landing Page`, `Dashboard`, `Modul Pembelajaran`, `Detail Modul`, `Pelajaran`, `Ruang Praktik` (+Laptop Sempit), `Progres`, `Pencapaian`, `Leaderboard` (+Masuk Peringkat), `Verifikasi Sertifikat` (+Hasil).

- [ ] **Step 1:** Per halaman: samakan hero/banner hijau, kartu modul putih, progress bar, empty state (ilustrasi/aksi jelas).
- [ ] **Step 2:** `Ruang Praktik`: layout 2 kolom → tumpuk di sempit; terminal tetap terbaca.
- [ ] **Step 3:** `npm run build` PASS + klik tiap CTA (tidak ada dead control).

### Task 4: Forum, Profil, Error

**Files:**
- Modify: `Pages/Forum/Index.vue`, `Pages/Forum/Show.vue`, `Pages/Profile/Edit.vue` (+ Partials), `Pages/Error.vue`.

**Interfaces:** Consumes Task 0-2. Referensi Pencil: `Forum Pelajaran`, `Detail Pertanyaan Forum`, `Pengaturan Profil`.

- [ ] **Step 1:** Samakan daftar pertanyaan, badge jawaban terbaik, form profil terang.
- [ ] **Step 2:** `npm run build` PASS.

### Task 5: Halaman Admin

**Files:**
- Modify: `Pages/Admin/Dashboard.vue`, `Pages/Admin/Modules/Index.vue`, `Pages/Admin/Lessons/Index.vue`, `Pages/Admin/Exercises/Index.vue`, `Pages/Admin/Badges/Index.vue`, `Pages/Admin/Users/Index.vue`, `Pages/Admin/Forum/Index.vue`.

**Interfaces:** Consumes Task 0-2. Referensi Pencil: `Dashboard Admin` + 6 frame `Admin — Kelola *`.

- [ ] **Step 1:** Samakan TopBar + tabel/aksi admin ke gaya Pencil (tetap: semua aksi destruktif konfirmasi).
- [ ] **Step 2:** `npm run build` PASS + `php artisan test` PASS (tidak ada logika berubah, regresi saja).

### Task 6: Verifikasi akhir

- [ ] **Step 1:** `npm run build` bersih + `php artisan test` lulus; laporkan apa adanya.
- [ ] **Step 2:** Screenshot tiap halaman vs frame Pencil (atau cek manual browser): tiap hard-gate antislop (kontras, keyboard, empty/loading/error, mobile) PASS dengan bukti per item.
- [ ] **Step 3:** Laporkan ke pengguna: file diubah, acceptance per batch, yang belum dicek + risiko.
