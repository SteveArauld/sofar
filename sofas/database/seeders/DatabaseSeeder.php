<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Importe le catalogue scrapé (../output/*.json + images).
        $this->command->call('catalog:import');
    }
}
