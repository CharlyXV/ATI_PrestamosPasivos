<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('recibo_detalles', function (Blueprint $table) {
        $table->decimal('monto_principal_original', 12, 2)->nullable();
        $table->decimal('monto_intereses_original', 12, 2)->nullable();
        $table->decimal('monto_seguro_original', 12, 2)->nullable();
        $table->decimal('monto_otros_original', 12, 2)->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recibo_detalles', function (Blueprint $table) {
            //
        });
    }
};
