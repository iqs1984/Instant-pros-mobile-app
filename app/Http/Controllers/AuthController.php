<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Validator;
class AuthController extends Controller
{
    public function _construct(){
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }
    public function register(Request $request)
    {

        if($request->role == 'user'){

            $validator = Validator::make($request->all(), [
                'role' => 'required|string',
                'name' => 'required',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed|min:6',
                'phone' => 'required|integer',
                'address' => 'required|string',
            ]);
    
           
        }else if($request->role == 'vendor'){

            $validator = Validator::make($request->all(), [
                'role' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed|min:6',
                'category' => 'required|string',
                'business_logo' => 'required',
                'business_name' => 'required',
                'country' => 'required|string',
                'state' => 'required|string',
                'zip_code' => 'required|integer',
                
            ]);
        }

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => ucwords($request->role).' Successfully Register',
            'user' => $user,
        ],200);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            'email' => 'required|email',
            'password' => 'required|string|min:6',
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        if(!$token = auth()->attempt($validator->validated())){
            return response()->json(['error' => 'Unauthorized'],401);
            
        }

        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expire_in' => auth()->factory()->getTTL()*60,
            'user'=>auth()->user()
        ]);
    }
}