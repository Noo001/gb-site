<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class SalesController extends Controller
{
    private array $sales = [
        [
            'slug' => 'kodovyy-zamok-udachi',
            'title' => 'Кодовый замок удачи',
            'description' => 'Получите персональный промокод на скидку при покупке от 10 000 ₽. Акция действует на широкий ассортимент смартфонов, аксессуаров и техники для дома.',
            'image' => '/images/original/promos/kodovyy-zamok-udachi.png',
        ],
        [
            'slug' => 'skidka-za-otzyv',
            'title' => 'Скидка за отзыв',
            'description' => 'Оставьте отзыв о покупке и получите купон на следующий заказ. Чем подробнее отзыв, тем выгоднее условия.',
            'image' => '/images/original/promos/skidka-za-otzyv.png',
        ],
        [
            'slug' => 'trade-in',
            'title' => 'Программа Trade-In',
            'description' => 'Обменяйте старый гаджет на новый с выгодной доплатой. Принимаем смартфоны, планшеты, ноутбуки и умные часы в любом состоянии.',
            'image' => '/images/original/promos/trade-in.png',
        ],
        [
            'slug' => 'rassrochka',
            'title' => 'Рассрочка 0%',
            'description' => 'Покупайте технику в рассрочку до 36 месяцев без переплат. Оформление занимает несколько минут, первоначальный взнос не требуется.',
            'image' => '/images/original/promos/rassrochka.PNG',
        ],
        [
            'slug' => 'besplatnaya-zashchita-ekrana-v-gadget-bar',
            'title' => 'Бесплатная защита экрана в Gadget bar',
            'description' => 'При покупке смартфона — бесплатная защита экрана. Акция действует на участвующие модели.',
            'image' => '/images/original/promo-screen.png',
        ],
        [
            'slug' => 'programma-trade-in',
            'title' => 'Программа Trade-In',
            'description' => 'Выгодный обмен старого устройства на новый. Принимаем смартфоны, планшеты, ноутбуки и умные часы.',
            'image' => '/images/original/promo-tradein.png',
        ],
    ];

    public function index()
    {
        return view('sales.index', ['sales' => $this->sales]);
    }

    public function show(string $slug)
    {
        $sale = collect($this->sales)->firstWhere('slug', $slug);

        if (! $sale) {
            abort(404);
        }

        return view('sales.show', compact('sale'));
    }
}
