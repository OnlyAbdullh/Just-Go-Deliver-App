<?php

namespace App\Repositories;

use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreRepository implements StoreRepositoryInterface
{

    public function all_with_pagination($page, $items): LengthAwarePaginator
    {
        return Store::with('user')->paginate($items, ['*'], 'page', $page);
    }

    public function store(array $data)
    {
        return Store::create($data);
    }

    public function uploadLogo(UploadedFile $file, string $directory, string $customName, string $disk = 'public'): bool|string
    {
        $fileName = $customName . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $fileName, $disk);

    }
}
