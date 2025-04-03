<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRecibo extends Model
{
    use HasFactory;
    
    protected $table = 'detalle_recibo';

    protected $fillable = [
        'recibo_id', 'planpago_id', 'numero_cuota',
        'monto_principal', 'monto_intereses',
        'monto_seguro', 'monto_otros', 'monto_cuota'
    ];

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(Recibo::class);
    }

    public function planpago(): BelongsTo
    {
        return $this->belongsTo(Planpago::class);
    }
}
