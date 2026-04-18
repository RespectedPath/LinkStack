<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // LinkStack's MySQL installer path calls PageSeeder and ButtonSeeder
        // explicitly. The SQLite path never does — it just relies on
        // whatever the environment's startup script runs. Railpack's start
        // hook calls `db:seed` (which lands here), so include both seeders
        // so the pages / buttons tables get populated on first boot.
        $this->call([
            PageSeeder::class,
            ButtonSeeder::class,
        ]);
    }
}
