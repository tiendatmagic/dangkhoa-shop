<?php

namespace App\Http\Controllers;

use App\Models\Bags;
use App\Models\EveryDays;
use App\Models\Items;
use App\Models\LoginHistory;
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

class RegisterController extends BaseController
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

    protected function respondWithToken($token, $newRefreshToken, Request $request)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'information' =>  response()->json(auth('api')->user())->getData()
        ]);
    }

    public function register(Request $request)
    {
        if (strlen($request->password) >= 8) {

            if (!User::where('email', $request->email)->first()) {
                $characters = '0123456789';
                User::insert([
                    'id' => Uuid::uuid4(),
                    'name' => 'User' . $this->generateRandomString($characters, 5),
                    'full_name' => 'User' . $this->generateRandomString($characters, 5),
                    'password' => Hash::make($request->password),
                    'email' => $request->email,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $credentials = request(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            $refreshToken = $this->createRefreshToken();
            return $this->respondWithToken($token, $refreshToken, request());
        } else {
            return "FAILED";
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

    public function checkEmailExists(Request $request)
    {
        if (User::where('email', $request->email)->first()) {
            return "true";
        } else {
            return "false";
        }
    }

    public function forgotPassword(Request $request)
    {
        $getEmail = $request->email;
        $characters = '0123456789';
        $user = User::where('email', $getEmail)->first();
        if (!$user) {
            return response()->json(['message' => false], 400);
        }
        $name = $user->username;
        $code = $this->generateRandomString($characters, 6);;
        Cache::put('email-' . $getEmail, [
            'code' => $code
        ], 300);
        Mail::send('emails.forgot-password', compact('getEmail', 'name', 'code'), function ($email) use ($name, $code, $getEmail) {
            $email->subject('Mã xác nhận khôi phục mật khẩu');
            $email->to($getEmail, $name, $code);
        });
        return true;
    }

    public function resetPassword(Request $request)
    {
        //
        $code = $request->code;
        $password = $request->password;
        // return  $request->code;
        if (strlen($code) >= 6 && strlen($password) >= 8 && $code == Cache::get('email-' . $request->email)['code']) {
            User::where('email', $request->email)->update([
                'password' => Hash::make($password)
            ]);
            return true;
        } else {
            return response()->json(['message' => false], 400);
        }
    }
}
