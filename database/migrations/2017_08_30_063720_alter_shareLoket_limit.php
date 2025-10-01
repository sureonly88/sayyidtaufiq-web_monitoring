<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShareLoketLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('web_mntr_shareLoket', function (Blueprint $table) {
            $table->double('limit')->nullable()->default(0);;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('web_mntr_shareLoket', function (Blueprint $table) {
            $table->dropColumn('limit');
        });
    }
}
