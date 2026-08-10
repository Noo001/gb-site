<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class SalesController extends Controller
{
    private array $sales = [
        [
            'slug' => 'besplatnaya-zashchita-ekrana-v-gadget-bar',
            'title' => 'Бесплатная защита экрана в Gadget Bar',
            'description' => 'При покупке любого смартфона в Gadget Bar установим защитное стекло или плёнку бесплатно. Акция действует во всех магазинах сети при наличии комплектующих на складе.',
            'image' => '/images/original/promo-screen.png',
            'period' => 'Бессрочная акция',
            'sticker' => '0₽',
        ],
        [
            'slug' => 'grand-theft-auto-vi-uzhe-dostupna-dlya-predzakaza',
            'title' => 'Grand Theft Auto VI уже доступна для предзаказа',
            'description' => 'Оформите предзаказ на долгожданную игру и получите бонусы при запуске.',
            'image' => '/images/original/promos/gta-vi.png',
            'period' => null,
            'sticker' => null,
        ],
        [
            'slug' => 'rassrochka',
            'title' => 'Рассрочка',
            'description' => 'Покупайте технику в рассрочку до 36 месяцев без переплат. Оформление занимает несколько минут, первоначальный взнос не требуется.',
            'image' => '/images/original/promos/rassrochka.PNG',
            'period' => 'Бессрочная акция',
            'sticker' => null,
        ],
        [
            'slug' => 'aktsiya-kodovyy-zamok-udachi',
            'title' => 'Акция «Кодовый замок удачи»',
            'description' => 'Получите персональный промокод на скидку при покупке от 10 000 ₽. Акция действует на широкий ассортимент смартфонов, аксессуаров и техники для дома.',
            'image' => '/images/original/promos/kodovyy-zamok-udachi.png',
            'period' => null,
            'sticker' => null,
        ],
        [
            'slug' => 'diskontnye-ustroystva-tekhnika-vygodnee-chem-kazhetsya',
            'title' => 'Дисконтные устройства: техника выгоднее, чем кажется',
            'description' => 'Скидки на устройства с незначительными косметическими дефектами. Полная функциональность и официальная гарантия.',
            'image' => '/images/original/promos/diskontnye-ustroystva.png',
            'period' => null,
            'sticker' => null,
        ],
        [
            'slug' => 'skidka-za-otzyv',
            'title' => 'Скидка за отзыв',
            'description' => 'Оставьте отзыв о покупке и получите купон на следующий заказ. Чем подробнее отзыв, тем выгоднее условия.',
            'image' => '/images/original/promos/skidka-za-otzyv.png',
            'period' => 'Бессрочная акция',
            'sticker' => '500₽',
        ],
        [
            'slug' => 'programma-trade-in',
            'title' => 'Программа Trade-In',
            'description' => 'Обменяйте старый гаджет на новый с выгодной доплатой. Принимаем смартфоны, планшеты, ноутбуки и умные часы в любом состоянии.',
            'image' => '/images/original/promos/programma-trade-in.png',
            'period' => null,
            'sticker' => null,
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
