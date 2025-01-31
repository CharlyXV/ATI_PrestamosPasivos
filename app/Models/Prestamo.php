<?php

namespace App\Models;
use App\Models\Producto;
use App\Casts\LoanController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// cambio tasas


class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [ 'empresa_id', 'numero_prestamo', 'banco_id', 'linea_id', 'forma_pago', 'moneda', 'formalizacion', 'vencimiento', 'proximo_pago', 'monto_prestamo', 'saldo_prestamo', 'plazo_meses', 'tipo_tasa_id', 'tasa_interes', 'tasa_spreed', 'cuenta_desembolso', 'estado', 'periodicidad_pago', 'observacion', ];

    public function banco(): BeLongsTo
    {
        return $this->beLongsTo(Banco::class);
        
    }

    public function empresa(): BeLongsTo
    {
        return $this->beLongsTo(Empresa::class);
        
    }
    
    public function linea(): BeLongsTo
    {
        return $this->beLongsTo(Linea::class);
        
    }
    
    public function producto(): BeLongsTo
    {
        return $this->beLongsTo(Productos::class);
        
    }
    
    public function tipotasa(): BeLongsTo
    {
        return $this->beLongsTo(Tipotasa::class);
        
    }
    

    public function planpago(): HasMany
    {
        return $this->hasMany(Planpago::class, 'prestamo_id');
        
    }

    public function planpagos() {
        return $this->hasMany(Planpago::class);
    }

}
