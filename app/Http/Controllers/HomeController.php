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

    public function getProductById($id)
    {
        try {
            $product = Products::findOrFail($id);
            $product->image = json_decode($product->image ?? '[]', true);
            $product->size = json_decode($product->size ?? '[]', true);

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
