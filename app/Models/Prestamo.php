<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'tipotasa_id', // Corregido para coincidir con el formulario
        'tasa_interes', 
        'tasa_spreed', 
        'cuenta_desembolso', 
        'estado', 
        'periodicidad_pago', 
        'observacion'
    ];

    // Relaciones corregidas (camelCase y tipo correcto BelongsTo)
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
}