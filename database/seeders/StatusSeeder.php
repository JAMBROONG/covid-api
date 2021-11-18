<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Status::create([
            "name" => "positive",
        ]);
        Status::create([
            "name" => "recovered",
        ]);
        Status::create([
            "name" => "dead",
        ]);
    }
}
