<?php

namespace App\Enums;

enum PrestamoEstadoEnum: string
{
    case A = 'A';
    case L = 'L';
    case I = 'I';
    
    public function label(): string
    {
        return match($this) {
            self::A => 'Activos',
            self::L => 'Liquidados',
            self::I => 'Incluidos',
        };
    }
}