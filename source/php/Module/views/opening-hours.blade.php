@if (!$hideTitle && $postTitle)
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
        {{ $postTitle }}
    @endtypography
@endif

<div class="mod-opening-hours" data-current-week-index="{{ $currentWeekIndex }}" data-total-weeks="{{ $totalWeeks }}"
    data-today-date-key="{{ $todayDateKey ?? '' }}"
    @if (!empty($inlineStyle)) style="{{ $inlineStyle }}" @endif>
    @if (!empty($showTodayHighlight) && $todayDay)
        <div class="mod-opening-hours__featured">
            <div class="mod-opening-hours__featured-slots">
                <div class="mod-opening-hours__featured-slot">
                    <p class="mod-opening-hours__today-label">
                        {{ __('Today', 'modularity-opening-hours') }}, <span class="mod-opening-hours__today-name">{{ $todayDay['name'] ?? '' }} {{ $todayDay['date'] ?? '' }}</span>
                    </p>
                    <p class="mod-opening-hours__today-hours">
                        @foreach ($todayDay['slots'] ?? [] as $slot)
                            @if (!empty($slot['closed']))
                                <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                            @else
                                <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
                            @endif
                        @endforeach
                    </p>
                    @if (!empty($todayDay['description']))
                        <p class="mod-opening-hours__description">{{ $todayDay['description'] }}</p>
                    @endif
                </div>
                @if (!empty($showTomorrowHighlight) && $tomorrowDay)
                    <div class="mod-opening-hours__featured-slot">
                        <p class="mod-opening-hours__today-label">
                            {{ __('Tomorrow', 'modularity-opening-hours') }}, <span class="mod-opening-hours__today-name">{{ $tomorrowDay['name'] ?? '' }} {{ $tomorrowDay['date'] ?? '' }}</span>
                        </p>
                        <p class="mod-opening-hours__today-hours">
                            @foreach ($tomorrowDay['slots'] ?? [] as $slot)
                                @if (!empty($slot['closed']))
                                    <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                                @else
                                    <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
                                @endif
                            @endforeach
                        </p>
                        @if (!empty($tomorrowDay['description']))
                            <p class="mod-opening-hours__description">{{ $tomorrowDay['description'] }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
    @if (!empty($weeks))
        <div class="mod-opening-hours__week-header">
            <h3 class="mod-opening-hours__week-title" data-week-title>{{ $currentWeek['weekLabel'] ?? '' }}</h3>
            <nav class="mod-opening-hours__paging"
                aria-label="{{ __('Opening hours week navigation', 'modularity-opening-hours') }}">
                <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--prev{{ ($paginationBtnStyle ?? 'text') === 'icon' ? ' mod-opening-hours__paging-link--icon' : '' }}"
                    {{ !$hasPrev ? 'disabled' : '' }}>
                    @if (($paginationBtnStyle ?? 'text') === 'icon')
                        @icon(['icon' => 'keyboard_arrow_left', 'size' => 'md'])@endicon
                        <span class="sr-only">{{ __('Previous week', 'modularity-opening-hours') }}</span>
                    @else
                        {{ __('Previous week', 'modularity-opening-hours') }}
                    @endif
                </button>
                <span class="mod-opening-hours__paging-info">{{ $currentWeekIndex + 1 }} / {{ $totalWeeks }}</span>
                <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--next{{ ($paginationBtnStyle ?? 'text') === 'icon' ? ' mod-opening-hours__paging-link--icon' : '' }}"
                    {{ !$hasNext ? 'disabled' : '' }}>
                    @if (($paginationBtnStyle ?? 'text') === 'icon')
                        @icon(['icon' => 'keyboard_arrow_right', 'size' => 'md'])@endicon
                        <span class="sr-only">{{ __('Next week', 'modularity-opening-hours') }}</span>
                    @else
                        {{ __('Next week', 'modularity-opening-hours') }}
                    @endif
                </button>
            </nav>
        </div>

        <div class="mod-opening-hours__list-container">
            @foreach ($weeks as $weekIndex => $week)
                <dl class="mod-opening-hours__list" data-week-index="{{ $weekIndex }}"
                    data-week-label="{{ $week['weekLabel'] }}" {!! $weekIndex !== $currentWeekIndex ? 'hidden' : '' !!}>
                    @foreach ($week['days'] as $day)
                        <div class="mod-opening-hours__row" data-date-key="{{ $day['dateKey'] ?? '' }}">
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
                                @if (!empty($day['description']))
                                    <span class="mod-opening-hours__description">- {{ $day['description'] }}</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endforeach
        </div>
    @endif
</div>
