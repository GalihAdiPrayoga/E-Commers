<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Hanya user yang login yang bisa akses profile
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => $user,
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string',
        ]);
        $user = User::findOrFail($id);
        $user->syncRoles([$request->role]);
        return response()->json([
            'success' => true,
            'message' => 'Role updated',
            'user' => $user,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
