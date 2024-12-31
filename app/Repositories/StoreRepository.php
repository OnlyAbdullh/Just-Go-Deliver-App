<?php

namespace App\Repositories;

use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StoreRepository implements StoreRepositoryInterface
{
    public function all_with_pagination($items): LengthAwarePaginator
    {
        $lang = app()->getLocale();

        return Store::with(['user' => function ($query) {
            $query->select('id', 'first_name', 'last_name');
        }])->select([
            'id',
            'name_' . $lang,
            'description_' . $lang,
            'location_' . $lang,
            'user_id',
            'logo',
        ])->paginate($items);
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

    public function findByName($name, $items)
    {
        $lang = app()->getLocale();

        return DB::table('stores')
            ->where('name_' . $lang, 'like', '%' . $name . '%')
            ->join('users', 'stores.user_id', '=', 'users.id')
            ->select([
                'stores.id',
                'stores.name_' . $lang . ' as name',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as manager'),
                DB::raw('CONCAT("' . asset('storage/') . '/", stores.logo) as image_url'),
                'stores.description_' . $lang . ' as description',
                'stores.location_' . $lang . ' as location',
            ])
            ->paginate($items);
    }
}
