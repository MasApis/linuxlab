<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleLessonSeeder extends Seeder
{
    /**
     * Jalankan seeder modul dan pelajaran LinuxLab sesuai SPEC Bagian 7.
     * Konten merupakan draf materi terstruktur dan akurat untuk pemula (Q-03).
     */
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // MODUL 1: Dasar File System
        // ---------------------------------------------------------------------
        $moduleFileSystem = Module::updateOrCreate(
            ['slug' => 'file-system'],
            [
                'title' => 'Dasar File System',
                'description' => 'Pahami arsitektur hierarki direktori Linux, navigasi path absolut dan relatif, manipulasi berkas dan folder, serta cara melihat isi file secara efisien melalui antarmuka baris perintah.',
                'order' => 1,
                'is_published' => true,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'navigasi-direktori'],
            [
                'module_id' => $moduleFileSystem->id,
                'title' => 'Navigasi Direktori',
                'order' => 1,
                'content' => <<<MARKDOWN
# Navigasi Direktori di Linux

Selamat datang di modul pertama LinuxLab! Dalam sistem operasi Linux, seluruh berkas dan folder tersusun dalam satu pohon hierarki tunggal yang dimulai dari akar atau **root directory** yang dilambangkan dengan tanda garis miring (`/`).

Tidak seperti Windows yang membagi partisi penyimpanan ke dalam drive `C:`, `D:`, dan seterusnya, Linux menggabungkan seluruh media penyimpanan di bawah direktori root ini.

---

## 1. Mengetahui Posisi Direktori: Perintah `pwd`

Perintah `pwd` merupakan singkatan dari **Print Working Directory**. Perintah ini digunakan untuk mencetak jalur lengkap direktori tempat shell Anda sedang aktif saat ini.

```bash
pwd
```

Jika terminal Anda menampilkan `/home/student`, itu berarti Anda saat ini berada di dalam direktori `student` yang terletak di dalam folder `/home`.

---

## 2. Melihat Isi Direktori: Perintah `ls`

Perintah `ls` (**List**) digunakan untuk melihat berkas dan folder yang ada di dalam sebuah direktori.

- `ls` : Menampilkan nama file dan folder biasa.
- `ls -l` : Menampilkan informasi terperinci (format panjang: izin akses, pemilik, ukuran, dan waktu modifikasi).
- `ls -a` : Menampilkan seluruh berkas, termasuk berkas tersembunyi (*hidden files* yang namanya diawali dengan titik `.`).
- `ls -la` : Menggabungkan opsi format panjang dan menampilkan berkas tersembunyi.

---

## 3. Berpindah Direktori: Perintah `cd`

Perintah `cd` (**Change Directory**) digunakan untuk berpindah dari satu direktori ke direktori lain.

### A. Path Absolut (Jalur Mutlak)
Jalur mutlak adalah alamat direktori yang ditulis lengkap mulai dari root (`/`). Jalur absolut **selalu diawali tanda garis miring (`/`)**. Di mana pun Anda berada, jalur absolut selalu mengarah ke lokasi yang sama.

Contoh:
```bash
cd /var/log
```

### B. Path Relatif (Jalur Relatif)
Jalur relatif dihitung berdasarkan posisi direktori kerja Anda saat ini. Jalur ini **tidak diawali** dengan tanda garis miring.

Linux menyediakan simbol pintas untuk mempermudah navigasi relatif:
- `.` (satu titik) : Merujuk pada direktori saat ini.
- `..` (dua titik) : Merujuk pada direktori induk (satu tingkat di atasnya).
- `~` (tilde) : Pintasan menuju direktori *home* pengguna saat ini (misal `/home/student`).
- `-` (tanda minus) : Berpindah kembali ke direktori sebelumnya yang baru saja dikunjungi.

Contoh navigasi relatif:
```bash
cd ..          # Naik satu tingkat ke direktori induk
cd ../var      # Naik satu tingkat lalu masuk ke direktori var
cd ~/dokumen   # Masuk ke folder dokumen di direktori home
```
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Cetak posisi direktori aktif
$ pwd
/home/student

# 2. Lihat isi direktori saat ini dengan detail lengkap
$ ls -la
total 24
drwxr-xr-x 3 student student 4096 Okt 19 10:00 .
drwxr-xr-x 3 root    root    4096 Okt 19 09:00 ..
-rw-r--r-- 1 student student  220 Okt 19 09:00 .bash_logout
-rw-r--r-- 1 student student 3771 Okt 19 09:00 .bashrc
drwxr-xr-x 2 student student 4096 Okt 19 10:05 latihan

# 3. Berpindah menggunakan path absolut
$ cd /var/log
$ pwd
/var/log

# 4. Berpindah kembali ke home direktori
$ cd ~
$ pwd
/home/student

# 5. Berpindah naik satu level menggunakan path relatif
$ cd ..
$ pwd
/home
TEXT,
                'cheat_sheet' => <<<TEXT
pwd       : Menampilkan direktori kerja saat ini (Print Working Directory)
ls        : Mendaftar berkas dan folder di direktori saat ini
ls -l     : Mendaftar berkas dengan format panjang terperinci
ls -a     : Mendaftar seluruh berkas termasuk berkas tersembunyi
ls -lh    : Format panjang dengan ukuran berkas mudah dibaca (human-readable)
cd <path> : Berpindah ke direktori tertentu
cd ..     : Naik satu tingkat ke direktori induk
cd ~      : Pindah langsung ke direktori home pengguna
cd /      : Pindah langsung ke root direktori sistem
cd -      : Kembali ke direktori sebelumnya
TEXT,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'membuat-dan-mengelola-file'],
            [
                'module_id' => $moduleFileSystem->id,
                'title' => 'Membuat dan Mengelola File',
                'order' => 2,
                'content' => <<<MARKDOWN
# Membuat dan Mengelola File di Linux

Setelah menguasai cara bernavigasi di antara direktori, langkah penting berikutnya adalah mempelajari pembuatan, penggandaan, pemindahan, dan penghapusan file serta folder.

---

## 1. Membuat File Kosong: Perintah `touch`

Perintah `touch` memiliki fungsi utama untuk memperbarui stempel waktu (*timestamp*) berkas. Namun, apabila berkas yang dituju belum ada, `touch` akan otomatis membuat berkas baru yang kosong tanpa membuka editor teks.

```bash
touch catatan.txt
touch file1.txt file2.txt file3.txt
```

---

## 2. Membuat Direktori: Perintah `mkdir`

Perintah `mkdir` (**Make Directory**) digunakan untuk membuat folder baru.

- `mkdir proyek` : Membuat direktori bernama `proyek` di direktori saat ini.
- `mkdir -p backend/api/v1` : Opsi `-p` (**parents**) membuat seluruh hierarki folder induk jika folder tersebut belum ada, tanpa menimbulkan error.

---

## 3. Menyalin File dan Direktori: Perintah `cp`

Perintah `cp` (**Copy**) menyalin berkas atau direktori dari sumber (*source*) ke tujuan (*destination*).

- Menyalin file:
  ```bash
  cp berkas.txt berkas_cadangan.txt
  ```
- Menyalin direktori secara rekursif:
  Untuk menyalin folder beserta seluruh isinya, wajib menggunakan opsi rekursif `-r` atau `-R`.
  ```bash
  cp -r proyek/ cadangan_proyek/
  ```

---

## 4. Memindahkan dan Mengganti Nama: Perintah `mv`

Perintah `mv` (**Move**) memiliki dua kegunaan utama:
1. Memindahkan file/folder ke lokasi lain.
2. Mengganti nama (*rename*) file atau direktori.

```bash
# Mengganti nama berkas
mv nama_lama.txt nama_baru.txt

# Memindahkan berkas ke folder lain
mv laporan.txt ~/dokumen/
```

---

## 5. Menghapus File dan Direktori: Perintah `rm` dan `rmdir`

Perhatian: Berkas yang dihapus di terminal Linux **tidak masuk ke tempat sampah (Recycle Bin)** melainkan langsung terhapus permanen!

- `rm berkas.txt` : Menghapus berkas biasa.
- `rmdir folder_kosong` : Menghapus folder yang benar-benar kosong.
- `rm -r folder/` : Menghapus folder beserta seluruh berkas dan sub-folder di dalamnya secara rekursif.
- `rm -f berkas` : Menghapus secara paksa tanpa meminta konfirmasi (*force*).
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Membuat direktori kerja baru
$ mkdir latihan_linux
$ cd latihan_linux

# 2. Membuat file kosong dengan touch
$ touch catatan.txt draft.md
$ ls
catatan.txt  draft.md

# 3. Membuat struktur folder bersarang sekaligus
$ mkdir -p app/Http/Controllers
$ ls -R app
app:
Http

app/Http:
Controllers

# 4. Menyalin berkas catatan.txt
$ cp catatan.txt catatan_backup.txt
$ ls
catatan.txt  catatan_backup.txt  draft.md

# 5. Mengganti nama file dengan mv
$ mv draft.md panduan.md
$ ls
catatan.txt  catatan_backup.txt  panduan.md

# 6. Menghapus file backup
$ rm catatan_backup.txt
TEXT,
                'cheat_sheet' => <<<TEXT
touch <file>       : Membuat berkas kosong baru atau memperbarui timestamp
mkdir <dir>        : Membuat direktori baru
mkdir -p <path>    : Membuat direktori bertingkat beserta folder induknya
cp <src> <dest>    : Menyalin berkas dari sumber ke tujuan
cp -r <src> <dest> : Menyalin direktori beserta seluruh isinya secara rekursif
mv <src> <dest>    : Memindahkan berkas/folder atau mengganti namanya
rm <file>          : Menghapus satu atau beberapa berkas
rm -r <dir>        : Menghapus direktori beserta seluruh isinya secara rekursif
rm -f <file>       : Menghapus secara paksa tanpa konfirmasi
rmdir <dir>        : Menghapus direktori yang kosong
TEXT,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'melihat-isi-file'],
            [
                'module_id' => $moduleFileSystem->id,
                'title' => 'Melihat Isi File',
                'order' => 3,
                'content' => <<<MARKDOWN
# Melihat Isi File di Linux

Ketika bekerja di server Linux tanpa antarmuka grafis (GUI), Anda akan sering membaca berkas konfigurasi, file teks, maupun catatan sistem (*log*). Linux menyediakan beberapa utilitas baris perintah yang dirancang untuk kebutuhan pembacaan berkas yang berbeda-beda.

---

## 1. Menampilkan Seluruh Isi: Perintah `cat`

Perintah `cat` (**Concatenate**) menampilkan seluruh isi berkas teks langsung ke layar terminal. Perintah ini ideal untuk berkas berukuran pendek.

```bash
cat pesan.txt
cat -n konfigurasi.conf   # Menampilkan nomor baris di setiap baris teks
```

Jika berkas memiliki ratusan baris, `cat` akan menggulung layar dengan cepat sehingga baris pertama tidak terlihat.

---

## 2. Melihat Baris Awal: Perintah `head`

Perintah `head` digunakan untuk menampilkan bagian paling awal dari suatu berkas. Secara bawaan, perintah ini menampilkan **10 baris pertama**.

Gunakan opsi `-n` untuk menentukan berapa jumlah baris yang ingin ditampilkan:
```bash
head /etc/passwd         # Menampilkan 10 baris pertama
head -n 5 /etc/passwd    # Menampilkan 5 baris pertama
```

---

## 3. Melihat Baris Akhir: Perintah `tail`

Perintah `tail` digunakan untuk menampilkan bagian paling akhir dari suatu berkas. Secara bawaan, perintah ini menampilkan **10 baris terakhir**.

- `tail file.log` : Menampilkan 10 baris terakhir.
- `tail -n 20 file.log` : Menampilkan 20 baris terakhir.
- `tail -f file.log` : Mode pantau (*follow*). Perintah akan terus aktif di layar dan menampilkan baris baru yang ditambahkan ke berkas log secara waktu-nyata (*real-time*). Tekan `Ctrl + C` untuk keluar.

---

## 4. Membaca Berkas Panjang Secara Interaktif: Perintah `less`

Untuk berkas teks yang panjang seperti dokumentasi sistem atau log historis, `less` adalah alat terbaik. Perintah ini memungkinkan Anda menelusuri isi berkas per halaman tanpa memuat seluruh berkas ke dalam memori.

```bash
less /var/log/syslog
```

Navigasi di dalam `less`:
- `Spasi` atau `f` : Maju satu halaman penuh ke bawah.
- `b` : Mundur satu halaman ke atas.
- `Panah Atas / Bawah` : Menggulung satu baris ke atas / bawah.
- `/kata_kunci` : Mencari kata kunci ke depan. Tekan `n` untuk lanjut ke temuan berikutnya.
- `q` : Keluar dari tampilan `less`.
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Menampilkan isi file teks pendek
$ cat /etc/hostname
linuxlab-server

# 2. Menampilkan berkas dengan nomor baris
$ cat -n /etc/hosts
     1  127.0.0.1       localhost
     2  127.0.1.1       linuxlab-server
     3  ::1             localhost ip6-localhost ip6-loopback

# 3. Menampilkan 3 baris teratas file
$ head -n 3 /etc/passwd
root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
bin:x:2:2:bin:/bin:/usr/sbin/nologin

# 4. Menampilkan 3 baris terbawah file
$ tail -n 3 /etc/passwd
nobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin
systemd-network:x:100:102:systemd Network Management,,,:/run/systemd:/usr/sbin/nologin
student:x:1000:1000:LinuxLab Student,,,:/home/student:/bin/bash
TEXT,
                'cheat_sheet' => <<<TEXT
cat <file>       : Menampilkan seluruh isi file ke terminal
cat -n <file>    : Menampilkan isi file disertai nomor baris
head <file>      : Menampilkan 10 baris pertama file
head -n <N> file : Menampilkan N baris pertama file
tail <file>      : Menampilkan 10 baris terakhir file
tail -n <N> file : Menampilkan N baris terakhir file
tail -f <file>   : Memantau penambahan baris baru pada file secara live
less <file>      : Membaca file besar secara interaktif (tekan q untuk keluar)
wc -l <file>     : Menghitung total jumlah baris dalam berkas
TEXT,
            ]
        );

        // ---------------------------------------------------------------------
        // MODUL 2: Izin dan Hak Akses File
        // ---------------------------------------------------------------------
        $modulePermission = Module::updateOrCreate(
            ['slug' => 'file-permissions'],
            [
                'title' => 'Izin dan Hak Akses File',
                'description' => 'Kuasai konsep keamanan dan perizinan berkas di Linux (notasi rwx), klasifikasi hak akses user, group, dan others, pengubahan izin dengan chmod, serta kepemilikan file.',
                'order' => 2,
                'is_published' => true,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'membaca-permission'],
            [
                'module_id' => $modulePermission->id,
                'title' => 'Membaca Permission',
                'order' => 1,
                'content' => <<<MARKDOWN
# Membaca Izin Akses (Permission) di Linux

Linux adalah sistem operasi *multi-user*. Untuk menjaga keamanan berkas dan stabilitas sistem, setiap file dan direktori memiliki hak akses terdefinisi yang menentukan siapa saja yang boleh membaca, memodifikasi, atau mengeksekusinya.

---

## 1. Memeriksa Permission dengan `ls -l`

Ketik `ls -l` pada terminal Anda. Setiap baris keluaran diawali dengan string 10 karakter seperti:

```
-rwxr-xr-- 1 student developers 4096 Okt 19 10:00 skrip.sh
drwxr-xr-x 2 student developers 4096 Okt 19 10:05 data/
```

Mari kita bedah 10 karakter pertama:
1. **Karakter ke-1: Jenis Objek**
   - `-` : Berkas biasa (*regular file*).
   - `d` : Direktori (*directory*).
   - `l` : Tautan simbolik (*symbolic link*).
2. **Karakter ke 2-4: Hak Akses Pemilik (User / Owner)**
   - Menentukan izin bagi pengguna yang memiliki berkas tersebut.
3. **Karakter ke 5-7: Hak Akses Kelompok (Group)**
   - Menentukan izin bagi seluruh anggota grup pengguna berkas.
4. **Karakter ke 8-10: Hak Akses Pengguna Lain (Others / World)**
   - Menentukan izin bagi semua akun lain di sistem.

---

## 2. Arti Huruf Notasi `r`, `w`, dan `x`

- `r` (**Read** / Baca) :
  - Pada file: Membaca atau melihat isi file.
  - Pada direktori: Melihat daftar nama file di dalam direktori (`ls`).
- `w` (**Write** / Tulis) :
  - Pada file: Mengubah, mengedit, atau menimpa isi file.
  - Pada direktori: Membuat file baru, menghapus file, atau mengubah nama file di dalam direktori.
- `x` (**Execute** / Eksekusi) :
  - Pada file: Menjalankan file sebagai program atau skrip.
  - Pada direktori: Masuk ke dalam direktori tersebut (`cd`).
- `-` : Hak akses tidak diberikan.

Contoh `-rwxr-xr--`:
- User (Pemilik): `rwx` (Baca, Tulis, Eksekusi)
- Group: `r-x` (Baca dan Eksekusi, Tidak Boleh Tulis)
- Others: `r--` (Hanya Baca)
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Menampilkan daftar file dengan permission lengkap
$ ls -l
total 8
-rw-r--r-- 1 student student 1024 Okt 19 10:00 catatan.txt
-rwxr-xr-x 1 student student  240 Okt 19 10:02 run.sh
drwxr-xr-x 2 student student 4096 Okt 19 10:05 dokumen

# 2. Analisis r-w-x pada berkas catatan.txt (-rw-r--r--):
# - Jenis  : Regular file (-)
# - User   : rw- (Bisa membaca dan mengedit)
# - Group  : r-- (Hanya bisa membaca)
# - Others : r-- (Hanya bisa membaca)

# 3. Analisis r-w-x pada skrip run.sh (-rwxr-xr-x):
# - User   : rwx (Bisa baca, tulis, eksekusi)
# - Group  : r-x (Bisa baca dan eksekusi)
# - Others : r-x (Bisa baca dan eksekusi)
TEXT,
                'cheat_sheet' => <<<TEXT
ls -l            : Menampilkan izin akses dan kepemilikan file secara detail
-                : Menandakan file biasa (regular file)
d                : Menandakan direktori (directory)
r (Read)         : Izin membaca berkas / melihat isi direktori
w (Write)        : Izin memodifikasi berkas / menambah/menghapus file di folder
x (Execute)      : Izin menjalankan program/skrip / berpindah masuk ke folder (cd)
User (u)         : Karakter ke 2–4 (hak akses pemilik berkas)
Group (g)        : Karakter ke 5–7 (hak akses grup pengguna)
Others (o)       : Karakter ke 8–10 (hak akses publik/pengguna lain)
TEXT,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'mengubah-permission'],
            [
                'module_id' => $modulePermission->id,
                'title' => 'Mengubah Permission',
                'order' => 2,
                'content' => <<<MARKDOWN
# Mengubah Permission dengan `chmod`

Perintah `chmod` (**Change Mode**) digunakan untuk mengubah izin akses berkas atau direktori di Linux. Hanya pemilik berkas atau pengguna dengan hak akses administratif (`root` / `sudo`) yang dapat mengubah izin berkas.

Terdapat dua cara untuk menentukan nilai permission: **Metode Oktal (Angka)** dan **Metode Simbolik (Huruf)**.

---

## 1. Metode Oktal (Angka Nilai Biner)

Dalam mode oktal, setiap huruf izin diwakili oleh angka bernilai eksponensial basis 2:
- `r` (Read) = **4**
- `w` (Write) = **2**
- `x` (Execute) = **1**
- `-` (Tidak ada izin) = **0**

Setiap segmen (*User*, *Group*, *Others*) diwakili oleh penjumlahan dari ketiga angka tersebut (nilai maksimal 4 + 2 + 1 = 7).

| Nilai Oktal | Hak Akses | Keterangan |
| :---: | :---: | :--- |
| **7** | `rwx` | Baca, tulis, dan eksekusi penuh (4 + 2 + 1) |
| **6** | `rw-` | Baca dan tulis (4 + 2) — standar untuk berkas teks |
| **5** | `r-x` | Baca dan eksekusi (4 + 1) — standar untuk folder/skrip |
| **4** | `r--` | Hanya baca (4) |
| **0** | `---` | Tidak memiliki hak akses sama sekali |

Kombinasi umum yang sering ditemui:
- `chmod 755 skrip.sh` : User = `rwx` (7), Group = `r-x` (5), Others = `r-x` (5). Cocok untuk skrip yang aman.
- `chmod 644 file.txt` : User = `rw-` (6), Group = `r--` (4), Others = `r--` (4). Standar berkas dokumen.
- `chmod 600 rahasia.key` : User = `rw-` (6), Group/Others = `---` (0). Hanya pemilik yang boleh mengakses.

---

## 2. Metode Simbolik

Metode simbolik mengubah permission menggunakan referensi huruf:
- **Pengguna:** `u` (user), `g` (group), `o` (others), `a` (all / semua)
- **Operator:** `+` (tambah izin), `-` (cabut izin), `=` (tetapkan izin persis)
- **Izin:** `r`, `w`, `x`

Contoh penggunaan simbolik:
```bash
chmod +x deploy.sh        # Menambahkan izin eksekusi untuk semua pihak
chmod u=rwx,go=rx app.sh  # Menetapkan user rwx, grup & others rx
chmod o-r dokumen.txt     # Mencabut hak baca dari pengguna luar
```
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Menjadikan file script dapat dieksekusi oleh user
$ touch skrip.sh
$ ls -l skrip.sh
-rw-r--r-- 1 student student 0 Okt 19 10:00 skrip.sh

$ chmod +x skrip.sh
$ ls -l skrip.sh
-rwxr-xr-x 1 student student 0 Okt 19 10:00 skrip.sh

# 2. Mengatur permission dengan nilai oktal 755
$ chmod 755 skrip.sh

# 3. Mengamankan berkas sensitif hanya untuk pemilik (600)
$ chmod 600 kunci_rahasia.txt
$ ls -l kunci_rahasia.txt
-rw------- 1 student student 0 Okt 19 10:01 kunci_rahasia.txt

# 4. Mengubah izin pada direktori beserta seluruh isinya secara rekursif
$ chmod -R 755 folder_proyek/
TEXT,
                'cheat_sheet' => <<<TEXT
r = 4 | w = 2 | x = 1 | - = 0
chmod 755 <file>  : User=rwx (7), Group=r-x (5), Others=r-x (5)
chmod 644 <file>  : User=rw- (6), Group=r-- (4), Others=r-- (4) — default berkas
chmod 600 <file>  : User=rw- (6), Group=--- (0), Others=--- (0) — berkas privat
chmod +x <file>   : Menambahkan izin eksekusi
chmod -x <file>   : Mencabut izin eksekusi
chmod u+w <file>  : Menambah izin tulis hanya untuk pemilik
chmod go-r <file> : Mencabut izin baca untuk group dan others
chmod -R <mode>   : Menerapkan izin secara rekursif ke seluruh file di folder
TEXT,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'kepemilikan-file'],
            [
                'module_id' => $modulePermission->id,
                'title' => 'Kepemilikan File',
                'order' => 3,
                'content' => <<<MARKDOWN
# Kepemilikan File di Linux: `chown` dan `chgrp`

Setiap berkas dan direktori di Linux dimiliki oleh satu **pengguna (User Owner)** dan satu **grup pengguna (Group Owner)**. Informasi kepemilikan ini dicatat di dalam inode berkas pada sistem berkas.

---

## 1. Konsep Pemilik dan Grup

Kolom ke-3 dan ke-4 pada keluaran `ls -l` menampilkan kepemilikan tersebut:
```
-rw-r--r-- 1 student developers 1024 Okt 19 10:00 index.html
```
- `student` : Nama akun pengguna pemilik berkas.
- `developers` : Nama kelompok/grup pemilik berkas.

---

## 2. Mengubah Pemilik dengan `chown`

Perintah `chown` (**Change Owner**) digunakan untuk memindahkan kepemilikan berkas ke pengguna lain.

```bash
chown nama_user nama_berkas
chown user:grup nama_berkas   # Mengubah pemilik sekaligus grup pemilik
chown -R user:grup folder/    # Mengubah kepemilikan secara rekursif
```

> **Catatan Keamanan Linux:** Hanya akun *superuser* (`root`) yang diizinkan memindahkan kepemilikan berkas ke pengguna lain. Pengguna biasa tidak dapat "menyerahkan" kepemilikan file miliknya ke akun lain tanpa hak administratif.

---

## 3. Mengubah Grup dengan `chgrp`

Perintah `chgrp` (**Change Group**) digunakan secara khusus untuk mengubah grup pemilik berkas.

```bash
chgrp developers laporan.txt
chgrp -R developers proyek/
```

Pengguna biasa dapat menggunakan `chgrp` asalkan pengguna tersebut merupakan anggota dari grup tujuan yang ingin dipasangkan pada berkas.

---

## 4. Batasan Praktik di Lingkungan Container

Pada laboratorium terminal interaktif LinuxLab, container Anda berjalan di bawah akun pengguna terisolasi (`student`) non-root untuk mematuhi prinsip keamanan `SEC-TERM-03`.

Oleh karena itu, perintah seperti `chown` ke pengguna sistem lain (seperti `root`) akan ditolak oleh kernel Linux dengan pesan:
```
chown: changing ownership of 'file': Operation not permitted
```
Hal ini merupakan perilaku wajar dan aman yang membuktikan isolasi lingkungan kerja Anda berjalan dengan benar!
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Melihat kepemilikan berkas saat ini
$ ls -l app.py
-rw-r--r-- 1 student student 512 Okt 19 10:00 app.py

# 2. Mengubah grup berkas dengan chgrp
$ chgrp student app.py

# 3. Contoh sintaks chown oleh administrator sistem (root)
# $ sudo chown www-data:www-data /var/www/html/index.php
# $ ls -l /var/www/html/index.php
# -rw-r--r-- 1 www-data www-data 1024 Okt 19 10:00 /var/www/html/index.php

# 4. Mengubah kepemilikan folder secara rekursif
# $ sudo chown -R student:student ~/proyek
TEXT,
                'cheat_sheet' => <<<TEXT
chown <user> <file>       : Mengubah pemilik file
chown <user>:<group> file : Mengubah pemilik sekaligus grup file
chown -R <user>:<group>   : Mengubah pemilik dan grup secara rekursif di direktori
chgrp <group> <file>      : Mengubah grup pemilik berkas
id                        : Menampilkan ID user (UID) dan grup (GID) saat ini
groups                    : Menampilkan daftar grup yang diikuti akun aktif
whoami                    : Menampilkan nama akun pengguna yang sedang login
TEXT,
            ]
        );

        // ---------------------------------------------------------------------
        // MODUL 3: Pemrosesan Teks dan Redirection
        // ---------------------------------------------------------------------
        $moduleTextProcessing = Module::updateOrCreate(
            ['slug' => 'text-processing'],
            [
                'title' => 'Pemrosesan Teks dan Redirection',
                'order' => 3,
                'description' => 'Pelajari manipulasi aliran data teks, pencarian pola menggunakan grep, teknik pengalihan input dan output (redirection), serta rantai perintah pipa (pipe |).',
                'is_published' => true,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'mencari-teks'],
            [
                'module_id' => $moduleTextProcessing->id,
                'title' => 'Mencari Teks dengan grep',
                'order' => 1,
                'content' => <<<MARKDOWN
# Mencari Teks dengan `grep`

Dalam ekosistem Unix dan Linux, prinsip dasar perancangannya adalah **"semuanya berbasis teks biasa"**. Konfigurasi sistem, status layanan, dan catatan aktivitas (*log*) seluruhnya tersimpan dalam berkas teks.

Perintah `grep` (**Global Regular Expression Print**) adalah utilitas baris perintah yang paling sering digunakan untuk mencari kata kunci, kalimat, atau pola teks di dalam satu atau banyak file.

---

## 1. Penggunaan Dasar `grep`

Sintaks dasar perintah `grep`:
```bash
grep "kata_kunci" nama_file
```

Perintah di atas akan membaca seluruh berkas dan mencetak setiap baris yang memuat teks `"kata_kunci"`.

---

## 2. Opsi Esensial yang Wajib Dikuasai

Berikut adalah opsi praktis `grep` yang paling sering dibutuhkan pemula:

- `-i` (**Ignore Case**) : Pencarian tidak membedakan huruf besar dan huruf kecil (*case-insensitive*). Kata `"linux"`, `"Linux"`, dan `"LINUX"` seluruhnya akan cocok.
  ```bash
  grep -i "error" /var/log/syslog
  ```
- `-n` (**Line Number**) : Menampilkan nomor baris tempat kata kunci tersebut ditemukan.
  ```bash
  grep -n "student" /etc/passwd
  ```
- `-v` (**Invert Match**) : Membalikkan hasil pencarian. Menampilkan semua baris yang **tidak** memuat kata kunci. Sangat berguna untuk menyaring baris komentar atau baris kosong.
  ```bash
  grep -v "#" konfigurasi.conf
  ```
- `-c` (**Count**) : Hanya menampilkan jumlah total baris yang cocok, bukan isi barisnya.
  ```bash
  grep -c "failed" auth.log
  ```
- `-r` (**Recursive**) : Mencari kata kunci di seluruh file yang ada di dalam sebuah folder dan sub-folder.
  ```bash
  grep -r "TODO" src/
  ```
- `-w` (**Word**) : Mencocokkan kata utuh, bukan bagian dari kata yang lebih panjang (misal mencari `root` tidak akan mencocokkan `rootfs`).
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Mencari baris yang memuat kata "root" di /etc/passwd
$ grep "root" /etc/passwd
root:x:0:0:root:/root:/bin/bash

# 2. Mencari kata kunci tanpa memperhatikan huruf besar/kecil (-i) beserta nomor baris (-n)
$ grep -in "bash" /etc/passwd
1:root:x:0:0:root:/root:/bin/bash
25:student:x:1000:1000:LinuxLab Student,,,:/home/student:/bin/bash

# 3. Menghitung jumlah baris yang memuat kata "nologin"
$ grep -c "nologin" /etc/passwd
18

# 4. Menyaring baris konfigurasi yang bukan komentar (invert match -v)
$ grep -v "^#" /etc/hosts
127.0.0.1       localhost
127.0.1.1       linuxlab-server
TEXT,
                'cheat_sheet' => <<<TEXT
grep "pola" <file>    : Mencari baris yang mengandung pola teks
grep -i "pola" <file> : Pencarian case-insensitive (abaikan besar/kecil huruf)
grep -n "pola" <file> : Menampilkan nomor baris yang cocok
grep -v "pola" <file> : Membalik pencarian (tampilkan baris yang TIDAK cocok)
grep -c "pola" <file> : Menghitung jumlah baris yang cocok
grep -w "pola" <file> : Mencocokkan kata secara utuh
grep -r "pola" <dir>  : Mencari pola secara rekursif di dalam direktori
TEXT,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'redirection-dan-pipe'],
            [
                'module_id' => $moduleTextProcessing->id,
                'title' => 'Redirection dan Pipe',
                'order' => 2,
                'content' => <<<MARKDOWN
# Pengalihan (Redirection) dan Pipa (Pipe `|`)

Di lingkungan Linux, setiap perintah baris perintah yang berjalan memiliki 3 saluran standar (*Standard Streams*):
1. **stdin (0)** : Masukan standar (biasanya papan ketik / keyboard).
2. **stdout (1)** : Keluaran standar (biasanya layar terminal).
3. **stderr (2)** : Keluaran kesalahan standar (pesan error di layar terminal).

Dengan memanfaatkan teknik **Redirection** dan **Pipe**, Anda dapat menyambungkan masukan dan keluaran perintah secara dinamis.

---

## 1. Operator Redirection: Menulis ke File

### A. Operator `>` (Simpan dan Timpa / Overwrite)
Mengarahkan *stdout* suatu perintah ke berkas. Jika berkas sudah ada, isinya akan **ditimpa** dari awal. Jika belum ada, berkas baru akan dibuat.

```bash
ls -l > daftar_file.txt
echo "Halo LinuxLab" > sambutan.txt
```

### B. Operator `>>` (Simpan dan Tambah / Append)
Mengarahkan *stdout* ke berkas tanpa menghapus isi yang sudah ada. Baris baru akan **ditambahkan di bagian akhir** berkas.

```bash
echo "Baris tambahan kedua" >> sambutan.txt
date >> log_aktivitas.txt
```

---

## 2. Operator Pipe: Merangkai Perintah (`|`)

Karakter garis tegak (`|`) disebut **Pipe**. Pipe mengambil keluaran (*stdout*) dari perintah di sebelah kiri dan menjadikannya sebagai masukan (*stdin*) untuk perintah di sebelah kanan.

Formula umum:
```
perintah_1 | perintah_2 | perintah_3
```

Contoh nyata:
- `ls /etc | wc -l` : Menghitung berapa banyak file dan folder yang ada di direktori `/etc`.
- `cat log.txt | grep "error" | sort` : Menemukan baris error lalu mengurutkannya secara alfabetis.

---

## 3. Perintah Pendamping: `wc` dan `sort`

- `wc` (**Word Count**) :
  - `wc -l file.txt` : Menghitung jumlah baris (*lines*).
  - `wc -w file.txt` : Menghitung jumlah kata (*words*).
- `sort` : Mengurutkan baris teks secara alfabetis.
  - `sort nama.txt` : Urutan dari A ke Z.
  - `sort -r nama.txt` : Urutan terbalik dari Z ke A (*reverse*).
  - `sort -n angka.txt` : Urutan numerik (angka).
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Menulis teks ke file dengan operator redirection >
$ echo "Linux adalah sistem operasi open source" > info.txt
$ cat info.txt
Linux adalah sistem operasi open source

# 2. Menambahkan teks ke file yang sama dengan >>
$ echo "Kernel Linux diciptakan oleh Linus Torvalds" >> info.txt
$ cat info.txt
Linux adalah sistem operasi open source
Kernel Linux diciptakan oleh Linus Torvalds

# 3. Merangkai perintah dengan Pipe: mencari user dan mengurutkannya
$ cat /etc/passwd | grep "/bin/bash" | sort
root:x:0:0:root:/root:/bin/bash
student:x:1000:1000:LinuxLab Student,,,:/home/student:/bin/bash

# 4. Menghitung jumlah file di direktori /bin menggunakan pipe dan wc
$ ls /bin | wc -l
142
TEXT,
                'cheat_sheet' => <<<TEXT
perintah > file    : Mengarahkan keluaran perintah ke file (menimpa/overwrite)
perintah >> file   : Menambahkan keluaran perintah ke akhir file (append)
perintah < file    : Membaca masukan dari file (stdin redirection)
cmd1 | cmd2        : Mengalirkan output cmd1 menjadi input cmd2
wc -l <file>       : Menghitung total jumlah baris
wc -w <file>       : Menghitung total jumlah kata
sort <file>        : Mengurutkan baris secara alfabetis A-Z
sort -r <file>     : Mengurutkan baris secara terbalik Z-A
sort -n <file>     : Mengurutkan baris secara nilai numerik angka
sort -u <file>     : Mengurutkan sekaligus membuang baris duplikat (unique)
TEXT,
            ]
        );

        Lesson::updateOrCreate(
            ['slug' => 'memotong-dan-menyaring-teks'],
            [
                'module_id' => $moduleTextProcessing->id,
                'title' => 'Memotong dan Menyaring Teks',
                'order' => 3,
                'content' => <<<MARKDOWN
# Memotong dan Menyaring Teks: `cut`, `head`, `tail`, dan Pipeline Lanjutan

Pada pelajaran ini, kita akan menggabungkan seluruh konsep pengolahan teks sebelumnya untuk melakukan pemotongan kolom, penyaringan data berformat (*tab-separated* atau *comma-separated*), dan pembacaan segmen data tertentu di Linux.

---

## 1. Memotong Kolom dengan Perintah `cut`

Perintah `cut` digunakan untuk mengekstrak kolom atau bagian tertentu dari setiap baris teks. Dua opsi utama yang sering digunakan adalah:

- `-d` (**Delimiter**) : Karakter pembatas kolom. Bawaannya adalah karakter tab, namun sering diganti dengan titik dua `:` atau koma `,`.
- `-f` (**Field**) : Nomor kolom yang ingin diambil (dimulai dari 1).

### Contoh Praktis: Mengambil Daftar Username
Berkas `/etc/passwd` di Linux menggunakan titik dua (`:`) sebagai pemisah kolom:
```
student:x:1000:1000:LinuxLab Student,,,:/home/student:/bin/bash
```
Kolom ke-1 adalah `username`, dan kolom ke-7 adalah `default shell`.

Untuk mengambil seluruh username saja:
```bash
cut -d: -f1 /etc/passwd
```

Untuk mengambil username (kolom 1) dan shell (kolom 7):
```bash
cut -d: -f1,7 /etc/passwd
```

---

## 2. Mengambil Segmen Tengah: Kombinasi `head` dan `tail`

Bagaimana jika Anda ingin melihat baris ke-5 sampai baris ke-8 dari sebuah berkas berisikan 100 baris?

Solusi elegan di Linux adalah menggabungkan `head` dan `tail` menggunakan **Pipe**:
```bash
# Ambil 8 baris pertama, lalu ambil 4 baris terbawah dari 8 baris tersebut (yaitu baris 5, 6, 7, 8)
head -n 8 /etc/passwd | tail -n 4
```

---

## 3. Rantai Pipeline Lanjutan

Dengan menggabungkan `cut`, `sort`, `uniq`, dan `grep`, Anda dapat membuat laporan data ringkas langsung dari terminal tanpa perlu perangkat lunak spreadsheet!

Contoh: Melihat semua jenis login shell yang digunakan di sistem tanpa duplikasi:
```bash
cut -d: -f7 /etc/passwd | sort | uniq
```
MARKDOWN,
                'example_commands' => <<<TEXT
# 1. Mengambil kolom pertama (username) dari /etc/passwd
$ cut -d: -f1 /etc/passwd | head -n 4
root
daemon
bin
sys

# 2. Mengambil kolom 1 dan 7 (username dan shell)
$ cut -d: -f1,7 /etc/passwd | head -n 3
root:/bin/bash
daemon:/usr/sbin/nologin
bin:/usr/sbin/nologin

# 3. Mengambil baris ke-4 sampai ke-6 dari /etc/passwd
$ head -n 6 /etc/passwd | tail -n 3
sync:x:4:65534:sync:/bin:/bin/sync
games:x:5:60:games:/usr/games:/usr/sbin/nologin
man:x:6:12:man:/var/cache/man:/usr/sbin/nologin

# 4. Menemukan shell yang paling banyak digunakan oleh akun sistem
$ cut -d: -f7 /etc/passwd | sort | uniq -c
     2 /bin/bash
     1 /bin/sync
    18 /usr/sbin/nologin
TEXT,
                'cheat_sheet' => <<<TEXT
cut -d'<char>' -f<N> : Memotong kolom ke-N berdasarkan karakter pembatas delimiter
cut -d: -f1 /etc/passwd : Mengambil hanya kolom username dari file passwd
cut -c1-10 <file>    : Memotong karakter ke-1 hingga ke-10 pada setiap baris
head -n M | tail -n N : Mengambil N baris terakhir dari M baris pertama (segmen tengah)
uniq                 : Membuang baris duplikat yang bersebelahan (gunakan setelah sort)
uniq -c              : Menghitung frekuensi kemunculan setiap baris unik
tr 'a-z' 'A-Z'       : Mengubah seluruh huruf kecil menjadi huruf besar
TEXT,
            ]
        );

        if ($this->command) {
            $this->command->info('✓ 3 Modul dan 9 Pelajaran berhasil di-seed sesuai SPEC Bagian 7.');
        }
    }
}
