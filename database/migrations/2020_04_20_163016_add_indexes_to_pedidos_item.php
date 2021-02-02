<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToPedidosItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos_item', function (Blueprint $table) {
            $table->index(['fk_pedido', 'fk_curso', 'fk_evento', 'fk_trilha', 'fk_assinatura'], 'pedidos_item_relatorio_financeiro_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos_item', function (Blueprint $table) {
            $table->dropIndex(['relatorio_financeiro']);
        });
    }
}
