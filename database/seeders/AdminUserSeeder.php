<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat akun administrator awal LinuxLab.
     * Password dibaca secara aman dari environment variable ADMIN_PASSWORD di .env
     * agar tidak pernah tersimpan di repositori kode (sesuai aturan keamanan).
     */
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            if ($this->command) {
                $this->command->warn('⚠️  Variabel lingkungan ADMIN_PASSWORD belum diatur di file .env.');
                $this->command->line('Tambahkan baris berikut ke file .env Anda:');
                $this->command->line('ADMIN_PASSWORD="KataSandiAdminKuatAnda"');
                $this->command->line('lalu jalankan: php artisan db:seed --class=AdminUserSeeder');
            }

            return;
        }

        $email = env('ADMIN_EMAIL', 'admin@linuxlab.local');
        $username = env('ADMIN_USERNAME', 'admin');
        $name = env('ADMIN_NAME', 'Administrator LinuxLab');

        // role/is_active di luar $fillable (anti mass-assignment) → tulis unguarded
        User::unguarded(fn () => User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]
        ));

        if ($this->command) {
            $this->command->info("✓ Akun administrator ({$username} / {$email}) berhasil disiapkan.");
        }
    }
}
