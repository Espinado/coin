<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => LegalPage::SLUG_TERMS,
                'title' => 'Условия использования',
                'body' => "Здесь размещаются условия использования платформы CloudFlops.\n\nАдминистратор может отредактировать этот текст в разделе Legal админ-панели.",
                'sort_order' => 10,
            ],
            [
                'slug' => LegalPage::SLUG_PRIVACY,
                'title' => 'Политика конфиденциальности',
                'body' => "Здесь размещается политика конфиденциальности CloudFlops.\n\nОпишите, какие данные собираются, как хранятся и кому передаются.",
                'sort_order' => 20,
            ],
            [
                'slug' => LegalPage::SLUG_RISKS,
                'title' => 'Раскрытие рисков',
                'body' => "Инвестиции связаны с риском. Доходность в прошлом не гарантирует доходность в будущем.\n\nДобавьте полный текст предупреждения о рисках для инвесторов.",
                'sort_order' => 30,
            ],
            [
                'slug' => LegalPage::SLUG_FAQ,
                'title' => 'Часто задаваемые вопросы',
                'body' => "В: Как пополнить счёт?\nО: Перейдите в личный кабинет → Кошелёк → Пополнение.\n\nВ: Когда начисляется прибыль?\nО: Ежедневно согласно условиям выбранного плана.\n\nДобавьте или измените вопросы и ответы в админ-панели.",
                'sort_order' => 40,
            ],
        ];

        foreach ($pages as $page) {
            LegalPage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'body' => $page['body'],
                    'is_published' => true,
                    'sort_order' => $page['sort_order'],
                ],
            );
        }
    }
}
