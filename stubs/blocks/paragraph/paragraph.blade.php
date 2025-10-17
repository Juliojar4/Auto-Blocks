@php
    $colorClass = $textColor === 'white' ? 'text-white' : 'text-black';
    $fontFamilyClass = 'font-' . $fontFamily;
@endphp

@if($enableAnimation)
    <p data-aos="{{ $animationType }}" data-aos-duration="700" data-aos-delay="{{ $animationDelay }}" class="{{ $fontClass }} {{ $colorClass }} {{ $marginBottom }} {{ $fontFamilyClass }}">
        {!! wp_kses_post($content) !!}
    </p>
@else
    <p class="{{ $fontClass }} {{ $colorClass }} {{ $marginBottom }} {{ $fontFamilyClass }}">
        {!! wp_kses_post($content) !!}
    </p>
@endif