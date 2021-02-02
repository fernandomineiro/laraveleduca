<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCertificadoEstruturaCurricular extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('estrutura_curricular', function (Blueprint $table) {
            $table->addColumn('integer', 'fk_certificado_layout', ['length' => 11])
                ->nullable();
            $table->foreign('fk_certificado_layout')
                ->references('id')
                ->on('certificado_layout');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('estrutura_curricular', function (Blueprint $table) {
            $table->dropForeign(['fk_certificado_layout']);
            $table->dropColumn(['fk_certificado_layout']);
        });
    }
}
