<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Este es necesario
use Illuminate\Database\Eloquent\Relations\HasMany; // Para hasMany
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Para belongsTo

class Recibo extends Model
{
   /* protected $table = 'recibos';

    protected $fillable = [
        'empresa_id', 'tipo_recibo', 'detalle', 'estado', 'banco_id', 
        'moneda_prestamo', 'numero_prestamo', 'monto_recibo', 'fecha_pago',
        'razon_anulacion', 'fecha_anulacion', 'saldo_anterior', 'saldo_actual'
    ];
*/
    // Relación con CuentaBancaria (un recibo pertenece a una cuenta bancaria)
    use HasFactory;
    public function cuentas():BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    // Relación con DetalleRecibo (un recibo tiene muchos detalles)
    public function detallerecibos():HasMany
    {
        return $this->hasMany(DetalleRecibo::class);
    }
    public function empresa(): BeLongsTo
    {
        return $this->beLongsTo(Empresa::class);
        
    }
    public function prestamo(): BeLongsTo
    {
        return $this->beLongsTo(Prestamo::class);
        
    }
    public function cuenta(): BeLongsTo
    {
        return $this->beLongsTo(Cuenta::class);
        
    }

}
