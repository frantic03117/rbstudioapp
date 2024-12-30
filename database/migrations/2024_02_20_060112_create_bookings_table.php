<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('studio_id')->unsigned();
            $table->integer('vendor_id')->unsigned();
            $table->string('bill_no')->nullable()->unique();
            $table->date('booking_date')->nullable();
            $table->integer('start_at')->nullable();
            $table->integer('end_at')->nullable();
            $table->double('studio_charge')->nullable()->comment('per hour');
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
        Schema::dropIfExists('bookings');
    }
};
