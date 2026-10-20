<?php
 
namespace Database\Seeders;
 
use App\Models\User;
use Illuminate\Database\Seeder;
 
class DatabaseSeeder extends Seeder
{
    /**
     * Seed user demo. Idempotent (aman dijalankan berulang saat container restart).
     * Email dan password dibaca dari env, tidak ada kredensial di dalam kode.
     */
    public function run(): void
    {
        $email = env('SEED_USER_EMAIL', 'admin@example.com');
        $password = env('SEED_USER_PASSWORD');
 
        if (empty($password)) {
            $this->command?->warn('SEED_USER_PASSWORD kosong, user demo tidak dibuat.');
 
            return;
        }
 
        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Demo User', 'password' => $password],
        );
    }
}