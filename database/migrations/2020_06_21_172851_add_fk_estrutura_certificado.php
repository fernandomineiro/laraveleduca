<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkEstruturaCertificado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('certificados', function (Blueprint $table) {
            $table->integer('fk_curso')->nullable()->change();
            $table->integer('fk_estrutura')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->integer('fk_curso')->change();
            $table->dropColumn(['fk_estrutura']);
        });
    }
}
