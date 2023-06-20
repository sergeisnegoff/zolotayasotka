<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\Preorder\PreorderCartController;
use App\Http\Controllers\Preorder\PreorderController;
use App\Http\Controllers\Preorder\PreorderReportsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Voyager\SalesystemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use TCG\Voyager\Http\Middleware\VoyagerAdminMiddleware;


Route::get('/',['as'=>'jquery.load_more',HomeController::class, 'index'])->name('home');

Route::post('/reset-password', [Controller::class, 'resetPassword'])->name('reset-password');

Route::get('/img/{path}/{img}', [ImageController::class, 'show'])->where('path', '.*');

Route::group([
    'prefix' => 'admin',
], function () {
    Voyager::routes();

    Route::post('/salesystems', [SalesystemController::class, 'updateTable'])->name('voyager.salesystems.updateTable');

    Route::post('/users/{id}/updateCategories', [ProfileController::class, 'updateTableCategories'])->name('voyager.users-categories.updateTable');
    Route::post('/users/{id}/updateBrands', [ProfileController::class, 'updateTableBrands'])->name('voyager.users-brands.updateTable');
});

Route::post('/users/active/{id}', [ProfileController::class, 'activeAccount'])->name('voyager.users-active');

//Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{title}', [ProductController::class, 'getProductsCat'])->name('products_parent_cats');
Route::get('/products/{id}/{title}', [ProductController::class, 'getProductsSubCat'])->name('products_cats');

Route::get('/product/{id}', [ProductController::class, 'getProduct'])->name('product');
Route::get('/searchProducts', [ProductController::class, 'searchProducts'])->name('searchProducts');
Route::post('add-to-cart/{id}', [HomeController::class, 'addToCart'])->name('addToCart');
Route::get('add-to-cart/{id}', [HomeController::class, 'addToCart']);
Route::patch('update-cart/{id}', [HomeController::class, 'update']);
Route::delete('remove-from-cart',[HomeController::class, 'remove']);
Route::get('basket/load', [CartController::class, 'loadMini']);
Route::get('/preorder/basket/load', [CartController::class, 'loadPreorderMini']);

Auth::routes();
Route::name('cart.')->prefix('cart')->group(function () {
    Route::post('/delete/{id?}', [CartController::class, 'delete'])->name('delete');
    Route::get('/empty', [CartController::class, 'empty'])->name('empty');
    Route::put('/create', [CartController::class, 'create'])->name('create');
    Route::post('/update-count', [CartController::class, 'updateCount'])->name('updateQty');
    Route::post('/preorder/update-count', [CartController::class, 'updatePreOrderCount'])->name('updatePreOrderQty');
    Route::post('reorder/{id?}', [ProfileController::class, 'reOrders'])->name('reorder');
});
Route::get('resend-orders', [CartController::class, 'resendMail']);

Route::prefix('import')->name('import')->group(function () {
    Route::get('products', [ImportController::class, 'products']);
});

Route::prefix('cron')->name('cron.')->group(function () {
    Route::post('save', [ImportController::class, 'cronSettings'])->name('save');
});

Route::get('update-order/{filename?}', [ImportController::class, 'orders']);
Route::get('update-kontr/{filename?}', [ImportController::class, 'contragents']);
Route::get('update-managers/{filename?}', [ImportController::class, 'managers']);
Route::get('update-catalog/{filename?}', [ImportController::class, 'products']);


Route::prefix('/profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');

    Route::prefix('/orders')->name('orders.')->group(function () {
        Route::get('/current', [ProfileController::class, 'orders'])->name('current');
        Route::get('/export-pdf/{order}', [ProfileController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-xls/{order}', [ProfileController::class, 'exportXls'])->name('export-xls');
        Route::get('/order-history', [ProfileController::class, 'orders'])->name('history');
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::get('/success/{order}', [CartController::class, 'success'])->name('success');
    });


    Route::post('/{id}', [ProfileController::class, 'update'])->name('update');
    Route::post('/change-password/{id}', [ProfileController::class, 'changePassword'])->name('change-password');

    Route::prefix('address')->name('address.')->group(function () {
        Route::get('/create', [ProfileController::class, 'address'])->name('create');
        Route::get('/edit/{id?}', [ProfileController::class, 'address'])->name('edit');
        Route::get('/autocomplete', [ProfileController::class, 'address'])->name('autocomplete');
        Route::get('/city', [ProfileController::class, 'address'])->name('city');
        Route::put('/store', [ProfileController::class, 'address'])->name('store');
        Route::patch('/update/{id}', [ProfileController::class, 'address'])->name('update');
        Route::post('/delete/{id?}', [ProfileController::class, 'address'])->name('delete');
        Route::post('/change/{id?}', [ProfileController::class, 'address'])->name('change');
    });

});

Route::group(['prefix' => 'preorders'], function () {
    Route::get('/', [PreorderController::class, 'index']);
    Route::get('/cart', [PreorderCartController::class, 'index']);
    Route::get('/{id}', [PreorderController::class, 'category']);
    Route::get('/info/{id}', [PreorderController::class, 'page']);
    Route::get('/category/{id}/products', [PreorderController::class, 'products']);
    Route::get('/product/{id}', [PreorderController::class, 'product']);
    Route::get('/product/{id}/remove', [PreorderController::class, 'removeFromCart']);

    Route::post('/add-to-cart', [PreorderController::class, 'addToCart'])->name('preorder.addToCart');
});


Route::get('/reorder/{id}', [ProfileController::class, 'reOrders']);

Route::get('/{page_slug}', [PagesController::class, 'index']);
Route::get('/our-life/{id}', [PagesController::class, 'index']);

Route::get('preorder/reports/export', [PreorderReportsController::class, 'index']);

Route::fallback([ErrorController::class, 'error_404']);
