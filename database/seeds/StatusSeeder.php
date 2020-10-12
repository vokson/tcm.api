<?php

use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transmittal = \App\Status(['TRANSMITTAL']);
        $transmittal->save();
    }
}
