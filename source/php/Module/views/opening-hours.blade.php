@if (!$hideTitle && $postTitle)
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
    {{ $postTitle }}
    @endtypography
@endif

<div class="mod-opening-hours">
    {{-- Add your module content here --}}
</div>

