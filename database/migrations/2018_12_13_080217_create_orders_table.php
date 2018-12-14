<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->enum('order_status' , ['pending' , 'delivered' , 'rejected']);
            $table->enum('client_decision' , ['accepted' , 'rejected']);
            $table->enum('restaurant_decision' , ['accepted' , 'rejected']);
            $table->decimal('price' , 8 , 2);
            $table->decimal('commission' , 8 , 2);
            $table->decimal('delivrey_fee' , 8 , 2);
            $table->decimal('total' , 8 , 2);
            $table->string('notes');
            $table->decimal('discount' ,8 ,2 );
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
        Schema::dropIfExists('orders');
    }
}
