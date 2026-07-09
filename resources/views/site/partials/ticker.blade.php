@php
    use App\Support\Quotes;
    $items = $quotes['items'];
@endphp

@if ($items)
    <div class="ticker" aria-label="KASE" @if ($quotes['time']) title="{{ $quotes['time'] }}" @endif>
        {{-- Duplicated once so the marquee can loop without a visible seam. --}}
        <div class="ticker__track">
            @foreach ([1, 2] as $pass)
                <div class="ticker__group" @if ($pass === 2) aria-hidden="true" @endif>
                    @foreach ($items as $item)
                        <span class="ticker__item">
                            @if ($logo = Quotes::logo($item['ticker']))
                                <img class="ticker__logo" src="{{ $logo }}" alt="" width="16" height="16">
                            @endif
                            <span class="ticker__code">{{ $item['ticker'] }}</span>
                            <span class="ticker__price">{{ $item['last'] }}</span>
                            <span class="ticker__arrow ticker__arrow--{{ strtolower($item['status']) ?: 'flat' }}">
                                @switch($item['status'])
                                    @case('UP') &#9650; @break
                                    @case('DOWN') &#9660; @break
                                    @default &#9644;
                                @endswitch
                            </span>
                        </span>
                        <span class="ticker__sep">|</span>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endif
