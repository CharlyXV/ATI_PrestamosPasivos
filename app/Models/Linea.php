<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Linea extends Model
{
    use HasFactory;
    public function prestamos(): BeLongsTo
    {
        return $this->beLongsTo(Prestamo::class);
        
    }
}
