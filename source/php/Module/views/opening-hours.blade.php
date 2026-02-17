@if (!$hideTitle && $postTitle)
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
        {{ $postTitle }}
    @endtypography
@endif

<div class="mod-opening-hours" data-current-week-index="{{ $currentWeekIndex }}" data-total-weeks="{{ $totalWeeks }}">
    @if (!empty($weeks))
        <div class="mod-opening-hours__week-header">
            <h3 class="mod-opening-hours__week-title" data-week-title>{{ $currentWeek['weekLabel'] ?? '' }}</h3>
            <nav class="mod-opening-hours__paging"
                aria-label="{{ __('Opening hours week navigation', 'modularity-opening-hours') }}">
                <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--prev"
                    {{ !$hasPrev ? 'disabled' : '' }}>{{ __('Previous week', 'modularity-opening-hours') }}</button>
                <span class="mod-opening-hours__paging-info">{{ $currentWeekIndex + 1 }} / {{ $totalWeeks }}</span>
                <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--next"
                    {{ !$hasNext ? 'disabled' : '' }}>{{ __('Next week', 'modularity-opening-hours') }}</button>
            </nav>
        </div>

        <div class="mod-opening-hours__list-container">
            @foreach ($weeks as $weekIndex => $week)
                <dl class="mod-opening-hours__list" data-week-index="{{ $weekIndex }}"
                    data-week-label="{{ $week['weekLabel'] }}" {!! $weekIndex !== $currentWeekIndex ? 'hidden' : '' !!}>
                    @foreach ($week['days'] as $day)
                        <div class="mod-opening-hours__row">
                            <dt class="mod-opening-hours__day">
                                {{ $day['name'] }}
                                <span class="mod-opening-hours__date">{{ $day['date'] }}</span>
                            </dt>
                            <dd class="mod-opening-hours__slots">
                                @foreach ($day['slots'] as $slot)
                                    @if (!empty($slot['closed']))
                                        <span
                                            class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                                    @else
                                        <span class="mod-opening-hours__range">{{ $slot['open'] }} –
                                            {{ $slot['close'] }}</span>
                                    @endif
                                    @if (!$loop->last)
                                        <span class="mod-opening-hours__sep">, </span>
                                    @endif
                                @endforeach
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endforeach
        </div>
    @endif
</div>
