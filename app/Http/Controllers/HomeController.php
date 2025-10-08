<?php

namespace App\Http\Controllers;

use App\Models\Bags;
use App\Models\EveryDays;
use App\Models\Items;
use App\Models\LoginHistory;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use App\Traits\Sharable;
use Tymon\JWTAuth\Facades\JWTAuth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HomeController extends BaseController
{
    /**
     * Create a new RegisterController instance.
     *
     * @return void
     */

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    use Sharable;

    public function getHomeProducts(Request $request)
    {
        try {
            $latestProducts = Products::orderBy('created_at', 'desc')->take(12)->get();
            $bestSellerProducts = Products::where('is_best_seller', 1)->orderBy('created_at', 'desc')->take(6)->get();

            $allProducts = $this->preloadAndProcessProducts($latestProducts->merge($bestSellerProducts));

            $latestProducts = $latestProducts->map(function ($product) use ($allProducts) {
                return $allProducts->firstWhere('id', $product->id);
            });
            $bestSellerProducts = $bestSellerProducts->map(function ($product) use ($allProducts) {
                return $allProducts->firstWhere('id', $product->id);
            });

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

    public function getProducts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:50',
                'sort' => 'in:relevant,low-high,high-low',
                'category' => 'string',
                'min_price' => 'numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

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

    private function updateAllProductsPrices()
    {
        try {
            $chunkSize = 100;
            $totalUpdated = 0;
            $totalProcessed = 0;

            Products::whereNotNull('product_type')
                ->where('product_type', '!=', '')
                ->where('product_type', '!=', 'none')
                ->chunk($chunkSize, function ($products) use (&$totalUpdated, &$totalProcessed) {
                    $processedProducts = $this->preloadAndProcessProducts($products);
                    $chunkUpdated = 0;
                    foreach ($processedProducts as $product) {
                        if ($product->wasRecentlySaved || $product->isDirty('price')) {
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

    public function updateAllProductsPricesPublic()
    {
        $result = $this->updateAllProductsPrices();
        return response()->json([
            'message' => 'All dynamic products prices updated successfully',
            'data' => $result
        ], 200);
    }

    private function preloadAndProcessProducts($products)
    {

        $cacheKey = 'last_full_price_update';
        $lastUpdate = Cache::get($cacheKey);
        $oneHourAgo = now()->subHour();

        if (!$lastUpdate || Carbon::parse($lastUpdate)->lt($oneHourAgo)) {
            Log::info('Triggering full price update due to cache expiration (1 hour)');
            Cache::put($cacheKey, now()->toDateTimeString(), now()->addHours(1));
        }

        $typeQuantities = $products
            ->filter(function ($product) {
                return $product->product_type !== '' && $product->product_type !== 'none';
            })
            ->map(function ($product) {
                $quantity = $product->quantity ?? 1;
                return "{$product->product_type}_{$quantity}";
            })
            ->unique()
            ->values();

        foreach ($typeQuantities as $typeQuantity) {
            [$productType, $quantity] = explode('_', $typeQuantity, 2);
            $quantity = (int) $quantity;

            $cacheKey = "dynamic_price_type_{$productType}_{$quantity}";
            $cachedPrice = Cache::get($cacheKey);

            if ($cachedPrice !== null) {
                continue;
            }

            try {
                $dynamicPrice = $this->calculateDynamicPriceWithTimeout($productType, $quantity);
                if ($dynamicPrice !== null) {
                    Cache::put($cacheKey, $dynamicPrice, now()->addMinutes(1));
                }
            } catch (\Exception $e) {
                $typeMap = [
                    'silver' => 'silver',
                    'gold' => 'gold',
                    'eth' => 'crypto',
                    'bnb' => 'crypto',
                    'pol' => 'crypto',
                    'usdt' => 'crypto',
                    'usdc' => 'crypto',
                    'paxg' => 'crypto',
                    'sol' => 'crypto'
                ];
                $typeKey = $typeMap[$productType] ?? 'product';
                Log::error("{$typeKey} price fetch error for type {$productType}: " . $e->getMessage());
                Cache::put($cacheKey, 0, now()->addMinutes(1));
            }
        }

        $products->each(function ($product) {
            $this->updateProductPriceOnFly($product);
            $product->image = json_decode($product->image ?? '[]', true);
            $product->size = json_decode($product->size ?? '[]', true);
        });

        return $products;
    }

    private function updateProductPriceOnFly($product)
    {
        $productType = $product->product_type;
        $quantity = $product->quantity ?? 1;

        if ($productType !== '' && $productType !== 'none') {
            $cacheKey = "dynamic_price_type_{$productType}_{$quantity}";
            $cachedPrice = Cache::get($cacheKey);

            if ($cachedPrice !== null) {
                if ($product->price != $cachedPrice) {
                    $product->price = $cachedPrice;
                    $product->save();
                    Log::info("Updated product ID {$product->id} with new price: {$cachedPrice}");
                }
                return;
            }

            Log::warning("No cached price for type {$productType}, using fallback");
            $product->price = $product->price ?? 0;
        }
    }

    private function calculateDynamicPriceWithTimeout($productType, $quantity)
    {
        return $this->calculateDynamicPrice($productType, $quantity);
    }

    private function calculateDynamicPrice($productType, $quantity)
    {
        $apiKey = env('GOLDAPI_KEY', 'goldapi-41ndhsmghqq7ku-io');

        if ($productType === 'silver') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->timeout(5)->get("https://www.goldapi.io/api/XAG/USD");
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch silver price');
            }
            $data = $response->json();
            $spotPrice = $data['price'];
            $spotPricePerTenth = $spotPrice / 10; // For 1/10 XAG
            return round($quantity * $spotPricePerTenth);
        } elseif ($productType === 'gold') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->timeout(5)->get("https://www.goldapi.io/api/XAU/USD");
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch gold price');
            }
            $data = $response->json();
            $spotPrice = $data['price'];
            $spotPricePerTenth = $spotPrice / 10; // For 1/10 XAU
            return round($quantity * $spotPricePerTenth);
        } elseif (in_array($productType, ['eth', 'bnb', 'pol', 'sol', 'paxg', 'usdt', 'usdc'])) {
            if (in_array($productType, ['usdt', 'usdc'])) {
                return round($quantity * 1); // Stablecoins pegged to 1 USD
            }

            $symbolMap = [
                'eth' => 'ETHUSDT',
                'bnb' => 'BNBUSDT',
                'paxg' => 'PAXGUSDT',
                'pol' => 'POLUSDT',
                'sol' => 'SOLUSDT'
            ];
            $symbol = $symbolMap[$productType] ?? '';

            if (empty($symbol)) {
                throw new \Exception('Invalid crypto symbol');
            }

            $response = Http::timeout(5)->get("https://api.binance.com/api/v3/ticker/price", [
                'symbol' => $symbol
            ]);
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch crypto price');
            }
            $data = $response->json();
            $spotPrice = (float) $data['price'];
            return $quantity * $spotPrice;
        }

        return null;
    }
}
