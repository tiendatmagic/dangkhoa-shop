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
use Symfony\Component\HttpFoundation\Cookie;

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

    private function requestIsSecure(Request $request): bool
    {
        $secureEnv = env('AUTH_COOKIE_SECURE');
        if ($secureEnv !== null) {
            return (bool) filter_var($secureEnv, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->isSecure()) {
            return true;
        }

        $forwardedProto = strtolower((string) $request->header('X-Forwarded-Proto'));
        return $forwardedProto === 'https';
    }

    private function authCookieDomain(): ?string
    {
        $domain = env('AUTH_COOKIE_DOMAIN');
        return is_string($domain) && $domain !== '' ? $domain : null;
    }

    private function authCookieSameSite(Request $request): string
    {
        $sameSiteEnv = env('AUTH_COOKIE_SAMESITE');
        if (is_string($sameSiteEnv) && $sameSiteEnv !== '') {
            $sameSite = ucfirst(strtolower($sameSiteEnv));
            return in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Lax';
        }

        $origin = $request->headers->get('Origin');
        $originHost = $origin ? (parse_url($origin, PHP_URL_HOST) ?: null) : null;

        if ($this->requestIsSecure($request) && $originHost && $originHost !== $request->getHost()) {
            return 'None';
        }

        return 'Lax';
    }

    private function authCookieSecure(Request $request): bool
    {
        $secure = $this->requestIsSecure($request);
        if ($this->authCookieSameSite($request) === 'None') {
            return true;
        }
        return (bool) $secure;
    }

    private function makeAuthCookie(Request $request, string $name, string $value, int $minutes): Cookie
    {
        return cookie(
            $name,
            $value,
            $minutes,
            '/',
            $this->authCookieDomain(),
            $this->authCookieSecure($request),
            true,
            false,
            $this->authCookieSameSite($request),
        );
    }

    protected function respondWithToken($token, $newRefreshToken, Request $request)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $accessMinutes = (int) $guard->factory()->getTTL();
        $refreshMinutes = (int) config('jwt.refresh_ttl');

        return response()
            ->json([
                'token_type' => 'bearer',
                'expires_in' => $accessMinutes * 60,
                'information' => response()->json(auth('api')->user())->getData(),
            ])
            ->withCookie($this->makeAuthCookie($request, 'dangkhoa_access', (string) $token, $accessMinutes))
            ->withCookie($this->makeAuthCookie($request, 'dangkhoa_refresh', (string) $newRefreshToken, $refreshMinutes));
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
        $name = $user->full_name ?? $user->name ?? $user->email;
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
        $cacheEntry = Cache::get('email-' . $request->email);
        $cachedCode = is_array($cacheEntry) ? ($cacheEntry['code'] ?? null) : null;

        if (strlen($code) >= 6 && strlen($password) >= 8 && $cachedCode && $code == $cachedCode) {
            User::where('email', $request->email)->update([
                'password' => Hash::make($password)
            ]);
            Cache::forget('email-' . $request->email);
            return true;
        } else {
            return response()->json(['message' => false], 400);
        }
    }
}
