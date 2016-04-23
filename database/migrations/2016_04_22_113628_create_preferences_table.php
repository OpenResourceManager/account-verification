<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('application_name');
            $table->string('application_email');
            $table->string('ldap_servers')->nullable();
            $table->unsignedInteger('ldap_port')->nullable();
            $table->boolean('ldap_ssl')->nullable();
            $table->string('ldap_bind_user_dn')->nullable();
            $table->string('ldap_bind_password')->nullable();
            $table->string('ldap_search_base')->nullable();
            $table->string('ldap_domain')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('preferences');
    }
}
