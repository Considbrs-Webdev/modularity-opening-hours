@foreach ($slots as $slot)
    @if (!empty($slot['closed']))
        <span class="mod-opening-hours__slot mod-opening-hours__slot--closed">
            <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
        </span>
    @else
        @php
            $slotLabel = trim((string) ($slot['label'] ?? ''));
        @endphp
        <span class="mod-opening-hours__slot">
            <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
            <span class="mod-opening-hours__detail">
                @if ($slotLabel !== '')
                    <span class="mod-opening-hours__sep" aria-hidden="true">·</span>
                    <span class="mod-opening-hours__description">{{ $slotLabel }}</span>
                @endif
            </span>
        </span>
    @endif
@endforeach
