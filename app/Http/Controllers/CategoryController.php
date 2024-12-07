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

    public function index(Request $request)
    {
        $items = $request->query('items');

        $data = $this->categoryService->getAllCategories($items);

        if (!$data) {
            return JsonResponseHelper::successResponse(__('messages.no_category_available'));
        }

        return JsonResponseHelper::successResponse(__('messages.category_fetched'));
    }

    public function store(CategoryRequest $request)
    {
        if (!Gate::allows('createCategory', User::class)) {
            return JsonResponseHelper::successResponse(__('messages.category_admin_only_create'), [], 401);
        }

        $category = $this->categoryService->createCategory($request->validated());

        return JsonResponseHelper::successResponse(__('messages.catgory_created'), CategoryResource::make($category), 201);
    }

    public function update(CategoryRequest $request, $categoryId)
    {

        if (!Gate::allows('updateCatgory', User::class)) {
            return JsonResponseHelper::successResponse(__('messages.category_admin_only_update'), [], 401);
        }

        $category = $this->categoryService->updateCategory($categoryId, $request->validated());

        if (!$category) {
            return JsonResponseHelper::successResponse(__('messages.category_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.category_updated'), CategoryResource::make($category));
    }

    public function destory($categoryId)
    {
        if (!Gate::allows('deleteCategory', User::class)) {
            return JsonResponseHelper::successResponse(__('messages.category_admin_only_update'), [], 401);
        }

        $category = $this->categoryService->deleteCategory($categoryId);

        if (!$category) {
            return JsonResponseHelper::successResponse(__('messages.category_not_found'), [], 404);
        }

        return JsonResponseHelper::successResponse(__('messages.category_deleted'));
    }
}
