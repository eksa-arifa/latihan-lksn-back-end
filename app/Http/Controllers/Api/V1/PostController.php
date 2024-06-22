<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Post_attachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                "page" => "integer|min:0",
                "size" => "integer|min:1"
            ]);

            if($validator->fails()){
                return response()->json([
                    "message" => "Invalid Field",
                    "errors" => $validator->errors()
                ], 422);
            }

            $page = request('page', 0);
            $size = request('size', 10);

            $posts = Post::with('user')->with('post_attachment')->paginate($size)->items();

            return response()->json([
                "page" => (int)$page,
                "size" => (int)$size,
                "posts" => $posts
            ]);
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                "caption" => "required",
                "attachments" => "required|image:jpg,jpeg,webp,png,gif"
            ]);

            if($validator->fails()){
                return response()->json([
                    "message" => "Invalid Field",
                    "errors" => $validator->errors()
                ], 422);
            }

            $uploadFolder = 'posts';
            $attachments = $request->file('attachments');
            $extension = $attachments->getClientOriginalExtension();

            $filename = time().'.'.$extension;

            $store = $attachments->storeAs('public/'.$uploadFolder, $filename);

            if(!$store){
                throw new Exception();
            }

            $createPost = Post::create([
                "caption" => $request->caption,
                "user_id" => $request->user()->id
            ]);

            Post_attachment::create([
                "storage_path" => "storage/".$uploadFolder."/".$filename,
                "post_id" => $createPost->id
            ]);


            return response()->json([
                "message" => "Create post success"
            ]);
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $post)
    {
        try{
            $post = Post::find($post);

            if(!$post){
                throw new Exception("Post not found", 404);
            }

            if($post->user_id != $request->user()->id){
                throw new Exception("Forbidden access", 403);
            }

            $attachments = Post_attachment::where('post_id', $post->id)->first();

            unlink($attachments->storage_path);

            $post->delete();

            return response()->json([
                "message" => "Delete Success"
            ], 204);
        }catch(Exception $e){
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
