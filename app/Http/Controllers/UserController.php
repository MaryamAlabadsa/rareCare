<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
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

    public function index()
    {
        $users = User::all();
        return $this->apiResponse('success', 200, 'Users retrieved successfully', $users);
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->apiResponse('success', 200, 'User retrieved successfully', $user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse('error', 404, 'User not found');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name'  => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'role'  => 'sometimes|string',
                'dob'   => 'sometimes|date',
            ]);

            $user->update($request->only('name', 'email', 'role', 'dob'));

            return $this->apiResponse('success', 200, 'User updated successfully', $user);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse('error', 404, 'User not found');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponse('error', 422, 'Validation failed', null, $e->errors());
        }
    }

    public function destroy($id)
    {
        try {
            User::findOrFail($id)->delete();
            return $this->apiResponse('success', 200, 'User deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse('error', 404, 'User not found');
        }
    }
}
