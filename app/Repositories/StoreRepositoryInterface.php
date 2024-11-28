<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;

interface StoreRepositoryInterface{
    public function all_with_pagination($page,$items);

    public function store(array $data);

    public function uploadLogo(UploadedFile $file, string $directory, string $customName, string $disk = 'public');
}
