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
            $lastUpdate = Cache::get('last_price_update');
            if (!$lastUpdate || now()->diffInMinutes(Carbon::parse($lastUpdate)) >= 1) {
                $dynamicProducts = Products::whereNotNull('product_type')
                    ->where('product_type', '!=', '')
                    ->where('product_type', '!=', 'none')
                    ->get();

                foreach ($dynamicProducts as $product) {
                    try {
                        $dynamicPrice = $this->calculateDynamicPrice($product->product_type, $product->quantity ?? 1);
                        if ($dynamicPrice !== null) {
                            $product->price = $dynamicPrice;
                            $product->save();
                        }
                    } catch (\Exception $e) {
                        Log::error("Price update error for product {$product->id}: " . $e->getMessage());
                    }
                }

                Cache::put('last_price_update', now()->toDateTimeString(), now()->addMinutes(1));
            }

            $latestProducts = Products::orderBy('created_at', 'desc')->take(12)->get();

            $bestSellerProducts = Products::where('is_best_seller', 1)->orderBy('created_at', 'desc')->take(6)->get();

            $latestProducts->each(function ($product) {
                $product->image = json_decode($product->image, true);
                $product->size = json_decode($product->size, true);
            });

            $bestSellerProducts->each(function ($product) {
                $product->image = json_decode($product->image, true);
                $product->size = json_decode($product->size, true);
            });

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

            $products->through(function ($product) {
                $product->image = json_decode($product->image ?? '[]', true);
                $product->size = json_decode($product->size ?? '[]', true);
                return $product;
            });

            return response()->json($products, 200);
        } catch (\Exception $e) {
            Log::error('Get products error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    private function calculateDynamicPrice($productType, $quantity)
    {
        $apiKey = env('GOLDAPI_KEY', 'goldapi-41ndhsmghqq7ku-io');

        if ($productType === 'silver') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->get("https://www.goldapi.io/api/XAG/USD");
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch silver price');
            }
            $data = $response->json();
            $spotPrice = $data['price'];
            $spotPricePerTenth = $spotPrice / 10; // For 1/10 XAG
            return round($quantity * $spotPricePerTenth);
        } elseif ($productType === 'gold') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->get("https://www.goldapi.io/api/XAU/USD");
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch gold price');
            }
            $data = $response->json();
            $spotPrice = $data['price'];
            $spotPricePerTenth = $spotPrice / 10; // For 1/10 XAU
            return round($quantity * $spotPricePerTenth);
        } elseif (in_array($productType, ['eth', 'bnb', 'pol', 'usdt', 'usdc', 'sol'])) {
            if (in_array($productType, ['usdt', 'usdc'])) {
                return round($quantity * 1); // Stablecoins pegged to 1 USD
            }

            $symbolMap = [
                'eth' => 'ETHUSDT',
                'bnb' => 'BNBUSDT',
                'pol' => 'POLUSDT',
                'sol' => 'SOLUSDT'
            ];
            $symbol = $symbolMap[$productType] ?? '';

            if (empty($symbol)) {
                throw new \Exception('Invalid crypto symbol');
            }

            $response = Http::get("https://api.binance.com/api/v3/ticker/price", [
                'symbol' => $symbol
            ]);
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch crypto price');
            }
            $data = $response->json();
            $spotPrice = (float) $data['price'];
            return round($quantity * $spotPrice);
        }

        return null;
    }

    public function getProductById($id)
    {
        try {
            $product = Products::findOrFail($id);
            $product->image = json_decode($product->image ?? '[]', true);
            $product->size = json_decode($product->size ?? '[]', true);

            $productType = $product->product_type;
            $quantity = $product->quantity ?? 1;

            if ($productType !== '' && $productType !== 'none') {
                try {
                    $dynamicPrice = $this->calculateDynamicPrice($productType, $quantity);
                    if ($dynamicPrice !== null) {
                        $product->price = $dynamicPrice;
                        $product->save();
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
                        'sol' => 'crypto'
                    ];
                    $typeKey = $typeMap[$productType] ?? 'product';
                    Log::error("{$typeKey} price fetch error: " . $e->getMessage());
                }
            }

            return response()->json([
                'message' => 'Product fetched successfully',
                'product' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get product by ID error: ' . $e->getMessage());
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            return response()->json(['error' => 'Failed to fetch product'], 500);
        }
    }
}
