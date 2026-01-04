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

  /**
   * Converts a decimal token amount into integer units (wei-like) based on decimals.
   * Example: amountDecimal="0.1", decimals=18 => "100000000000000000".
   */
  public function decimalToUnits(string $amountDecimal, int $decimals): string
  {
    $amountDecimal = trim($amountDecimal);
    if ($amountDecimal === '' || ! is_numeric($amountDecimal)) {
      throw new \InvalidArgumentException('Invalid amountDecimal');
    }
    if ($decimals < 0 || $decimals > 36) {
      throw new \InvalidArgumentException('Invalid decimals');
    }

    $multiplier = '1' . str_repeat('0', $decimals);

    if (function_exists('bcmul')) {
      // 0 scale => integer string
      return bcmul($amountDecimal, $multiplier, 0);
    }

    // Fallback (less precise)
    $val = (float) $amountDecimal * (10 ** $decimals);
    return sprintf('%.0f', $val);
  }
}
