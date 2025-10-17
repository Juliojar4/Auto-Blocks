@php
    $bgClass = match($backgroundColor) {
        'transparent' => '',
        'white' => 'bg-white',
        'black' => 'bg-black',
        'gray-50' => 'bg-gray-50',
        'gray-100' => 'bg-gray-100',
        'gray-200' => 'bg-gray-200',
        'purple-50' => 'bg-purple-50',
        'purple-100' => 'bg-purple-100',
        'blue-50' => 'bg-blue-50',
        'blue-100' => 'bg-blue-100',
        default => ''
    };

    $gridClass = $useGrid ? 'grid grid-cols-1 md:grid-cols-2 gap-8' : '';
    $borderClass = $addBottomBorder ? 'bg-black h-[1px]' : '';
    $containerClass = $fullWidth ? '' : 'container mx-auto';
@endphp
<div class="{{ $bgClass }}">
    <div class="{{ $bgClass }}" style="padding-top: {{ $paddingTop }}px; padding-bottom: {{ $paddingBottom }}px;">
        <div
            class="{{ $containerClass }}  {{ $gridClass ? 'grid-container' : '' }}"       
        >
            {!! $content !!}
        </div>

    </div>
    <div class="container">
        <div class="{{ $borderClass }}"></div>
    </div>
</div>