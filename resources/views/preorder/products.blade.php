<?php
$atrProd = '';
$showBuyButton = (\Carbon\Carbon::parse($category->preorder->end_date))->isSameDay(\Carbon\Carbon::now());
?>


@extends('layouts.app')
@section('content')
    <div class="box__breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ul>
                        <li><a href="/">{{setting('site.main_title_buttom')}}</a></li>
                        <li><a href="/preorders/{{ $category->preorder_id }}">Предзаказ {{ $category->preorder->title }}</a></li>
                        <li>{{ $category->title }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <section class="box__product-catalog">
        <div class="container">
            <div class="box__product-header">
                <div style="margin-bottom: 32px;" class="row">
                    <div class="col-3" style="font-size: 28px;font-weight: bolder">Предзаказ {{ $category->preorder->title }}</div>
                    <div class="col-7 col-md-4 col-lg-4 col-xl-5">
                        <div class="box__catalog-sorting">
                            <div class="wrapper-sorting-title d-none d-xl-block">Сортировать</div>
                            <div>
                                <select name="sort" id="sortSelect"
                                        style="width: 100%; border-color: #6dac52; border-radius: 20px;padding: 5px 20px;">
                                    <option selected>По умолчанию</option>
                                    <option value="ASC/title">Название (А - Я)</option>
                                    <option value="DESC/title">Название (Я - А)</option>
                                    <option value="ASC/price">Цена (низкая &gt; высокая)</option>
                                    <option value="DESC/price">Цена (высокая &gt; низкая)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-5 col-md-5 col-lg-5 col-xl-4">
                        <div class="box__catalog-view">
                            <ul>
                                <li data-catalog-grid
                                    class="gridAttr <?= $atrProd == 'data-catalog-grid' ? 'active'
                                        : '' ?>  <?= empty($atrProd) ? 'active' : '' ?>">
                                    <button class="gridBtn" type="button" value="data-catalog-grid"><span
                                            style="background-image: url({{asset('img/icon/sorting-card.svg')}});"></span>
                                    </button>
                                </li>
                                <li data-catalog-list
                                    class="listAttr <?= $atrProd == 'data-catalog-list' ? 'active' : '' ?>">
                                    <button class="listBtn" type="button" value="data-catalog-list"><span
                                            style="background-image: url({{asset('img/icon/sorting-list.svg')}});"></span>
                                    </button>
                                </li>
                                <li data-catalog-card
                                    class="cardAttr <?= $atrProd == 'data-catalog-card' ? 'active' : '' ?>">
                                    <button class="cardBtn" type="button" value="data-catalog-card"><span
                                            style="background-image: url({{asset('img/icon/sorting-grid.svg')}});"></span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3" style="font-size: 28px;font-weight: bolder;">{{ $category->title }}</div>
                </div>
            </div>

            <div id="productFind">
                <div id="productData">
                    <div class="row prodAttr"
                             data-catalog <?= !empty($atrProd) ? $atrProd : 'data-catalog-grid'  ?>>
                            @foreach ($category->products as $seed)
                                <div class="col-6 col-md-4 col-xl-2 fadeIn">
                                    <div class="box__product-item">
                                        <div class="wrapper-img">
                                            <div class="box__image"
                                                 style="width: 100%;height: 100%;position: relative;">
                                                <div class="swiper gallery-product-card" style="height: 100%;">
                                                    <div class="swiper-wrapper">
                                                        <div class="swiper-slide">
                                                            <a class="aslide" href="/preorders/product/{{$seed->id}}">
                                                                <span class="imgslide lazy"
                                                                      data-bg="{{ thumbImg($seed->image ?? $category->preorder->default_image , 220, 346) }}">
                                                                    <div class="swiper-lazy-preloader"></div>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <!-- If we need pagination -->
                                                    <div class="swiper-pagination"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wrapper-info">
                                            <div class="box__category"><a
                                                    href="/preorders/category/{{ $category->id }}/products">{{ @$seed->category->title }}</a>
                                            </div>
                                            <div class="box__title"><a href="/preorders/product/{{$seed->id}}">
                                                    <h3> {{$seed->title}} </h3></a>
                                            </div>
                                            <div class="box__description">
                                                <div class="box__characteristics">
                                                </div>
                                            </div>
                                        </div>
                                        @if($showBuyButton)
                                        @switch(true)
                                            @case(!\Illuminate\Support\Facades\Auth::check())
                                                <div class="wrapper-button">
                                                    <div class="btn"><a href="javascript:;" data-btn-popup="authorization">
                                                            Купить</a></div>
                                                </div>
                                            @break
                                            @case(auth()->user()->active == 'off')
                                                <div class="wrapper-button">
                                                    <div class="btn"><a href="javascript:;" data-btn-popup="manager">
                                                            Купить</a></div>
                                                </div>
                                            @break
                                            @default
                                                <div class="wrapper-button wrapper-button-auth">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row">
                                                                <div class="col-6 col-md-6">
                                                                    <div class="box__product-price">
                                                                    <span
                                                                        class="box__price-sale">{{$seed->price}} ₽</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 col-md-6">
                                                                    <div class="box__quality" style="margin-left: auto;">
                                                                        <div class="box__quality-value"><input type="number"
                                                                                                               name="quantity"
                                                                                                               class="quantity{{$seed->id}}"
                                                                                                               data-number="{{ $seed->multiplicity }}"
                                                                                                               step="{{ $seed->multiplicity }}"
                                                                                                               min="{{ $seed->multiplicity }}"
                                                                                                               max="{{ $seed->total }}"
                                                                                                               value="{{ $seed->multiplicity }}">
                                                                        </div>
                                                                        {{--                                                                    @if ($seed->multiplicity <= $seed->total)--}}
                                                                        <span class="btn__quality-nav">
                                                                                    <span
                                                                                        class="btn__quality-minus update-cart"
                                                                                        data-id="{{$seed->id}}"
                                                                                        data-prev-quality>-</span>
                                                                        <span class="btn__quality-plus update-cart"
                                                                              data-id="{{$seed->id}}" data-next-quality>+</span>

                                                                        </span>
                                                                        {{--                                                                    @endif--}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-6">
                                                            <div class="btn" style="text-align: left;">
                                                                <button
                                                                    class="add-to-cart-preorder {{ $cartKeys->contains($seed->id) ? 'ifcart' : '' }}"
                                                                    value="{{$seed->id}}">{{ $cartKeys->contains($seed->id) ? 'Докупить' : 'Купить' }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-6">
                                                            <div class="ifcart">@if($cartKeys->contains($seed->id))Товар
                                                                есть в корзине@endif</div>
                                                        </div>
                                                    </div>
                                                </div>
                                        @endswitch
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function () {
            $('body').on('click', '.box__catalog-view button', function (e) {
                e.preventDefault();
                let _self = $(this);

                $(this).parent().addClass('active');
                $(this).parent().siblings('.active').removeClass('active')
                $.each($('.prodAttr')[0].attributes, function (item) {
                    if (/catalog$/.test($('.prodAttr')[0].attributes[item].name) === false && $('.prodAttr')[0].attributes[item].name.includes('data')) {
                        $('.prodAttr').removeAttr($('.prodAttr')[0].attributes[item].name);
                        $('.prodAttr').attr(_self.attr('value'), true);
                    }
                });
                $('#productData .swiper').each(function () {
                    this.swiper.update();
                });
                return false;
            }).on('change', '.box__catalog-limit input', function () {
                $(this).closest('.box__catalog-limit').find('.active').removeClass('active');
                $(this).parent().addClass('active');
            }).on('click', '.box__product-item', function (e) {
            })
        });
    </script>

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


