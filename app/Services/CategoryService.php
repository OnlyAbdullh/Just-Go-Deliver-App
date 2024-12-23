<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryService
{
    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(int $items)
    {
        return $this->categoryRepository->all_with_pagination($items);
    }

    public function findOrCreate($nameEn, $nameAr)
    {
        $category = $this->categoryRepository->findByName($nameEn);
        if (! $category) {
            $category = $this->categoryRepository->store(['name_ar' => $nameAr, 'name_en' => $nameEn]);
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

        if (! $category) {
            return null;
        }

        return $category->update($data);
    }

    public function deleteCategory($categoryId)
    {
        $category = $this->categoryRepository->findById($categoryId);

        if (! $category) {
            return null;
        }

        return $this->categoryRepository->delete($category);
    }
}
