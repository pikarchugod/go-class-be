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
    // 1. 註冊
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        // 可直接幫註冊者產生 Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registered',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }

    // 2. 登入
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login success',
            'access_token' => $token
        ]);
    }

    // 3. 登出 (需帶 Token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // 1. 寄送重設密碼郵件
    public function sendResetLink(Request $request)
    {
    $request->validate(['email' => 'required|email']);
    $status = Password::sendResetLink(['email' => $request->email]);

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Reset link sent'])
        : response()->json(['message' => 'Error sending reset link'], 400);
    }

    // 2. 重設密碼 (透過郵件連結帶過來的 token)
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
    
        // 1. 引導使用者去 Google 登入
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // 2. Google callback (取得使用者資料)
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // 查看是否已存在
        $user = User::where('email', $googleUser->getEmail())->first();

        // 若無，建立新用戶
        if (!$user) {
            $user = User::create([
                'name'  => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // 隨機或其他方式
            ]);
        }

        // 簽發 Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 依需求回傳 Token 或重導回前端
        return response()->json([
            'message' => 'Google login success',
            'token'   => $token,
            'user'    => $user
        ]);
    }


   
}

