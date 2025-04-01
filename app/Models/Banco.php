<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banco extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_banco',
        'cuenta_desembolsoB', // Este es el nuevo campo agregado
        'created_at',
        'updated_at'
    ];
  
    // Relación correcta (pero debería ser HasMany si un banco tiene muchos préstamos)
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class); // Cambiado de belongsTo a hasMany
    }
}
