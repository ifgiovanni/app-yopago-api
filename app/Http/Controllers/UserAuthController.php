<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;
use App\Models\LogLogin;

class UserAuthController
{
    //
    public function register(Request $request){

        try {
            $registerUserData = $request->validate([
                'name'=>'required|string',
                'email'=>'required|string|email|unique:users',
                'password'=>'required|min:8'
            ]);
            
            $user = User::create([
                'name' => $registerUserData['name'],
                'email' => $registerUserData['email'],
                'password' => Hash::make($registerUserData['password']),
            ]);
            return response()->json([
                'message' => 'User Created ',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'User Creation Failed',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function login(Request $request){
        $loginUserData = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);
        $user = User::where('email', $loginUserData['email'])->first();
        if(!$user || !Hash::check($loginUserData['password'],$user->password)){
            
            $log = new LogLogin();
            $log->user_id = $user->id;
            $log->ip = $request->ip();
            $log->user_agent = $request->header('User-Agent');
            $log->login_at = now();
            $log->result = 'WRONG CREDENTIALS';
            $log->save();

            return response()->json([
                'message' => 'El usuario y/o contraseña son incorrectos'
            ], 401);
        }
        
        $token = $user->createToken(
            $user->name.'-AuthToken', ['*'], now()->addWeek()
        )->plainTextToken;

        $user_role = UserRole::where('user_id', $user->id)->first();

        $log = new LogLogin();
        $log->user_id = $user->id;
        $log->ip = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->login_at = now();
        $log->result = 'SUCCESS';
        $log->save();

        return response()->json([
            'access_token' => $token,
            'role' => $user_role->role->code,
            'token_type' => 'Bearer',
            'name' => $user->name,
            'email' => $user->email,
            'referral_code' => $user->referral_code,
        ]);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
    
        return response()->json([
          "message"=>"logged out"
        ]);
    }
}
