<?php

use Illuminate\Database\Seeder;
use App\City;
use App\Neighborhood;

class NeighborhoodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $city1 = City::where('name' , 'Cairo')->first();
        $city2 = City::where('name' , 'Mansoura')->first();
        $city3 = City::where('name' , 'Alexandria')->first();

        $n11 = new Neighborhood;
        $n11->name = 'A';
        $city1->neighborhoods()->save($n11);

        $n12 = new Neighborhood;
        $n12->name = 'B';
        $city1->neighborhoods()->save($n12);
        
        $n13 = new Neighborhood;
        $n13->name = 'C';
        $city1->neighborhoods()->save($n13);
        
        $n14 = new Neighborhood;
        $n14->name = 'D';
        $city1->neighborhoods()->save($n14);

        $n21 = new Neighborhood;
        $n21->name = 'A';
        $city2->neighborhoods()->save($n21);
        
        $n22 = new Neighborhood;
        $n22->name = 'B';
        $city2->neighborhoods()->save($n22);
        
        $n23 = new Neighborhood;
        $n23->name = 'C';
        $city2->neighborhoods()->save($n23);
        
        $n24 = new Neighborhood;
        $n24->name = 'D';
        $city2->neighborhoods()->save($n24);

        $n31 = new Neighborhood;
        $n31->name = 'A';
        $city3->neighborhoods()->save($n31);

        $n32 = new Neighborhood;
        $n32->name = 'B';
        $city3->neighborhoods()->save($n32);

        $n33 = new Neighborhood;
        $n33->name = 'C';
        $city3->neighborhoods()->save($n33);

        $n34 = new Neighborhood;
        $n34->name = 'D';
        $city3->neighborhoods()->save($n34);

    }
}
