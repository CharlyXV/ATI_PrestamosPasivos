<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Controllers\ReportPayController;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'numero_prestamo',
        'banco_id',
        'linea_id',
        'forma_pago',
        'moneda',
        'formalizacion',
        'vencimiento',
        'proximo_pago',
        'monto_prestamo',
        'saldo_prestamo',
        'plazo_meses',
        'tipotasa_id',
        'tasa_interes',
        'tasa_spreed',
        'cuenta_desembolso',
        'estado',
        'periodicidad_pago',
        'observacion'
    ];

    public function setPlazoMesesAttribute($value)
    {
        $this->attributes['plazo_meses'] = is_numeric($value) ? (int)$value : 0;
    }

    public function setPeriodicidadPagoAttribute($value)
    {
        $this->attributes['periodicidad_pago'] = is_numeric($value) ? (int)$value : 0;
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function linea(): BelongsTo
    {
        return $this->belongsTo(Linea::class);
    }

    public function tipotasa(): BelongsTo
    {
        return $this->belongsTo(Tipotasa::class, 'tipotasa_id');
    }

    public function planpagos()
    {
        return $this->hasMany(Planpago::class);
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class);
    }

    protected static function boot()
{
    parent::boot();

    static::deleting(function($prestamo) {
        // Eliminar todas las relaciones en cascada (solo para desarrollo)
        if (app()->environment('local', 'development')) {
            // 1. Primero eliminar los detalles de los recibos
            $prestamo->recibos->each(function($recibo) {
                // Eliminar los detalles que referencian planpagos
                $recibo->detalles()->delete();
            });
            
            // 2. Eliminar los recibos
            $prestamo->recibos()->delete();
            
            // 3. Ahora eliminar los planpagos (que ya no son referenciados)
            $prestamo->planpagos()->delete();
        }
    });
}
}
