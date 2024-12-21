<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Fetch paginated list of categories",
     *     description="Retrieve a paginated list of categories with their details, including the translated name based on the selected language.",
     *     operationId="getCategories",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\Parameter(
     *         name="items",
     *         in="query",
     *         description="The number of items to display per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CategoryResource")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(
     *                     property="currentPage",
     *                     type="integer",
     *                     description="The current page number",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="totalPages",
     *                     type="integer",
     *                     description="The total number of pages",
     *                     example=5
     *                 ),
     *                 @OA\Property(
     *                     property="hasMorePage",
     *                     type="boolean",
     *                     description="Indicates if there are more pages available",
     *                     example=true
     *                 )
     *             ),
     *            @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No categories available",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="There are no categories available"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */



    public function index(Request $request)
    {
        $items = $request->query('items');

        $categories = $this->categoryService->getAllCategories($items);

        if (!$categories) {
            return JsonResponseHelper::successResponse(__('messages.no_category_available'));
        }

        return response()->json([
            'successful' => true,
            'message' => __('messages.category_fetched'),
            'data' => [
                'categories' => CategoryResource::collection($categories)
            ],
            'pagination' => [
                'currentPage' => $categories->currentPage(),
                'totalPages' => $categories->lastPage(),
                'hasMorePage' => $categories->hasMorePages()
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     description="Create a new category with translated names (Arabic and English).",
     *     operationId="storeCategory",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data to be created",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name_ar", "name_en"},
     *                 @OA\Property(
     *                     property="name_ar",
     *                     type="string",
     *                     description="The Arabic name of the category",
     *                     example="اسم الفئة"
     *                 ),
     *                 @OA\Property(
     *                     property="name_en",
     *                     type="string",
     *                     description="The English name of the category",
     *                     example="Category Name"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category has been created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="The ID of the created category",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name_ar",
     *                     type="string",
     *                     description="The Arabic name of the category",
     *                     example="اسم الفئة"
     *                 ),
     *                 @OA\Property(
     *                     property="name_en",
     *                     type="string",
     *                     description="The English name of the category",
     *                     example="Category Name"
     *                 )
     *             ),
     *            @OA\Property(property="status_code", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name_ar", type="array", @OA\Items(type="string", example="The name_ar field is required.")),
     *                 @OA\Property(property="name_en", type="array", @OA\Items(type="string", example="The name_en field is required."))
     *             ),
     *             @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     )
     * )
     */

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());

        return JsonResponseHelper::successResponse(__('messages.catgory_created'), CategoryResource::make($category), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{categoryId}",
     *     summary="Update an existing category",
     *     description="Update an existing category's translated names (Arabic and English) by its ID.",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}}, 
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         description="The ID of the category to be updated",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data to be updated",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name_ar", "name_en"},
     *                 @OA\Property(
     *                     property="name_ar",
     *                     type="string",
     *                     description="The Arabic name of the category",
     *                     example="اسم الفئة"
     *                 ),
     *                 @OA\Property(
     *                     property="name_en",
     *                     type="string",
     *                     description="The English name of the category",
     *                     example="Category Name"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category has been updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="The ID of the updated category",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name_ar",
     *                     type="string",
     *                     description="The updated Arabic name of the category",
     *                     example="اسم الفئة الجديدة"
     *                 ),
     *                 @OA\Property(
     *                     property="name_en",
     *                     type="string",
     *                     description="The updated English name of the category",
     *                     example="Updated Category Name"
     *                 )
     *             ),
     *            @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name_ar", type="array", @OA\Items(type="string", example="The name_ar field is required.")),
     *                 @OA\Property(property="name_en", type="array", @OA\Items(type="string", example="The name_en field is required."))
     *             ),
     *            @OA\Property(property="status_code", type="integer", example=400)
     *         )
     *     )
     * )
     */

    public function update(CategoryRequest $request, $categoryId)
    {

        $category = $this->categoryService->updateCategory($categoryId, $request->validated());

        if (!$category) {
            return JsonResponseHelper::successResponse(__('messages.category_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.category_updated'), CategoryResource::make($category));
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{categoryId}",
     *     summary="Delete a category",
     *     description="Delete a category by its ID.",
     *     operationId="deleteCategory",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         description="The ID of the category to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="The language to return results in (ar for Arabic, en for English)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="en")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category has been deleted successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="successful", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */

    public function destory($categoryId)
    {
        $category = $this->categoryService->deleteCategory($categoryId);

        if (!$category) {
            return JsonResponseHelper::successResponse(__('messages.category_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.category_deleted'));
    }
}
