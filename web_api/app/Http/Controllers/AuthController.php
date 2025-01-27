<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile()
    {
        return response()->json(auth('api')->User());
    }
    public function registerUser(Request $request)
    {
        if (User::where('username', $request->name)->exists()) {
            return response()->json(['error' => 'Username đã tồn tại'], 400);
        }
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email đã tồn tại'], 400);
        }

        if (User::where('phone_number', $request->phone_number)->exists()) {
            return response()->json(['error' => 'Sdt đã tồn tại'], 400);
        }
        
        // Tạo người dùng với role_id là 2 (user)
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email, 
            'password' => Hash::make($request->password),
            'role_id' => 2,
            'full_name' => $request->full_name,
            'birth_year' => $request->birth_year,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);
    
        return response()->json(['user' => $user], 201);
    }

    public function registerAdmin(Request $request)
    {
        // Kiểm tra quyền đăng ký admin (chỉ admin mới có thể tạo tài khoản admin)
        if (auth('api')->User()->role_id != 1) {
            return response()->json(['error' => 'Không có quyền tạo admin, ktra lại thông tin người dùng trong data'], 403);
        }

        if (User::where('username', $request->name)->exists()) {
            return response()->json(['error' => 'Username đã tồn tại'], 400);
        }
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email đã tồn tại'], 400);
        }

        // Tạo người dùng với role_id là 1 (admin)
        $admin = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 1, 
            'full_name' => $request->full_name,
            'birth_year' => $request->birth_year,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);

        return response()->json(['user' => $admin], 201);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'reset_token' => 'required|string'
        ]);

        $resetData = Cache::get('password-reset-' . $request->email);

        if (!$resetData || $resetData != $request->reset_token) {
            return response()->json(['error' => 'Token không hợp lệ'], 400);
        }
        // Cập nhật mật khẩu mới
        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();
        Cache::forget('password-reset-' . $request->email);

        return response()->json(['message' => 'Mật khẩu đã được thay đổi thành công']);
    }
}
