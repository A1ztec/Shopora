<?php

namespace Database\Seeders;

use App\Enum\User\UserStatus;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->delete();
        $data = json_decode(file_get_contents(database_path('data/Users.json')), true);

        foreach ($data['users'] as $userData) {
            $role = $userData['role'] ?? null;

            //dd($role);

            $userData['password'] = bcrypt('password');
            $userData['email_verified_at'] = now();
            $userData['status'] = UserStatus::ACTIVE;

            $userData = Arr::except($userData, ['role']);

            $user = User::create($userData);

            if ($role) {
                $user->assignRole($role);
            }
        }
    }
}
