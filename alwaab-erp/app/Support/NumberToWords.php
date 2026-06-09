<?php

namespace App\Support;

class NumberToWords
{
    private const ONES = [
        '', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE',
        'TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN',
        'SEVENTEEN', 'EIGHTEEN', 'NINETEEN',
    ];

    private const TENS = [
        '', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY',
    ];

    public static function aed(float $amount): string
    {
        $dirhams = (int) floor($amount);
        $fils = (int) round(($amount - $dirhams) * 100);

        $words = self::convert($dirhams).' DIRHAM';
        if ($dirhams !== 1) {
            $words .= 'S';
        }

        if ($fils > 0) {
            $words .= ' AND '.self::convert($fils).' FIL';
            if ($fils !== 1) {
                $words .= 'S';
            }
        }

        return $words.' ONLY';
    }

    private static function convert(int $n): string
    {
        if ($n === 0) {
            return 'ZERO';
        }

        $parts = [];

        if ($n >= 1_000_000) {
            $parts[] = self::convert((int) floor($n / 1_000_000)).' MILLION';
            $n %= 1_000_000;
        }
        if ($n >= 1_000) {
            $parts[] = self::convertHundreds((int) floor($n / 1_000)).' THOUSAND';
            $n %= 1_000;
        }
        if ($n >= 100) {
            $parts[] = self::convertHundreds($n);
        } elseif ($n > 0) {
            $parts[] = self::convertHundreds($n);
        }

        return implode(' ', array_filter($parts));
    }

    private static function convertHundreds(int $n): string
    {
        $parts = [];

        if ($n >= 100) {
            $parts[] = self::ONES[(int) floor($n / 100)].' HUNDRED';
            $n %= 100;
        }

        if ($n >= 20) {
            $parts[] = self::TENS[(int) floor($n / 10)];
            $n %= 10;
        }

        if ($n > 0) {
            $parts[] = self::ONES[$n];
        }

        return implode(' ', array_filter($parts));
    }
}
