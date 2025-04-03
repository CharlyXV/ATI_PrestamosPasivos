<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detalle_recibo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('planpago_id')->constrained('planpagos');
            $table->unsignedInteger('numero_cuota');
            $table->decimal('monto_principal', 15, 2);
            $table->decimal('monto_intereses', 15, 2);
            $table->decimal('monto_seguro', 15, 2);
            $table->decimal('monto_otros', 15, 2);
            $table->decimal('monto_cuota', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_recibo');
    }
};
