<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DynamicPriceService
{
    /**
     * Tính toán giá động cho sản phẩm dựa trên loại và số lượng
     */
    public function getPrice($productType, $quantity = 1)
    {
        $productType = strtolower(trim($productType));
        Log::info("DynamicPriceService: getPrice for [{$productType}], quantity: {$quantity}");
        if (!$productType || $productType === 'none') {
            return 0;
        }

        $price = null;
        try {
            // 1. Chỉ dùng GoldAPI cho Gold/Silver (XAU, XAG)
            if ($productType === 'gold' || $productType === 'xau' || $productType === 'sjc') {
                $spotPrice = Cache::get('spot_price_xau');
                if (!$spotPrice) {
                    $spotPrice = $this->fetchSpotPrice('XAU');
                    if ($spotPrice) Cache::put('spot_price_xau', $spotPrice, now()->addMinutes(2));
                }
                return $spotPrice ? round($quantity * $spotPrice) : 0;
            }

            if ($productType === 'xag' || $productType === 'silver') {
                $spotPrice = Cache::get('spot_price_xag');
                if (!$spotPrice) {
                    $spotPrice = $this->fetchSpotPrice('XAG');
                    if ($spotPrice) Cache::put('spot_price_xag', $spotPrice, now()->addMinutes(2));
                }
                return $spotPrice ? round($quantity * $spotPrice) : 0;
            }

            // 2. Usdt/Usdc
            if (in_array($productType, ['usdt', 'usdc'])) {
                return round($quantity * 1, 6);
            }

            // 3. Crypto khác dùng Binance
            $spotPrice = Cache::get("spot_price_crypto_{$productType}");
            if (!$spotPrice) {
                $spotPrice = $this->fetchCryptoPrice($productType, 1);
                if ($spotPrice) Cache::put("spot_price_crypto_{$productType}", $spotPrice, now()->addMinutes(2));
            }

            return $spotPrice ? ($quantity * $spotPrice) : 0;
        } catch (\Exception $e) {
            Log::error("DynamicPriceService exception for {$productType}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy giá Spot từ GoldAPI với fallback
     */
    /**
     * Lấy giá Spot từ GoldAPI với fallback
     */
    private function fetchSpotPrice($symbol)
    {
        $apiKeyConfig = env('GOLDAPI_KEY', 'goldapi-bjhc1smldvh897-io');
        $apiKeys = [];
        if (is_string($apiKeyConfig) && str_starts_with($apiKeyConfig, '[') && str_ends_with($apiKeyConfig, ']')) {
            $apiKeys = json_decode($apiKeyConfig, true) ?? [$apiKeyConfig];
        } else {
            $apiKeys = [$apiKeyConfig];
        }

        foreach ($apiKeys as $key) {
            try {
                Log::info("GoldAPI ({$symbol}) attempting with key: " . substr($key, 0, 8) . "...");
                $response = Http::timeout(15)
                    ->withoutVerifying()
                    ->withHeaders(['x-access-token' => $key])
                    ->get("https://www.goldapi.io/api/{$symbol}/USD");

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['price'])) {
                        Log::info("GoldAPI ({$symbol}) spot price success: " . $data['price']);
                        return (float)$data['price'];
                    }
                    Log::warning("GoldAPI ({$symbol}) missing price field in response: " . json_encode($data));
                } else {
                    Log::warning("GoldAPI ({$symbol}) error response (Status " . $response->status() . "): " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("GoldAPI ({$symbol}) exception: " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Lấy giá Crypto từ Binance
     */
    private function fetchCryptoPrice($productType, $quantity)
    {
        $symbolMap = [
            'eth' => 'ETHUSDT',
            'bnb' => 'BNBUSDT',
            'paxg' => 'PAXGUSDT',
            'pol' => 'POLUSDT',
            'sol' => 'SOLUSDT',
            'ondo' => 'ONDOUSDT',
            'ton' => 'TONUSDT',
            'avax' => 'AVAXUSDT',
            'btc' => 'BTCUSDT',
            'xrp' => 'XRPUSDT',
            'trx' => 'TRXUSDT',
            'sui' => 'SUIUSDT',
            'shib' => 'SHIBUSDT',
            'doge' => 'DOGEUSDT',
            'near' => 'NEARUSDT',
            'fil' => 'FILUSDT',
            'etc' => 'ETCUSDT',
            'ena' => 'ENAUSDT',
            'link' => 'LINKUSDT',
            'ada' => 'ADAUSDT',
            'tao' => 'TAOUSDT',
            'arb' => 'ARBUSDT',
            'apt' => 'APTUSDT',
            'aave' => 'AAVEUSDT',
            'ltc' => 'LTCUSDT',
        ];

        $symbol = $symbolMap[$productType] ?? null;
        if (!$symbol) {
            Log::warning("Binance: No symbol mapping for product_type: {$productType}");
            return null;
        }

        try {
            Log::info("Binance: Fetching price for {$symbol}...");
            $response = Http::timeout(10)->get('https://api.binance.com/api/v3/ticker/price', [
                'symbol' => strtoupper($symbol),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['price'])) {
                    Log::info("Binance ({$symbol}) price success: " . $data['price']);
                    return $quantity * (float)$data['price'];
                }
                Log::warning("Binance ({$symbol}) missing price field: " . json_encode($data));
            } else {
                Log::warning("Binance ({$symbol}) error response (Status " . $response->status() . "): " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Binance exception for {$productType}: " . $e->getMessage());
        }

        return null;
    }
}
