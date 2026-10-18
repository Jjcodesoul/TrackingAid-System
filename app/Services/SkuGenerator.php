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
            ?? substr(preg_replace('/[^A-Z0-9]/', '', $categoryKey) ?: 'GEN', 0, 3);
        $typeCode = in_array(strtoupper($type), ['RETURNABLE', 'REUSABLE'], true) ? 'R' : 'C';

        return $categoryCode
            .$this->nameCode($name)
            .$this->attributeCode($size, 5, 'STD')
            .$typeCode
            .$this->attributeCode($variant, 2, 'ST')
            .'01';
    }

    private function nameCode(string $name): string
    {
        $words = preg_split('/[^A-Z0-9]+/i', strtoupper(trim($name)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_values(array_filter(
            $words,
            static fn (string $word): bool => ! preg_match('/^\d+(ML|L|KG|G|CM|M|PCS|CT)$/', $word)
        ));

        if (count($words) > 1) {
            return substr($words[0], 0, 3).substr($words[1], 0, 2);
        }

        return substr($words[0] ?? 'ITEM', 0, 4);
    }

    private function attributeCode(?string $value, int $length, string $default): string
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '' || $value === 'NONE') {
            return $default;
        }

        $value = preg_replace('/[^A-Z0-9]/', '', $value) ?: $default;

        return substr($value, 0, $length);
    }
}
