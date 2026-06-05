<?php

namespace App\Tool\Alchemy;

use App\DTO\Http\Response\TransactionDTO;
use App\Helper\StrHelper;

class TransactionParser
{
    // Сигнатуры методов ERC-20
    private const METHOD_TRANSFER = 'a9059cbb';

    private const METHOD_APPROVE = '095ea7b3';
    private const METHOD_TRANSFER_FROM = '23b872dd';
    private const TOKEN_DECIMALS = [
        '0xdac17f958d2ee523a2206206994597c13d831ec7' => 6,  // USDT
        '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48' => 6,  // USDC
        '0x2260fac5e5542a773aa44fbcfedf7c193bc2c599' => 8,  // WBTC
    ];

    public static function parse(array $data) : ?TransactionDTO
    {
        $input = $data['input'] ?? '';

        if (str_starts_with($input, '0xa9059cbb')) {
            $cleanInput = substr($input, 10);

            $toParam = substr($cleanInput, 0, 64);

            return new TransactionDTO(
                (string)hexdec($data['blockNumber']),
                $data['hash'],
                $data['from'],
                '0x' . substr($toParam, 24),
                $data['to'],
                StrHelper::bchexdec(substr($cleanInput, 64, 64))
            );
        }

        return null;
    }

//    public static function parse(array $tx): array // old variant with pending data
//    {
//        $gasLimit      = hexdec($tx['gas'] ?? '0x0');
//        $gasPrice      = self::hexToDecimal($tx['gasPrice'] ?? '0x0');
//        $feeWeiMax     = bcmul((string)$gasLimit, $gasPrice);
//        $feeEthMax     = bcdiv($feeWeiMax, bcpow('10', '18'), 18);
//        $contractAddr  = strtolower($tx['to'] ?? '');
//        $input         = $tx['input'] ?? '0x';
//
//        return [
//            'hash'           => $tx['hash'],
//            'from'           => $tx['from'],
//            'to'             => $tx['to'],
//            'value_eth'      => self::hexToEth($tx['value'] ?? '0x0'),
//            'value_wei'      => self::hexToDecimal($tx['value'] ?? '0x0'),
//            'gas_limit'      => $gasLimit,
//            'gas_price'      => $gasPrice,
//            'fee_eth_max'    => $feeEthMax,
//            'fee_wei_max'    => $feeWeiMax,
//            'nonce'          => hexdec($tx['nonce'] ?? '0x0'),
//            'type'           => self::detectType($tx),
//            'method'         => self::detectMethod($input),
//            'token_transfer' => self::parseInput($input, $contractAddr),
//        ];
//    }

    private static function detectType(array $tx): string
    {
        $input = $tx['input'] ?? '0x';

        if (strtolower($tx['from']) === strtolower($tx['to'] ?? '')) {
            return 'self';
        }

        // Нет input данных — чистый перевод ETH
        if ($input === '0x' || $input === '') {
            return 'eth_transfer';
        }

        // Есть input — вызов контракта
        return 'contract_call';
    }

    private static function detectMethod(string $input): ?string
    {
        if (strlen($input) < 10) return null;

        $signature = substr($input, 2, 8); // убираем 0x, берём 4 байта

        return match ($signature) {
            self::METHOD_TRANSFER => 'transfer',
            self::METHOD_APPROVE => 'approve',
            self::METHOD_TRANSFER_FROM => 'transferFrom',
            '38ed1739'                 => 'swapExactTokensForTokens',
            '7ff36ab5'                 => 'swapExactETHForTokens',
            '18cbafe5'                 => 'swapExactTokensForETH',
            '5ae401dc', 'ac9650d8'    => 'multicall',
            default => $signature,
        };
    }

    private static function hexToEth(string $hex): string
    {
        // wei → ETH (делим на 10^18)
        if ($hex === '0x0' || $hex === '0x' || empty($hex)) return '0';

        $wei = gmp_strval(gmp_init(ltrim($hex, '0x'), 16));

        return bcdiv($wei, bcpow('10', '18'), 18);
    }

    private static function parseTokenTransfer(string $input, string $contractAddress): ?array
    {
        if (strlen($input) < 10) return null;

        $signature = substr($input, 2, 8);
        if ($signature !== self::METHOD_TRANSFER) return null;

        $data = substr($input, 10);
        if (strlen($data) < 128) return null;

        $amountRaw = self::hexToDecimal(substr($data, 64, 64));
        $decimals  = self::TOKEN_DECIMALS[$contractAddress] ?? 18;

        return [
            'to'           => '0x' . substr($data, 24, 40),
            'amount_raw'   => $amountRaw,
            'amount_human' => bcdiv($amountRaw, bcpow('10', (string)$decimals), $decimals),
            'decimals'     => $decimals,
        ];
    }

    // Универсальный hex → decimal без потери точности
    private static function hexToDecimal(string $hex): string
    {
        $hex = ltrim($hex, '0x');
        if (empty($hex)) return '0';
        return gmp_strval(gmp_init($hex, 16));
    }

    private static function parseTokenTransferFrom(string $input, string $contractAddress): ?array
    {
        if (strlen($input) < 10) return null;

        $signature = substr($input, 2, 8);
        if ($signature !== self::METHOD_TRANSFER_FROM) return null;

        $data = substr($input, 10);
        if (strlen($data) < 192) return null; // 3 аргумента по 64 символа

        $amountRaw = self::hexToDecimal(substr($data, 128, 64));
        $decimals  = self::TOKEN_DECIMALS[$contractAddress] ?? 18;

        return [
            'from'   => '0x' . substr($data, 24, 40),
            'to'     => '0x' . substr($data, 88, 40),
            'amount_raw'   => $amountRaw,
            'amount_human' => bcdiv($amountRaw, bcpow('10', (string)$decimals), $decimals),
            'decimals'     => $decimals,
        ];
    }

    private static function parseInput(string $input, string $contractAddress): ?array
    {
        if (strlen($input) < 10) return null;

        $signature = substr($input, 2, 8);

        return match($signature) {
            self::METHOD_TRANSFER      => self::parseTokenTransfer($input, $contractAddress),
            self::METHOD_TRANSFER_FROM => self::parseTokenTransferFrom($input, $contractAddress),
            default                    => null,
        };
    }
}
