<?php

namespace App\Support;

class Phone
{
    /**
     * Normalizes an Egyptian mobile number to 01XXXXXXXXX (accepts Arabic-Indic digits, spaces, dashes,
     * +20 / 0020 prefixes). Returns null when it is not a valid Egyptian mobile number.
     */
    public static function normalizeEgyptianMobile(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $digits = strtr($input, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $digits = (string) preg_replace('/\D+/', '', $digits);

        if (str_starts_with($digits, '0020')) {
            $digits = '0'.substr($digits, 4);
        } elseif (str_starts_with($digits, '20') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '0'.$digits;
        }

        return preg_match('/^01[0125]\d{8}$/', $digits) ? $digits : null;
    }
}
