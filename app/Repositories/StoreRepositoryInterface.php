<?php

namespace App\Repositories;

use App\Models\Store;
use Illuminate\Http\UploadedFile;

interface StoreRepositoryInterface{
    public function all_with_pagination($items);

    public function store(array $data);

    public function uploadLogo(UploadedFile $file, string $directory, string $disk = 'public');

    public function update(Store $store,array $data);

    public function findById(int $id);

    public function delete(Store $store);
}
