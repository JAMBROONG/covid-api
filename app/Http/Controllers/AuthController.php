<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    // fungsi login sanctum
    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_OK);
        }else {
            $credentials = request(["email", "password"]);
            $credentials = Arr::add($credentials, "status", "active");
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    "message" => "Unauthorized!"
                ], Response::HTTP_UNAUTHORIZED);
            }
                $user = User::where("email", $request->email)->first();
                if (!$user || !Hash::check($request->password, $user->password, [])) {
                    return response()->json([
                        "message" => "Unauthorized!"
                    ], Response::HTTP_UNAUTHORIZED);
                }

                $token = $user->createToken("token-name")->plainTextToken;

                    return response()->json([
                        "message" => "Login successfully!",
                        "user" => $user,
                        "token" => $token,
                        "type" => "Bearer"
                    ], Response::HTTP_OK);
        }
    }

// fungsi logout dan hapus bearer akses token yang sebelumnya login
// masukkan data token "Bearer (spasi) token yang di dapat saat login" pada header Authorization
    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "Logout successfully!"
        ], Response::HTTP_OK);
    }

    function logout_all(Request $request)
    {

    }
}
