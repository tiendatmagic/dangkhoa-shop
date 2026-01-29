<?php

namespace App\Http\Controllers;

use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use App\Models\OrderPayout;
use App\Models\TokenAsset;
use App\Services\BnbPayoutService;
use App\Services\EvmContractPayoutService;
use App\Services\PriceQuoteService;
use App\Traits\Sharable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Defuse\Crypto\Key;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, Sharable, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'coinbaseWebhook', 'sepayWebhook']]);
    }

    public function systemSetting(Request $request)
    {
        $name = $request->name;

        switch ($name) {
            case 'app-key':
                Artisan::call('config:cache');
                Artisan::call('key:generate');
                break;
            case 'jwt-token':
                Artisan::call('config:cache');
                Artisan::call('jwt:secret --force');
                break;
            case 'clear-all-cache':
                Artisan::call('cache:clear');
                break;
            case 'clear-web-compiled':
                Artisan::call('view:cache');
                break;
            case 'clear-config':
                Artisan::call('config:cache');
                break;
            case 'clear-routing':
                Artisan::call('route:cache');
                break;
            case 'clear-log':
                file_put_contents(storage_path('logs/laravel.log'), '');
                break;
            case 'clear-all':
                Artisan::call('key:generate');
                Artisan::call('cache:clear');
                Artisan::call('view:cache');
                Artisan::call('route:cache');
                file_put_contents(storage_path('logs/laravel.log'), '');
                Artisan::call('jwt:secret --force');
                Artisan::call('config:cache');
                break;
            default:
                break;
        }

        return true;
    }

    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);
        if (strlen($request->password) >= 8) {
            if (! $token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $user = auth('api')->user();

            // Kiểm tra nếu tài khoản có bật 2FA
            if ($user->two_factor_secret) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => '2FA required',
                ], 200);
            }

            $newRefreshToken = $this->createRefreshToken();

            return $this->respondWithToken($token, $newRefreshToken, null, request());
        }
    }

    public function createRefreshToken()
    {
        $data = [
            'user_id' => auth('api')->user()->id,
            'random' => rand() . time(),
            'exp' => time() + config('jwt.refresh_ttl'),
        ];

        $refreshToken = JWTAuth::getJWTProvider()->encode($data);

        return $refreshToken;
    }

    public function me(Request $request)
    {
        $data = response()->json(auth('api')->user())->getData();
        // $appVersion = Settings::where('key', 'version')->first();
        $arr = [];

        // foreach ($data as $key => $value) {
        //     $arr[$key] = $value;
        //     $arr['level'] = $this->calcLevel($data->exp);
        //     $arr['app_version'] = $appVersion->value;
        // }
        return $data;
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $refreshToken = request()->refresh_token;

        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $refreshToken = request()->refresh_token;
        try {
            $decoded = JWTAuth::getJWTProvider()->decode($refreshToken);
            $user = User::find($decoded['user_id']);
            if (Cache::has("blacklist:{$refreshToken}")) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            } else {
                if (! $user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized',
                    ], 401);
                }

                $token = auth('api')->login($user);
                $newRefreshToken = $this->createRefreshToken();

                return $this->respondWithToken($token, $newRefreshToken, $refreshToken, request());
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|max:200',
            'newPassword' => 'required|min:8|max:200',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'can_not_save_password',
                'messages' => $validator->errors(),
            ], 400);
        }

        $user = $request->user();
        $getUserPassword = $user->password;

        if (! password_verify($request->password, $getUserPassword)) {
            return response()->json(['error' => 'password_does_not_match'], 400);
        }

        $user->update([
            'password' => password_hash($request->newPassword, PASSWORD_DEFAULT),
        ]);

        return response()->json(['success' => 'password_changed'], 200);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|min:2|max:30',
            'address' => 'required|string|min:1|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|min:10|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'invalid_input', 'messages' => $validator->errors()], 422);
        }

        User::where('id', $request->user()->id)->update([
            'full_name' => $request->full_name,
            'address' => $request->address,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $getProfile = User::where('id', $request->user()->id)->first();

        return response()->json([
            'success' => 'profile_updated',
            'data' => $getProfile,
        ], 200);
    }

    /**
     * Get the token array structure.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken($token, $newRefreshToken, $refreshToken, Request $request)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'information' => response()->json(auth('api')->user())->getData(),
        ]);
    }

    public function confirmOrder(Request $request)
    {
        $payment = $request->payment;
        $usedCodes = Orders::pluck('order_code')->toArray();
        $usedCodes = array_flip($usedCodes);

        $generateOrderCode = function () use (&$usedCodes) {
            do {
                $num = rand(1, 99999999);
                $order_code = str_pad($num, 8, '0', STR_PAD_LEFT);
            } while (isset($usedCodes[$order_code]));
            $usedCodes[$order_code] = true;

            return $order_code;
        };

        if ($payment == 'cash') {
            $orderId = UUID::uuid4();
            $order_code = $generateOrderCode();

            Orders::insert([
                'id' => $orderId,
                'order_code' => $order_code,
                'user_id' => $request->user()->id,
                'payment' => $payment,
                'status' => 'pending',
                'name' => $request->data['name'],
                'email' => $request->data['email'],
                'phone' => $request->data['phone'],
                'address' => $request->data['address'],
                'note' => $request->data['note'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cart = $request->data['cart'];
            foreach ($cart as $item) {
                $product = Products::find($item['id']);
                $currentPrice = $product ? $product->price : $item['price'];

                OrderItems::insert([
                    'id' => UUID::uuid4(),
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'] <= 0 ? 1 : $item['quantity'],
                    'price' => $currentPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $getEmail = 'doandangkhoa1492004@gmail.com';
            $getName = 'Admin DangKhoa Shop';

            $total = array_sum(array_map(function ($item) {
                $quantity = $item['quantity'] <= 0 ? 1 : $item['quantity'];
                $price = $item['price'];

                return $quantity * $price;
            }, $cart));

            Mail::send('emails.confirm-order', compact('request', 'order_code', 'total'), function ($message) use ($getEmail, $getName) {
                $message->to($getEmail, $getName)
                    ->subject('Hệ thống ghi nhận đơn hàng');
            });

            return response()->json([
                'success' => 'order_confirmed',
                'order_id' => $orderId,
            ]);
        } elseif ($payment === 'usdt') {
            $data = (object) $request->input('data');

            $txHash = strtolower($data->transactionHash);
            $expectedAmount = $data->amount;
            $expectedReceiver = strtolower('0x1ad11e0e96797a14336bf474676eb0a332055555');
            $usdtContract = strtolower('0x55d398326f99059ff775485246999027b3197955');

            $apiKey = env('ETHERSCAN_API_KEY');
            $apiUrl = "https://api.etherscan.io/v2/api?chainid=56&module=proxy&action=eth_getTransactionByHash&txhash={$txHash}&apikey={$apiKey}";
            $response = Http::get($apiUrl)->json();

            if (! isset($response['result'])) {
                return response()->json(['error' => 'Invalid response from explorer'], 400);
            }

            $tx = $response['result'];

            if (strtolower($tx['to']) !== $usdtContract) {
                return response()->json(['error' => 'Not a USDT transaction'], 400);
            }

            $input = $tx['input'];
            $method = substr($input, 0, 10);
            if ($method !== '0xa9059cbb') {
                return response()->json(['error' => 'Not a transfer() call'], 400);
            }

            $recipient = '0x' . substr($input, 10 + 24, 40);
            $amountHex = '0x' . substr($input, 10 + 64, 64);
            $amount = gmp_strval(gmp_init($amountHex, 16)) / 1e18;

            if (strtolower($recipient) !== $expectedReceiver) {
                return response()->json(['error' => 'Wrong recipient'], 400);
            }

            if ($amount < $expectedAmount) {
                return response()->json(['error' => 'Amount not matched'], 400);
            }

            if (Orders::where('txhash', $txHash)->exists()) {
                return response()->json(['error' => 'Transaction already used'], 400);
            }

            $orderId = UUID::uuid4();
            $order_code = $generateOrderCode();

            Orders::insert([
                'id' => $orderId,
                'order_code' => $order_code,
                'user_id' => $request->user()->id,
                'payment' => $payment,
                'status' => 'pending',
                'name' => $request->data['name'],
                'email' => $request->data['email'],
                'phone' => $request->data['phone'],
                'address' => $request->data['address'],
                'note' => $request->data['note'],
                'txhash' => $txHash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $cart = $request->data['cart'];
            foreach ($request->data['cart'] as $item) {
                $product = Products::find($item['id']);
                $currentPrice = $product ? $product->price : $item['price'];

                OrderItems::insert([
                    'id' => UUID::uuid4(),
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'] <= 0 ? 1 : $item['quantity'],
                    'price' => $currentPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $getEmail = 'doandangkhoa1492004@gmail.com';
            $getName = 'Admin DangKhoa Shop';

            $total = array_sum(array_map(function ($item) {
                $quantity = $item['quantity'] <= 0 ? 1 : $item['quantity'];
                $price = $item['price'];

                return $quantity * $price;
            }, $cart));

            Mail::send('emails.confirm-order', compact('request', 'order_code', 'total'), function ($message) use ($getEmail, $getName) {
                $message->to($getEmail, $getName)
                    ->subject('Hệ thống ghi nhận đơn hàng');
            });

            return response()->json([
                'success' => 'usdt_payment_verified',
                'txhash' => $txHash,
                'amount' => $amount,
                'receiver' => $recipient,
                'order_id' => $orderId,
            ]);
        } elseif ($payment === 'coinbase') {
            $cart = $request->data['cart'] ?? [];
            $productIds = [];
            $cartSizes = [];
            foreach ($cart as $item) {
                if (isset($item['id'])) {
                    $productIds[] = $item['id'];
                }
                if (isset($item['size'])) {
                    $cartSizes[] = strtoupper(trim((string) $item['size']));
                }
            }

            // If the cart contains any crypto-like product_type OR a token symbol stored in size, require a wallet address in Note.
            $requiresWalletAddress = false;
            if (! empty($productIds)) {
                $requiresWalletAddress = Products::query()
                    ->whereIn('id', $productIds)
                    ->whereNotNull('product_type')
                    ->whereNotIn('product_type', ['none', 'gold', 'silver'])
                    ->exists();
            }

            if (! $requiresWalletAddress && ! empty($cartSizes)) {
                $requiresWalletAddress = TokenAsset::query()
                    ->where('enabled', true)
                    ->whereIn('symbol', array_values(array_unique($cartSizes)))
                    ->exists();
            }

            if ($requiresWalletAddress) {
                $note = $request->data['note'] ?? null;
                $bnbPayoutService = app(BnbPayoutService::class);
                if (! $bnbPayoutService->isValidBscAddress($note)) {
                    return response()->json([
                        'error' => 'invalid_wallet_address',
                        'message' => 'Please enter a valid wallet address (0x...) in Note.',
                    ], 422);
                }
            }

            $orderId = UUID::uuid4();
            $order_code = $generateOrderCode();

            Orders::insert([
                'id' => $orderId,
                'order_code' => $order_code,
                'user_id' => $request->user()->id,
                'payment' => $payment,
                'status' => 'pending',
                'name' => $request->data['name'],
                'email' => $request->data['email'],
                'phone' => $request->data['phone'],
                'address' => $request->data['address'],
                'note' => $request->data['note'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cart as $item) {
                $product = Products::find($item['id']);
                $currentPrice = $product ? $product->price : $item['price'];

                OrderItems::insert([
                    'id' => UUID::uuid4(),
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'] <= 0 ? 1 : $item['quantity'],
                    'price' => $currentPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $total = array_sum(array_map(function ($item) {
                $quantity = $item['quantity'] <= 0 ? 1 : $item['quantity'];
                $price = $item['price'];

                return $quantity * $price;
            }, $cart));

            $coinbaseApiKey = env('COINBASE_COMMERCE_API_KEY');
            $coinbaseVersion = env('COINBASE_COMMERCE_API_VERSION', '2018-03-22');
            $currency = env('COINBASE_COMMERCE_CURRENCY', 'USD');

            $body = [
                'name' => "Order {$order_code}",
                'description' => 'DangKhoa Shop order',
                'local_price' => [
                    'amount' => number_format($total, 2, '.', ''),
                    'currency' => $currency,
                ],
                'pricing_type' => 'fixed_price',
                'metadata' => [
                    'order_id' => $orderId,
                ],
                'redirect_url' => rtrim(env('FRONTEND_URL', env('APP_URL')), '/') . '/checkout/' . $orderId,
                'cancel_url' => rtrim(env('FRONTEND_URL', env('APP_URL')), '/') . '/cart',
            ];

            try {
                $response = Http::withHeaders([
                    'X-CC-Api-Key' => $coinbaseApiKey,
                    'X-CC-Version' => $coinbaseVersion,
                ])->post('https://api.commerce.coinbase.com/charges', $body)->json();

                if (isset($response['data'])) {
                    $chargeId = $response['data']['id'] ?? null;
                    $hostedUrl = $response['data']['hosted_url'] ?? null;
                    $expiresAt = $response['data']['expires_at'] ?? null;

                    // Force a 10-minute expiry for our checkout regardless of Coinbase default
                    $dbExpires = Carbon::now()->addMinutes(10)->toDateTimeString();
                    $returnExpires = Carbon::parse($dbExpires)->toIso8601String();

                    Orders::where('id', $orderId)->update([
                        'coinbase_charge_id' => $chargeId,
                        'coinbase_hosted_url' => $hostedUrl,
                        'coinbase_expires_at' => $dbExpires,
                        'updated_at' => now(),
                    ]);

                    return response()->json([
                        'success' => 'coinbase_charge_created',
                        'hosted_url' => $hostedUrl,
                        'expires_at' => $returnExpires,
                        'order_id' => $orderId,
                    ]);
                } else {
                    return response()->json(['error' => 'coinbase_error', 'detail' => $response], 500);
                }
            } catch (\Throwable $th) {
                return response()->json(['error' => 'coinbase_request_failed', 'message' => $th->getMessage()], 500);
            }
        } elseif ($payment === 'sepay') {
            $cart = $request->data['cart'] ?? [];

            $orderId = UUID::uuid4();
            $order_code = $generateOrderCode();

            Orders::insert([
                'id' => $orderId,
                'order_code' => $order_code,
                'user_id' => $request->user()->id,
                'payment' => $payment,
                'status' => 'pending',
                'name' => $request->data['name'],
                'email' => $request->data['email'],
                'phone' => $request->data['phone'],
                'address' => $request->data['address'],
                'note' => $request->data['note'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cart as $item) {
                $product = Products::find($item['id']);
                $currentPrice = $product ? $product->price : $item['price'];

                OrderItems::insert([
                    'id' => UUID::uuid4(),
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'] <= 0 ? 1 : $item['quantity'],
                    'price' => $currentPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $total = array_sum(array_map(function ($item) {
                $quantity = $item['quantity'] <= 0 ? 1 : $item['quantity'];
                $price = $item['price'];

                return $quantity * $price;
            }, $cart));
            // Build Sepay transfer code and QR URL using configured bank details
            $bankCode = env('SEPAY_BANK_CODE');
            $bankAccount = env('SEPAY_BANK_ACCOUNT');
            $bankOwner = env('SEPAY_BANK_OWNER');

            if (! $bankCode || ! $bankAccount) {
                return response()->json(['error' => 'sepay_not_configured'], 500);
            }

            // Generate a numeric transfer code (6 digits). If collision, lengthen to 7 or 8.
            $generateNumericCode = function (int $initialLength = 6) {
                $length = $initialLength;
                $maxLength = 8;

                while ($length <= $maxLength) {
                    $min = (int) pow(10, $length - 1);
                    $max = (int) (pow(10, $length) - 1);
                    try {
                        $num = random_int($min, $max);
                    } catch (\Throwable $e) {
                        // fallback to mt_rand
                        $num = mt_rand($min, $max);
                    }

                    $digits = (string) $num;
                    $code = 'DK' . $digits;
                    // check uniqueness in orders table
                    $exists = Orders::where('sepay_code', $code)->exists();
                    if (! $exists) {
                        return $code;
                    }

                    $length++;
                }

                // Fallback: create less structured but unique code with DK prefix
                do {
                    $code = 'DK' . strtoupper(Str::random(6));
                } while (Orders::where('sepay_code', $code)->exists());

                return $code;
            };

            $transferCode = $generateNumericCode(6);

            // Convert order total (assumed USD) to VND for Sepay QR. Use env rate or default 23000.
            $rate = (float) env('SEPAY_EXCHANGE_RATE', 23000);
            $amountVnd = (int) round((float) $total * $rate);

            $params = http_build_query([
                'acc' => $bankAccount,
                'bank' => $bankCode,
                'amount' => $amountVnd,
                'des' => $transferCode,
            ]);

            $qrUrl = 'https://qr.sepay.vn/img?' . $params;

            // store hosted url and expires using existing coinbase fields to avoid new migration
            $dbExpires = Carbon::now()->addMinutes(15)->toDateTimeString();
            $returnExpires = Carbon::parse($dbExpires)->toIso8601String();

            Orders::where('id', $orderId)->update([
                'coinbase_hosted_url' => $qrUrl,
                'coinbase_expires_at' => $dbExpires,
                'sepay_code' => $transferCode,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => 'sepay_charge_created',
                'hosted_url' => $qrUrl,
                'qr_url' => $qrUrl,
                'expires_at' => $returnExpires,
                'order_id' => $orderId,
                'transfer_code' => $transferCode,
                'amount_vnd' => $amountVnd,
                'amount_display' => number_format($amountVnd, 0, ',', '.') . ' VND',
                'bank' => [
                    'code' => $bankCode,
                    'account' => $bankAccount,
                    'owner' => $bankOwner,
                ],
            ]);
        }
    }

    public function getOrder(Request $request)
    {

        $order = Orders::where('id', $request->id)->first();
        $orderItems = OrderItems::where('order_id', $request->id)->get();
        $total = $orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $payouts = [];
        if ($order && ($order->payment ?? null) === 'coinbase') {
            $payouts = OrderPayout::query()
                ->where('order_id', $request->id)
                ->orderBy('asset_symbol')
                ->get();
        }

        return response()->json([
            'order' => $order,
            'items' => $orderItems,
            'total' => $total,
            'payouts' => $payouts,
        ]);
    }

    public function getMyOrder(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $orders = Orders::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
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

    public function getOrderDetail(Request $request)
    {
        $order = Orders::where('user_id', $request->user()->id)
            ->find($request->id);

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

    public function coinbaseWebhook(Request $request)
    {
        $signature = $request->header('X-CC-Webhook-Signature');
        $payload = $request->getContent();
        $secret = env('COINBASE_COMMERCE_WEBHOOK_SECRET');

        if (! $signature || ! $secret) {
            return response()->json(['error' => 'missing_signature_or_secret'], 400);
        }

        $computed = hash_hmac('sha256', $payload, $secret);
        if (! hash_equals($computed, $signature)) {
            return response()->json(['error' => 'invalid_signature'], 400);
        }

        $data = json_decode($payload, true);
        $eventType = $data['event']['type'] ?? null;
        $chargeData = $data['event']['data'] ?? null;
        $metadata = $chargeData['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if ($orderId && $eventType === 'charge:confirmed') {
            // Mark as completed when Coinbase confirms the charge
            Orders::where('id', $orderId)->update([
                'status' => 'completed',
                'paid_at' => now(),
                'coinbase_charge_id' => $chargeData['id'] ?? null,
                'updated_at' => now(),
            ]);

            $this->handleEvmPayoutForOrder((string) $orderId);
        }

        return response()->json(['received' => true]);
    }

    public function sepayWebhook(Request $request)
    {
        $apiKey = env('SEPAY_TOKEN');
        $authHeader = (string) $request->header('Authorization');
        $xApiKey = (string) $request->header('X-Api-Key');

        if ($apiKey && ! $this->isValidSepayAuthHeader($apiKey, $authHeader, $xApiKey)) {
            return response()->json(['success' => false], 401);
        }

        $data = $request->all();

        $code = $this->extractOrderCodeFromPayload($data);

        if (! $code) {
            return response()->json(['success' => true], 200);
        }

        $order = Orders::where('order_code', $code)->first();
        if (! $order && str_starts_with(strtoupper($code), 'DK')) {
            $order = Orders::where('sepay_code', strtoupper($code))->first();
        }

        if (! $order) {
            $transferAmount = $this->extractTransferAmountFromPayload($data);
            $haystack = $this->collectPayloadText($data);

            $accountMatch = null;
            if (preg_match('/\b0[0-9]{6,11}\b/', $haystack, $m)) {
                $accountMatch = $m[0];
            }

            $candidates = Orders::where('payment', 'sepay')->where('status', 'pending')->get();
            $rate = (float) env('SEPAY_EXCHANGE_RATE', 23000);
            $best = null;
            $bestScore = 0;

            foreach ($candidates as $cand) {
                $items = OrderItems::where('order_id', $cand->id)->get();
                $total = $items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
                $calcVnd = (int) round((float) $total * $rate);

                $parsed = [];
                if (! empty($cand->coinbase_hosted_url)) {
                    $qpos = strpos($cand->coinbase_hosted_url, '?');
                    if ($qpos !== false) parse_str(substr($cand->coinbase_hosted_url, $qpos + 1), $parsed);
                }

                $score = 0;
                if ($calcVnd === $transferAmount) $score += 5;
                if (! empty($parsed['amount']) && (int)$parsed['amount'] === $transferAmount) $score += 8;
                if ($accountMatch && ! empty($parsed['acc']) && str_replace(['+', ' '], '', $parsed['acc']) === str_replace(['+', ' '], '', $accountMatch)) $score += 10;
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $cand;
                }
            }

            if ($best && $bestScore > 0) {
                $order = $best;
            } else {
                return response()->json(['success' => true], 200);
            }
        }

        if ($order->status === 'completed') {
            return response()->json(['success' => true], 200);
        }

        $transferAmount = $this->extractTransferAmountFromPayload($data);

        $orderItems = OrderItems::where('order_id', $order->id)->get();
        $total = $orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // Compute expected VND using configured exchange rate (same logic as order creation)
        $rate = (float) env('SEPAY_EXCHANGE_RATE', 23000);
        $expectedVnd = (int) round((float) $total * $rate);

        // Accept payment if transfer amount is greater than or equal to expected VND
        if ($transferAmount < $expectedVnd) {
            // optional: log mismatch for review
            return response()->json(['success' => true], 200);
        }

        Orders::where('id', $order->id)->update([
            'status' => 'completed',
            'paid_at' => now(),
            'txhash' => $data['id'] ?? null,
            'updated_at' => now(),
        ]);

        // If EVM payout is needed (e.g., coinbase flow), call the handler
        try {
            $this->handleEvmPayoutForOrder((string) $order->id);
        } catch (\Throwable $th) {
            // ignore payout errors
        }

        return response()->json(['success' => true], 200);
    }

    public function cancelOrder(Request $request)
    {
        $orderId = $request->input('id') ?? $request->input('order_id') ?? null;
        if (! $orderId) {
            return response()->json(['error' => 'missing_order_id'], 400);
        }

        $order = Orders::where('id', $orderId)->first();
        if (! $order) {
            return response()->json(['error' => 'order_not_found'], 404);
        }

        // only allow cancelling pending Sepay orders by the owner (or admin via admin routes)
        $user = $request->user();
        if ($user && $order->user_id !== $user->id) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        if (($order->payment ?? null) !== 'sepay') {
            return response()->json(['error' => 'not_sepay_order'], 400);
        }

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'cannot_cancel_non_pending'], 400);
        }

        Orders::where('id', $order->id)->update([
            'status' => 'canceled',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true], 200);
    }

    private function isValidSepayAuthHeader(string $apiKey, string $authHeader, string $xApiKey): bool
    {
        if ($xApiKey && hash_equals($apiKey, $xApiKey)) {
            return true;
        }

        if (! $authHeader) {
            return false;
        }

        $normalized = strtolower(trim($authHeader));
        $expected = strtolower('Apikey ' . $apiKey);
        $expectedApiKey = strtolower('ApiKey ' . $apiKey);
        $expectedBearer = strtolower('Bearer ' . $apiKey);

        return hash_equals($expected, $normalized)
            || hash_equals($expectedApiKey, $normalized)
            || hash_equals($expectedBearer, $normalized);
    }

    private function extractOrderCodeFromPayload(array $data): ?string
    {
        $haystack = $this->collectPayloadText($data);

        if (! $haystack) return null;

        if (preg_match('/DK[0-9]{6,8}/i', $haystack, $matches)) {
            return strtoupper($matches[0]);
        }

        if (preg_match('/NAP[0-9A-Z]+/i', $haystack, $matches)) {
            return strtoupper($matches[0]);
        }

        // Fallback: 8-digit numeric order_code
        if (preg_match('/\b[0-9]{8}\b/', $haystack, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function extractTransferAmountFromPayload(array $data): int
    {
        $preferredKeys = [
            'transferAmount',
            'amount',
            'amountIn',
            'amount_in',
            'creditAmount',
            'credit_amount',
            'receivedAmount',
            'received_amount',
        ];

        $queue = [$data];

        while ($queue) {
            $current = array_shift($queue);

            foreach ($current as $key => $value) {
                if (is_array($value)) {
                    $queue[] = $value;
                    continue;
                }

                if (in_array((string) $key, $preferredKeys, true)) {
                    if (is_numeric($value)) return (int) $value;
                    $digits = preg_replace('/[^0-9]/', '', (string) $value);
                    return $digits ? (int) $digits : 0;
                }
            }
        }

        return 0;
    }

    private function collectPayloadText(array $data): string
    {
        $parts = [];

        $queue = [$data];

        while ($queue) {
            $current = array_shift($queue);

            foreach ($current as $value) {
                if (is_array($value)) {
                    $queue[] = $value;
                    continue;
                }

                if (is_string($value) || is_numeric($value)) {
                    $parts[] = (string) $value;
                }
            }
        }

        return trim(implode(' ', $parts));
    }

    private function handleEvmPayoutForOrder(string $orderId): void
    {
        try {
            $order = Orders::query()->where('id', $orderId)->first();
            if (! $order) {
                return;
            }

            // Only run for Coinbase-paid orders
            if (($order->payment ?? null) !== 'coinbase') {
                return;
            }

            $toAddress = trim((string) ($order->note ?? ''));
            $bnbPayoutService = app(BnbPayoutService::class);
            if (! $bnbPayoutService->isValidBscAddress($toAddress)) {
                // No valid address to pay out to
                return;
            }

            $items = OrderItems::query()
                ->where('order_id', $orderId)
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->select([
                    'products.product_type as product_type',
                    'order_items.size as size',
                    'order_items.quantity as quantity',
                    'order_items.price as price',
                ])
                ->get();

            if ($items->isEmpty()) {
                return;
            }

            // Enabled assets define which symbols are eligible for payout.
            // Some existing products store the token selection in order_items.size (e.g., 'BNB'), not products.product_type.
            $enabledAssets = TokenAsset::query()
                ->where('enabled', true)
                ->get()
                ->keyBy(function ($a) {
                    return strtoupper(trim((string) $a->symbol));
                });

            $priceQuote = app(PriceQuoteService::class);
            $contractPayout = app(EvmContractPayoutService::class);

            // Aggregate by symbol (prefer product_type; fallback to size if it matches an enabled TokenAsset)
            $grouped = [];
            foreach ($items as $row) {
                $productType = strtoupper(trim((string) ($row->product_type ?? '')));
                $size = strtoupper(trim((string) ($row->size ?? '')));

                $symbol = '';
                if ($productType !== '' && ! in_array($productType, ['NONE', 'GOLD', 'SILVER'], true)) {
                    $symbol = $productType;
                } elseif ($size !== '' && $enabledAssets->has($size)) {
                    $symbol = $size;
                }

                if ($symbol === '') {
                    continue;
                }
                $lineUsd = (string) ((float) $row->quantity * (float) $row->price);
                if (! isset($grouped[$symbol])) {
                    $grouped[$symbol] = '0';
                }
                if (function_exists('bcadd')) {
                    $grouped[$symbol] = bcadd($grouped[$symbol], $lineUsd, 8);
                } else {
                    $grouped[$symbol] = (string) ((float) $grouped[$symbol] + (float) $lineUsd);
                }
            }

            foreach ($grouped as $symbol => $totalUsd) {
                $symbol = strtoupper($symbol);

                // Ensure one attempt per (order, symbol)
                $existing = OrderPayout::query()->where('order_id', $orderId)->where('asset_symbol', $symbol)->first();
                // If a payout was already sent, do not resend. If it failed, allow retry after config fixes.
                if ($existing && $existing->sent_at) {
                    continue;
                }

                $asset = $enabledAssets->get($symbol);
                if (! $asset) {
                    OrderPayout::query()->updateOrCreate(
                        ['order_id' => $orderId, 'asset_symbol' => $symbol],
                        [
                            'chain_id' => 56,
                            'is_native' => false,
                            'token_address' => null,
                            'to_address' => $toAddress,
                            'total_usd' => $totalUsd,
                            'error' => 'Missing token asset configuration for ' . $symbol,
                        ]
                    );
                    continue;
                }

                $chainId = (int) ($asset->chain_id ?? 56);
                $decimals = (int) ($asset->decimals ?? 18);
                $tokenAddress = $asset->token_address;

                try {
                    $spotUsd = $priceQuote->getSpotUsdt($symbol);
                    $amountDecimal = $priceQuote->usdToTokenAmount((string) $totalUsd, (string) $spotUsd, $decimals);
                    $amountWei = $priceQuote->decimalToUnits($amountDecimal, $decimals);

                    $payout = OrderPayout::query()->updateOrCreate(
                        ['order_id' => $orderId, 'asset_symbol' => $symbol],
                        [
                            'chain_id' => $chainId,
                            'is_native' => (bool) $asset->is_native,
                            'token_address' => $tokenAddress,
                            'to_address' => $toAddress,
                            'total_usd' => $totalUsd,
                            'price_usd' => $spotUsd,
                            'amount_decimal' => $amountDecimal,
                            'amount_wei' => $amountWei,
                            'error' => null,
                        ]
                    );

                    $result = null;
                    if ($asset->is_native) {
                        $result = $contractPayout->withdrawNative($chainId, $toAddress, $amountWei);
                    } else {
                        if (! $tokenAddress || ! preg_match('/^0x[a-fA-F0-9]{40}$/', (string) $tokenAddress)) {
                            throw new \RuntimeException('Missing/invalid token_address for ' . $symbol);
                        }
                        $result = $contractPayout->withdrawErc20($chainId, (string) $tokenAddress, $toAddress, $amountWei);
                    }

                    $tx = null;
                    if (is_string($result)) {
                        $tx = $result;
                    } elseif (is_array($result)) {
                        $tx = $result['tx'] ?? $result['txHash'] ?? $result['result'] ?? null;
                    }

                    $payout->tx_hash = is_string($tx) ? $tx : null;
                    $payout->sent_at = now();
                    $payout->error = null;
                    $payout->save();
                } catch (\Throwable $e) {
                    $err = get_class($e) . ': ' . $e->getMessage();
                    $err .= ' @ ' . $e->getFile() . ':' . $e->getLine();

                    // Diagnostics (no secrets): helps verify whether our forced ECC adapter is active.
                    $eccAdapter = null;
                    try {
                        if (class_exists(\Mdanter\Ecc\Math\MathAdapterFactory::class)) {
                            $eccAdapter = get_class(\Mdanter\Ecc\Math\MathAdapterFactory::getAdapter());
                        }
                    } catch (\Throwable $ignored) {
                        // ignore
                    }
                    $err .= ' | gmp=' . (extension_loaded('gmp') ? '1' : '0');
                    if (is_string($eccAdapter) && $eccAdapter !== '') {
                        $err .= ' | ecc_adapter=' . $eccAdapter;
                    }

                    // Extra ECC diagnostics: verify pow() is actually overridden and report adapter version.
                    try {
                        if (extension_loaded('gmp') && class_exists(\Mdanter\Ecc\Math\MathAdapterFactory::class)) {
                            $adapter = \Mdanter\Ecc\Math\MathAdapterFactory::getAdapter();
                            $declaring = (new \ReflectionMethod($adapter, 'pow'))->getDeclaringClass()->getName();
                            $err .= ' | ecc_pow_decl=' . $declaring;

                            $adapterClass = get_class($adapter);
                            if (defined($adapterClass . '::VERSION')) {
                                $err .= ' | ecc_ver=' . constant($adapterClass . '::VERSION');
                            }

                            // Small self-test that should be safe if our adapter is active.
                            try {
                                $adapter->pow(gmp_init(2, 10), 256);
                                $err .= ' | ecc_pow256=ok';
                            } catch (\Throwable $t) {
                                $err .= ' | ecc_pow256=fail';
                            }
                        }
                    } catch (\Throwable $ignored) {
                        // ignore
                    }

                    OrderPayout::query()->updateOrCreate(
                        ['order_id' => $orderId, 'asset_symbol' => $symbol],
                        [
                            'chain_id' => $chainId,
                            'is_native' => (bool) $asset->is_native,
                            'token_address' => $tokenAddress,
                            'to_address' => $toAddress,
                            'total_usd' => $totalUsd,
                            'error' => $err,
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('Payout error for order ' . $orderId . ': ' . $e->getMessage());
        }
    }

    public function checkCoinbaseStatus(Request $request)
    {
        $orderId = $request->id;
        $order = Orders::where('id', $orderId)->first();
        if (! $order) {
            return response()->json(['error' => 'order_not_found'], 404);
        }

        if (! $order->coinbase_charge_id) {
            return response()->json(['error' => 'no_charge_id'], 400);
        }

        $coinbaseApiKey = env('COINBASE_COMMERCE_API_KEY');
        $coinbaseVersion = env('COINBASE_COMMERCE_API_VERSION', '2018-03-22');

        try {
            $resp = Http::withHeaders([
                'X-CC-Api-Key' => $coinbaseApiKey,
                'X-CC-Version' => $coinbaseVersion,
            ])->get("https://api.commerce.coinbase.com/charges/{$order->coinbase_charge_id}")->json();

            if (! isset($resp['data'])) {
                return response()->json(['error' => 'coinbase_error', 'detail' => $resp], 500);
            }

            $data = $resp['data'];
            // timeline contains status updates; look for COMPLETED
            $timeline = $data['timeline'] ?? [];
            $completed = false;

            // 1) Standard timeline completion check
            // Coinbase may show status=CONFIRMED before COMPLETED depending on the flow.
            foreach ($timeline as $t) {
                $tstatus = strtolower((string) ($t['status'] ?? ''));
                if (in_array($tstatus, ['completed', 'confirmed'], true)) {
                    $completed = true;
                    break;
                }
            }

            // 2) Check payments array for confirmed/completed statuses
            if (! $completed) {
                $payments = $data['payments'] ?? [];
                foreach ($payments as $p) {
                    $pstatus = strtolower($p['status'] ?? '');
                    if (in_array($pstatus, ['completed', 'confirmed', 'success'])) {
                        $completed = true;
                        break;
                    }
                }
            }

            // 3) For Web3 flows, Coinbase may emit a detected payment before finalization.
            // If a transaction_id / payment_id exists and there are success_events we can
            // treat it as completed for UX (optional: you can tighten this by verifying
            // on-chain confirmations separately).
            if (! $completed) {
                $payments = $data['payments'] ?? [];
                $web3Success = $data['web3_data']['success_events'] ?? [];
                $hasTx = false;
                foreach ($payments as $p) {
                    if (! empty($p['transaction_id']) || ! empty($p['payment_id'])) {
                        $hasTx = true;
                        break;
                    }
                }
                if ($hasTx && ! empty($web3Success)) {
                    $completed = true;
                }
            }

            if ($completed) {
                Orders::where('id', $orderId)->update([
                    'status' => 'completed',
                    'paid_at' => $order->paid_at ?? now(),
                    'coinbase_charge_id' => $data['id'] ?? $order->coinbase_charge_id,
                    'updated_at' => now(),
                ]);

                // If webhook delivery is missing, still attempt payout on completion.
                $this->handleEvmPayoutForOrder((string) $orderId);

                $payouts = OrderPayout::query()->where('order_id', $orderId)->orderBy('asset_symbol')->get();
                return response()->json(['status' => 'completed', 'payouts' => $payouts]);
            }

            $payouts = OrderPayout::query()->where('order_id', $orderId)->orderBy('asset_symbol')->get();
            return response()->json(['status' => 'pending', 'detail' => $data, 'payouts' => $payouts]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'coinbase_request_failed', 'message' => $th->getMessage()], 500);
        }
    }
}