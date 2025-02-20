<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('pagos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('planpago_id')->constrained()->onDelete('cascade'); // Relación con la cuota
        $table->foreignId('prestamo_id')->constrained()->onDelete('cascade'); // Relación con la cuota
        $table->decimal('monto', 10, 2); // Monto del pago
        $table->date('fecha_pago'); // Fecha del pago
        $table->string('referencia')->nullable(); // Referencia del depósito bancario
        $table->string('estado')->default('pendiente'); // Estado del pago (pendiente, completado, etc.)
        $table->timestamps();
    });
}
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
