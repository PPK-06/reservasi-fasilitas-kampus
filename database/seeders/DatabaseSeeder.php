<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Akun demo mengikuti bagian 5 dokumen pembagian modul: 1 admin,
     * 2 petugas, dan pengguna yang mencakup keempat status C2. Seluruhnya
     * memakai satu password seragam, yaitu UserFactory::DEMO_PASSWORD.
     */
    public function run(): void
    {
        $this->seedUsers();
    }

    /**
     * Akun demo. Status diisi lewat state factory — factory berjalan
     * unguarded, sehingga kolom status yang tidak fillable (F10) tetap terisi
     * tanpa melanggar aturan mass assignment.
     */
    private function seedUsers(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Kampus',
            'email' => 'admin@kampus.test',
        ]);

        User::factory()->petugas()->create([
            'name' => 'Petugas Sarana',
            'email' => 'petugas1@kampus.test',
        ]);

        User::factory()->petugas()->create([
            'name' => 'Petugas Laboratorium',
            'email' => 'petugas2@kampus.test',
        ]);

        User::factory()->create([
            'name' => 'Pengguna Verified',
            'email' => 'pengguna.verified@kampus.test',
            'identity_number' => '2311000001',
            'user_type' => 'mahasiswa',
        ]);

        User::factory()->pending()->create([
            'name' => 'Pengguna Pending',
            'email' => 'pengguna.pending@kampus.test',
            'identity_number' => '2311000002',
            'user_type' => 'mahasiswa',
        ]);

        User::factory()->rejected()->create([
            'name' => 'Pengguna Rejected',
            'email' => 'pengguna.rejected@kampus.test',
            'identity_number' => '2311000003',
            'user_type' => 'dosen',
        ]);

        User::factory()->suspended()->create([
            'name' => 'Pengguna Suspended',
            'email' => 'pengguna.suspended@kampus.test',
            'identity_number' => '2311000004',
            'user_type' => 'staf',
        ]);

        $this->command->info('Akun demo dibuat. Password seragam: '.UserFactory::DEMO_PASSWORD);
    }
}
