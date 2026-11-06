<?php

namespace App\Services;

class SkuGenerator
{
    public function isValidFormat(string $sku): bool
    {
        return preg_match('/^[A-Z0-9]{7,12}$/', $sku) === 1;
    }

    private const CATEGORY_CODES = [
        'FOOD' => 'FO', 'MEDICAL' => 'ME', 'RESCUE' => 'RE', 'RELIEF' => 'RL',
        'HYGIENE' => 'HY', 'SHELTER' => 'SH', 'COMMUNICATIONS' => 'CO',
    ];

    public function generate(
        string $category,
        string $name,
        ?string $size,
        ?string $variant,
        ?string $beneficiary,
        string $type,
        string $unitType = 'PCS',
        iterable $usedSkus = [],
    ): string {
        $categoryKey = strtoupper(trim($category));
        $categoryCode = self::CATEGORY_CODES[$categoryKey]
            ?? substr(preg_replace('/[^A-Z0-9]/', '', $categoryKey) ?: 'GN', 0, 2);
        $itemCode = $this->nameCode($name, 4);
        $unitCode = $this->unitCode($unitType);
        $used = array_fill_keys(array_map('strtoupper', is_array($usedSkus) ? $usedSkus : iterator_to_array($usedSkus)), true);
        $base = $categoryCode.$itemCode.$unitCode;
        $next = 1;
        foreach ($used as $existingSku => $_) {
            if (preg_match('/^'.preg_quote($base, '/').'([0-9]{2,})$/', $existingSku, $matches)) {
                $next = max($next, (int) $matches[1] + 1);
            }
        }

        $suffix = str_pad((string) $next, 2, '0', STR_PAD_LEFT);
        // Keep the complete SKU within 12 characters as the sequence grows.
        $allowedBaseLength = 12 - strlen($suffix);
        if (strlen($base) > $allowedBaseLength) {
            $base = substr($categoryCode, 0, min(strlen($categoryCode), 2))
                .substr($itemCode, 0, max(1, $allowedBaseLength - min(strlen($categoryCode), 2) - strlen($unitCode)))
                .substr($unitCode, 0, max(1, $allowedBaseLength - min(strlen($categoryCode), 2) - max(1, $allowedBaseLength - min(strlen($categoryCode), 2) - strlen($unitCode))));
        }

        return $base.$suffix;
    }

    private function nameCode(string $name, int $length): string
    {
        $words = preg_split('/[^A-Z0-9]+/i', strtoupper(trim($name)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_values(array_filter(
            $words,
            static fn (string $word): bool => ! preg_match('/^\d+(ML|L|KG|G|CM|M|PCS|CT)$/', $word)
        ));

        if (count($words) > 1) {
            return substr($words[0].($words[1] ?? ''), 0, $length);
        }

        return substr($words[0] ?? 'ITEM', 0, $length);
    }

    private function unitCode(string $unit): string
    {
        $unit = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($unit)) ?: 'PC');
        $codes = ['BOX' => 'BX', 'PACK' => 'PK', 'PCS' => 'PC', 'PIECE' => 'PC', 'SACK' => 'SK', 'BAG' => 'BG', 'BOTTLE' => 'BT', 'GALLON' => 'GL'];

        return $codes[$unit] ?? substr($unit, 0, 2);
    }
}
