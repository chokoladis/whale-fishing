<?php

namespace App\Tool\Alchemy;

use App\DTO\Http\Response\TransactionDTO;
use App\Helper\StrHelper;

class TransactionParser
{

    public static function parse(mixed $data) : ?TransactionDTO
    {
        $input = $data['input'] ?? '';

        if (str_starts_with($input, '0xa9059cbb')) {
            $cleanInput = substr($input, 10);

            $toParam = substr($cleanInput, 0, 64);

            return new TransactionDTO(
                $data['blockNumber'],
                $data['hash'],
                $data['from'],
                '0x' . substr($toParam, 24),
                $data['to'],
                StrHelper::bchexdec(substr($cleanInput, 64, 64))
            );
        }

        return null;
    }
}
