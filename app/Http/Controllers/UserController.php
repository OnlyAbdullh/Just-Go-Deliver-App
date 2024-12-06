<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get a list of all users",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="first_name", type="string", example="abdullah"),
     *                 @OA\Property(property="last_name", type="string", example="alksm"),
     *                 @OA\Property(property="email", type="string", example="abdallaalkasm9@gmail.com"),
     *                 @OA\Property(property="location", type="string", example="Damascus"),
     *                 @OA\Property(property="phone_number", type="string", example="0969090711"),
     *                 @OA\Property(property="fcm_token", type="string", example="3213"),
     *                 @OA\Property(property="role", type="string", example="user")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $users = $this->userService->getAllUsers();
        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get details of a specific user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=10),
     *             @OA\Property(property="first_name", type="string", example="abdullah"),
     *             @OA\Property(property="last_name", type="string", example="alksm"),
     *             @OA\Property(property="email", type="string", example="abdallaalkasm9@gmail.com"),
     *             @OA\Property(property="location", type="string", example="Damascus"),
     *             @OA\Property(property="phone_number", type="string", example="0969090711"),
     *             @OA\Property(property="fcm_token", type="string", example="3213"),
     *             @OA\Property(property="role", type="string", example="user")
     *         )
     *     )
     * )
     */
    public function show(User $user)
    {
        $userDetails = $this->userService->getUserDetails($user);
        return response()->json($userDetails);
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update a user's information",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string", example="abdullah"),
     *             @OA\Property(property="last_name", type="string", example="alksm"),
     *             @OA\Property(property="email", type="string", example="abdallaalkasm9@gmail.com"),
     *             @OA\Property(property="location", type="string", example="Damascus"),
     *             @OA\Property(property="phone_number", type="string", example="0969090711"),
     *             @OA\Property(property="fcm_token", type="string", example="3213"),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated user details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=10),
     *             @OA\Property(property="first_name", type="string", example="abdullah"),
     *             @OA\Property(property="last_name", type="string", example="alksm"),
     *             @OA\Property(property="email", type="string", example="abdallaalkasm9@gmail.com"),
     *             @OA\Property(property="location", type="string", example="Damascus"),
     *             @OA\Property(property="phone_number", type="string", example="0969090711"),
     *             @OA\Property(property="fcm_token", type="string", example="3213"),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     )
     * )
     */
    public function update(Request $request, User $user)
    {
        $updatedUser = $this->userService->updateUser($user, $request->all());
        return response()->json($updatedUser);
    }

    /**
     * @OA\Post(
     *     path="/users/{id}/upload",
     *     summary="Upload a profile image for a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image",
     *                     description="Profile image file",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="path", type="any path")
     *         )
     *     )
     * )
     */
    public function storeImage(User $user, Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $image = $request->file('image');

        if (!$image) {
            return JsonResponseHelper::errorResponse('No image was uploaded or the image is invalid');
        }

        $imageName = 'profile_' . time() . '.' . $image->getClientOriginalExtension();

        $path = storage_path('app/profiles/' . $imageName);

        $image->move(storage_path('app/profiles'), $imageName);
        $user->image = $path;
        $user->save();
        return JsonResponseHelper::successResponse('Image has been saved successfully', $path);
    }
}
