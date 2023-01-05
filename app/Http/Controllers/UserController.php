<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check if data exist
        if ($request->user()->tokenCan('admin:see_profile')) {
            if (User::count() > 0) {
                // Get all data
                $users = User::all();
                // Return response
                return response()->json(['data' => $users], 200);
            } else {
                // Return response
                return response()->json([
                    "message" => "No users found"
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'You are not authorized to access this route'
            ], 401);
        }
    }



    public function store(Request $request)
    {
        // only admin can see all users
        if ($request->user()->tokenCan('admin:see_profile')) {
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

                // Return response
                return response()->json($user, 201);
            }
        } else {
            return response()->json([
                'message' => 'You are not authorized to access this route'
            ], 401);
        }
    }




    public function show($id, Request $request)
    {

        if ($request->user()->tokenCan("admin:see_profile")) {
            // Check if user exists
            if (User::where('id', $id)->exists()) {
                // Get user by its id
                $user = User::where('id', $id)->get();
                // Return response
                return response($user, 200);
            } else {
                // Return response
                return response()->json([
                    "message" => "User not found"
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'You are not authorized to access this route'
            ], 401);
        }
    }




    public function update(Request $request, $id)
    {

        if ($request->user()->tokenCan("user:edit_profile") || $request->user()->tokenCan("contributor:edit_profile")) {

            if ($request->user()->id == $id) {
                $userEmail = User::where('id', $id)->first()->email;

                // If email user now is same with email user request
                if ($userEmail == $request->email) {
                    $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'email' => 'required|email',
                        'password' => 'required',
                    ]);
                } else {
                    $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required',
                    ]);
                }


                // If validation fails
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 401);
                } else {
                    // Get all validated data
                    $data = $request->all();
                    // Encrypt password
                    $data['password'] = bcrypt($request->password);

                    // Updating data to database using try and catch
                    try {
                        $user = User::find($id);
                        // Check if user exists
                        if ($user) {
                            // Update user
                            $user->update($data);
                            // Return response
                            return response()->json([
                                "message" => "Update successfully"
                            ], 200);
                        } else {
                            // Return error message
                            return response()->json([
                                "message" => "User not found"
                            ], 404);
                        }
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Return error message
                        return response()->json([
                            "message" => $e
                        ], 500);
                    }
                }
            } else {
                return response()->json([
                    'message' => 'You are not authorized to access this route'
                ], 401);
            }
        }

        if ($request->user()->tokenCan("admin:update_profile")) {
            $userEmail = User::where('id', $id)->first()->email;

            // If email user now is same with email user request
            if ($userEmail == $request->email) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|email',
                    'password' => 'required',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
                ]);
            }


            // If validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 401);
            } else {
                // Get all validated data
                $data = $request->all();
                // Encrypt password
                $data['password'] = bcrypt($request->password);

                // Updating data to database using try and catch
                try {
                    $user = User::find($id);
                    // Check if user exists
                    if ($user) {
                        // Update user
                        $user->update($data);
                        // Return response
                        return response()->json([
                            "message" => "Update successfully"
                        ], 200);
                    } else {
                        // Return error message
                        return response()->json([
                            "message" => "User not found"
                        ], 404);
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    // Return error message
                    return response()->json([
                        "message" => $e
                    ], 500);
                }
            }
        }



        // Check email user now

    }


    public function destroy($id)
    {
        // Check if user exists
        if (User::where('id', $id)->exists()) {
            // Get user by its id
            $user = User::find($id);
            // Delete user using try and catch

            $user->delete();
            // Return response
            return response()->json([
                "message" => "User deleted"
            ], 202);
        } else {
            // Return response
            return response()->json([
                "message" => "User not found"
            ], 404);
        }
    }
}
