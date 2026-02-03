<?php

namespace App\Http\Controllers;

use App\Models\AdminWalletSetting;
use App\Models\TokenAsset;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use App\Services\BnbPayoutService;
use App\Traits\Sharable;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SWeb3\SWeb3;
use SWeb3\SWeb3_Contract;
use SWeb3\Utils;
use App\Models\Customization;

class AdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, Sharable, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'getCustomization']]);
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
        $newStatus = $request->input('status');

        // If attempting to mark completed and payment requires crypto payout, validate wallet BEFORE saving
        if ($newStatus === 'completed') {
            try {
                $paymentType = $order->payment ?? null;
                if (in_array($paymentType, ['coinbase', 'sepay'], true)) {
                    $bnbPayoutService = app(\App\Services\BnbPayoutService::class);
                    $toAddress = trim((string) ($order->note ?? ''));
                    if (! $bnbPayoutService->isValidBscAddress($toAddress)) {
                        return response()->json([
                            'error' => 'invalid_wallet_address',
                            'message' => 'Order note must contain a valid BSC wallet address for crypto payouts.',
                        ], 422);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Pre-update payout check failed for order ' . $order->id . ': ' . $e->getMessage());
                return response()->json(['error' => 'payout_validation_failed'], 500);
            }
        }

        // Save status after validation
        $order->status = $newStatus;
        $order->save();

        // If order marked completed and payment requires crypto payout, trigger payout handler AFTER saving
        if ($order->status === 'completed') {
            try {
                $paymentType = $order->payment ?? null;
                if (in_array($paymentType, ['coinbase', 'sepay'], true)) {
                    try {
                        $auth = app(\App\Http\Controllers\AuthController::class);
                        $auth->handleEvmPayoutForOrder((string) $order->id);
                    } catch (\Throwable $e) {
                        Log::error('Payout trigger failed for order ' . $order->id . ': ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Post-update payout check failed for order ' . $order->id . ': ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order]);
    }

    public function getAllProduct(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $products = Products::orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        foreach ($products as $product) {
            $product->image = json_decode($product->image);
            $product->size = json_decode($product->size);

            if ($product->product_type && $product->product_type !== 'none') {
                $cacheKey = "admin_product_price_{$product->product_type}_{$product->quantity}";
                $cachedPrice = Cache::get($cacheKey);

                if ($cachedPrice !== null) {
                    $product->price = $cachedPrice;
                } else {
                    try {
                        $dynamicPrice = $this->calculateDynamicPrice(
                            $product->product_type,
                            $product->quantity ?? 1
                        );
                        $product->price = $dynamicPrice ?? $product->price ?? 0;
                        Cache::put($cacheKey, $product->price, now()->addMinutes(1));
                    } catch (\Exception $e) {
                        Log::error("Failed to fetch price for {$product->product_type}: " . $e->getMessage());
                        $product->price = $product->price ?? 0;
                    }
                }
            }
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
            $response = Http::withHeaders(['x-access-token' => $apiKey])->get('https://www.goldapi.io/api/XAG/USD');
            if (! $response->successful()) {
                throw new \Exception('Failed to fetch silver price');
            }
            $data = $response->json();
            $spotPrice = $data['price'];
            $spotPricePerTenth = $spotPrice / 10;

            return round($quantity * $spotPricePerTenth);
        } elseif ($productType === 'gold') {
            $response = Http::withHeaders(['x-access-token' => $apiKey])->get('https://www.goldapi.io/api/XAU/USD');
            if (! $response->successful()) {
                throw new \Exception('Failed to fetch gold price');
            }
            $data = $response->json();
            $spotPrice = $data['price'];
            $spotPricePerTenth = $spotPrice / 10;

            return round($quantity * $spotPricePerTenth);
        }

        if (in_array($productType, ['usdt', 'usdc'])) {
            return round($quantity * 1);
        }

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
            'near' => 'NEARUSDT',
            'fil' => 'FILUSDT',
            'etc' => 'ETCUSDT',
            'ena' => 'ENAUSDT',
            'doge' => 'DOGEUSDT',
            'link' => 'LINKUSDT',
            'ada' => 'ADAUSDT',
            'tao' => 'TAOUSDT',
            'arb' => 'ARBUSDT',
            'apt' => 'APTUSDT',
            'aave' => 'AAVEUSDT',
            'ltc' => 'LTCUSDT',
        ];

        $symbol = $symbolMap[strtolower($productType)] ?? null;

        if (! $symbol) {
            throw new \Exception('Invalid crypto symbol: ' . $productType);
        }

        $response = Http::get('https://api.binance.com/api/v3/ticker/price', [
            'symbol' => strtoupper($symbol),
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to fetch crypto price');
        }

        $data = $response->json();
        $spotPrice = (float) $data['price'];

        return $quantity * $spotPrice;
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
            'description' => 'nullable|string|max:1000',
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
        $product->description = $request->description ?? $product->description;

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
                $typeKey = in_array($productType, ['gold', 'silver']) ? $productType : 'crypto';
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
            'product' => $product,
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
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $productType = $request->product_type ?? 'none';
        $quantity = $request->quantity ?: 1;
        $price = $request->price ?? 0;

        if ($productType !== '' && $productType !== 'none') {
            try {
                $dynamicPrice = $this->calculateDynamicPrice($productType, $quantity);
                if ($dynamicPrice !== null) {
                    $price = $dynamicPrice;
                }
            } catch (\Exception $e) {
                $typeKey = in_array($productType, ['gold', 'silver']) ? $productType : 'crypto';
                Log::error("{$typeKey} price fetch error: " . $e->getMessage());

                return response()->json(['error' => "Failed to fetch realtime {$typeKey} price"], 500);
            }
        }

        $product = new Products;
        $product->name = $request->name;
        $product->price = $price;
        $product->category = $request->category ?? '';
        $product->product_type = $productType;
        $product->is_best_seller = $request->is_best_seller;
        $product->quantity = $quantity;
        $product->size = json_encode($request->size);
        $product->image = json_encode([$request->image]);
        $product->description = $request->description ?? '';
        $product->save();

        $product->image = json_decode($product->image);
        $product->size = json_decode($product->size);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ]);
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
                    'data' => array_values($revenues),
                ],
            ],
        ];

        return response()->json([
            'chart' => $chart,
        ]);
    }

    // Get admin customization (latest)
    public function getCustomization()
    {
        $cacheKey = 'customization_public';
        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $custom = Customization::orderBy('id', 'desc')->first();
            if (!$custom) {
                return ['slides' => [], 'collections' => [], 'banner' => '', 'about_content' => '', 'contact_content' => ''];
            }
            return [
                'slides' => $custom->slides ?? [],
                'collections' => $custom->collections ?? [],
                'banner' => $custom->banner ?? '',
                'about_content' => (string) ($custom->about_content ?? ''),
                'contact_content' => (string) ($custom->contact_content ?? ''),
            ];
        });

        return response()->json($data, 200);
    }

    // Save customization (create or update latest)
    public function saveCustomization(Request $request)
    {
        $data = $request->only(['slides', 'collections', 'banner', 'about_content', 'contact_content']);

        $validator = Validator::make($data, [
            'slides' => 'nullable|array',
            'slides.*' => 'nullable|string',
            'collections' => 'nullable|array',
            'banner' => 'nullable|string',
            'about_content' => ['nullable', 'string', 'max:200000', 'not_regex:/<\s*script\b/i'],
            'contact_content' => ['nullable', 'string', 'max:200000', 'not_regex:/<\s*script\b/i'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Prevent hard SQL errors if migrations weren't run yet.
        $hasAbout = Schema::hasColumn('customizations', 'about_content');
        $hasContact = Schema::hasColumn('customizations', 'contact_content');
        if (! $hasAbout && ! empty($data['about_content'])) {
            return response()->json([
                'error' => 'migration_required',
                'message' => 'Missing column about_content in customizations table. Please run migrations.',
            ], 422);
        }
        if (! $hasContact && ! empty($data['contact_content'])) {
            return response()->json([
                'error' => 'migration_required',
                'message' => 'Missing column contact_content in customizations table. Please run migrations.',
            ], 422);
        }

        try {
            $custom = Customization::create([
                'slides' => $data['slides'] ?? [],
                'collections' => $data['collections'] ?? [],
                'banner' => $data['banner'] ?? '',
                'about_content' => $data['about_content'] ?? '',
                'contact_content' => $data['contact_content'] ?? '',
            ]);

            // Refresh cache so public endpoint returns latest immediately
            $cacheKey = 'customization_public';
            Cache::put($cacheKey, [
                'slides' => $custom->slides ?? [],
                'collections' => $custom->collections ?? [],
                'banner' => $custom->banner ?? '',
                'about_content' => (string) ($custom->about_content ?? ''),
                'contact_content' => (string) ($custom->contact_content ?? ''),
            ], now()->addMinutes(5));

            return response()->json(['message' => 'Customization saved', 'data' => $custom], 200);
        } catch (\Exception $e) {
            Log::error('Save customization error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save customization'], 500);
        }
    }

    public function getOrderDetailAdmin(Request $request)
    {
        $order = Orders::find($request->id);

        $orderItems = OrderItems::where(
            [
                ['order_id', $request->id],
            ]
        )
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('order_items.*', 'products.name', 'products.image', 'products.category', 'products.is_best_seller')
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

    public function getWalletSettings()
    {
        $settings = AdminWalletSetting::query()->first();

        return response()->json([
            'chain_id' => $settings?->chain_id,
            'rpc_url' => $settings?->rpc_url,
            'contract_address' => $settings?->contract_address,
            'from_address' => $settings?->from_address,
            'has_private_key' => (bool) ($settings?->private_key),
        ]);
    }

    public function updateWalletSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chain_id' => ['nullable', 'integer', 'min:1'],
            'rpc_url' => ['nullable', 'string', 'max:2048', 'url'],
            'contract_address' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'from_address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            // Allow updating other fields without re-sending the key; if provided, validate after normalization.
            'private_key' => ['nullable', 'string', 'min:32'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $settings = AdminWalletSetting::query()->first();
        if (! $settings) {
            $settings = new AdminWalletSetting;
        }

        $settings->from_address = trim($request->input('from_address'));

        $incomingKey = $request->input('private_key');
        if ($incomingKey !== null && trim((string) $incomingKey) !== '') {
            $pk = trim((string) $incomingKey);
            if (str_starts_with($pk, '0x') || str_starts_with($pk, '0X')) {
                $pk = substr($pk, 2);
            }
            $pk = strtolower(trim($pk));
            if (strlen($pk) !== 64 || ! ctype_xdigit($pk)) {
                return response()->json([
                    'error' => ['private_key' => ['Private key must be 64 hex characters (you may omit 0x prefix).']],
                ], 422);
            }

            // Store normalized key (no 0x). Model cast encrypts at rest.
            $settings->private_key = $pk;
        }
        $settings->chain_id = $request->input('chain_id', $settings->chain_id);
        $settings->rpc_url = $request->input('rpc_url', $settings->rpc_url);
        $settings->contract_address = $request->input('contract_address', $settings->contract_address);
        $settings->save();

        return response()->json([
            'message' => 'Wallet settings updated',
            'chain_id' => $settings->chain_id,
            'rpc_url' => $settings->rpc_url,
            'contract_address' => $settings->contract_address,
            'from_address' => $settings->from_address,
            'has_private_key' => (bool) ($settings->private_key),
        ]);
    }

    public function getTokenAssets()
    {
        $assets = TokenAsset::query()
            ->orderBy('symbol')
            ->get();

        return response()->json([
            'data' => $assets,
        ]);
    }

    public function upsertTokenAsset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'symbol' => ['required', 'string', 'max:32'],
            'chain_id' => ['nullable', 'integer', 'min:1'],
            'is_native' => ['required', 'boolean'],
            'token_address' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'decimals' => ['nullable', 'integer', 'min:0', 'max:36'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $symbol = strtoupper(trim($request->input('symbol')));
        $isNative = (bool) $request->boolean('is_native');
        $tokenAddress = $request->input('token_address');
        if ($isNative) {
            $tokenAddress = null;
        }

        $asset = TokenAsset::query()->updateOrCreate(
            ['symbol' => $symbol],
            [
                'chain_id' => (int) ($request->input('chain_id', 56)),
                'is_native' => $isNative,
                'token_address' => $tokenAddress ? trim((string) $tokenAddress) : null,
                'decimals' => (int) ($request->input('decimals', 18)),
                'enabled' => $request->has('enabled') ? (bool) $request->boolean('enabled') : true,
            ]
        );

        return response()->json([
            'message' => 'Token asset saved',
            'asset' => $asset,
        ]);
    }

    public function deleteTokenAsset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'symbol' => ['required', 'string', 'max:32'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $symbol = strtoupper(trim($request->input('symbol')));
        TokenAsset::query()->where('symbol', $symbol)->delete();

        return response()->json(['message' => 'Token asset deleted']);
    }

    public function getABI()
    {
        $path = base_path('app/abi/ABI.json');
        if (! file_exists($path)) {
            return response()->json(['error' => 'ABI file not found'], 404);
        }

        $abi = file_get_contents($path);
        $decoded = json_decode($abi, true);

        return response()->json([
            'abi' => $decoded ?? $abi,
        ]);
    }

    public function sendBNB(Request $request, BnbPayoutService $bnbPayoutService)
    {
        $validator = Validator::make($request->all(), [
            'to' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'amount_bnb' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $result = $bnbPayoutService->sendBnb(
            $request->input('to'),
            (int) $request->input('amount_bnb')
        );

        return response()->json($result);
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
            'gasLimit' => 210000,
        ];
        $result = $contract->send(
            'transfer',
            [
                '0x282eae859073adC4bC3Cf4DE24a2436bC1888888',
                '1000000000000000000',
            ],
            $extra_data
        );

        return $result;
    }
}
