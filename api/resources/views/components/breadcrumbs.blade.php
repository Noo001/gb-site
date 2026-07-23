@props(['items' => []])

<nav class="breadcrumbs">
    @foreach ($items as $item)
        @if (!$loop->last)
            <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
            <span>/</span>
        @else
            <span>{{ $item['name'] }}</span>
        @endif
    @endforeach
</nav>
