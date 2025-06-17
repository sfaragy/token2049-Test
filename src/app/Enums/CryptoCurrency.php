<?php

namespace App\Enums;

enum CryptoCurrency: string
{
    case BITCOIN = 'BTC';
    case ETHEREUM = 'ETH';
    case USD_COIN = 'USDC';
    case TETHER = 'USDT';
    case LITECOIN = 'LTC';
    case BITCOIN_CASH = 'BCH';
    case DOGECOIN = 'DOGE';
    case DAI = 'DAI';
    case POLYGON = 'MATIC';
    case APECOIN = 'APE';
    case SHIBA_INU = 'SHIB';
    case SOLANA = 'SOL';

     public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
