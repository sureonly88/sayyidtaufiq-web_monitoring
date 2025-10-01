<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WebMntrShareLoket extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_mntr_shareLoket', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_lokets');
            $table->double('pdam')->nullable()->default(0);
            $table->double('pln_postpaid')->nullable()->default(0);
            $table->double('pln_prepaid')->nullable()->default(0);
            $table->double('pln_nontaglis')->nullable()->default(0);
            $table->double('pln_postpaid_n')->nullable()->default(0);
            $table->double('pln_prepaid_n')->nullable()->default(0);
            $table->double('pln_nontaglis_n')->nullable()->default(0);
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
        Schema::dropIfExists('web_mntr_shareLoket');
    }
}
