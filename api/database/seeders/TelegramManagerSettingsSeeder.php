<?php

namespace Database\Seeders;

use App\Models\TelegramManagerSetting;
use Illuminate\Database\Seeder;

class TelegramManagerSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key' => 'welcome_message',
                'label' => 'Приветствие (главное меню)',
                'value' => "Привет! Я бот Gadget Bar.\n\nЗдесь вы можете:\n• связаться с менеджером,\n• узнать условия франшизы,\n• посмотреть адреса магазинов.",
            ],
            [
                'key' => 'franchise_conditions',
                'label' => 'Условия франшизы',
                'value' => "Условия франшизы Gadget Bar:\n"
                    . "• Паушальный взнос — от 100 000 ₽\n"
                    . "• Роялти — 1,6%\n"
                    . "• Поддержка 24/7\n"
                    . "• Готовая концепция, обучение, IT и маркетинг\n\n"
                    . "Напишите менеджеру, и мы рассчитаем прибыль для вашего города.",
            ],
            [
                'key' => 'contact_manager_prompt',
                'label' => 'Приглашение написать менеджеру',
                'value' => "Опишите ваш вопрос одним сообщением. Менеджер получит уведомление в Bitrix24 и ответит вам здесь.",
            ],
            [
                'key' => 'final_thanks',
                'label' => 'Благодарность после передачи лида',
                'value' => "Спасибо! Ваш запрос передан менеджеру. Мы ответим вам в этом чате.",
            ],
            [
                'key' => 'stores_header',
                'label' => 'Заголовок списка магазинов',
                'value' => "Наши магазины:",
            ],
            [
                'key' => 'stores_empty',
                'label' => 'Сообщение «магазины не найдены»',
                'value' => "Магазинов не найдено.",
            ],
        ];

        foreach ($defaults as $item) {
            TelegramManagerSetting::firstOrCreate(
                ['key' => $item['key']],
                ['label' => $item['label'], 'value' => $item['value'], 'is_active' => true]
            );
        }
    }
}
