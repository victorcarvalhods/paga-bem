<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Wallet::factory(10)->user()->create();
        Wallet::factory(10)->merchant()->create();
    }
}
