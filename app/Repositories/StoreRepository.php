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
            'name_'.$lang,
            'description_'.$lang,
            'location_'.$lang,
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

    public function getStore(int $id)
    {
        $lang = app()->getLocale();
        $stores = DB::table('stores')
            ->where('stores.id', $id)
            ->join('users', 'stores.user_id', '=', 'users.id')
            ->join('store_products', 'stores.id', '=', 'store_products.store_id')
            ->join('products', 'store_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select([
                'stores.id as store_id',
                'stores.name_'.$lang.' as store_name',
                'stores.description_'.$lang.' as store_description',
                'stores.location_'.$lang.' as location',
                DB::raw('CONCAT("'.asset('storage/').'/", stores.logo) as logo'),
                'users.id',
                DB::raw('GROUP_CONCAT(CONCAT(users.first_name, " ", users.last_name) SEPARATOR ", ") as manager'),
                'store_products.id as store_product_id',
                'store_products.product_id', 
                'store_products.store_id',  
                'products.id as product_id',
                'products.name_'.$lang.' as product_name',
                DB::raw('CONCAT("'.asset('storage/').'/", store_products.main_image) as main_image'),

                DB::raw('IF('.
                    (auth()->check() ? 'EXISTS (SELECT 1 FROM favorites WHERE user_id = '.auth()->id().' AND product_id = store_products.product_id AND store_id = store_products.store_id)' : '0').
                    ', 1, 0) AS is_favorite'),
                'store_products.price',
                'store_products.quantity',
                'store_products.description_'.$lang.' as product_description',
                'products.category_id',
                'categories.name_'.$lang.' as category_name',
            ])->groupBy([
                'stores.id',
                'stores.name_'.$lang,
                'stores.description_'.$lang,
                'stores.location_'.$lang,
                'stores.logo',
                'users.id',
                'store_products.id',
                'store_products.product_id',
                'store_products.store_id',   
                'products.id',
                'products.name_'.$lang,
                'store_products.main_image',
                'store_products.price',
                'store_products.quantity',
                'store_products.description_'.$lang,
                'products.category_id',
                'categories.name_'.$lang,
            ])
            ->get();

        $groupedData = $stores->groupBy('id')->map(function ($storeProducts) {
            $firstProduct = $storeProducts->first();

            return [
                'id' => $firstProduct->id,
                'manager' => $firstProduct->manager,
                'name' => $firstProduct->store_name,
                'image_url' => $firstProduct->logo,
                'location' => $firstProduct->location,
                'description' => $firstProduct->store_description,
                'products' => $storeProducts->map(function ($product) use ($firstProduct) {
                    return [
                        'store_id' => $firstProduct->store_id,
                        'store_name' => $firstProduct->store_name,
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'main_image' => $product->main_image,
                        'price' => $product->price,
                        'quantity' => $product->quantity,
                        'description' => $product->product_description,
                        'is_favorite' => $product->is_favorite,
                        'category_id' => $product->category_id,
                        'category_name' => $product->category_name,
                    ];
                })->toArray(),
            ];
        })->values();

        return $groupedData;
    }

    public function delete(Store $store): bool
    {
        return $store->delete();
    }

    public function findByName($name, $items)
    {
        $lang = app()->getLocale();

        return DB::table('stores')
            ->where('name_'.$lang, 'like', '%'.$name.'%')
            ->join('users', 'stores.user_id', '=', 'users.id')
            ->select([
                'stores.id',
                'stores.name_'.$lang.' as name',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as manager'),
                DB::raw('CONCAT("'.asset('storage/').'/", stores.logo) as image_url'),
                'stores.description_'.$lang.' as description',
                'stores.location_'.$lang.' as location',
            ])
            ->paginate($items);
    }
}
