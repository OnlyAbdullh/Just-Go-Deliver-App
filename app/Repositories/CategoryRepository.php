<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all_with_pagination($items)
    {
        $categories = Category::paginate($items);

        if ($categories->isEmpty()) {
            return null;
        }

        return $categories;
    }

    public function store($data): Category
    {
        return Category::create($data);
    }

    public function findById($id): Category
    {
        return Category::where('id', $id)->first();
    }

    public function findByName($name)
    {
        return Category::where('name_en', $name)->first();
    }

    public function findOrCreate($nameEn, $nameAr)
    {
        $category = $this->findByName($nameEn);
        if (! $category) {
            $category = $this->store(['name_ar' => $nameAr, 'name_en' => $nameEn]);
        }

        return $category;
    }

    public function delete(Category $category)
    {
        return $category->delete();
    }
}
