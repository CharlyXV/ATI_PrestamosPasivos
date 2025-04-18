<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Producto extends Model
{
    use HasFactory;
    public function prestamos(): BeLongsTo
    {
        return $this->beLongsTo(Prestamo::class);
        
    }
}
