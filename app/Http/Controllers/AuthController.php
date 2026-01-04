<?php

namespace App\Http\Controllers;

use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use App\Services\BnbPayoutService;
use App\Traits\Sharable;
use Carbon\Carbon;
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
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'coinbaseWebhook']]);
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
            foreach ($cart as $item) {
                if (isset($item['id'])) {
                    $productIds[] = $item['id'];
                }
            }

            $requiresBnbPayout = false;
            if (! empty($productIds)) {
                $requiresBnbPayout = Products::query()
                    ->whereIn('id', $productIds)
                    ->where('product_type', 'bnb')
                    ->exists();
            }

            if ($requiresBnbPayout) {
                $note = $request->data['note'] ?? null;
                $bnbPayoutService = app(BnbPayoutService::class);
                if (! $bnbPayoutService->isValidBscAddress($note)) {
                    return response()->json([
                        'error' => 'invalid_wallet_address',
                        'message' => 'Please enter a valid BSC wallet address in Note.',
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
        }
    }

    public function getOrder(Request $request)
    {

        $order = Orders::where('id', $request->id)->first();
        $orderItems = OrderItems::where('order_id', $request->id)->get();
        $total = $orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return response()->json([
            'order' => $order,
            'items' => $orderItems,
            'total' => $total,
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

            $this->handleBnbPayoutForOrder($orderId);
        }

        return response()->json(['received' => true]);
    }

    private function handleBnbPayoutForOrder(string $orderId): void
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

            // Prevent double-send if Coinbase retries webhook
            if (! empty($order->bnb_sent_at)) {
                return;
            }

            $bnbQuantity = (int) OrderItems::query()
                ->where('order_id', $orderId)
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('products.product_type', 'bnb')
                ->sum('order_items.quantity');

            if ($bnbQuantity <= 0) {
                return;
            }

            $toAddress = trim((string) ($order->note ?? ''));
            $bnbPayoutService = app(BnbPayoutService::class);

            if (! $bnbPayoutService->isValidBscAddress($toAddress)) {
                Orders::where('id', $orderId)->update([
                    'bnb_send_error' => 'Missing/invalid BSC wallet address in note',
                    'updated_at' => now(),
                ]);

                return;
            }

            $result = $bnbPayoutService->sendBnb($toAddress, $bnbQuantity);

            $tx = null;
            if (is_string($result['result'] ?? null)) {
                $tx = $result['result'];
            } elseif (is_array($result['result'] ?? null)) {
                $tx = $result['result']['tx'] ?? $result['result']['txHash'] ?? null;
            }

            Orders::where('id', $orderId)->update([
                'bnb_sent_at' => now(),
                'bnb_send_txhash' => $tx,
                'bnb_send_error' => null,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('BNB payout error for order ' . $orderId . ': ' . $e->getMessage());
            try {
                Orders::where('id', $orderId)->update([
                    'bnb_send_error' => $e->getMessage(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $inner) {
                // ignore
            }
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
            foreach ($timeline as $t) {
                if (isset($t['status']) && strtolower($t['status']) === 'completed') {
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
                    'updated_at' => now(),
                ]);

                return response()->json(['status' => 'completed']);
            }

            return response()->json(['status' => 'pending', 'detail' => $data]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'coinbase_request_failed', 'message' => $th->getMessage()], 500);
        }
    }
}
