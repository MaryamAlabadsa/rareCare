<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;

class AuthController extends Controller
{
    protected function apiResponse($status, $code, $message, $data = null, $errors = null)
    {
        return response()->json([
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
        ], $code);
    }

    // Register
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role'     => 'required|string',
                'dob'      => 'date',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
               'dob'      => $request->dob,
               'avatar_url' => $request->avatar_url ?? 'default-avatar.png',

            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->apiResponse('success', 201, 'User registered successfully', [
                'user'  => $user,
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return $this->apiResponse('error', 422, 'Validation error', null, $e->errors());
        } catch (QueryException $e) {
            return $this->apiResponse('error', 500, 'Database error', null, ['db' => $e->getMessage()]);
        } catch (Exception $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->apiResponse('error', 401, 'The provided credentials are incorrect');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->apiResponse('success', 200, 'Login successful', [
                'user'  => $user,
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return $this->apiResponse('error', 422, 'Validation error', null, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return $this->apiResponse('success', 200, 'Logged out successfully');
        } catch (Exception $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
        }
    }

    // Get authenticated user
    public function profile(Request $request)
    {
        try {
            return $this->apiResponse('success', 200, 'Profile fetched successfully', $request->user());
        } catch (Exception $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
        }
    }

    // Change Password
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password'      => 'required|string',
                'new_password'          => 'required|string|min:6|confirmed',
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->apiResponse('error', 403, 'Current password is incorrect');
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return $this->apiResponse('success', 200, 'Password changed successfully');
        } catch (ValidationException $e) {
            return $this->apiResponse('error', 422, 'Validation error', null, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse('error', 500, 'Unexpected error', null, ['exception' => $e->getMessage()]);
        }
    }
}

