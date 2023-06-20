<?php
$endDate = \Carbon\Carbon::parse($preorder->end_date);
$now = \Carbon\Carbon::now();
$showBuyButton = $endDate->isSameDay($now);
$diff = $endDate->diff($now);
?>


@extends('layouts.app')
@section('content')
    <div class="box__breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ul>
                        <li><a href="/">{{setting('site.main_title_buttom')}}</a></li>
                        <li>{{ $preorder->title }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <section class="box__product-catalog">
        <div class="container">
            <div style="margin-bottom: 20px;">
                <div style="justify-content:space-between;align-items: end" class="d-flex">
                    <p style="font-size:28px;font-weight:bolder;margin-bottom:0;"><b>{{ $preorder->title }}</b></p>
                    <p style="margin-bottom:0;"><b>Предзаказ закончится через {{ $diff->d }} дней, {{ $diff->h }}
                            часов, {{ $diff->i }} минут</b></p>
                </div>
                <p style="font-size:18px;margin-bottom:0;font-weight:bolder;">Минимальная сумма
                    заказа {{ number_format($preorder->min_order, 0, 2, ' ') }} рублей</p>
                <p style="font-size:18px;margin-bottom:0;font-weight:bolder;">Предварительная оплата составляет
                    {{ $preorder->prepay_percent }}% от суммы заказа</p>
                <p style="font-size:18px;margin-botton:0; font-weight:bolder;">С информацией по данном заказу можно
                    ознакомиться на
                    <a href="/preorders/info/{{$preorder->id}}/" style="display:inline;" class="btn">
                            <button>
                                странице заказа
                            </button>
                    </a>
                </p>
                <p></p>

            </div>

            <div id="productFind">
                <div id="productData">
                    @foreach ($categories as $cat)
                        @if ($cat->products()->limit(4)->count() > 0)
                            <p style="font-size: 28px;font-weight: bolder;">{{ $cat->title }}</p>
                            <div class="row">

                                @foreach ($cat->products()->limit(4)->get() as $seed)
                                    <div class="col-6 col-md-4 col-xl-3 fadeIn" style="max-width: 20%;">


                                        <div class="box__product-item">

                                            <div class="wrapper-img" style="position: relative;">
                                                <div class="box__image"
                                                     style="width: 100%;height: 100%;position: relative;">
                                                    <div class="swiper gallery-product-card"
                                                         style="height: 100%;">

                                                        <div class="swiper-wrapper">
                                                            <div class="swiper-slide">
                                                                <a class="aslide"
                                                                   href="/preorders/product/{{$seed->id}}">
                                                                    <span class="imgslide lazy"
                                                                          data-bg="{{ $seed->image ? asset('storage/'.$seed->image) :  asset('/storage/'.$preorder->default_image) }}">
                                                                        <div
                                                                            class="swiper-lazy-preloader"></div>
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wrapper-info">
                                                <div class="box__category">
                                                    <a href="/preorders/category/{{ $cat->id }}">{{ $cat->title }}</a>
                                                </div>
                                                <div class="box__title">
                                                    <a href="/preorders/product/{{$seed->id}}">
                                                        <h3>{{$seed->title}}</h3>
                                                    </a>
                                                </div>
                                            </div>
                                            @guest
                                                @if ($showBuyButton)
                                                    <div class="wrapper-button">
                                                        <div class="btn"><a href="{{route('login')}}"> Купить</a>
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="wrapper-button wrapper-button-auth">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="box__product-price">
                                                                <span
                                                                    class="box__price-sale">{{$seed->price}} ₽</span>
                                                            </div>
                                                        </div>
                                                        @if($showBuyButton)
                                                            <div class="col-6">
                                                                <div class="box__quality">
                                                                    <div class="box__quality-value">
                                                                        <input
                                                                            type="number"
                                                                            name="quantity"
                                                                            class="quantity{{$seed->id}}"
                                                                            data-number="{{ $seed->multiplicity }}"
                                                                            step="{{ $seed->multiplicity }}"
                                                                            min="{{ $seed->multiplicity }}"
                                                                            max="{{ $seed->total }}"
                                                                            value="{{ $seed->multiplicity }}">
                                                                    </div>
                                                                    <span class="btn__quality-nav">
                                                                        <span class="btn__quality-minus update-cart"
                                                                              data-id="{{$seed->id}}"
                                                                              data-prev-quality>-</span>
                                                                        <span class="btn__quality-plus update-cart"
                                                                              data-id="{{$seed->id}}" data-next-quality>+</span>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="btn d-flex">
                                                                    <a
                                                                        class="add-to-cart-preorder {{ $cartKeys->contains($seed->id) ? 'ifcart' : '' }}"
                                                                        style="color: white; margin-right: 20px; {{ $cartKeys->contains($seed->id) ? "background: #A16C21" : '' }}"
                                                                        value="{{ $seed->id }}">{{ $cartKeys->contains($seed->id) ? 'Докупить' : 'Купить' }}
                                                                    </a>
                                                                    <div class="ifcart">
                                                                        @if($cartKeys->contains($seed->id))
                                                                            Товар есть в корзине
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endguest
                                        </div>
                                    </div>
                                @endforeach
                                <div class="fadeIn" style="width: 20%; padding: 0 15px;">
                                    <div class="box__product-item">
                                        <div class="wrapper" style="position: relative; min-height: 100%">
                                            <div class="btn"><a
                                                    href="/preorders/category/{{ $cat->id }}/products">Посмотреть
                                                    все</a></div>
                                            <div class="btn btn-white"><a href="{{ route('home') }}">На главную</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @include('filter')
    @include('scripts.filter')

    <style>
        .box__catalog-limit label {
            transition: .3s;
        }

        .box__catalog-limit label:hover {
            color: #6DAC52;
            cursor: pointer;
        }
    </style>
@endsection


