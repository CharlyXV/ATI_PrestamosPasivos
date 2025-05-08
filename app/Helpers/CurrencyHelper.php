<?php

namespace App\Helpers;

class CurrencyHelper
{
    // Tasas de cambio de ejemplo (deberías actualizarlas periódicamente o usar una API)
    protected static array $rates = [
        'USD' => 1.0,
        'CRC' => 0.0018, // 1 CRC = 0.0018 USD (ejemplo)
        'EUR' => 1.07,   // 1 EUR = 1.07 USD (ejemplo)
    ];

    public static function convertToBaseCurrency(float $amount, string $fromCurrency): float
    {
        $baseCurrency = 'USD'; // Convertimos todo a USD como base
        $rate = self::$rates[$fromCurrency] ?? 1.0;
        return $amount * $rate;
    }

    public static function formatCurrency(float $amount, string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'CRC' => '₡',
            'EUR' => '€',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . ' ' . number_format($amount, 2);
    }
}