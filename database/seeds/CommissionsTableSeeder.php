<?php

use Illuminate\Database\Seeder;
use App\Commission;
class CommissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $com = new Commission;
        $com->commission = 10.00;
        $com->save();
    }
}
