<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use App\Services\RoleService;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }


    public function store(RoleRequest $request)
    {
       
        $result =  $this->roleService->assignRoleForUser($request->user_id,$request->role);
        return response()->json(['message' => 'Role assigned successfully', 'status_code' => 200], 200);

        if (!$result) {
            return response()->json(['message' => 'User not found', 'status_code' => 404], 404);
        }

    }

    public function delete(RoleRequest $request)
    {
        $result =  $this->roleService->revokeRoleForUser($request->user_id, $request->role);
        if (!$result) {
            return response()->json(['message' => 'User not found', 'status_code' => 404], 404);
        }
        return response()->json(['message' => 'Role revoked successfully', 'status_code' => 200], 200);
    }
}
