<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function assign(Request $request, Template $template)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Only admin can assign permissions
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Only admins can assign permissions.'], 403);
        }

        $permission = Permission::create([
            'template_id' => $template->id,
            'user_id' => $request->user_id
        ]);

        return response()->json($permission, 201);
    }

    public function revoke(Request $request, Template $template, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Only admins can revoke permissions.'], 403);
        }

        Permission::where('template_id', $template->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Permission revoked successfully']);
    }

    public function index(Template $template)
    {
        $permissions = $template->permissions()
            ->with('user:id,name,email')
            ->get();

        return response()->json($permissions);
    }
} 