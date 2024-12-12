<?php

namespace App\Repositories\Contracts;

interface ImageRepositoryInterface
{
    public function store($storeId, $productId, $images);
}
