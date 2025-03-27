<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // En el archivo de migración generado
public function up()
{
    Schema::table('pagos', function (Blueprint $table) {
        $table->string('moneda', 3)->default('CRC')->after('monto');
    });
}

public function down()
{
    Schema::table('pagos', function (Blueprint $table) {
        $table->dropColumn('moneda');
    });
}
};
