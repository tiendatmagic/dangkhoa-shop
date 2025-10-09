<?php

namespace App\Http\Controllers;

use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use App\Models\TwoFactorUsers;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\Sharable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Login;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;
use SWeb3\SWeb3;
use SWeb3\SWeb3_Contract;
use SWeb3\Utils;

class AdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Sharable;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh']]);
    }

    public function getAllOrder(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $orders = Orders::orderBy('created_at', 'desc')
            ->skip(0)
            ->take($perPage)
            ->get();

        foreach ($orders as $order) {
            $orderItems = OrderItems::where('order_id', $order->id)
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->select('order_items.*', 'products.name', 'products.image', 'products.category', 'products.is_best_seller')
                ->get();

            foreach ($orderItems as $item) {
                $item->image = json_decode($item->image);
            }

            $total = $orderItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });

            $order->items = $orderItems;
            $order->total = $total;
        }

        $getOrders = [
            'data' => $orders,
            'total' => Orders::count(),
            'page' => $page,
            'per_page' => $perPage,
        ];
        return response()->json($getOrders);
    }

    public function updateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,processing,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $order = Orders::find($request->input('id'));
        $order->status = $request->input('status');
        $order->save();
        return response()->json(['message' => 'Order status updated successfully', 'order' => $order]);
    }

    public function getAllProduct(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $products = Products::orderBy('created_at', 'desc')
            ->skip(0)
            ->take($perPage)
            ->get();


        foreach ($products as $product) {
            $product->image = json_decode($product->image);
            $product->size = json_decode($product->size);
        }

        $getProducts = [
            'data' => $products,
            'total' => Products::count(),
            'page' => $page,
            'per_page' => $perPage,
        ];
        return response()->json($getProducts);
    }

    public function getProductDetail(Request $request)
    {
        $id = $request->input('id');
        $product = Products::find($id);
        $product->image = json_decode($product->image);
        $product->size = json_decode($product->size);
        return response()->json($product);
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

            $response = Http::get("https://api.binance.com/api/v3/ticker/price", [
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

    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string|max:100',
            'product_type' => 'nullable|string|max:100',
            'quantity' => 'nullable|numeric|min:0',
            'is_best_seller' => 'required|in:0,1',
            'size' => 'nullable|array',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $product = Products::find($request->id);
        $product->name = $request->name;
        $product->category = $request->category ?? $product->category;
        $product->product_type = $request->product_type ?? $product->product_type;
        $product->quantity = $request->quantity ?? $product->quantity;
        $product->is_best_seller = $request->is_best_seller;
        $product->size = json_encode($request->size ?? json_decode($product->size, true));

        $price = $request->price ?? $product->price;
        $productType = $request->product_type ?? $product->product_type;
        $quantity = $request->quantity ?? $product->quantity;

        if ($productType !== '' && $productType !== 'none') {
            try {
                $dynamicPrice = $this->calculateDynamicPrice($productType, $quantity);
                if ($dynamicPrice !== null) {
                    $price = $dynamicPrice;
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
                Log::error("{$typeKey} price fetch error: " . $e->getMessage());
                return response()->json(['error' => "Failed to fetch realtime {$typeKey} price"], 500);
            }
        }

        $product->price = $price;
        if ($request->image) {
            $product->image = json_encode([$request->image]);
        }
        $product->save();

        $product->image = json_decode($product->image);
        $product->size = json_decode($product->size);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '_' . time() . '.' . $extension;

            $path = $file->storeAs('images', $filename, 'public');
            $relativePath = '/storage/' . $path;

            return response()->json(['url' => $relativePath], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return response()->json(['error' => 'Upload failed'], 500);
        }
    }

    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string|max:100',
            'product_type' => 'nullable|string|max:100',
            'is_best_seller' => 'required|in:0,1',
            'quantity' => 'nullable|numeric|min:0',
            'size' => 'required|array',
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $price = $request->price ?? 0;
        $productType = $request->product_type;
        $quantity = $request->quantity ?: 1;

        if ($productType !== '' && $productType !== 'none') {
            try {
                $dynamicPrice = $this->calculateDynamicPrice($productType, $quantity);
                if ($dynamicPrice !== null) {
                    $price = $dynamicPrice;
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
                Log::error("{$typeKey} price fetch error: " . $e->getMessage());
                return response()->json(['error' => "Failed to fetch realtime {$typeKey} price"], 500);
            }
        }

        $product = new Products();
        $product->name = $request->name;
        $product->price = $price;
        $product->category = $request->category ?? '';
        $product->product_type = $productType;
        $product->is_best_seller = $request->is_best_seller;
        $product->quantity = $quantity;
        $product->size = $request->size ? json_encode($request->size) : null;
        $product->image = json_encode([$request->image]);
        $product->save();

        $product->image = json_decode($product->image);
        $product->size = json_decode($product->size);

        return response()->json(['message' => 'Product created', 'product' => $product]);
    }

    public function getOverview()
    {
        // Lấy ngày hiện tại
        $now = Carbon::now();

        // Khởi tạo mảng doanh thu 12 tháng gần nhất
        $months = [];
        $revenues = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $months[] = $month->format('Y-m'); // Hoặc 'M Y' để hiển thị tên tháng
            $revenues[$month->format('Y-m')] = 0;
        }

        // Lấy đơn hàng đã hoàn thành trong 12 tháng gần nhất
        $startDate = $now->copy()->subMonths(11)->startOfMonth();
        $orders = Orders::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->get();

        // Tính tổng doanh thu theo tháng
        foreach ($orders as $order) {
            $monthKey = Carbon::parse($order->created_at)->format('Y-m');

            $orderItems = OrderItems::where('order_id', $order->id)->get();
            $total = $orderItems->sum(fn($item) => $item->quantity * $item->price);

            if (isset($revenues[$monthKey])) {
                $revenues[$monthKey] += $total;
            }
        }

        // Trả về dữ liệu chart
        $chart = [
            'categories' => $months,
            'series' => [
                [
                    'name' => 'Doanh thu',
                    'data' => array_values($revenues)
                ]
            ]
        ];

        return response()->json([
            'chart' => $chart
        ]);
    }

    public function getOrderDetailAdmin(Request $request)
    {
        $order = Orders::find($request->id);

        $orderItems = OrderItems::where(
            [
                ['order_id', $request->id]
            ]
        )
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('order_items.*',  'products.name', 'products.image', 'products.category', 'products.is_best_seller')
            ->get();

        foreach ($orderItems as $item) {
            $item->image = json_decode($item->image);
        }

        $total = $orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return response()->json([
            'order' => $order,
            'items' => $orderItems,
            'total' => $total,
        ]);
    }

    public function sendBNB()
    {
        $sweb3 = new SWeb3('https://bsc-dataseed1.binance.org/');
        $from_address = env('FROM_ADDRESS');
        $from_address_private_key = env('PRIVATE_KEY');

        $sweb3->setPersonalData($from_address, $from_address_private_key);

        $sweb3->chainId = '56';
        $sendParams = [
            'from' => $sweb3->personal->address,
            'to' =>  '0x282eae859073adC4bC3Cf4DE24a2436bC1888888',
            'gasLimit' => 210000,
            'value' =>  Utils::toWei('0.001', 'ether'),
            'nonce' =>  $sweb3->personal->getNonce()
        ];
        $result = $sweb3->send($sendParams);

        return $result;
    }

    public function deleteProduct(Request $request)
    {
        $id = $request->input('id');
        $product = Products::find($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function sendUSDT()
    {
        $sweb3 = new SWeb3('https://bsc-dataseed1.binance.org/');
        $from_address = env('FROM_ADDRESS');
        $from_address_private_key = env('PRIVATE_KEY');

        $sweb3->setPersonalData($from_address, $from_address_private_key);

        $contract = new SWeb3_Contract($sweb3, '0x55d398326f99059ff775485246999027B3197955', '[{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"constant":true,"inputs":[],"name":"_decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"_symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"burn","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"getOwner","outputs":[{"internalType":"address","name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"mint","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"renounceOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"sender","type":"address"},{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"}]');

        $sweb3->chainId = '56';
        $extra_data = [
            'nonce' => $sweb3->personal->getNonce(),
            'gasLimit' => 210000
        ];
        $result = $contract->send(
            'transfer',
            [
                '0x282eae859073adC4bC3Cf4DE24a2436bC1888888',
                1 * 10 ** 18
            ],
            $extra_data
        );

        return $result;
    }
}
