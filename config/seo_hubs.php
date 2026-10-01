<?php

/*
|--------------------------------------------------------------------------
| Public SEO / AI keyword hubs
|--------------------------------------------------------------------------
|
| One cluster → one indexable URL. Synonyms live in copy, not as doorway pages.
| status: "live" | "planned"
|
*/

return [

    'hubs' => [

        'home' => [
            'status' => 'live',
            'route' => 'home',
            'path' => '/',
            'clusters' => ['brand', 'investment_primary'],
            'intent' => ['commercial', 'transactional'],
            'notes' => 'Primary brand + investment offer (plans, APR, deposit/withdraw).',
        ],

        'invest' => [
            'status' => 'live',
            'route' => 'seo.invest',
            'path' => '/invest',
            'clusters' => ['ai_infrastructure_investment', 'revenue_business_model'],
            'intent' => ['commercial', 'informational'],
            'notes' => 'Separate Investment section — do not mix with technical GPU/CaaS queries.',
        ],

        'ai-compute' => [
            'status' => 'planned',
            'route' => null,
            'path' => '/ai-compute',
            'clusters' => ['ai_compute', 'ml_ai_infrastructure', 'caas_cloud'],
            'intent' => ['commercial', 'informational'],
            'notes' => 'Technical AI compute narrative only if product claims stay accurate.',
        ],

        'gpu-compute' => [
            'status' => 'planned',
            'route' => null,
            'path' => '/gpu-compute',
            'clusters' => ['gpu_compute'],
            'intent' => ['commercial', 'transactional'],
            'notes' => 'GPU rental / marketplace wording delayed until product matches.',
        ],

        'ai-agents' => [
            'status' => 'planned',
            'route' => null,
            'path' => '/ai-agents',
            'clusters' => ['ai_agents'],
            'intent' => ['commercial', 'informational'],
            'notes' => 'Optional; only if agent workloads are a real positioning pillar.',
        ],

        'legal' => [
            'status' => 'live',
            'route' => 'legal.show',
            'path' => '/legal/{slug}',
            'clusters' => ['trust', 'compliance'],
            'intent' => ['informational'],
            'notes' => 'About, FAQ, Terms, Privacy, Risks — trust layer for SEO + AI.',
        ],

    ],

];
