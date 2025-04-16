<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRecibo extends Model
{
    use HasFactory;
    
    protected $table = 'detalle_recibo';

    // app/Models/DetalleRecibo.php
protected $fillable = [
    'recibo_id',
    'planpago_id',
    'numero_cuota',
    'monto_principal',
    'monto_intereses',
    'monto_seguro',
    'monto_otros',
    'monto_cuota'
];

protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        $model->numero_cuota = $model->numero_cuota ?? optional($model->planpago)->numero_cuota ?? 0;
        $model->monto_cuota = $model->monto_cuota ?? (
            ($model->monto_principal ?? 0) +
            ($model->monto_intereses ?? 0) +
            ($model->monto_seguro ?? 0) +
            ($model->monto_otros ?? 0)
        );
    });
}

    public function recibo(): BelongsTo
    {
        return $this->belongsTo(Recibo::class);
    }

    public function planpago(): BelongsTo
    {
        return $this->belongsTo(Planpago::class);
    }

    

}
