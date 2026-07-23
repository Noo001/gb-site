<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $orders = app(OrderController::class)->index($request)->getData()->data;

        return view('account.index', compact('orders'));
    }
}
