<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Planpago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportPayController extends Controller
{
    public function store(Request $request)
    {
        // Crear el préstamo una sola vez y sin duplicados
        $prestamo = Prestamo::create($request->all());

        // Crear el plan de pagos
        $this->createPaymentPlan($prestamo);

        return redirect()->route('prestamos.index')->with('success', 'Préstamo y plan de pagos creados exitosamente.');
    }

    public function createPaymentPlan(Prestamo $prestamo)
    {
        // Eliminar todos los pagos existentes asociados a este préstamo y registrar los borrados
        $planPagos = Planpago::where('prestamo_id', $prestamo->id)->get();
        foreach ($planPagos as $planPago) {
            Log::info('Eliminando plan de pago', ['id' => $planPago->id, 'prestamo_id' => $prestamo->id]);
            $planPago->delete();
        }

        $loanAmount = $prestamo->monto_prestamo;
        $interestRate = $prestamo->tasa_interes / 100;
        $term = $prestamo->plazo_meses;
        $monthlyRate = $interestRate / 12;

        $numberOfPayments = $term;

        $monthlyPayment = $loanAmount * $monthlyRate / (1 - pow(1 + $monthlyRate, -$numberOfPayments));
        $remainingBalance = $loanAmount;

        for ($i = 1; $i <= $numberOfPayments; $i++) {
            $monthlyInterest = $remainingBalance * $monthlyRate;
            $principalPayment = $monthlyPayment - $monthlyInterest;
            $remainingBalance -= $principalPayment;

            Planpago::create([
                'prestamo_id' => $prestamo->id,
                'numero_cuota' => $i,
                'fecha_pago' => now()->addMonths($i - 1),
                'monto_principal' => $principalPayment,
                'monto_interes' => $monthlyInterest,
                'monto_seguro' => 0,
                'monto_otros' => 0,
                'saldo_prestamo' => $remainingBalance,
                'tasa_interes' => $prestamo->tasa_interes,
                'saldo_principal' => $principalPayment,
                'saldo_interes' => $monthlyInterest,
                'saldo_seguro' => 0,
                'saldo_otros' => 0,
                'observaciones' => '',
            ]);
        }
    }

    public function generateReport(Prestamo $prestamo) {
        // Cargar la relación explícitamente
        $prestamo->load('planpagos'); 
    
        // Verificar si hay registros
        if ($prestamo->planpagos->isEmpty()) {
            abort(404, 'No hay pagos asociados a este préstamo.');
        }
    
        // Procesar los datos
        $planPagos = $prestamo->planpagos->map(function($planPago) {
            $planPago->fecha_pago = \Carbon\Carbon::parse($planPago->fecha_pago);
            return $planPago;
        });
    
        // Generar el PDF
        $pdf = Pdf::loadView('report.pay_report', compact('prestamo', 'planPagos'));
        return $pdf->download('pay_report.pdf');
    }
}
