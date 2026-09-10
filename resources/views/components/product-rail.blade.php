@props(['title', 'tabs'])
@if ($tabs->isNotEmpty())
<div class="descontos70">
    <div class="container">
        <h2 class="title"><span>{{ $title }}</span></h2>
        <div class="tabs">
            @foreach ($tabs as $i => $tab)
                <input type="radio" id="tab_{{ $tab['id'] }}" name="descontos-tabs" @checked($i === 0)>
                <label for="tab_{{ $tab['id'] }}">{{ $tab['label'] }}</label>
                <div class="tab_content">
                    <div>
                        <div class="owl-carousel Sliders">
                            @foreach ($tab['products'] as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    function build($s) {
        if ($s.hasClass('owl-loaded')) { return; }
        $s.owlCarousel({
            loop: $s.children().length > 2,
            margin: 18,
            autoplay: true,
            autoplayTimeout: 3500,
            autoplayHoverPause: true,
            smartSpeed: 600,
            dots: false,
            nav: true,
            navText: ['&#10094;', '&#10095;'],
            responsive: { 0: { items: 2 }, 600: { items: 3 }, 1000: { items: 4 }, 1400: { items: 5 } }
        });
    }
    function init() {
        if (!window.jQuery || !jQuery.fn.owlCarousel) { return setTimeout(init, 200); }
        // n'initialise que l'onglet visible (les carrousels cachés se calculent mal)
        jQuery('.descontos70 .tabs > input[type=radio]').on('change', function () {
            var $c = jQuery(this).nextAll('.tab_content').first().find('.Sliders');
            if ($c.length && !$c.hasClass('owl-loaded')) { build($c); }
        });
        var $first = jQuery('.descontos70 .tabs input:checked').nextAll('.tab_content').first().find('.Sliders');
        if ($first.length) { build($first); }
    }
    init();
})();
</script>
@endpush
@endonce
@endif
