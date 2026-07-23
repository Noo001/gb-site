@extends('layouts.site')

@section('title', 'Заказ оформлен — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <div class="card card-padded" style="text-align: center; max-width: 600px; margin: 0 auto;">
                <h1 class="section-title">Спасибо за заказ!</h1>
                <p>Ваш заказ <strong>#{{ $orderId }}</strong> принят.</p>
                <p class="text-muted">Наш менеджер свяжется с вами в ближайшее время для уточнения деталей и стоимости.</p>
                <a href="/" class="btn btn-primary" style="margin-top: 1rem;">На главную</a>
            </div>
        </div>
    </section>
@endsection
