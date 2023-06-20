<ul class="d-block {{ !isset($is_child) || !$is_child ? 'd-xl-flex' : '' }}">
    @php
        if (Voyager::translatable($items)) {
            $items = $items->load('translations');
        }
    @endphp
    @foreach ($items as $item)
        @php

            $originalItem = $item;
            if (Voyager::translatable($item)) {
                $item = $item->translate($options->locale);
            }

            $isActive = null;
            $styles = null;
            $icon = null;

            // Background Color or Color
            if (isset($options->color) && $options->color == true) {
                $styles = 'color:'.$item->color;
            }
            if (isset($options->background) && $options->background == true) {
                $styles = 'background-color:'.$item->color;
            }

            // Check if link is current
            if(url($item->link()) == url()->current()){
                $isActive = 'active';
            }

            // Set Icon
            if(isset($options->icon) && $options->icon == true){
                $icon = '<i class="' . $item->icon_class . '"></i>';
            }

        @endphp
        <li class="{{ $isActive }}">
            <a href="{{ url($item->link()) }}" target="{{ $item->target }}" style="{{ $styles }}">
                {{ $item->title }}
                @if (!$originalItem->children->isEmpty())
                    <span class="d-none d-xl-inline-block"></span>
                @endif
            </a>
            @if(!$originalItem->children->isEmpty())
                @include('layouts.headerMenu', ['items' => $originalItem->children, 'options' => $options, 'is_child' => true])
            @endif
        </li>
    @endforeach

    <?php
        if (!isset($is_child)) {
            $preorders = \App\Models\Preorder::where('end_date', '>', date('Y-m-d'));

            if ($preorders->count() > 0) {
                ?>
                <li>
                    <a href="/preorders/" style="position: relative;">
                        Предзаказы
                        <span class="d-none d-xl-inline-block"></span>
                    </a>
                    <ul>
                        <?php
                            foreach ($preorders->whereDate('end_date', '>', now()->toDateString())->get() as $preorder) {
                                ?>
                                <li>
                                    <a href="/preorders/{{ $preorder->id }}" style="position:relative;">
                                        {{ $preorder->title }}
                                    </a>
                                </li>
                                <?php
                            }
                        ?>
                    </ul>
                </li>
                <?php
            }
        }
    ?>
</ul>
