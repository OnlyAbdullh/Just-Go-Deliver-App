<?php

namespace App\Services;

use App\Helpers\JsonResponseHelper;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryService
{
    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(int $items)
    {
        $categories = $this->categoryRepository->all_with_pagination($items);

        if (!$categories) {
            return null;
        }

        $hasMorePages = $categories->hasMorePages();

        return [
            'categories' => CategoryResource::collection($categories),
            'hasMorePages' => $hasMorePages,
        ];
    }

    public function findOrCreate($name)
    {
        $category = $this->categoryRepository->findByName($name);
        if (!$category) {
            $category = $this->categoryRepository->store(['name' => $name]);
        }
        return $category;
    }

    public function createCategory($data)
    {
        return $this->categoryRepository->store($data);
    }

    public function updateCategory($categoryId, $data)
    {
        $category = $this->categoryRepository->findById($categoryId);

        if (!$category) {
            return null;
        }

        return $category->update($data);
    }

    public function deleteCategory($categoryId)
    {
        $category = $this->categoryRepository->findById($categoryId);

        if (!$category) {
            return null;
        }

        return $this->categoryRepository->delete($category);
    }
}
