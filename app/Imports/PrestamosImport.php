<?php
namespace App\Imports;

use App\Models\Prestamo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PrestamosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Prestamo([
          'empresa_id' => $row['empresa_id'],
            'numero_prestamo' => $row['numero_prestamo'],
            'banco_id' => $row['banco_id'],
            'linea_id' => $row['linea_id'],
            'forma_pago' => $row['forma_pago'],
            'moneda' => $row['moneda'],            
            'formalizacion' => \Carbon\Carbon::parse($row['formalizacion']),            
            'vencimiento' => \Carbon\Carbon::parse($row['vencimiento']),
            'proximo_pago' => $row['proximo_pago'],
            'monto_prestamo' => $row['monto_prestamo'],
            'saldo_prestamo' => $row['saldo_prestamo'],
            'plazo_meses' => $row['plazo_meses'],
            'tipo_tasa_id' => $row['tipo_tasa_id'],
            'tasa_interes' => $row['tasa_interes'],
            'tasa_spreed' => $row['tasa_spreed'],
            'cuenta_desembolso' => $row['cuenta_desembolso'],
            'estado' => $row['estado'],
            'periodicidad_pago' => $row['periodicidad_pago'],
            'observacion' => $row['observacion'],
        ]);
    }
}


