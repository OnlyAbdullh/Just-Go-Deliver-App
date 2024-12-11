<?php

namespace App\Services;

use App\Helpers\JsonResponseHelper;
use App\Models\Product;
use App\Repositories\FavoriteRepository;
use Illuminate\Support\Facades\Auth;

class FavoriteService
{
    protected $favoriteRepository;

    public function __construct(FavoriteRepository $favoriteRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
    }

    public function addToFavorites(int $product_id, int $store_id): string
    {
        $user = Auth::user();

        if (!$this->favoriteRepository->isProductInStore($product_id, $store_id)) {
            return 'not_in_store';
        }

        if ($this->favoriteRepository->isFavorite($user, $product_id, $store_id)) {
            return 'already_favorited';
        }

        $this->favoriteRepository->add($user, $product_id, $store_id);

        return 'success';
    }


}
