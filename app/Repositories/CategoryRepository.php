<?php

namespace App\Repositories;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Models\Category;

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

    public function delete(Category $category)
    {
        return $category->delete();
    }
}
