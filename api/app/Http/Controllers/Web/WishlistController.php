<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index()
    {
        return view('wishlist.index');
    }
}
