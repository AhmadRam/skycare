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
        Schema::create('country_state_city_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale');
            $table->text('default_name')->nullable();

            $table->integer('country_state_city_id')->unsigned();
            $table->foreign('country_state_city_id')->references('id')->on('country_state_cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country_state_city_translations');
    }
};
