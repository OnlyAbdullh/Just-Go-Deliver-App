<?php

namespace App\Repositories;

use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreRepository implements StoreRepositoryInterface
{

    public function all_with_pagination($items): LengthAwarePaginator
    {
        return  Store::with('user')->paginate($items);
    }

    public function store(array $data)
    {
        return Store::create($data);
    }

    public function uploadLogo(UploadedFile $file, string $directory, string $disk = 'public'): bool|string
    {
        return $file->store($directory, $disk);
    }

    public function update(Store $store, array $data): Store
    {
        $store->update($data);

        return $store;
    }

    public function findById(int $id)
    {
        return Store::where('id', $id)->first();
    }

    public function delete(Store $store): bool
    {
        return $store->delete();
    }
}
