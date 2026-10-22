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
}
