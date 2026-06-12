<?php

namespace App\Helper;

class StrHelper
{
    static function bchexdec(string $hex): string
    {
        $dec = '0';
        $len = strlen($hex);

        // Если строка начинается с 0x, убираем этот префикс
        if (str_starts_with($hex, '0x')) {
            $hex = substr($hex, 2);
            $len -= 2;
        }

        for ($i = 0; $i < $len; $i++) {
            $hexDigit = hexdec($hex[$i]);
            $dec = bcadd(bcmul($dec, '16'), (string)$hexDigit);
        }

        return $dec;
    }
}
