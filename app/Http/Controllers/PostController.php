<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{

    public function index(Request $request)
    {

        // Select all posts with user data
        $posts = Post::with(['user' => function ($query) {
            $query->select('id', 'name', 'email');
        }]);

        // Sort by title or date
        if ($sort = $request->query('sortBy')) {
            // Sort by title
            if ($sort == 'title') {
                $posts->orderBy('title');
            }
            // Sort by date
            if ($sort == 'date') {
                $posts->orderBy('created_at');
            }
        }

        // Search by title
        if ($search = $request->query('search')) {
            $posts->where('title', 'like', '%' . $search . '%');
        }


        $posts = $posts->get();


        if ($posts->isNotEmpty()) {
            return response()->json([
                'data' => $posts
            ], 200);
        } else {
            return response()->json([
                'message' => 'No post found'
            ], 404);
        }
    }


    public function store(Request $request)
    {


        // validate if user is authorized to create post
        if ($request->user()->tokenCan('admin:add_post') || $request->user()->tokenCan('contributor:add_post')) {
            // Validate all request data
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'body' => 'required',
                'user_id' => 'required',
            ]);

            // If validation fails
            if ($validator->fails()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 401);
            } else {
                $data = $request->all();
                $post = Post::create($data);
                return response()->json([
                    'data' => $post,
                    'message' => 'Post created successfully',
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'You are not authorized to access this route'
            ], 401);
        }
    }


    public function show($id)
    {
        // Get data by its id
        $post = Post::with('user')->find($id);

        // Check if post exists
        if ($post) {
            return response()->json([
                'post' => $post
            ], 200);
        } else {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }
    }


    public function update(Request $request, $id)
    {
        // Check if user authorized to update post
        if ($request->user()->tokenCan('contributor:edit_post')) {
            $post = Post::with('user')->find($id);

            if ($post) {
                if ($request->user()->id == $post->user_id) {

                    // If post exists
                    $validator = Validator::make($request->all(), [
                        'title' => 'required',
                        'body' => 'required',
                    ]);

                    // If validation fails
                    if ($validator->fails()) {
                        return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 401);
                    } else {


                        // Update post
                        $post->update([
                            'title' => $request->title,
                            'body' => $request->body,
                        ]);

                        return response()->json([
                            'data' => $post,
                            'message' => 'Post updated successfully',
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'You are not authorized to access this route'
                    ], 401);
                }
            } else {
                return response()->json([
                    'message' => 'Post not found'
                ], 404);
            }
        }

        if ($request->user()->tokenCan('admin:edit_post')) {
            $post = Post::with('user')->find($id);
            // If post exists
            if ($post) {
                // Validate all request data
                $validator = Validator::make($request->all(), [
                    'title' => 'required',
                    'body' => 'required',
                ]);

                // If validation fails
                if ($validator->fails()) {
                    return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 401);
                } else {


                    // Update post
                    $post->update([
                        'title' => $request->title,
                        'body' => $request->body,
                    ]);

                    return response()->json([
                        'data' => $post,
                        'message' => 'Post updated successfully',
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Post not found'
                ], 404);
            }
        }

        return response()->json([
            'message' => 'You are not authorized to access this route'
        ], 401);
    }


    public function destroy(Request $request, $id)
    {

        // Check if user authorized to delete post
        if ($request->user()->tokenCan('contributor:edit_post')) {
            $post = Post::with('user')->find($id);

            if ($post) {
                if ($request->user()->id == $post->user_id) {

                    $post->delete();
                    return response()->json([
                        'message' => 'Post deleted successfully'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'You are not authorized to access this route'
                    ], 401);
                }
            } else {
                return response()->json([
                    'message' => 'Post not found'
                ], 404);
            }
        }

        if ($request->user()->tokenCan('admin:edit_post')) {
            $post = Post::with('user')->find($id);
            // If post exists
            if ($post) {
                // Validate all request data
                $post->delete();
                return response()->json([
                    'message' => 'Post deleted successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Post not found'
                ], 404);
            }
        }

        return response()->json([
            'message' => 'You are not authorized to access this route'
        ], 401);
    }
}
