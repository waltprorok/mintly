<?php

namespace App\Services;

use Illuminate\Support\Str;

class MerchantNormalizer
{
    public static function normalize(?string $merchant): string
    {
        $merchant = Str::lower(trim($merchant ?? ''));

        // Remove variable transaction identifiers after an asterisk.
        $merchant = preg_replace('/\*[a-z0-9]+$/i', '', $merchant);

        // Normalize whitespace.
        return trim(preg_replace('/\s+/', ' ', $merchant));
    }

    public static function display(?string $merchant): string
    {
        $merchant = Str::of($merchant ?? '')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->replaceMatches('/\s+\d{1,2}\/\d{1,2}$/', '')
            ->replaceMatches('/\b\d{3}[- ]?\d{3}[- ]?\d{4}\b/', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim();

        return Str::of($merchant)
            ->words(3, '')
            ->title()
            ->toString();
    }
}
