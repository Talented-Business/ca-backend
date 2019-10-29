<?php

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
         $this->call(UserSeeder::class);
         $this->call(RolesSeeder::class);
         //$this->call(RecruitsSeeder::class);
         //$this->call(AttributesTableSeeder::class);
         //$this->call(DepartmentsSeeder::class);
         //$this->call(CompaniesSeeder::class);
    }
}
