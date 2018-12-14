<?php

use Illuminate\Database\Seeder;
use App\City;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $city = new City;
        $city->name = "Cairo";
        $city->save();

        $city2 = new City;
        $city2->name = "Mansoura";
        $city2->save();
        
        $city3 = new City;
        $city3->name = "Alexandria";
        $city3->save();
    }
}
