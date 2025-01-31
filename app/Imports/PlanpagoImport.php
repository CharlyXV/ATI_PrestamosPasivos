<?php
namespace App\Imports;

use App\Models\Planpago; 
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PlanpagoImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Planpago([
            'prestamo_id'=> $row['prestamo_id'],
            'numero_cuota'=> $row['numero_cuota'],            
            'fecha_pago' => \Carbon\Carbon::parse($row['fecha_pago']),            
            'monto_principal'=> $row['monto_principal'],
            'monto_interes'=> $row['monto_interes'],
            'monto_seguro'=> $row['monto_seguro'],
            'monto_otros'=> $row['monto_otros'],
            'saldo_prestamo'=> $row['saldo_prestamo'],
            'tasa_interes'=> $row['tasa_interes'],
            'saldo_principal'=> $row['saldo_principal'],
            'saldo_interes'=> $row['saldo_interes'],
            'saldo_seguro'=> $row['saldo_seguro'],
            'saldo_otros'=> $row['saldo_otros'],
            'observaciones'=> $row['observaciones'],
           
        ]);
    }
}


