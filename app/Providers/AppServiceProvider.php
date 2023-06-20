<?php

namespace App\Providers;

use App\FormFields\TimeStampFormField;
use App\Logging\Logger;
use App\Models\Preorder;
use App\Models\PreorderProduct;
use Illuminate\Support\Arr;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Voyager::addFormField(TimeStampFormField::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.*', function (\Illuminate\View\View $view){
            if(!in_array($view->name(), [
                'layouts.error.404',
                'layouts.error.500',
                'layouts.app',
            ])){
                return;
            }
            $view
                ->with('categories', categoryTreeSort())
                ->with('errors', optional($view['errors'] ?? null));
            $view = $this->addPreorderMinimalCost($view);
        });

        Logger::register($this->app);
    }
    public function addPreorderMinimalCost($view) {
        if (count(session('preorder_cart', []))) {
            $cart = session('preorder_cart');
            $preorder = Preorder::find(Arr::first($cart)['preorder_id']);
            if ($preorder) {
                return $view
                    ->with('preorder_minimal', $preorder->min_order);
            }
        }
        return $view;
    }
}
