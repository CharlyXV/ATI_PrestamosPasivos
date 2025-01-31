<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Este es necesario
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Para belongsTo

class DetalleRecibo extends Model
{
   /* protected $table = 'detalle_recibos';

    protected $fillable = [
        'recibos_id', 'numero_cuota', 'monto_principal', 
        'monto_intereses', 'monto_seguro', 'monto_otros', 'monto_cuota', 'dere_creado_por'
    ];

    */

    // Relación con Recibo (un detalle pertenece a un recibo)
    use HasFactory;
    public function recibos():BelongsTo
    {
        return $this->belongsTo(Recibo::class);
    }
}

