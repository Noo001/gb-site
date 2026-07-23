<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\OrderController;
use App\Models\Order;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_phone'   => ['required', 'string', 'max:50'],
            'customer_email'   => ['nullable', 'email', 'max:255'],
            'customer_city'    => ['nullable', 'string', 'max:255'],
            'customer_comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $apiRequest = $request->duplicate($validated);
        $response = app(OrderController::class)->store($apiRequest);

        if ($response->status() >= 400) {
            return back()->withInput()->withErrors(['order' => 'Не удалось оформить заказ.']);
        }

        $order = $response->getData(true)['data'];

        return redirect()->route('checkout.success', ['id' => $order['id']]);
    }

    public function success(Request $request)
    {
        $order = Order::findOrFail($request->integer('id'));
        $response = app(OrderController::class)->show($request, $order);
        if ($response->status() === 404) {
            abort(404);
        }
        $order = $response->getData(true)['data'];

        return view('checkout.success', ['orderId' => $order['id']]);
    }
}
