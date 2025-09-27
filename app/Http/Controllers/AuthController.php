<?php

namespace App\Http\Controllers;

use App\Models\OrderItems;
use App\Models\Orders;
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
use Illuminate\Auth\Events\Login;
use PhpParser\Node\Stmt\TryCatch;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;

class AuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Sharable;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh']]);
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
            if (!$token = auth('api')->attempt($credentials)) {
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
                    'message' => '2FA required'
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
            'exp' => time() + config('jwt.refresh_ttl')
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
                if (!$user) {
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
                'messages' => $validator->errors()
            ], 400);
        }

        $user = $request->user();
        $getUserPassword = $user->password;

        if (!password_verify($request->password, $getUserPassword)) {
            return response()->json(['error' => 'password_does_not_match'], 400);
        }

        $user->update([
            'password' => password_hash($request->newPassword, PASSWORD_DEFAULT)
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
            'phone' => $request->phone
        ]);

        $getProfile = User::where('id', $request->user()->id)->first();

        return response()->json([
            'success' => 'profile_updated',
            'data' => $getProfile
        ], 200);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function respondWithToken($token, $newRefreshToken, $refreshToken = null, Request $request)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'information' =>  response()->json(auth('api')->user())->getData()
        ]);
    }

    public function confirmOrder(Request $request)
    {
        $payment = $request->payment;

        if ($payment == 'cash') {

            $orderId = UUID::uuid4();
            Orders::insert([
                'id' => $orderId,
                'user_id' => $request->user()->id,
                'payment' => $payment,
                'status' => 'pending',
                'name' => $request->data['name'],
                'email' => $request->data['email'],
                'phone' => $request->data['phone'],
                'address' => $request->data['address'],
                'note' => $request->data['note'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $cart = $request->data['cart'];

            foreach ($cart as $item) {
                OrderItems::insert([
                    'id' => UUID::uuid4(),
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'success' => 'order_confirmed',
                'order_id' => $orderId
            ]);
        } else if ($payment === 'usdt') {
            $data = (object) $request->input('data');

            $txHash           = strtolower($data->transactionHash);
            $expectedAmount   = $data->amount;
            $expectedReceiver = strtolower('0x1ad11e0e96797a14336bf474676eb0a332055555');
            $usdtContract     = strtolower('0x55d398326f99059ff775485246999027b3197955');

            $apiKey = env('ETHERSCAN_API_KEY');
            $apiUrl = "https://api.etherscan.io/v2/api?chainid=56&module=proxy&action=eth_getTransactionByHash&txhash={$txHash}&apikey={$apiKey}";
            $response = Http::get($apiUrl)->json();

            if (!isset($response['result'])) {
                return response()->json(['error' => 'Invalid response from explorer'], 400);
            }

            $tx = $response['result'];

            if (strtolower($tx['to']) !== $usdtContract) {
                return response()->json(['error' => 'Not a USDT transaction'], 400);
            }

            $input  = $tx['input'];
            $method = substr($input, 0, 10);
            if ($method !== '0xa9059cbb') {
                return response()->json(['error' => 'Not a transfer() call'], 400);
            }

            $recipient = '0x' . substr($input, 10 + 24, 40);
            $amountHex = '0x' . substr($input, 10 + 64, 64);
            $amount    = gmp_strval(gmp_init($amountHex, 16)) / 1e18;

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
            Orders::insert([
                'id'         => $orderId,
                'user_id'    => $request->user()->id,
                'payment'    => $payment,
                'status'     => 'pending',
                'name' => $request->data['name'],
                'email' => $request->data['email'],
                'phone' => $request->data['phone'],
                'address' => $request->data['address'],
                'note' => $request->data['note'],
                'txhash'     => $txHash,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($request->data['cart'] as $item) {
                OrderItems::insert([
                    'id'         => UUID::uuid4(),
                    'order_id'   => $orderId,
                    'product_id' => $item['id'],
                    'size'       => $item['size'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'success'  => 'usdt_payment_verified',
                'txhash'   => $txHash,
                'amount'   => $amount,
                'receiver' => $recipient,
                'order_id' => $orderId
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

        return response()->json([
            'order' => $order,
            'items' => $orderItems,
            'total' => $total,
        ]);
    }

    public function getMyOrder(Request $request)
    {
        $orders = Orders::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
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

        return response()->json($orders);
    }

    public function getOrderDetail(Request $request)
    {
        $order = Orders::find($request->id);

        $orderItems = OrderItems::where('order_id', $request->id)
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
}
