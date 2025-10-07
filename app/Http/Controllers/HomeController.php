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
            // LATEST COLLECTION: Sản phẩm mới nhất, sort created_at DESC, limit 12 (tùy chỉnh)
            $latestProducts = Products::orderBy('created_at', 'desc')->take(12)->get();

            // BEST SELLERS: Filter is_best_seller = 1, sort created_at DESC, limit 6
            $bestSellerProducts = Products::where('is_best_seller', 1)->orderBy('created_at', 'desc')->take(6)->get();

            // Decode JSON fields để return array (nhưng relative URL giữ nguyên)
            $latestProducts->each(function ($product) {
                $product->image = json_decode($product->image, true);  // Array relative paths
                $product->size = json_decode($product->size, true);   // Array sizes
            });

            $bestSellerProducts->each(function ($product) {
                $product->image = json_decode($product->image, true);
                $product->size = json_decode($product->size, true);
            });

            return response()->json([
                'message' => 'Home products fetched successfully',
                'latest_collection' => $latestProducts,  // Array products mới nhất
                'best_sellers' => $bestSellerProducts   // Array best sellers
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get home products error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }
}
