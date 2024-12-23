<?php

namespace App\Repositories\Contracts;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function all_with_pagination($items);

    public function store($data);

    public function delete(Category $category);

    public function findOrCreate($nameEn, $nameAr);

    public function findById($id);

    public function findByName($name);
}
