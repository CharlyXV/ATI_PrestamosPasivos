<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Planpago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportPayController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $this->validatePrestamoData($request);
        
        $prestamo = DB::transaction(function() use ($validatedData) {
            $prestamo = Prestamo::create($validatedData);
            $this->createPaymentPlan($prestamo);
            return $prestamo;
        });

        return redirect()->route('prestamos.index')
               ->with('success', 'Préstamo y plan de pagos creados exitosamente.');
    }

    protected function validatePrestamoData(Request $request)
    {
        return $request->validate([
            'empresa_id' => 'required|integer',
            'numero_prestamo' => 'required|string|unique:prestamos',
            'monto_prestamo' => 'required|numeric|min:0.01',
            'tasa_interes' => 'required|numeric|min:0',
            'plazo_meses' => 'required|integer|min:1',
            'banco_id' => 'required|integer',
            'linea_id' => 'required|integer',
            'formalizacion' => 'required|date',
            // otros campos necesarios
        ]);
    }

    public function createPaymentPlan(Prestamo $prestamo)
    {
        try {
            Planpago::where('prestamo_id', $prestamo->id)->delete();

            $loanAmount = (float)$prestamo->monto_prestamo;
            $interestRate = (float)$prestamo->tasa_interes / 100;
            $term = (int)$prestamo->plazo_meses;
            $monthlyRate = $interestRate / 12;

            $monthlyPayment = $loanAmount * $monthlyRate / (1 - pow(1 + $monthlyRate, -$term));
            $remainingBalance = $loanAmount;

            for ($i = 1; $i <= $term; $i++) {
                $interestPayment = $remainingBalance * $monthlyRate;
                $principalPayment = $monthlyPayment - $interestPayment;
                $remainingBalance -= $principalPayment;

                Planpago::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $i,
                    'fecha_pago' => $this->calculateDueDate($prestamo->formalizacion, $i),
                    'monto_principal' => $this->formatDecimal($principalPayment),
                    'monto_interes' => $this->formatDecimal($interestPayment),
                    'monto_seguro' => 0,
                    'monto_otros' => 0,
                    'saldo_prestamo' => $this->formatDecimal(max($remainingBalance, 0)),
                    'tasa_interes' => $prestamo->tasa_interes,
                    'saldo_principal' => $this->formatDecimal($principalPayment),
                    'saldo_interes' => $this->formatDecimal($interestPayment),
                    'saldo_seguro' => 0,
                    'saldo_otros' => 0,
                    'observaciones' => 'Cuota '.$i.' de '.$term,
                    'plp_estados' => 'pendiente'
                ]);
            }

            Log::info("Plan de pagos generado para préstamo {$prestamo->id}");

        } catch (\Exception $e) {
            Log::error("Error generando plan de pagos: ".$e->getMessage());
            throw $e;
        }
    }

    protected function calculateDueDate($startDate, $monthOffset)
    {
        return Carbon::parse($startDate)
               ->addMonths($monthOffset)
               ->format('Y-m-d');
    }

    private function formatDecimal($value): float
    {
        return round((float)$value, 2);
    }

    public function generateReport(Prestamo $prestamo)
    {
        $prestamo->load('planpagos');
    
        if ($prestamo->planpagos->isEmpty()) {
            abort(404, 'No se encontró plan de pagos para este préstamo.');
        }
    
        $pdf = Pdf::loadView('report.pay_report', [
            'prestamo' => $prestamo,
            'planPagos' => $prestamo->planpagos->sortBy('numero_cuota')
        ]);
        
        return $pdf->download('plan_de_pagos_'.$prestamo->numero_prestamo.'.pdf');
    }
}