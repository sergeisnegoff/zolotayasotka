<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Золотая сотка алтая') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">


    <script src="{{asset('js/jquery-3.5.1.js')}}"></script>
    <script src="{{asset('assets/js/jquery-ui.js')}}"></script>
    <script src="{{ asset('js/common.js') }}"></script>
</head>
<body>
<script>

    @if (in_array(\Illuminate\Support\Facades\Request::segment(1), ['login', 'register']))
        window.location.href = '/?popup={{ \Illuminate\Support\Facades\Request::segment(1) }}';
    @endif

    @if (isset($_GET['popup']))
        @switch($_GET['popup'])
            @case('login')
                $('[data-popup=authorization]').iziModal('open');
                @break

            @case('register')
                $('[data-popup=registration]').iziModal('open');
                @break
        @endswitch
    @endif
</script>
<header>
    <div class="box__header-top">
        <div class="container">
            <div class="row">
                <div class="order-2 order-md-1 col-4 col-md-2 col-lg-2 col-xl-1">
                    <?php $site_logo_img = Voyager::setting('site.logo', ''); ?>
                    <div class="box__logo"><a href="/"><img src="{{ Voyager::image($site_logo_img) }}" alt=""></a></div>
                </div>
                <div class="order-1 order-md-2 col-4 col-md-4 col-lg-4 d-xl-none">
                    <div class="btn-nav">
                        <button data-btn-popup="navigation"><span></span><span></span>Меню</button>
                    </div>
                </div>
                <div class="order-4 order-md-3 col-12 d-none d-xl-block col-xl-4">
                    <div class="box__search">
                        <div class="box__form">
                            <form action="{{ route('searchProducts') }}" method="GET" role="search">
                                <div class="box__input"><input type="text" name="products" class="searchTitle"
                                                               placeholder="Найти товар"></div>
                                <input type="hidden" name="ajax-req" id="ajax-req" value="1">
                                <div id="productData">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="order-5 order-md-4 col-12 d-none d-xl-block col-xl-3">
                    <div class="box__nav-sub">
                        {{ menu('header', 'layouts.headerMenu') }}
                    </div>
                </div>
                <div class="order-3 order-md-5 col-4 col-md-6 col-lg-6 col-xl-4">
                    <div class="wrapper__header-right">
                        @guest
                            @if (Route::has('login'))
                                <div class="box__personalarea d-md-block">
                                    <a class="nav-link" href="javascript:;" data-btn-popup="authorization" style="color:black">{{setting('site.login')}}</a>
                                </div>
                            @endif

                            @if (Route::has('register'))
                                <div style="margin-left: 5px;" class="box__personalarea d-none d-md-block">
                                    <a href="javascript:;" data-btn-popup="registration" style="color:black">{{setting('site.registration')}}</a>
                                </div>
                            @endif
                        @else
                            <div class="box__personalarea d-md-block">
                                <button onclick="location.href = '{{ route('profile.index') }}'"><span class="head-icon"></span><span class="d-none d-md-inline-block">{{setting('site.personal_area')}}</span></button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        Выйти
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        @endguest
                        @if(session('cart', []))
                            <div class="box__card d-none">
                                <button data-btn-popup="basket"><span class="head-icon"><span
                                            class="box__card-quality">{{count(session('cart', []))}}</span></span></button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box__header-center d-none d-xl-block">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav>
                        @include('layouts.categories')
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<main>
    @yield('content')
</main>
<footer>
    <div class="btn-up"></div>
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-4 col-xl-4">
                <h3>{{setting('site.help_customer')}}</h3>
                <div class="box__footer-nav">
                    {!! menu('footer_help') !!}
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-3">
                <h3>{{setting('site.company_footer')}}</h3>
                <div class="box__footer-nav">
                    {!! menu('footer_company') !!}
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-5">
                <div class="box__footer-link"><a href="tel:8(3852)463620">8 (3852) 46-36-20</a><span>8 (3852) 46-36-20</span></div>
                <div class="box__footer-link"><a href="tel:8(3852)504098">8 (3852) 50-40-98</a><span>8 (3852) 50-40-98</span>
                </div>
                <div class="box__footer-info">{{setting('site.ADDRESS_TEXT')}}</div>
                <div class="box__footer-link"><a href="mailto:info@sotka-sem.ru">{{setting('site.CONTACTS_EMAIL')}}</a></div>
            </div>
        </div>
        <div class="row align-center">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="box__copyright">{{setting('site.copyright')}}</div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="box__link-personal"><a href="{{setting('site.personal_rights')}}">{{setting('site.personal_title_text')}}</a></div>
            </div>
            <div class="col-12 col-md-12 col-xl-5">
                <?php $dev_logo_img = Voyager::setting('site.dev_logo', ''); ?>
                <div class="box__logo-dev"><span>{{setting('site.dev_text')}}</span><a href="{{setting('site.dev_link')}}"><img
                            src="{{ Voyager::image($dev_logo_img) }}" alt=""></a></div>
            </div>
        </div>
    </div>
</footer>

