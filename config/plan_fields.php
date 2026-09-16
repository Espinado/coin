<?php

return [
    'sections' => [
        'display' => 'Что видит пользователь на карточке',
        'purchase' => 'Покупка и доходность',
        'calculator' => 'Калькулятор инвестиций',
        'admin' => 'Система и админка',
    ],

    'fields' => [
        'name' => [
            'label' => 'Название плана',
            'section' => 'display',
            'type' => 'text',
            'step' => null,
            'help' => 'Заголовок на карточках, в кабинете и в договоре (например Enterprise).',
        ],
        'tier_label' => [
            'label' => 'Бейдж на карточке',
            'section' => 'display',
            'type' => 'text',
            'step' => null,
            'help' => 'Маленькая метка в углу карточки (START, POPULAR, CUSTOM).',
        ],
        'price_label' => [
            'label' => 'Цена для показа (только текст)',
            'section' => 'display',
            'type' => 'text',
            'step' => null,
            'help' => 'Маркетинговый текст вроде «1 100 USDT» или «По договору». Не сумма списания с баланса.',
        ],
        'infra' => [
            'label' => 'Название пула',
            'section' => 'display',
            'type' => 'text',
            'step' => null,
            'help' => 'Подзаголовок на карточке и в новых договорах (например Reserved racks).',
        ],
        'capacity_percent' => [
            'label' => 'Заполнение полоски ёмкости (%)',
            'section' => 'display',
            'type' => 'number',
            'step' => null,
            'help' => 'Декоративная полоска внизу карточки. Не ограничивает продажи.',
        ],
        'min_deposit' => [
            'label' => 'Минимальная покупка (USDT)',
            'section' => 'purchase',
            'type' => 'number',
            'step' => '0.01',
            'help' => 'Минимальная сумма в долларах при нажатии «Купить план». Пусто у enterprise → «Связаться с менеджером».',
        ],
        'price_amount' => [
            'label' => 'Сумма покупки по умолчанию (USDT)',
            'section' => 'purchase',
            'type' => 'number',
            'step' => '0.01',
            'help' => 'Сумма в USDT, если пользователь не ввёл свою.',
        ],
        'annual_profit_percent' => [
            'label' => 'Годовая доходность · APR (%)',
            'section' => 'purchase',
            'type' => 'number',
            'step' => '0.01',
            'help' => 'Считает дневную прибыль: сумма × APR ÷ 365. Копируется в договор при покупке.',
        ],
        'currency' => [
            'label' => 'Валюта',
            'section' => 'purchase',
            'type' => 'hidden',
            'step' => null,
            'help' => null,
        ],
        'duration_days' => [
            'label' => 'Срок блокировки (дней)',
            'section' => 'purchase',
            'type' => 'number',
            'step' => null,
            'help' => 'Сколько дней сумма остаётся заблокированной. Пусто → «По договору» в интерфейсе.',
        ],
        'daily_estimate' => [
            'label' => 'Подсказка на карточке: прибыль (~USDT/день)',
            'section' => 'purchase',
            'type' => 'number',
            'step' => '0.01',
            'help' => 'Необязательный статичный «~X / день» на карточке. Реальное начисление всегда по APR выше.',
        ],
        'tflops' => [
            'label' => 'Стартовая сумма калькулятора (USDT)',
            'section' => 'calculator',
            'type' => 'number',
            'step' => null,
            'help' => 'При выборе плана ползунок прыгает сюда. Устаревшее имя колонки в БД: tflops.',
        ],
        'max_tflops' => [
            'label' => 'Макс. сумма калькулятора (USDT)',
            'section' => 'calculator',
            'type' => 'number',
            'step' => null,
            'help' => 'Если ползунок выше — переключается на следующий план. Устаревшее имя колонки: max_tflops.',
        ],
        'slug' => [
            'label' => 'Системный ключ (slug)',
            'section' => 'admin',
            'type' => 'text',
            'step' => null,
            'help' => 'Уникальный ID: node, core, enterprise. slug=enterprise включает особые правила «только через менеджера».',
        ],
        'sort_order' => [
            'label' => 'Порядок в списке',
            'section' => 'admin',
            'type' => 'number',
            'step' => null,
            'help' => 'Меньше число — выше в списке. Также для логики кнопки «Повысить план».',
        ],
        'reward_multiplier' => [
            'label' => '[Устар.] Множитель награды',
            'section' => 'admin',
            'type' => 'number',
            'step' => '0.01',
            'help' => 'Из старой эры AI-compute. Не используется в начислении прибыли — можно оставить 1.0.',
        ],
    ],

    'flags' => [
        'is_active' => [
            'label' => 'Опубликован — пользователи могут купить',
            'help' => 'Если выключено, план скрыт в личном кабинете.',
        ],
        'is_featured' => [
            'label' => 'Выделить в списке админки',
            'help' => 'Показывает «Featured» только в админке; карточки пользователя пока не меняет.',
        ],
    ],
];
