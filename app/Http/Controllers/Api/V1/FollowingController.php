<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class FollowingController extends Controller
{
    public function follow(Request $request, $username)
    {
        try{
            $user = User::where('username', $username)->first();

            if(!$user){
                throw new Exception("User not found", 404);
            }

            if($user->id == $request->user()->id){
                throw new Exception("You are not allowed to follow yourself", 422);
            }

            $checkfollowing = Follow::where('follower_id', $request->user()->id)->where('following_id', $user->id)->first();

            if($checkfollowing){
                return response()->json([
                    "message" => "You are already followed",
                    "status" => ($checkfollowing->is_accepted)?"Following":"Requested"
                ], 422);
            }

            $status = ($user->is_private)?0:1;

            Follow::create([
                'follower_id' => $request->user()->id,
                'following_id' => $user->id,
                'is_accepted' => $status
            ]);

            return response()->json([
                "message" => "Follow success",
                "status" => ($status)?"Following":"Requested"
            ]);
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function unfollow(Request $request, $username)
    {
        try{
            $user = User::where('username', $username)->first();

            if(!$user){
                throw new Exception("User not found", 404);
            }

            $checkfollowing = Follow::where('follower_id', $request->user()->id)->where('following_id', $user->id)->first();

            if(!$checkfollowing){
                throw new Exception("You are not following the user", 422);
            }

            $deleteFollowing = $checkfollowing->delete();

            if($deleteFollowing){
                return response()->json([
                    "message" => "Unfollow success"
                ], 204);
            }else{
                throw new Exception("Unexpected Error", 500);
            }
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
