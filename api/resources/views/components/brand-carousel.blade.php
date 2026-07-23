@php
$brands = [
    ['name' => 'Apple', 'href' => '/brands/apple'],
    ['name' => 'SAMSUNG', 'href' => '/brands/samsung'],
    ['name' => 'Xiaomi', 'href' => '/brands/xiaomi'],
    ['name' => 'HONOR', 'href' => '/brands/honor'],
    ['name' => 'Huawei', 'href' => '/brands/huawei'],
    ['name' => 'SONY', 'href' => '/brands/sony'],
    ['name' => 'dyson', 'href' => '/brands/dyson'],
    ['name' => 'smeg', 'href' => '/brands/smeg'],
    ['name' => 'JBL', 'href' => '/brands/jbl'],
    ['name' => 'DJI', 'href' => '/brands/dji'],
];
@endphp

<section class="page-section">
    <div class="container-theme">
        <div class="brand-grid">
            @foreach ($brands as $brand)
                <a href="{{ $brand['href'] }}" class="brand-item">{{ $brand['name'] }}</a>
            @endforeach
        </div>
    </div>
</section>
