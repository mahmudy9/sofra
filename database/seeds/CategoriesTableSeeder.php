<?php

use Illuminate\Database\Seeder;
use App\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cat = new Category;
        $cat->name = "Burger and Sandwiches";
        $cat->save();

        $cat2 = new Category;
        $cat2->name = "Pizza";
        $cat2->save();
    }
}
