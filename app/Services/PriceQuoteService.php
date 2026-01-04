<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PriceQuoteService
{
  /**
   * Returns the spot price in USDT (close enough to USD for stablecoins).
   * Example: symbol=BNB => fetch BNBUSDT.
   */
  public function getSpotUsdt(string $symbol): string
  {
    $symbol = strtoupper(trim($symbol));
    if ($symbol === 'USDT' || $symbol === 'USDC') {
      return '1';
    }

    $pair = $symbol . 'USDT';

    $resp = Http::get('https://api.binance.com/api/v3/ticker/price', [
      'symbol' => $pair,
    ]);

    if (! $resp->successful()) {
      throw new \RuntimeException('Failed to fetch price for ' . $pair);
    }

    $data = $resp->json();
    $price = (string) ($data['price'] ?? '');

    if ($price === '' || ! is_numeric($price)) {
      throw new \RuntimeException('Invalid price response for ' . $pair);
    }

    return $price;
  }

  /**
   * Converts a USD amount into a token amount by dividing by spot price.
   * Uses bcmath when available.
   */
  public function usdToTokenAmount(string $totalUsd, string $tokenSpotUsd, int $scale = 18): string
  {
    $totalUsd = trim($totalUsd);
    $tokenSpotUsd = trim($tokenSpotUsd);

    if ($totalUsd === '' || ! is_numeric($totalUsd)) {
      throw new \InvalidArgumentException('Invalid totalUsd');
    }
    if ($tokenSpotUsd === '' || ! is_numeric($tokenSpotUsd) || (float) $tokenSpotUsd <= 0) {
      throw new \InvalidArgumentException('Invalid tokenSpotUsd');
    }

    if (function_exists('bcdiv')) {
      return bcdiv($totalUsd, $tokenSpotUsd, $scale);
    }

    // Fallback (less precise): keep 18 decimals
    $val = (float) $totalUsd / (float) $tokenSpotUsd;
    return number_format($val, $scale, '.', '');
  }
}