@if (auth()->check() && auth()->user()->active == 'off')
    <div class="box__popup-basket" data-popup="basket">
        <div class="wrapper-popup">
            <div class="btn__close">
                <button aria-label="Закрыть попап" data-btn-closepopup><span></span></button>
            </div>
            <div class="wrapper-popup-top">
                <div class="row">
                    <div class="col-12">
                        <h2>Корзина</h2>
                    </div>
                </div>
            </div>

            <div class="wrapper-popup-center">
            <?php
                $total = 0;
                $totalAll = 0;
            ?>
            @foreach(session('cart', []) as $id => $details)
                <div class="box__basket-item">
                    <div class="row">
                        <div class="col-3">
                            <div class="box__image"><a href="#"><img
                                        src="{{ Voyager::image( $details['images'] ) }}" alt=""></a></div>
                        </div>
                        <div class="col-9">
                            <a href="#" class="item_remove remove-from-cart" data-id="{{ $id }}">x</a>
                            <div class="row">
                                <div class="col-12"><a href="/product/{{$id}}"><h3>{{$details['title']}}</h3></a></div>
                                <div class="col-5">
                                    <div class="box__quality">
                                        <div class="box__quality-value"><input type="number" data-number="0"
                                                                               max="{{ $details['total'] }}"
                                                                               name="quantity[]"
                                                                               class="quantityUpdate{{ $id }}"
                                                                               value="{{$details['quantity']}}">
                                        </div>
                                        <span class="btn__quality-nav">
                                    <span class="btn__quality-minus update-cart" data-id="{{ $id }}" data-prev-quality>-</span>
                                    <span class="btn__quality-plus update-cart" data-id="{{ $id }}" data-next-quality>+</span>
                                </span>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="box__price"> {{ $details['price'] * $details['quantity'] }} <span>₽</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $total += $details['price'] * $details['quantity']
                ?>
            @endforeach
            </div>
            <div class="wrapper-popup-bottom">
                <div class="row">
                    <div class="col-6">
                        <div class="box__price-title">Итого:</div>
                    </div>
                    <div class="col-6 text-right">
                        <div class="box__price">{{ $total }} <span>₽</span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="btn"><a href="{{route('profile.orders.cart')}}">В корзину</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="box__popup-filter" data-popup="filter"></div>

<div class="box__popup-popup" data-popup="navigation">
    <div class="wrapper-popup">
        <div class="btn__close">
            <button aria-label="Закрыть попап" data-btn-closepopup><span></span></button>
        </div>
        <div class="wrapper-popupfilter-top">
            <div class="box__search">
                <div class="box__form">
                    <form action="{{ route('searchProducts') }}" method="GET" role="search">
                        <div class="box__input"><input type="text" name="products" class="searchTitle"
                                                       placeholder="Найти товар"></div>
                        <div class="btn__search">
                            <button><span></span></button>
                        </div>
                        <div id="productData">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="wrapper-popupfilter-center">
            <div class="box__popupfilter-tabs">
                <ul>
                    <li class="active" data-popupfilter="menu">{{setting('site.menu_text')}}</li>
                    <li data-popupfilter="category">{{setting('site.cats_text')}}</li>
                </ul>
            </div>
            <div class="box__popupfilter-tabcontent active" data-popupfilter="menu">
                <div class="box__popupfilter-string">
                    {{ menu('header', 'layouts.headerMenu') }}
                </div>
            </div>
            <div class="box__popupfilter-tabcontent" data-popupfilter="category">
                <div class="box__popupfilter-string">
                    @include('layouts.categories')
                </div>
            </div>
        </div>
    </div>
</div>
@auth
    @if (auth()->user()->active == 'off')
        <div class="box__popup" data-popup='stat_acc' aria-hidden="false" role="dialog">
            <div class="wrapper-popup">
                <div class="btn__close">
                    <button aria-label="Закрыть попап" data-btn-closepopup=""><span></span></button>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h2 class="text-center">{{setting('site.stat_acc')}}</h2>
                        <div class="box__description">{{setting('site.stat_acc_text')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth
@if (!empty(session()->get('cart')))
    @include('scripts.basket')
@endif

@include('scripts.ajax')
@yield('script')


<script>
    window.onload = () => {
        $(function () {
            $('.box__header-center nav li').hover(function () {
                let width = $(this).find('.dropdown-categories').width(),
                    position = $(this).offset(),
                    userScreen = document.documentElement.clientWidth;

                if (width + position.left > userScreen) {
                    $(this).children('.dropdown-categories').css('left', userScreen - (width + position.left) - 60)
                }
            }, function () {});
        });
    }
</script>

@if (!\Illuminate\Support\Facades\Auth::check())
    @include('auth.popup')
@endif

<style>
    .box__personalarea .dropdown-menu{
        display: none;
        /* background-color: #fff; */
        text-align: right;
        position: absolute;
        top: 56%;
        width: 100%;
        /* box-shadow: 0px 5px 6px 0px #39393942; */
        height: 30px;
        z-index: 9999;
        padding: 0 10px;
    }
    .box__personalarea .dropdown-menu a {
        color: #a70000;
    }
    .box__personalarea:hover .dropdown-menu {
        display: block;

    }
</style>

</body>
</html>
