<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUudPrefs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preferences', function($table) {
            $table->dropColumn('uud_api_url');
            $table->dropColumn('uud_api_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preferences', function($table) {
            $table->string('uud_api_url');
            $table->string('uud_api_key');
        });
    }
}
