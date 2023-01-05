<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PostCommentUser;

class CommentController extends Controller
{

    public function index(Request $request)
    {


        $comment =  Comment::with('post', 'user')->get();
        if ($comment) {
            return response()->json([
                'data' => $comment
            ], 200);
        } else {
            return response()->json([
                'message' => 'No comment found'
            ], 404);
        }
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'comment' => 'required',
            'user_id' => 'required',
            'post_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        } else {

            $data = $request->all();

            // save only comment to database
            $comment = Comment::create(['comment' => $data['comment']]);

            // save post_id and user_id and comment id to PostCommentUser
            PostCommentUser::create([
                'post_id' => $data['post_id'],
                'user_id' => $data['user_id'],
                'comment_id' => $comment->id
            ]);


            return response()->json([
                'data' => $comment
            ], 200);
        }
    }
    public function show(Request $request, $id)
    {
        if ($request->user()->tokenCan('admin:comment_post')) {
            $comment = Comment::with('post', 'user')->find($id);

            if ($comment) {
                return response()->json([
                    'data' => $comment
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Comment not found'
                ], 404);
            }
        }
    }


    public function update(Request $request, $id)
    {

        $comment = Comment::with('user')->find($id);

        if ($comment) {
            if ($comment->user[0]->id == $request->user()->id) {
                $validator = Validator::make($request->all(), [
                    'comment' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
                } else {

                    $data = $request->all();
                    $comment->update($data);
                    return response()->json([
                        "data" => $comment,
                        "message" => "Comment updated successfully"
                    ], 204);
                }
            } else {
                return response()->json([
                    'message' => 'You are not authorized to access this route'
                ], 401);
            }
        } else {
            return response()->json([
                'message' => 'Comment not found'
            ], 404);
        }
    }


    public function destroy(Request $request, $id)
    {
        $comment = Comment::with('post', 'user')->find($id);
        if ($comment) {

            if ($request->user()->tokenCan('contributor:delete_post_comment') || $request->user()->tokenCan('admin:delete_post_comment')) {


                if ($comment->post[0]->user_id == $request->user()->id) {
                    $comment->delete();
                    return response()->json([
                        "message" => "Comment deleted successfully"
                    ], 204);
                }

                if ($comment->user[0]->id == $request->user()->id) {
                    $comment->delete();
                    return response()->json([
                        "message" => "Comment deleted successfully"
                    ], 204);
                }

                return response()->json([
                    'message' => 'You are not authorized to access this route'
                ], 401);
            }

            if ($comment->user[0]->id == $request->user()->id) {
                $comment->delete();
                return response()->json([
                    "message" => "Comment deleted successfully"
                ], 204);
            }

            return response()->json([
                'message' => 'You are not authorized to access this route'
            ], 404);
        } else {
            return response()->json([
                'message' => 'Comment not found'
            ], 404);
        }
    }
}
