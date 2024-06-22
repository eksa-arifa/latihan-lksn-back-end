<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'username'  => 'required|unique:users|min:3|regex:/^[a-zA-Z0-9._]+$/',
                'password' => 'required|min:6',
                'bio' => 'required|max:100',
                'is_private' => 'required|boolean'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    "message" => "Invalid Field",
                    "errors" => $validator->errors()
                ], 422);
            }

            $input = $request->all();
            $input['password'] = Hash::make($request->password);
    
            $create = User::create($input);
    
            $token = $create->createToken('authentication')->plainTextToken;

            return response()->json([
                "message" => "Register Success",
                "token" => $token,
                "user" => $create
            ]);
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function login(Request $request)
    {
        try{
            if(Auth::attempt(["username"=>$request->username, "password" => $request->password])){
                $user = User::where("username", $request->username)->first();

                $token = $user->createToken('authentication')->plainTextToken;

                return response()->json([
                    "message" => "Login Success",
                    "token" => $token,
                    "user" => $user
                ]);
            }else{

                return response()->json([
                    "message" => "Wrong Username Or Password"
                ], 401);
            }



        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function logout(Request $request)
    {
        try{
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "message" => "Logout Success"
            ]);
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }
}
