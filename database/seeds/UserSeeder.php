<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $guest = \App\ApiUser();
        $guest->name ='guest';
        $guest->surname ='guest';
        $guest->email = 'guest@mail.com';
        $guest->password = hash('sha256', '1234');
        $guest->role = 'guest';
        $guest->active = 1;
        $guest->save();

        $admin = \App\ApiUser();
        $admin->name ='admin';
        $admin->surname ='admin';
        $admin->email = 'admin@mail.com';
        $admin->password = hash('sha256', '1234');
        $admin->role = 'admin';
        $admin->active = 1;
        $admin->save();
    }
}
