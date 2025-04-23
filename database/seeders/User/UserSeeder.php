<?php

namespace Database\Seeders\User;

use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->command->info('Creating Admin User...');


        $user = new User();
        $user->name = 'Mohamed Hassan';
        $user->email = 'admin@admin.com';
        $user->password = 'Mans123456';
        $user->is_active = UserStatus::ACTIVE;
        $type = UserType::ADMIN;
        $user->email_verified_at = now();
        $user->phone = '1234567890';
        $user->save();

    }
}
