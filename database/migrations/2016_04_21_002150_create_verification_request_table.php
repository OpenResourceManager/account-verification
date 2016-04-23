<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerificationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('verified');
            $table->string('request_username');
            $table->string('request_identifier');
            $table->string('request_ssn');
            $table->string('request_dob');
            $table->string('returned_username')->nullable();
            $table->string('returned_identifier')->nullable();
            $table->string('returned_ssn')->nullable();
            $table->string('returned_dob')->nullable();
            $table->unsignedInteger('returned_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verification_requests', function (Blueprint $table) {
            $table->dropForeign('verification_requests_user_id_foreign');
        });

        Schema::drop('verification_requests');
    }
}
