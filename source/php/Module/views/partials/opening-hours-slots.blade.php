@foreach ($slots as $slot)
    @if (!empty($slot['closed']))
        <span class="mod-opening-hours__slot mod-opening-hours__slot--closed">
            <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
        </span>
    @else
        @php
            $slotLabel = trim((string) ($slot['label'] ?? ''));
            if ($slotLabel === '' && $loop->first && !empty($dayDescription)) {
                $slotLabel = trim((string) $dayDescription);
            }
        @endphp
        <span class="mod-opening-hours__slot">
            <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
            @if ($slotLabel !== '')
                <span class="mod-opening-hours__detail">
                    <span class="mod-opening-hours__sep" aria-hidden="true">·</span>
                    <span class="mod-opening-hours__description">{{ $slotLabel }}</span>
                </span>
            @endif
        </span>
    @endif
@endforeach
