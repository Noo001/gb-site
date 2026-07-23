@extends('layouts.site')

@section('title', 'Контакты — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <h1 class="section-title">Контакты</h1>
            <div class="contacts-grid">
                <div class="card card-padded">
                    <h2>Магазин в Воронеже</h2>
                    <ul>
                        <li><span>Адрес:</span> ул. Примерная, 123, ТЦ «Галерея»</li>
                        <li><span>Телефон:</span> <a href="tel:88005051307">8 (800) 505-13-07</a></li>
                        <li><span>Режим работы:</span> ежедневно с 10:00 до 21:00</li>
                        <li><span>Email:</span> <a href="mailto:info@gadget-bar.ru">info@gadget-bar.ru</a></li>
                    </ul>
                </div>
                <div class="card card-padded">
                    <h2>Социальные сети</h2>
                    <p><a href="https://vk.com/gadgetbarru" target="_blank" rel="noopener">ВКонтакте</a></p>
                </div>
            </div>
        </div>
    </section>
@endsection
