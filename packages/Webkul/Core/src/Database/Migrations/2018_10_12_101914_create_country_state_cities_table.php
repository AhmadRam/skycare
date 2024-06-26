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
        Schema::create('country_state_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country_code')->nullable();
            $table->string('state_code')->nullable();
            $table->string('default_name')->nullable();
            $table->string('code')->nullable();

            $table->integer('country_state_id')->nullable()->unsigned();
            $table->foreign('country_state_id')->references('id')->on('country_states')->onDelete('cascade');
            $table->integer('country_id')->nullable()->unsigned();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country_state_cities');
    }
};
