<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate all request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 401);
        } else {

            /// Get all validated data
            $data = $request->all();
            // Encrypt password
            $data['password'] = bcrypt($request->password);

            // Save data to database
            $user = User::create($data);




            $response = [
                'data' => $user,

            ];


            // Return response
            return response()->json($response, 201);
        }
    }

    public function login(Request $request)
    {
        // Validate all request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 401);
        } else {

            // Get all validated data
            $data = $request->all();

            // Check if user exists
            $user = User::where('email', $data['email'])->first();

            // If user exists
            if ($user) {
                // Check if password matches
                if (password_verify($data['password'], $user->password)) {
                    // Create token

                    // check if token already existed 
                    $token = $user->tokens()->where('tokenable_id', $user->id)->first();
                    if ($token) {

                        // delete token if existed
                        $token->delete();
                    }



                    if ($user->role_id == '1') {
                        // for user
                        $token = $user->createToken('auth_token', ["user:edit_profile", "user:see_post", "user:comment_post", "user:edit_comment", "user:delete_comment", "user:show_profile"])->plainTextToken;
                    };

                    if ($user->role_id == '2') {
                        // for contributor
                        $token = $user->createToken('auth_token', ["contributor:edit_profile", "contributor:see_post", "contributor:comment_post", "contributor:edit_comment", "user:delete_comment", "contributor:delete_post_comment", "contributor:edit_post", "contributor:delete_post", "contributor:add_post"])->plainTextToken;
                    };

                    if ($user->role_id == '3') {
                        // for contributor
                        $token = $user->createToken('auth_token', ["admin:see_profile", "admin:edit_profile", "admin:see_post", "admin:comment_post", "admin:edit_comment", "admin:delete_comment", "admin:delete_post_comment", "admin:edit_post", "admin:delete_post", "admin:delete_profile", "admin:edit_profile", "admin:add_profile", "admin:add_post"])->plainTextToken;
                    };



                    // generate new token


                    $response = [
                        'user' => $user,
                        'token' => $token
                    ];

                    // Return response
                    return response()->json($response, 201);
                } else {
                    // Return error response
                    return response()->json(['message' => 'Invalid credentials'], 401);
                }
            } else {
                // Return error response
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        }
    }


    public function logout(Request $request)
    {
        // Revoke token
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }
}
