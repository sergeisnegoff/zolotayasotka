<?php

namespace App\Http\Controllers\Preorder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Preorder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PreorderCartController extends Controller
{
    public function index() {
        $cart = session()->get('preorder_cart', []);
        $cartKeys = collect(array_keys($cart));
        $page = 'preorder';

        $address = auth()->user()->address()->get() ?? collect();
        $user = auth()->user();
        $preorder_minimal = 0;
        if (count($cart)) {
            $preorder_minimal = (Preorder::find(Arr::first($cart)['preorder_id']))->min_order;
        }
        return view('preorder.cart', compact('cart', 'cartKeys', 'page', 'address', 'user', 'preorder_minimal'));
    }
}
