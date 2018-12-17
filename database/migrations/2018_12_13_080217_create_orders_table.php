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
            $table->unsignedInteger('restaurant_id');
            $table->unsignedInteger('offer_id')->nullable();
            $table->enum('order_status' , ['pending' , 'delivered' , 'rejected']);
            $table->enum('client_decision' , ['accepted' , 'rejected','pending']);
            $table->enum('restaurant_decision' , ['accepted' , 'rejected' , 'pending']);
            $table->decimal('price' , 8 , 2);
            $table->decimal('commission' , 8 , 2);
            $table->decimal('delivery_fee' , 8 , 2);
            $table->decimal('total' , 8 , 2);
            $table->string('notes')->nullable();
            $table->decimal('discount' ,8 ,2 )->nullable();
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
