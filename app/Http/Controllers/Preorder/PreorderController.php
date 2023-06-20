<?php

namespace App\Http\Controllers\Preorder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Preorder;
use App\Models\PreorderCategory;
use App\Models\PreorderProduct;
use Illuminate\Http\Request;

class PreorderController extends Controller
{
    public function index() {
        $preorders = Preorder::whereDate('end_date', '>', now()->toDateString())->get();
        $info = Page::where('slug', 'preorders')->first();

        return view('preorder.index', compact('preorders', 'info'));
    }

    public function category(int $id)
    {
        $categories = PreorderCategory::where('preorder_id', $id)
            ->get();

        $preorder = Preorder::find($id);

        $cartKeys = collect(array_keys(session()->get('preorder_cart', [])));

        return view('preorder.category', compact('categories', 'preorder', 'cartKeys'));
    }

    public function products(int $id)
    {
        $category = PreorderCategory::with(['products', 'preorder'])
            ->where('id', $id)
            ->first();

        $cartKeys = collect(array_keys(session()->get('preorder_cart', [])));

        return view('preorder.products', compact('category', 'cartKeys'));
    }

    public function product(int $id)
    {
        $product = PreorderProduct::with(['category', 'category.parent'])
            ->where('id', $id)
            ->first();

        $cartKeys = collect(array_keys(session()->get('preorder_cart', [])));

        return view('preorder.product', compact('product', 'cartKeys'));
    }

    public function page(int $id)
    {
        $info = Preorder::find($id);

        $preorders = Preorder::query()
            ->where('id', '!=', $id)
            ->get();

        return view('preorder.page', compact('info', 'preorders'));
    }

    public function addToCart(Request $request) {
        $product = PreorderProduct::find($request->id);
        $cart = session()->get('preorder_cart', []);
        $cart[$product->id] = [
            'id' => $product->id,
            'name' => $product->title,
            'price' => $product->price,
            'quantity' => $request->quantity,
            'multiplicity' => $product->multiplicity,
            'image' => $product->image,
            'preorder_id' => $product->category->preorder_id,
        ];

        session()->put('preorder_cart', $cart);
    }

    public function removeFromCart(int $id) {
        $cart = session()->get('preorder_cart', []);
        unset($cart[$id]);
        session()->put('preorder_cart', $cart);
    }
}
