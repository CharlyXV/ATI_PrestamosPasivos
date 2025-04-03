<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_recibo')->unique();
            $table->string('tipo_recibo', 2); // CN, CA, LI
            $table->text('detalle');
            $table->string('estado', 1); // I, C, A
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->decimal('monto_recibo', 15, 2);
            $table->date('fecha_pago');
            $table->date('fecha_deposito');
            $table->foreignId('prestamo_id')->constrained('prestamos');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recibos');
    }
};
