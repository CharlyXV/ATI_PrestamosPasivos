<?php

// app/Models/Recibo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recibo extends Model
{
    protected $fillable = [
        'numero_recibo', 'tipo_recibo', 'detalle', 'estado', 
        'cuenta_id', 'monto_recibo', 'fecha_pago', 'fecha_deposito',
        'prestamo_id'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_deposito' => 'date',
        'monto_recibo' => 'decimal:2'
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRecibo::class);
    }

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }
}

// app/Models/DetalleRecibo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRecibo extends Model
{
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