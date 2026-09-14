<?php

return [
    'name' => 'Display name on plan cards, dashboard, and contracts.',
    'slug' => 'Unique ID (node, core). enterprise slug enables “Contact sales” when min investment is empty.',
    'tier_label' => 'Small badge on plan card (START, POPULAR, CUSTOM).',
    'price_label' => 'Marketing price text only — not charged from balance.',
    'min_deposit' => 'Minimum investment in USDT. Validated on purchase. Empty + enterprise = sales only.',
    'price_amount' => 'Default investment if user does not enter an amount.',
    'annual_profit_percent' => 'APR — drives daily profit accrual (principal × APR ÷ 365).',
    'currency' => 'Plan currency, usually USDT.',
    'tflops' => 'Legacy: sets calculator slider default when plan is selected (≈ investment amount).',
    'duration_days' => 'Contract term; principal locked until maturity.',
    'infra' => 'Infrastructure label on cards and new contracts.',
    'reward_multiplier' => 'Legacy AI-compute field; not used in profit accrual. Keep ~1.0.',
    'daily_estimate' => 'Optional static “~X / day” hint on card; real calc uses APR.',
    'max_tflops' => 'Upper bound for calculator slider plan matching.',
    'sort_order' => 'Display order (lower = first). Also used for upgrade comparison.',
    'capacity_percent' => 'Decorative capacity bar on plan card (0–100). Does not limit sales.',
    'is_active' => 'If off, plan is hidden from user dashboard.',
    'is_featured' => 'Marked as featured in admin list only.',
];
