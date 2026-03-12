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
    data-all-days="{{ $allDaysJson ?? '[]' }}" data-today-date-key="{{ $todayDateKey ?? '' }}"
    data-initial-active-date-key="{{ $initialActiveDateKey ?? '' }}"
    data-show-tomorrow="{{ !empty($showTomorrowHighlight) ? '1' : '0' }}"
    data-label-today="{{ __('Today', 'modularity-opening-hours') }}"
    data-label-tomorrow="{{ __('Tomorrow', 'modularity-opening-hours') }}"
    data-label-closed="{{ __('Closed', 'modularity-opening-hours') }}">
    @if (!empty($showTodayHighlight) && ($initialDay1 ?? $todayDay))
        @php
            $day1 = $initialDay1 ?? $todayDay;
            $day2 = $initialDay2 ?? null;
            $todayKey = $todayDateKey ?? '';
            $tomorrowKey = $todayKey ? (new \DateTimeImmutable($todayKey . ' +1 day'))->format('Y-m-d') : '';
            $slot1Label = ($initialActiveDateKey ?? '') === $todayKey ? __('Today', 'modularity-opening-hours') : ($day1['name'] ?? '');
            $slot2Label = $day2 && ($day2['dateKey'] ?? '') === $tomorrowKey ? __('Tomorrow', 'modularity-opening-hours') : ($day2['name'] ?? '');
        @endphp
        <div class="mod-opening-hours__featured">
            <nav class="mod-opening-hours__day-nav" aria-label="{{ __('Opening hours day navigation', 'modularity-opening-hours') }}">
                <button type="button" class="mod-opening-hours__day-nav-link mod-opening-hours__day-nav-link--prev"
                    aria-label="{{ __('Previous day', 'modularity-opening-hours') }}">‹</button>
                <button type="button" class="mod-opening-hours__day-nav-link mod-opening-hours__day-nav-link--next"
                    aria-label="{{ __('Next day', 'modularity-opening-hours') }}">›</button>
            </nav>
            <div class="mod-opening-hours__featured-slots">
                <div class="mod-opening-hours__featured-slot" data-slot-index="0" data-date-key="{{ $day1['dateKey'] ?? '' }}">
                    <p class="mod-opening-hours__today-label" data-slot-label>
                        {{ $slot1Label }}{{ $slot1Label ? ', ' : '' }}
                        <span class="mod-opening-hours__today-name" data-slot-name>{{ $day1['name'] ?? '' }} {{ $day1['date'] ?? '' }}</span>
                    </p>
                    <p class="mod-opening-hours__today-hours" data-slot-hours>
                        @foreach ($day1['slots'] ?? [] as $slot)
                            @if (!empty($slot['closed']))
                                <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                            @else
                                <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
                            @endif
                        @endforeach
                    </p>
                </div>
                @if (!empty($showTomorrowHighlight))
                    <div class="mod-opening-hours__featured-slot" data-slot-index="1" data-date-key="{{ $day2['dateKey'] ?? '' }}">
                        <p class="mod-opening-hours__today-label" data-slot-label>
                            {{ $slot2Label }}{{ $slot2Label ? ', ' : '' }}
                            <span class="mod-opening-hours__today-name" data-slot-name>{{ $day2['name'] ?? '' }} {{ $day2['date'] ?? '' }}</span>
                        </p>
                        <p class="mod-opening-hours__today-hours" data-slot-hours>
                            @if ($day2)
                                @foreach ($day2['slots'] ?? [] as $slot)
                                    @if (!empty($slot['closed']))
                                        <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                                    @else
                                        <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
                                    @endif
                                @endforeach
                            @else
                                <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                            @endif
                        </p>
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
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endforeach
        </div>
    @endif
</div>
