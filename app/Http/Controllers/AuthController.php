<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;


class AuthController extends Controller
{
    /**
     * 使用者註冊（Token 模式）
     * 前端可傳送 { name, email, password }
     * 回傳 { token, user }
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        // 建立使用者
        $user = User::create([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 產生 Sanctum Personal Access Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registered',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }

    /**
     * 使用者登入（Token 模式）
     * 前端可傳送 { email, password }
     * 回傳 { token, user }
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // 先用 email 找 user
        $user = User::where('email', $validated['email'])->first();
        // 驗證密碼
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // 建立 token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    /**
     * 使用者登出（需帶 Bearer Token）
     * 前端帶 Authorization: Bearer <token>
     */
    public function logout(Request $request)
    {
        // 刪除當前 token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * 寄送重設密碼郵件
     * 前端傳 { email }
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(['email' => $request->email]);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent'])
            : response()->json(['message' => 'Error sending reset link'], 400);
    }

    /**
     * 重設密碼 (透過郵件連結帶過來的 token)
     * 前端傳 { email, token, password, password_confirmation }
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset success'])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * Google OAuth 登入 - 1. 引導使用者去 Google
     */
    public function redirectToGoogle()
    {
        // 如果 stateless 報錯，可先移除 stateless()
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Google OAuth 登入 - 2. Google callback
     * 回傳 { token, user }
     */
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // 找或建立使用者
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'password' => bcrypt(Str::random(16)), // 隨機密碼
            ]
        );

        // 建立 Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Google login success',
            'token'   => $token,
            'user'    => $user
        ]);
    }
}
