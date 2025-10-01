<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WebMonitorLoketPeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_mntr_loketPeta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
            $table->string('alamat')->nullable();
            $table->double('latitude')->nullable()->default(0);
            $table->double('longitude')->nullable()->default(0);
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
        Schema::dropIfExists('web_mntr_loketPeta');
    }
}
