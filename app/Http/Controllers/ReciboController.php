<?php


namespace App\Http\Controllers;

use App\Models\Recibo;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboController extends Controller
{
    public function download(Recibo $recibo)
    {
        $pdf = Pdf::loadView('recibos.template', [
            'recibo' => $recibo,
            'detalles' => $recibo->detalles
        ]);

        return $pdf->download("recibo-{$recibo->numero_recibo}.pdf");
    }
}