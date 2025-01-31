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
    Schema::create('detalle_recibos', function (Blueprint $table) {
        $table->id(); // Primary Key: ID
        $table->foreignId('recibo_id')->constrained('recibos')->cascadeOnDelete();;
        $table->unsignedInteger('numero_cuota'); // NUMERIC(5)
        $table->decimal('monto_principal');
        $table->decimal('monto_intereses');
        $table->decimal('monto_seguro');
        $table->decimal('monto_otros');
        $table->decimal('monto_cuota'); // DEBE SER IGUAL AL DEL RECIBO        
        $table->timestamps();

        
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_recibos');
    }
};
