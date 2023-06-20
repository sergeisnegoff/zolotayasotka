<?php

namespace App\Http\Controllers;

use App\Mail\SuccessOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProfileAddress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// use Spatie\ArrayToXml\ArrayToXml;


class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart');

        if ($cart) {
            $keysProducts = array_keys($cart);
            $removed_products = [];
            $quantity_changes = [];


            $items = Product::multiplicity()
                ->total()
                ->select(['products.id', 'total', 'multiplicity', 'category_id', 'products.title'])
                ->sortByCategory()
                ->orderBy('products.title')
                ->findMany($keysProducts);


            $sorting = $items->pluck('id');
            $sortingKeys = $sorting->flip();

            $itemsIds = $items->keyBy('id');

            $need_save_cart = false;

            foreach ($cart as $id => &$cartItem) {
                if (!$itemsIds->has($id)) {
                    $need_save_cart = true;
                    $removed_products[] = $id;
                    continue;
                } elseif ($itemsIds[$id]->total < $cartItem['quantity']) {
                    $new_total = floor(
                            $itemsIds[$id]->total / $itemsIds[$id]->multiplicity
                        ) * $itemsIds[$id]->multiplicity;

                    $quantity_changes[] = array_merge($cartItem, [
                        'quantity_changes' => $new_total - $cartItem['quantity'],
                    ]);
                    $cartItem['quantity'] = $new_total;
                    $need_save_cart = true;
                }
                $cartItem['order'] = $sortingKeys[$id];
                $cartItem['id'] = $id;
                unset($cartItem);
            }


            $cart = array_diff_key($cart, array_flip($removed_products));


            if ($need_save_cart) {
                $cartInfo = collect($cart)->map(function ($item) {
                    return Arr::except($item, ['id', 'order']);
                })->toArray();
                DB::table('cart')->updateOrInsert(['user_id' => Auth::id()], ['json' => json_encode($cartInfo)]);
                session(['cart' => $cartInfo]);
            }

            $orders = array_column($cart, 'order');

            array_multisort($orders, SORT_ASC, SORT_NUMERIC, $cart);
            $productInfo = Product::multiplicity()->findMany(array_column($cart, 'id'))->keyBy('id');
            $removed_products = Product::query()->whereIn('id', $removed_products)->get();


            return view('profile.orders.cart', compact('cart', 'removed_products', 'productInfo', 'quantity_changes'), [
                'page'       => 'basket',
                'user'       => $user = Auth::user(),
                'address'    => ProfileAddress::query()->where('user_id', $user?->id)->get(),
                'hasChanges' => $need_save_cart || $removed_products->isNotEmpty() || count($quantity_changes)
            ]);
        } else {
            return view('profile.orders.cart', [
                'page' => 'basket',
                'user' => Auth::user(),
            ]);
        }
    }

    public function updateCount(Request $request)
    {
        $post = $request->validate([
            'id'  => 'bail|required|int',
            'qty' => 'bail|required|int',
        ]);

        session()->put('cart.' . $post['id'] . '.quantity', $post['qty']);

        $item = session()->get('cart.' . $post['id']);

        $totalAmount = 0;
        foreach (session()->get('cart') as $id => $product) {
            if ($id == $post['id'] && !isset($product['price'])) {
                $item = Product::multiplicity()->find($id);
                session()->put('cart.' . $id, [
                    "art"      => null,
                    "price"    => $item->price,
                    "title"    => $item->title,
                    "images"   => $item->images,
                    "quantity" => $post['qty'],
                    "total"    => $post['qty'] * $item->price
                ]);

                $totalAmount += $item->price * $post['qty'];
            } else {
                $totalAmount += $item['price'] * $product['quantity'];
            }
        }

        if (DB::table('cart')->where('user_id', Auth::id())->first()) {
            DB::table('cart')->where('user_id', Auth::id())->update(['json' => json_encode(session()->get('cart'))]
            );
        } else {
            DB::table('cart')->insert(['user_id' => Auth::id(), 'json' => json_encode(session()->get('cart'))]);
        }

        return [
            'status'      => 'success',
            'itemAmount'  => number_format($item['price'] * $item['quantity'], 0, '.', ''),
            'totalAmount' => number_format($totalAmount, 0, '.', '')
        ];
    }

    public function updatePreOrderCount(Request $request)
    {
        $post = $request->validate([
            'id'  => 'bail|required|int',
            'qty' => 'bail|required|int',
        ]);

        $cart = session()->get('preorder_cart', []);
        $cart[$post['id']]["quantity"] = $post['qty'];
        $cart[$post['id']]["total"] = $post['qty'] * $cart[$post['id']]['price'];

        session()->put('preorder_cart', $cart);

        return [
            'status'      => 'success',
            'itemAmount'  => number_format($cart[$post['id']]["quantity"], 0, '.', ''),
            'totalAmount' => number_format($cart[$post['id']]["total"], 0, '.', '')
        ];
    }


    public function loadMini()
    {
        $cart = session()->get('cart');
        return view('profile.components.mini-basket', compact('cart'));
    }

    public function loadPreorderMini()
    {
        $miniCartData = session()->get('preorder_cart');
        return view('profile.components.mini-preorder-basket', compact('miniCartData'));
    }

    public function resendMail()
    {
        foreach (Order::where('created_at', '>', Carbon::now()->subDay()->setTime(0, 0))->get() as $order) {
            $user = $order->user;
            Mail::to(['sotkasaitzakaz@yandex.ru'])->send(
                new SuccessOrder(
                    $order,
                    Order::getOrderProducts($order->id),
                    ProfileAddress::getByID($order->address_id),
                    $user
                )
            );


            $user_address = $user->address()->where('address_id', $order->address_id)->first();

            $expr = DB::table('order_products')
                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                ->join('products', 'order_products.product_id', '=', 'products.id')
                ->select(
                    'products.title AS title',
                    'products.id as prod_id',
                    'order_products.order_id',
                    'products.price as price',
                    'order_products.qty as quantity',
                    'products.oneC_7 as oneC_7'
                )
                ->where('orders.id', $order->id)
                ->get();


            $orderData = [
                'order_info' => [
                    'random'       => $order->id,
                    "order_date"   => $order->created_at,
                    "comment"      => $order->comment,
                    "user_name"    => $user->name,
                    "user_phone"   => $user->phone,
                    "user_region"  => @$user_address->region,
                    "user_city"    => @$user_address->city,
                    "user_address" => @$user_address->address ?: 'Самовывоз',
                    "user_house"   => @$user_address->house,
                    'id'           => $user->id
                ],
            ];


            foreach ($expr as $exp) {
                $orderData[] = [
                    'product_' . $exp->prod_id => [
                        "title"    => $exp->title,
                        "quantity" => $exp->quantity,
                        "price"    => $exp->price,
                        "oneC_7"   => $exp->oneC_7,
                    ]
                ];
            }


            $datetime = $order->created_at->format('Y-m-d_H-m-s');
            $filename = "orders/{$datetime}_{$order->id}.json";

            $disk = Storage::disk('public');

            $orderJson = json_encode($orderData);
            if ($orderJson === false) {
                $jsonErrorMsg = json_last_error_msg();
            } else {
                if ($disk->put($filename, $orderJson)) {
                } else {
                }
            }
        }
    }


    public function create(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'address_id' => 'required',
            'comment'    => 'nullable'
        ]);

        dd($user, $data);
        /** @var Logger $log */
        $log = Log::channel('orders')->withContext([
            'userId' => $user->id,
        ]);

        $data['user_id'] = $user->id;

        $cartItems = session()->get('cart');
        if (is_null($cartItems)) {
            $log->error('Cart items empty');
            return back();
        }

        $order = Order::query()->create($data);

        $log->withContext(
            [
                'orderId' => $order->id,
            ]
        );

        $log->info('Cart items found');

        foreach ($cartItems as $id => $product) {
            Order::orderProducts($order->id, $id, $product['quantity'], $product['price'] * $product['quantity']);
        }

        $log->info('Filled cart items');

        try {
            Mail::to([$user, 'sotkasaitzakaz@yandex.ru'])->send(
                new SuccessOrder(
                    $order,
                    Order::getOrderProducts($order->id),
                    ProfileAddress::getByID($order->address_id),
                    $user
                )
            );
        } catch (\Exception $e) {
            Log::channel('orders')->error($e->getMessage(), [
                'exception' => $e,
                'data'      => $data,
            ]);
        }

        $log->info('Sent e-mail');

        $this->empty();

        $log->info('Cart items clear');

        $log->info('Start success');

        try {
            /** @var User $user */
            $user = $order->user;

            $log->info('Fetched User');

            $user_address = $user->address()->where('address_id', $order->address_id)->first();

            $log->info('Fetched User address');

            $expr = DB::table('order_products')
                ->join('orders', 'orders.id', '=', 'order_products.order_id')
                ->join('products', 'order_products.product_id', '=', 'products.id')
                ->select(
                    'products.title AS title',
                    'products.id as prod_id',
                    'order_products.order_id',
                    'products.price as price',
                    'order_products.qty as quantity',
                    'products.oneC_7 as oneC_7'
                )
                ->where('orders.id', $order->id)
                ->get();

            $log->info('Fetched Order Products');

            $orderData = [
                'order_info' => [
                    'random'       => $order->id,
                    "order_date"   => $order->created_at,
                    "comment"      => $order->comment,
                    "user_name"    => $user->name,
                    "user_phone"   => $user->phone,
                    "user_region"  => @$user_address->region,
                    "user_city"    => @$user_address->city,
                    "user_address" => @$user_address->address ?: 'Самовывоз',
                    "user_house"   => @$user_address->house,
                    'id'           => $user->id
                ],
            ];

            $log->info('Created data for order');

            foreach ($expr as $exp) {
                $orderData[] = [
                    'product_' . $exp->prod_id => [
                        "title"    => $exp->title,
                        "quantity" => $exp->quantity,
                        "price"    => $exp->price,
                        "oneC_7"   => $exp->oneC_7,
                    ]
                ];
            }

            $log->info('Filled order data via products');

            $datetime = $order->created_at->format('Y-m-d_H-m-s');
            $filename = "orders/{$datetime}_{$order->id}.json";

            $disk = Storage::disk('public');

            $orderJson = json_encode($orderData);
            if ($orderJson === false) {
                $jsonErrorMsg = json_last_error_msg();
                $log->error("Failed to encode order to JSON: $jsonErrorMsg");
            } else {
                if ($disk->put($filename, $orderJson)) {
                    $log->info("Saved order to file `$filename`");
                } else {
                    $log->warning("Failed to save order to file `$filename`");
                }
            }

            $log->info("Full order in file `$filename`", [
                'order'   => $orderData,
                'written' => $ex = $disk->exists($filename),
                'time'    => $ex ? Carbon::createFromTimestamp(filemtime($disk->path($filename)))->format(
                    'Y-m-d H:i:s'
                ) : null,
            ]);
        } catch (\Exception $e) {
            $log->error($e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }

        return response()->redirectToRoute('profile.orders.success', ['order' => $order->id]);
    }

    public function success(Request $request, Order $order)
    {
        return view('profile.orders.success', compact('order'), [
            'page'     => 'basket',
            'products' => Order::getOrderProducts($order->id),
        ]);
    }

    public function delete(Request $request, $id = 0)
    {
        session()->remove('cart.' . $id);

        if (DB::table('cart')->where('user_id', Auth::id())->first()) {
            DB::table('cart')->where('user_id', Auth::id())->update(['json' => json_encode(session()->get('cart'))]);
        }
    }

    public function empty()
    {
        session()->remove('cart');
        DB::table('cart')->where('user_id', Auth::id())->delete();

        return response()->redirectTo('profile/orders/cart');
    }
}
