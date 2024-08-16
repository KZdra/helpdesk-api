<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    
    public function getUsers(){
        $users = DB::table('users')->select('id','name','role','email')->get();
        return response()->json($users);
    }
    public function getUser($id) {
        $user = DB::table('users')->where('id', $id)->first();
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }

    }
    
    
     public function register()
    {
        $validate = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',

        ]);

        if ($validate->fails()) {
            return response()->json($validate->messages());
        }

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'role' => 'client',
            'password' => Hash::make(request('password')),
        ]);

        if ($user) {
            return response()->json(['message' => 'Registrasi Sukses']);
        } else {
            return response()->json(['message' => 'Gagal']);
        }
    }

    
    public function updateUser($id)
    {
        // Validate the input data
        $validate = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required',
        ]);
    
        // If validation fails, return the error messages
        if ($validate->fails()) {
            return response()->json($validate->messages(), 400);
        }
    
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Find the user by ID
            $user = DB::table('users')->where('id', $id)->first();
    
            // If user not found, return an error message
            if (!$user) {
                DB::rollBack(); // Rollback the transaction
                return response()->json(['message' => 'User not found'], 404);
            }
    
            // Prepare data for updating
            $data = [
                'name' => request('name'),
                'email' => request('email'),
                'role' => request('role'),
            ];
    
            // If password is provided, hash it and include in the update data
            if (request('password')) {
                $data['password'] = Hash::make(request('password'));
            }
    
            // Update the user in the database
            $updated = DB::table('users')
                ->where('id', $id)
                ->update($data);
    
            // Check if the update was successful
            if ($updated) {
                DB::commit(); // Commit the transaction
                return response()->json(['message' => 'User updated successfully']);
            } else {
                DB::rollBack(); // Rollback the transaction
                return response()->json(['message' => 'Failed to update user'], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on error
            return response()->json(['message' => 'Failed to update user', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function deleteUser($id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($user->delete()) {
       return response()->json(['message'=> 'USER DELETED!']);
    } else {
        return response()->json(['message' => 'Failed to Delete User'], 500);

    }
}
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user= auth()->user();
        return $this->respondWithToken($token,$user);
    }
    
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function me()
    // {
    //     return response()->json(auth()->user());
    // }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh(),auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token ,$user)
    {
        return response()->json([
            'access_token' => $token,
            'user'=> $user,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1440
        ]);
    }
}
