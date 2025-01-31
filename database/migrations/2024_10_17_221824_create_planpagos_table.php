<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('planpagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos')->cascadeOnDelete();
            $table->unsignedInteger('numero_cuota');
            $table->dateTime('fecha_pago');
            $table->decimal('monto_principal');
            $table->decimal('monto_interes');
            $table->decimal('monto_seguro');
            $table->decimal('monto_otros');
            $table->decimal('saldo_prestamo');
            $table->decimal('tasa_interes');
            $table->decimal('saldo_principal');
            $table->decimal('saldo_interes');
            $table->decimal('saldo_seguro');
            $table->decimal('saldo_otros');
            $table->string('observaciones'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planpagos');
    }
};
