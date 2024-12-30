<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DashBoardController extends Controller
{
    public function getProducts(Request $request,User $user){
        return $user->store->products;
    }
}
