<?php

namespace App\Services;

class SkuGenerator
{
    private const CATEGORY_CODES = [
        'FOOD' => 'FOO',
        'MEDICAL' => 'MED',
        'RESCUE' => 'RES',
        'RELIEF' => 'REL',
        'HYGIENE' => 'HYG',
        'SHELTER' => 'SHE',
        'COMMUNICATIONS' => 'COM',
    ];

    public function generate(
        string $category,
        string $name,
        ?string $size,
        ?string $variant,
        ?string $beneficiary,
        string $type,
    ): string {
        $categoryKey = strtoupper(trim($category));
        $categoryCode = self::CATEGORY_CODES[$categoryKey]
            ?? strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $categoryKey) ?: 'GEN', 0, 3));

        // Retain a stable, compact identifier. The metadata-derived suffix keeps
        // identical items together while distinguishing size, variant and type.
        $identity = implode('|', array_map(
            static fn (?string $value): string => strtoupper(trim((string) $value)),
            [$categoryKey, $name, $size, $variant, $beneficiary, $type]
        ));

        return $categoryCode.'-'.strtoupper(substr(hash('sha256', $identity), 0, 8));
    }
}
