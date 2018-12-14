<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('neighborhood_id');
            $table->string('email');
            $table->string('password');
            $table->unsignedInteger('category_id');
            $table->decimal('min_order' , 8 , 2);
            $table->decimal('delivery_fee' , 8 ,2);
            $table->time('deliverytime_from');
            $table->time('deliverytime_to');
            $table->enum('order_days' , ['all days' , 'all days except friday' , 'all days except saturday' , 'all days except sunday' , 'all days except sunday and saturday']);
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('pic');
            $table->decimal('rating' , 8 , 2)->nullable();
            $table->enum('status' , ['open' , 'closed'])->default('closed');
            $table->tinyInteger('activated')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurants');
    }
}
