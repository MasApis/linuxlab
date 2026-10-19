<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Hint;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    /**
     * Seeder latihan dan hint sesuai SPEC Bagian 7, Bagian 11.2, dan 11.3.
     * Minimal 2 latihan per pelajaran untuk seluruh 9 pelajaran (total 18 latihan).
     */
    public function run(): void
    {
        $exercisesData = [
            // =================================================================
            // MODUL 1: Dasar File System
            // =================================================================

            // Pelajaran 1.1: Navigasi Direktori
            'navigasi-direktori' => [
                [
                    'title' => 'Menyimpan Jalur Direktori Aktif',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'system_state',
                    'instruction' => 'Tampilkan jalur direktori aktif saat ini dengan perintah `pwd`, lalu simpan keluaran perintah tersebut ke berkas `posisi.txt` di direktori home (`/home/student/posisi.txt`) menggunakan operator redirection `>`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/posisi.txt'],
                            ['type' => 'file_contains', 'path' => '/home/student/posisi.txt', 'text' => '/home/student'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Perintah `pwd` (Print Working Directory) digunakan untuk menampilkan direktori tempat Anda berada saat ini.'],
                        ['order' => 2, 'content' => 'Gunakan operator redirection `>` untuk mengarahkan keluaran perintah ke berkas: `pwd > /home/student/posisi.txt`.'],
                    ],
                ],
                [
                    'title' => 'Melihat Berkas Termasuk Berkas Tersembunyi',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Jalankan perintah untuk melihat seluruh berkas di direktori home Anda termasuk berkas tersembunyi seperti `.bashrc`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'command_output_contains', 'command' => 'ls -a /home/student', 'text' => '.bashrc'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Berkas tersembunyi di Linux diawali dengan tanda titik (`.`). Opsi apa pada perintah `ls` yang menampilkan seluruh berkas?'],
                        ['order' => 2, 'content' => 'Gunakan opsi `-a` pada perintah `ls`: `ls -a /home/student`.'],
                    ],
                ],
            ],

            // Pelajaran 1.2: Membuat dan Mengelola File
            'membuat-dan-mengelola-file' => [
                [
                    'title' => 'Membuat File hello.txt',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'system_state',
                    'instruction' => 'Buatlah berkas bernama `hello.txt` di dalam folder `/home/student/latihan/` yang berisi teks `Halo Linux`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/latihan/hello.txt'],
                            ['type' => 'file_contains', 'path' => '/home/student/latihan/hello.txt', 'text' => 'Halo Linux'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Buat foldernya terlebih dahulu jika belum ada menggunakan `mkdir -p /home/student/latihan`.'],
                        ['order' => 2, 'content' => 'Gunakan perintah `echo \'Halo Linux\' > /home/student/latihan/hello.txt` untuk membuat berkas beserta isinya.'],
                    ],
                ],
                [
                    'title' => 'Membuat Struktur Folder Proyek',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'system_state',
                    'instruction' => 'Buat direktori baru bernama `proyek` di dalam `/home/student`, lalu buat sub-direktori bernama `backup` di dalamnya (`/home/student/proyek/backup`).',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'directory_exists', 'path' => '/home/student/proyek'],
                            ['type' => 'directory_exists', 'path' => '/home/student/proyek/backup'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Perintah `mkdir` digunakan untuk membuat direktori baru.'],
                        ['order' => 2, 'content' => 'Gunakan opsi `-p` untuk membuat direktori bertingkat sekaligus: `mkdir -p /home/student/proyek/backup`.'],
                    ],
                ],
            ],

            // Pelajaran 1.3: Melihat Isi File
            'melihat-isi-file' => [
                [
                    'title' => 'Melihat Isi File Catatan',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Buat berkas `catatan.txt` di direktori `/home/student/` yang memuat baris `LinuxLab Interaktif`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/catatan.txt'],
                            ['type' => 'command_output_contains', 'command' => 'cat /home/student/catatan.txt', 'text' => 'LinuxLab Interaktif'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Gunakan perintah `echo` atau teks editor untuk mengisi berkas `catatan.txt`.'],
                        ['order' => 2, 'content' => 'Jalankan: `echo \'LinuxLab Interaktif\' > /home/student/catatan.txt` lalu periksa dengan `cat /home/student/catatan.txt`.'],
                    ],
                ],
                [
                    'title' => 'Menyalin Berkas Sistem dan Membaca Isinya',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'combined',
                    'instruction' => 'Salin berkas sistem `/etc/issue` ke `/home/student/salinan_issue.txt`, lalu pastikan berkas salinan tersebut ada dan memuat tulisan `Ubuntu`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/salinan_issue.txt'],
                            ['type' => 'file_contains', 'path' => '/home/student/salinan_issue.txt', 'text' => 'Ubuntu'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Perintah `cp <sumber> <tujuan>` digunakan untuk menyalin berkas.'],
                        ['order' => 2, 'content' => 'Ketik perintah `cp /etc/issue /home/student/salinan_issue.txt`.'],
                    ],
                ],
            ],

            // =================================================================
            // MODUL 2: Permission
            // =================================================================

            // Pelajaran 2.1: Membaca Permission
            'membaca-permission' => [
                [
                    'title' => 'Memeriksa Permission Berkas Rahasia',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Buat berkas bernama `rahasia.txt` di `/home/student/`, lalu periksa bahwa berkas tersebut berhasil tercatat dalam daftar keluaran `ls -l`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/rahasia.txt'],
                            ['type' => 'command_output_contains', 'command' => 'ls -l /home/student/rahasia.txt', 'text' => 'rahasia.txt'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Buat berkas kosong dengan perintah `touch /home/student/rahasia.txt`.'],
                        ['order' => 2, 'content' => 'Gunakan `ls -l /home/student/rahasia.txt` untuk melihat izin akses berkas.'],
                    ],
                ],
                [
                    'title' => 'Melihat Permission Direktori Home',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Periksa permission direktori home `/home/student` menggunakan opsi `-d` pada `ls` agar hanya direktori itu sendiri yang ditampilkan.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'command_output_contains', 'command' => 'ls -ld /home/student', 'text' => 'student'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Opsi `-d` mencegah `ls` mencantumkan isi dari sebuah direktori.'],
                        ['order' => 2, 'content' => 'Jalankan perintah `ls -ld /home/student` pada terminal.'],
                    ],
                ],
            ],

            // Pelajaran 2.2: Mengubah Permission
            'mengubah-permission' => [
                [
                    'title' => 'Mengubah Permission Skrip Menjadi 755',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'system_state',
                    'instruction' => 'Buat berkas skrip `run.sh` di folder `/home/student/latihan/` dan ubah permission-nya menjadi `755` (rwxr-xr-x).',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/latihan/run.sh'],
                            ['type' => 'file_permission', 'path' => '/home/student/latihan/run.sh', 'mode' => '755'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Buat berkas terlebih dahulu: `mkdir -p /home/student/latihan && touch /home/student/latihan/run.sh`.'],
                        ['order' => 2, 'content' => 'Gunakan perintah `chmod 755 /home/student/latihan/run.sh`.'],
                    ],
                ],
                [
                    'title' => 'Mengamankan Kunci Privat Menjadi 600',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'system_state',
                    'instruction' => 'Buat berkas kunci `privat.key` di direktori home `/home/student/` dan atur permission-nya menjadi `600` (rw-------), sehingga hanya pemilik berkas yang dapat membaca dan menulis.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/privat.key'],
                            ['type' => 'file_permission', 'path' => '/home/student/privat.key', 'mode' => '600'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Buat berkas dengan `touch /home/student/privat.key`.'],
                        ['order' => 2, 'content' => 'Ubah permission ke mode 600 dengan perintah `chmod 600 /home/student/privat.key`.'],
                    ],
                ],
            ],

            // Pelajaran 2.3: Kepemilikan File
            'kepemilikan-file' => [
                [
                    'title' => 'Memverifikasi Kepemilikan Pengguna student',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Buat berkas `dokumen.txt` di direktori `/home/student/` dan pastikan berkas tersebut dimiliki oleh user `student` dan group `student`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/dokumen.txt'],
                            ['type' => 'command_output_contains', 'command' => 'ls -l /home/student/dokumen.txt', 'text' => 'student student'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Setiap berkas yang dibuat oleh user student otomatis menjadi miliknya.'],
                        ['order' => 2, 'content' => 'Ketik `touch /home/student/dokumen.txt` dan periksa dengan `ls -l /home/student/dokumen.txt`.'],
                    ],
                ],
                [
                    'title' => 'Membuat Direktori Bersama dengan Izin 775',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'system_state',
                    'instruction' => 'Buat direktori `/home/student/shared` dan atur permission-nya menjadi `775` (rwxrwxr-x).',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'directory_exists', 'path' => '/home/student/shared'],
                            ['type' => 'file_permission', 'path' => '/home/student/shared', 'mode' => '775'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Gunakan `mkdir -p /home/student/shared` untuk membuat foldernya.'],
                        ['order' => 2, 'content' => 'Gunakan `chmod 775 /home/student/shared` untuk menerapkan permission 775.'],
                    ],
                ],
            ],

            // =================================================================
            // MODUL 3: Text Processing
            // =================================================================

            // Pelajaran 3.1: Mencari Teks
            'mencari-teks' => [
                [
                    'title' => 'Menghitung Jumlah Baris Berisi Kata Linux',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Buat berkas `kata.txt` di `/home/student/` dengan tepat 3 baris yang memuat kata `linux`. Perintah `grep -c \'linux\' /home/student/kata.txt` harus menghasilkan angka `3`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/kata.txt'],
                            ['type' => 'command_output_equals', 'command' => "grep -c 'linux' /home/student/kata.txt", 'text' => '3'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Opsi `-c` pada perintah `grep` berfungsi menghitung jumlah baris yang cocok.'],
                        ['order' => 2, 'content' => 'Buat file dengan tepat 3 baris memuat kata \'linux\', contoh: `printf \'belajar linux\\nkernel linux\\nsistem linux\\n\' > /home/student/kata.txt`.'],
                    ],
                ],
                [
                    'title' => 'Mencari Pesan Error pada File Log',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'combined',
                    'instruction' => 'Buat berkas `server.log` di `/home/student/` yang memuat baris `ERROR: database connection failed`. Pastikan pencarian case-insensitive `grep -i \'error\'` berhasil menemukan baris tersebut.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/server.log'],
                            ['type' => 'command_output_contains', 'command' => "grep -i 'error' /home/student/server.log", 'text' => 'database connection failed'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Opsi `-i` pada `grep` mengabaikan perbedaan huruf besar dan huruf kecil (*case-insensitive*).'],
                        ['order' => 2, 'content' => 'Isi berkas dengan: `echo \'ERROR: database connection failed\' > /home/student/server.log`.'],
                    ],
                ],
            ],

            // Pelajaran 3.2: Redirection dan Pipe
            'redirection-dan-pipe' => [
                [
                    'title' => 'Membuat Direktori data dan File Redirection',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'combined',
                    'instruction' => 'Buat direktori `/home/student/latihan/data` dan simpan daftar berkas dari direktori `/etc` ke dalam berkas `/home/student/latihan/data/hasil.txt` menggunakan redirection `>`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'directory_exists', 'path' => '/home/student/latihan/data'],
                            ['type' => 'file_exists', 'path' => '/home/student/latihan/data/hasil.txt'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Buat direktori terlebih dahulu dengan `mkdir -p /home/student/latihan/data`.'],
                        ['order' => 2, 'content' => 'Arahkan keluaran `ls /etc` dengan: `ls /etc > /home/student/latihan/data/hasil.txt`.'],
                    ],
                ],
                [
                    'title' => 'Menghitung Jumlah Baris Berkas dengan wc -l',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'output',
                    'instruction' => 'Buat berkas `daftar_nama.txt` di `/home/student/` yang berisi tepat 5 baris nama. Pastikan keluaran perintah `wc -l /home/student/daftar_nama.txt` menghasilkan angka `5`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/daftar_nama.txt'],
                            ['type' => 'command_output_contains', 'command' => 'wc -l /home/student/daftar_nama.txt', 'text' => '5'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Perintah `wc -l` menghitung jumlah baris dalam berkas.'],
                        ['order' => 2, 'content' => 'Tulis 5 baris nama, contoh: `printf \'Andi\\nBudi\\nCitra\\nDewi\\nEko\\n\' > /home/student/daftar_nama.txt`.'],
                    ],
                ],
            ],

            // Pelajaran 3.3: Memotong dan Menyaring Teks
            'memotong-dan-menyaring-teks' => [
                [
                    'title' => 'Menyaring Tiga Baris Pertama User Sistem',
                    'order' => 1,
                    'points' => 100,
                    'validation_type' => 'combined',
                    'instruction' => 'Ambil kolom pertama nama pengguna dari berkas `/etc/passwd` menggunakan `cut -d: -f1`, potong 3 baris pertama menggunakan `head -n 3`, lalu simpan hasilnya ke `/home/student/pengguna.txt`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/pengguna.txt'],
                            ['type' => 'file_contains', 'path' => '/home/student/pengguna.txt', 'text' => 'root'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Hubungkan perintah `cut` dan `head` menggunakan pipa (`|`): `cut -d: -f1 /etc/passwd | head -n 3`.'],
                        ['order' => 2, 'content' => 'Arahkan hasil pipa ke berkas dengan redirection: `cut -d: -f1 /etc/passwd | head -n 3 > /home/student/pengguna.txt`.'],
                    ],
                ],
                [
                    'title' => 'Mengurutkan Angka Secara Numerik',
                    'order' => 2,
                    'points' => 100,
                    'validation_type' => 'combined',
                    'instruction' => 'Buat berkas `angka.txt` di `/home/student/` berisi angka acak (misal `9`, `2`, `5`), lalu urutkan secara numerik dengan `sort -n` dan simpan hasilnya ke `/home/student/angka_urut.txt`.',
                    'validation_config' => [
                        'checks' => [
                            ['type' => 'file_exists', 'path' => '/home/student/angka_urut.txt'],
                            ['type' => 'command_output_equals', 'command' => 'head -n 1 /home/student/angka_urut.txt', 'text' => '2'],
                        ],
                    ],
                    'hints' => [
                        ['order' => 1, 'content' => 'Buat berkas sumber dengan: `printf \'9\\n2\\n5\\n\' > /home/student/angka.txt`.'],
                        ['order' => 2, 'content' => 'Urutkan secara numerik dengan `sort -n /home/student/angka.txt > /home/student/angka_urut.txt`.'],
                    ],
                ],
            ],
        ];

        foreach ($exercisesData as $lessonSlug => $exercises) {
            $lesson = Lesson::where('slug', $lessonSlug)->first();
            if (! $lesson) {
                continue;
            }

            foreach ($exercises as $exerciseItem) {
                $exercise = Exercise::updateOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                        'order' => $exerciseItem['order'],
                    ],
                    [
                        'title' => $exerciseItem['title'],
                        'instruction' => $exerciseItem['instruction'],
                        'validation_type' => $exerciseItem['validation_type'],
                        'validation_config' => $exerciseItem['validation_config'],
                        'points' => $exerciseItem['points'],
                    ]
                );

                if (isset($exerciseItem['hints'])) {
                    foreach ($exerciseItem['hints'] as $hintItem) {
                        Hint::updateOrCreate(
                            [
                                'exercise_id' => $exercise->id,
                                'order' => $hintItem['order'],
                            ],
                            [
                                'content' => $hintItem['content'],
                            ]
                        );
                    }
                }
            }
        }
    }
}
