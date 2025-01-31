<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuenta extends Model
{
   /* protected $table = 'cuentas'; // Nombre de la tabla en la BD
    protected $primaryKey = 'codigo_banco'; // Llave primaria personalizada
    public $incrementing = false; // Llave primaria no es incremental
    protected $keyType = 'string'; // Tipo de la llave primaria
*/
   // protected $fillable = ['codigo_banco', 'numero_cuenta', 'nombre_cuenta', 'moneda'];

    // Relación con los recibos
    public function recibos():HasMany
    {
        return $this->hasMany(Recibo::class);
    }
}