<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('users')->delete();

        \DB::table('users')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'name' => 'Admin User',
                    'alias' => 'admin',
                    'email' => 'admin@demo.com',
                    'email_verified_at' => NULL,
                    'password' => bcrypt('12345678'),
                    'bio' => 'Administrator of QCFit',
                    'avatar' => null,
                    'remember_token' => NULL,
                    'created_at' => '2025-07-25 08:51:49',
                    'updated_at' => '2025-07-25 08:51:49',
                ),
            1 =>
                array(
                    'id' => 2,
                    'name' => 'Demo User',
                    'alias' => 'demouser',
                    'email' => 'user@demo.com',
                    'email_verified_at' => NULL,
                    'password' => bcrypt('12345678'),
                    'bio' => 'A demo user for QCFit',
                    'avatar' => null,
                    'remember_token' => NULL,
                    'created_at' => '2025-07-25 08:51:50',
                    'updated_at' => '2025-07-25 08:51:50',
                ),
        ));


    }
}
