<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /** @return list<array<string, mixed>> */
    public static function definitions(): array
    {
        return [
            [
                'slug' => 'node',
                'name' => 'Node',
                'tier_label' => 'START',
                'price_label' => '$250',
                'tflops' => 250,
                'duration_days' => 90,
                'infra' => 'Shared pool',
                'reward_multiplier' => 0.86,
                'daily_estimate' => 0.9,
                'max_tflops' => 600,
                'sort_order' => 1,
                'is_featured' => false,
                'is_active' => true,
                'capacity_percent' => 12,
            ],
            [
                'slug' => 'core',
                'name' => 'Core',
                'tier_label' => 'POPULAR',
                'price_label' => '$1,100',
                'tflops' => 1200,
                'duration_days' => 180,
                'infra' => 'Priority pool',
                'reward_multiplier' => 1.00,
                'daily_estimate' => 5.0,
                'max_tflops' => 2500,
                'sort_order' => 2,
                'is_featured' => true,
                'is_active' => true,
                'capacity_percent' => 34,
            ],
            [
                'slug' => 'cluster',
                'name' => 'Cluster',
                'tier_label' => 'PRO',
                'price_label' => '$3,400',
                'tflops' => 4000,
                'duration_days' => 365,
                'infra' => 'Dedicated pods',
                'reward_multiplier' => 1.12,
                'daily_estimate' => 18.8,
                'max_tflops' => 6000,
                'sort_order' => 3,
                'is_featured' => false,
                'is_active' => true,
                'capacity_percent' => 62,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'tier_label' => 'CUSTOM',
                'price_label' => 'Custom',
                'tflops' => 10000,
                'duration_days' => null,
                'infra' => 'Reserved racks',
                'reward_multiplier' => 1.20,
                'daily_estimate' => null,
                'max_tflops' => null,
                'sort_order' => 4,
                'is_featured' => false,
                'is_active' => true,
                'capacity_percent' => 100,
            ],
        ];
    }

    /** @return array<string, Plan> */
    public static function plansBySlug(): array
    {
        return Plan::query()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('slug')
            ->all();
    }

    public function run(): void
    {
        foreach (self::definitions() as $data) {
            Plan::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }
}
