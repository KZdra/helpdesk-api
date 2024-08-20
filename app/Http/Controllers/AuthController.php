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

    
     public function getUsers() {
        $users = DB::table('users')->select('id', 'name', 'email', 'role_id')->get();
    
        foreach ($users as $user) {
            $role = DB::table('roles')->where('id', $user->role_id)->value('name');
            $user->role = $role;
        }
    
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
    
    public function getRoles(){
        $role = DB::table('roles')->get();
        if ($role) {
            return response()->json($role);
        } else {
            return response()->json(['message' => 'role not found'], 404);
        }

    }
    //  public function register()
    // {
    //     $validate = Validator::make(request()->all(), [
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required',

    //     ]);

    //     if ($validate->fails()) {
    //         return response()->json($validate->messages());
    //     }

    //     $user = User::create([
    //         'name' => request('name'),
    //         'email' => request('email'),
    //         'role' => 'client',
    //         'password' => Hash::make(request('password')),
    //     ]);

    //     if ($user) {
    //         return response()->json(['message' => 'Registrasi Sukses']);
    //     } else {
    //         return response()->json(['message' => 'Gagal']);
    //     }
    // }

    
    public function updateUser($id)
    {
        // Validate the input data
        $validate = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required',
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
                'role_id' => request('role_id'),
            ];
    
            if (request('password')) {
                $data['password'] = Hash::make(request('password'));
            }
    
            $updated = DB::table('users')
                ->where('id', $id)
                ->update($data);
    
            if ($updated) {
                DB::commit(); 
                return response()->json(['message' => 'User updated successfully']);
            } 
        } catch (\Exception $e) {
            DB::rollBack(); 
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
        $role = DB::table('roles')->where('id', $user->role_id)->value('name');
        $user->role =$role;
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
        $user= auth()->user();
        $role = DB::table('roles')->where('id', $user->role_id)->value('name');
        $user->role =$role;
        return $this->respondWithToken(auth()->refresh(),$user);
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
