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
        Schema::create('recibos', function (Blueprint $table) {
            $table->id(); // Primary Key: ID
            $table->string('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('prestamo_id')->constrained('prestamos')->cascadeOnDelete();
            $table->string('tipo_recibo'); // CUOTA NORMAL, ANTICIPADO, LIQUIDACION
            $table->string('detalle'); 
            $table->string('estado'); // I = INCLUIDO, C = CONTABILIZADO, A = ANULADO
            $table->foreignId('cuenta_id')->constrained('cuentas')->cascadeOnDelete();                   
            $table->decimal('monto_recibo'); 
            $table->date('fecha_pago'); //fecha que se realiza el pago
            $table->date('fecha_deposito'); //fecha que se realiza el deposito
            $table->string('razon_anulacion')->nullable(); //se actualiza cuando se anula el recibo
            $table->date('fecha_anulacion')->nullable(); //se actualiza cuando se anula el recibo
            $table->decimal('saldo_anterior');  // se actualizan cuando se procesa el recibo
            $table->decimal('saldo_actual'); // se actualizan cuando se procesa el recibo
            $table->timestamps();
    
            
            
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
