<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Routing\Controller as BaseController;

class HomeController extends BaseController
{
    /**
     * Lấy sản phẩm cho trang Home
     */
    public function getHomeProducts(Request $request)
    {
        try {
            $latestProducts = Products::orderBy('created_at', 'desc')->take(12)->get();
            $bestSellerProducts = Products::where('is_best_seller', 1)->orderBy('created_at', 'desc')->take(6)->get();

            $allProducts = $this->preloadAndProcessProducts($latestProducts->merge($bestSellerProducts));

            $latestProducts = $latestProducts->map(fn($p) => $allProducts->firstWhere('id', $p->id));
            $bestSellerProducts = $bestSellerProducts->map(fn($p) => $allProducts->firstWhere('id', $p->id));

            $this->updateAllProductsPrices();

            return response()->json([
                'message' => 'Home products fetched successfully',
                'latest_collection' => $latestProducts,
                'best_sellers' => $bestSellerProducts
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get home products error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    /**
     * Lấy sản phẩm theo filter / paginate
     */
    public function getProducts(Request $request)
    {
        try {
            $query = Products::query();

            if ($request->filled('category')) {
                $categories = explode(',', $request->category);
                $query->whereIn('category', $categories);
            }
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            switch ($request->get('sort', 'relevant')) {
                case 'low-high':
                    $query->orderBy('price', 'asc');
                    break;
                case 'high-low':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);

            $processedProducts = $this->preloadAndProcessProducts(collect($products->items()));
            $products->setCollection($processedProducts);
            $this->updateAllProductsPrices();
            return response()->json($products, 200);
        } catch (\Exception $e) {
            Log::error('Get products error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    /**
     * Lấy 1 sản phẩm theo ID
     */
    public function getProductById($id)
    {
        try {
            $product = Products::findOrFail($id);
            $processedProduct = $this->preloadAndProcessProducts(collect([$product]))->first();

            return response()->json([
                'message' => 'Product fetched successfully',
                'product' => $processedProduct
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get product by ID error: ' . $e->getMessage());
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            return response()->json(['error' => 'Failed to fetch product'], 500);
        }
    }

    /**
     * Cập nhật toàn bộ giá sản phẩm và lưu vào DB nếu thay đổi
     */
    public function updateAllProductsPrices()
    {
        try {
            $fullUpdateCacheKey = 'update_all_prices_last_run';
            $cacheDuration = now()->addMinutes(30);

            if (Cache::has($fullUpdateCacheKey)) {
                Log::info('Skipped updateAllProductsPrices: cache still valid.');
                return [
                    'updated_count' => 0,
                    'total_processed' => 0,
                    'message' => 'Skipped: cache still valid'
                ];
            }

            Cache::put($fullUpdateCacheKey, now(), $cacheDuration);

            $chunkSize = 100;
            $totalUpdated = 0;
            $totalProcessed = 0;

            Products::whereNotNull('product_type')
                ->where('product_type', '!=', '')
                ->where('product_type', '!=', 'none')
                ->chunk($chunkSize, function ($products) use (&$totalUpdated, &$totalProcessed) {
                    $processedProducts = $this->preloadAndProcessProducts($products, true);

                    $chunkUpdated = 0;
                    foreach ($processedProducts as $product) {
                        if ($product->wasRecentlySaved || $product->isDirty('price')) {
                            $product->save();
                            $chunkUpdated++;
                        }
                    }
                    $totalUpdated += $chunkUpdated;
                    $totalProcessed += $products->count();
                });

            Log::info("Updated all dynamic products prices: {$totalUpdated} updated out of {$totalProcessed} processed");

            return [
                'updated_count' => $totalUpdated,
                'total_processed' => $totalProcessed
            ];
        } catch (\Exception $e) {
            Log::error('Update all products prices error: ' . $e->getMessage());
            return [
                'updated_count' => 0,
                'total_processed' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Phiên bản public gọi qua API
     */
    public function updateAllProductsPricesPublic()
    {
        $result = $this->updateAllProductsPrices();
        return response()->json([
            'message' => 'All dynamic products prices updated successfully',
            'data' => $result
        ], 200);
    }

    /**
     * Preload và tính giá sản phẩm
     */
    private function preloadAndProcessProducts($products, $isFullUpdate = false)
    {
        $cachePrefix = $isFullUpdate ? 'update_all_prices' : 'dynamic_price_type';
        $cacheDuration = $isFullUpdate ? now()->addMinutes(30) : now()->addMinutes(1);

        $typeQuantities = $products
            ->filter(fn($p) => $p->product_type && $p->product_type !== 'none')
            ->map(fn($p) => "{$p->product_type}_" . ($p->quantity ?? 1))
            ->unique()
            ->values();

        foreach ($typeQuantities as $typeQuantity) {
            [$productType, $quantity] = explode('_', $typeQuantity, 2);
            $quantity = (int) $quantity;

            $cacheKey = "{$cachePrefix}_{$productType}_{$quantity}";
            $cachedPrice = Cache::get($cacheKey);

            if ($cachedPrice !== null) continue;

            try {
                $dynamicPrice = $this->calculateDynamicPriceWithTimeout($productType, $quantity);
                if ($dynamicPrice !== null) {
                    Cache::put($cacheKey, $dynamicPrice, $cacheDuration);
                }
            } catch (\Exception $e) {
                Log::error("Price fetch error for {$productType}: {$e->getMessage()}");
                Cache::put($cacheKey, 0, $cacheDuration);
            }
        }

        $products->each(fn($product) => $this->updateProductPriceOnFly($product, $cachePrefix));

        // Decode image & size
        $products->each(fn($p) => $p->image = json_decode($p->image ?? '[]', true));
        $products->each(fn($p) => $p->size = json_decode($p->size ?? '[]', true));

        return $products;
    }

    /**
     * Cập nhật giá hiển thị theo cache
     */
    private function updateProductPriceOnFly($product, $cachePrefix)
    {
        $productType = $product->product_type;
        $quantity = $product->quantity ?? 1;

        if ($productType && $productType !== 'none') {
            $cacheKey = "{$cachePrefix}_{$productType}_{$quantity}";
            $cachedPrice = Cache::get($cacheKey);
            if ($cachedPrice !== null) {
                if ($product->price != $cachedPrice) {
                    $product->price = $cachedPrice;
                    Log::info("Updated product ID {$product->id} with new price: {$cachedPrice}");
                }
            } else {
                Log::warning("No cached price for type {$productType}, using fallback");
                $product->price = $product->price ?? 0;
            }
        }
    }

    /**
     * Wrapper gọi hàm tính giá
     */
    private function calculateDynamicPriceWithTimeout($productType, $quantity)
    {
        return $this->calculateDynamicPrice($productType, $quantity);
    }

    /**
     * Hàm tính giá động theo loại sản phẩm
     */
    private function calculateDynamicPrice($productType, $quantity)
    {
        $apiKey = env('GOLDAPI_KEY');

        if ($productType === 'silver') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->timeout(5)->get("https://www.goldapi.io/api/XAG/USD");
            $price = $response->successful() ? $response->json()['price'] / 10 : null;
            return $price ? round($price * $quantity) : null;
        }

        if ($productType === 'gold' || $productType === 'paxg') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->timeout(5)->get("https://www.goldapi.io/api/XAU/USD");
            $price = $response->successful() ? $response->json()['price'] / 10 : null;
            return $price ? round($price * $quantity) : null;
        }

        $symbolMap = [
            'eth' => 'ETHUSDT',
            'bnb' => 'BNBUSDT',
            'sol' => 'SOLUSDT',
            'pol' => 'POLUSDT',
            'btc' => 'BTCUSDT',
            'xrp' => 'XRPUSDT',
            'trx' => 'TRXUSDT',
            'sui' => 'SUIUSDT',
            'shib' => 'SHIBUSDT',
            'near' => 'NEARUSDT',
            'fil' => 'FILUSDT',
            'etc' => 'ETCUSDT',
            'ena' => 'ENAUSDT',
            'ondo' => 'ONDOUSDT',
            'link' => 'LINKUSDT',
            'ada' => 'ADAUSDT',
            'tao' => 'TAOUSDT',
            'arb' => 'ARBUSDT',
            'apt' => 'APTUSDT',
            'aave' => 'AAVEUSDT',
            'ltc' => 'LTCUSDT',
            'usdt' => 'USDT',
            'usdc' => 'USDC'
        ];

        if (in_array($productType, ['usdt', 'usdc'])) return $quantity * 1;

        if (!isset($symbolMap[$productType])) return null;

        $response = Http::timeout(5)->get("https://api.binance.com/api/v3/ticker/price", ['symbol' => $symbolMap[$productType]]);
        $spotPrice = $response->successful() ? (float)$response->json()['price'] : null;

        return $spotPrice ? $quantity * $spotPrice : null;
    }
}