@php
    $colorClass = $textColor === 'white' ? 'text-white' : 'text-black';
    $fontFamilyClass = 'font-' . $fontFamily;
@endphp

@php
    $raw = preg_replace('/<br\s*\/?>/i', "\n", $content);
    $lines = preg_split('/\r\n|\r|\n/', trim($raw));
    $delayBase = $animationDelay ?? 100; // ms between each line from user settings
@endphp

<h2 class="!font-normal {{ $fontClass }} {{ $colorClass }} {{ $marginBottom }} {{ $fontFamilyClass }}">
    @foreach($lines as $i => $line)
        @php $delay = $i * $delayBase; @endphp
        @if($enableAnimation)
            <span class="block" data-aos="{{ $animationType }}" data-aos-delay="{{ $delay }}">
                {!! wp_kses_post(trim($line)) !!}
            </span>
        @else
            <span class="block">
                {!! wp_kses_post(trim($line)) !!}
            </span>
        @endif
    @endforeach
</h2>