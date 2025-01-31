<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// cambio tasas
class Tipotasa extends Model
{
    use HasFactory;
    public function prestamos(): BeLongsTo
    {
        return $this->beLongsTo(Prestamo::class);
        
    }
}
