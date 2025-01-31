<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planpago extends Model
{

    use HasFactory;

    protected $fillable = [ 'prestamo_id', 'numero_cuota', 'fecha_pago', 'monto_principal', 'monto_interes', 'monto_seguro', 'monto_otros', 'saldo_prestamo', 'tasa_interes', 'saldo_principal', 'saldo_interes', 'saldo_seguro', 'saldo_otros', 'observaciones', ];
    public function prestamos(): BeLongsTo
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
        
    }


}
