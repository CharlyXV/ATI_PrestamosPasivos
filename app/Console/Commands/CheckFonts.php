<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\PDF;

class CheckFonts extends Command
{
    protected $signature = 'check:fonts';
    protected $description = 'Verificar fuentes disponibles en DomPDF';

    public function handle()
    {
        $fontMetrics = app('dompdf')->getFontMetrics();
        $font = $fontMetrics->getFont('dejavu');
        
        if ($font) {
            $this->info('✅ Fuente "dejavu" cargada correctamente');
            $this->line("Detalles: " . print_r($font, true));
        } else {
            $this->error('❌ La fuente "dejavu" no se encontró');
        }
    }
}
