@if (!$hideTitle && $postTitle)
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
    {{ $postTitle }}
    @endtypography
@endif

<div class="mod-opening-hours" data-current-week-index="{{ $currentWeekIndex }}">
    @if (!empty($weeks))
        <div class="mod-opening-hours__week-header">
            <h3 class="mod-opening-hours__week-title">{{ $weeks[$currentWeekIndex]['weekLabel'] ?? '' }}</h3>
            @if ($totalWeeks > 1)
                <nav class="mod-opening-hours__paging" aria-label="{{ __('Opening hours week navigation', 'modularity-opening-hours') }}">
                    <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--prev" {{ !$hasPrev ? 'disabled' : '' }}>{{ __('Previous week', 'modularity-opening-hours') }}</button>
                    <span class="mod-opening-hours__paging-info">{{ $currentWeekIndex + 1 }} / {{ $totalWeeks }}</span>
                    <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--next" {{ !$hasNext ? 'disabled' : '' }}>{{ __('Next week', 'modularity-opening-hours') }}</button>
                </nav>
            @endif
        </div>

        @foreach ($weeks as $index => $week)
            <div class="mod-opening-hours__week-panel" data-week-index="{{ $index }}" data-week-label="{{ $week['weekLabel'] }}" aria-hidden="{{ $index !== $currentWeekIndex ? 'true' : 'false' }}" {{ $index !== $currentWeekIndex ? 'hidden' : '' }}>
                <dl class="mod-opening-hours__list">
                    @foreach ($week['days'] as $day)
                        <div class="mod-opening-hours__row">
                            <dt class="mod-opening-hours__day">{{ $day['name'] }}</dt>
                            <dd class="mod-opening-hours__slots">
                                @foreach ($day['slots'] as $slot)
                                    @if (!empty($slot['closed']))
                                        <span class="mod-opening-hours__closed">{{ __('Closed', 'modularity-opening-hours') }}</span>
                                    @else
                                        <span class="mod-opening-hours__range">{{ $slot['open'] }} – {{ $slot['close'] }}</span>
                                    @endif
                                    @if (!$loop->last)
                                        <span class="mod-opening-hours__sep">, </span>
                                    @endif
                                @endforeach
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endforeach
    @endif
</div>

