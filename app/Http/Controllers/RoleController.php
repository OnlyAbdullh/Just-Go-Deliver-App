<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
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

    /**
     * @OA\Post(
     *     path="/users/assign-role",
     *     summary="Assign a role to a user",
     *     description="Assign a specific role to a user. Only accessible by managers.",
     *     tags={"Roles"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         description="Role assignment data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"role", "user_id"},
     *                 @OA\Property(property="role", type="string", example="admin", description="The role name to assign"),
     *                 @OA\Property(property="user_id", type="integer", example=1, description="The ID of the user to whom the role will be assigned")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Role assigned successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Only manager can assign roles"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     )
     * )
     */


    public function store(RoleRequest $request)
    {

        if (!Gate::allows('assign-role', User::class)) {
            return JsonResponseHelper::errorResponse(__('messages.only_manager_can_assign_roles'), [], 403);
        }
        $result =  $this->roleService->assignRoleForUser($request->user_id, $request->role);

        if ($result === 'has role') {
            return JsonResponseHelper::successResponse(__('messages.role_already_assigned'));
        } else if (!$result) {
            return JsonResponseHelper::errorResponse(__('messages.user_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.role_assign_success'));
    }

    /**
     * @OA\Post(
     *     path="/users/revoke-role",
     *     summary="Revoke a role from a user",
     *     description="Revoke a specific role from a user. Only accessible by managers.",
     *     tags={"Roles"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         description="Role revocation data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"role", "user_id"},
     *                 @OA\Property(property="role", type="string", example="admin", description="The role name to revoke"),
     *                 @OA\Property(property="user_id", type="integer", example=1, description="The ID of the user from whom the role will be revoked")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role revoked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Role revoked successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only manager can revoke roles"),
     *             @OA\Property(property="status_code", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example="false"),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
     *         )
     *     )
     * )
     */
    public function delete(RoleRequest $request)
    {
        if (!Gate::allows('revokeRole', User::class)) {
            return JsonResponseHelper::errorResponse(__('messages.only_manager_can_revoke_roles'), [], 403);
        }

        $result =  $this->roleService->revokeRoleForUser($request->user_id, $request->role);

        if ($result === 'has not role') {
            return JsonResponseHelper::successResponse(__('messages.role_already_revoked'));
        } else if (!$result) {
            return JsonResponseHelper::errorResponse(__('messages.user_not_found'), [], 404);
        }
        return JsonResponseHelper::successResponse(__('messages.role_revoke_success'));
    }
}
