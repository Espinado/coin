# Поля CRUD инвестиционного плана

Справочник для формы **Admin → Plans → Create / Edit**.  
Колонка **DB** — имя поля в таблице `plans` (исторические имена сохранены).

---

## What users see on cards

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **Plan name** | `name` | Название плана («Enterprise», «Core»). | Карточки, dashboard, контракты, транзакции «Investment». |
| **Card badge** | `tier_label` | Бейдж уровня (`START`, `CUSTOM`). | Угол карточки плана. |
| **Headline price (display only)** | `price_label` | Маркетинговый текст (`Custom`, `$1,100`). **Не списывается.** | Крупная цифра на карточке. |
| **Pool name** | `infra` | Подпись пула (`Reserved racks`). | Карточка, sidebar калькулятора, `location_label` контракта. |
| **Capacity bar fill (%)** | `capacity_percent` | Декоративная полоска 0–100. | Низ карточки. **Не лимит продаж.** |

---

## Purchase & returns

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **Minimum purchase (USDT)** | `min_deposit` | Минимум при Invest. | `PlanPurchaseService`. Enterprise без min → «Contact sales». |
| **Default purchase amount (USDT)** | `price_amount` | Сумма по умолчанию. | `PlanPurchaseService` если amount не передан. |
| **Annual return · APR (%)** | `annual_profit_percent` | **Главный параметр прибыли.** | Daily = principal × APR ÷ 365. `ProfitAccrualService`. |
| **Currency** | `currency` | Валюта (USDT). | Суммы, транзакции, контракты. |
| **Lock period (days)** | `duration_days` | Срок блокировки principal. | `ends_at`, maturity release. Пусто → «By agreement». |
| **Card hint: daily profit** | `daily_estimate` | Статичная подсказка «~X/day». | Только display; расчёт через APR. |

---

## Investment calculator slider

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **Calculator start amount (USDT)** | `tflops` | Старт слайдера при выборе плана. | `Dashboard::selectPlan()`. Legacy имя колонки. |
| **Calculator max amount (USDT)** | `max_tflops` | Верхняя граница автоподбора плана. | `DashboardDataService::planForPower()`. |

---

## System & admin

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **System key (slug)** | `slug` | Уникальный ключ (`enterprise`). | Enterprise rules, seeder. |
| **List order** | `sort_order` | Порядок в списке (меньше = выше). | Dashboard, upgrade logic. |
| **[Legacy] Reward multiplier** | `reward_multiplier` | Старое поле AI Compute. **Не влияет на profit.** | Оставить 1.0. |
| **Published** | `is_active` | План виден и доступен для покупки. | `DashboardDataService`. |
| **Highlighted in admin list** | `is_featured` | Метка в admin index. | Только админка. |

---

## Связь полей с потоком пользователя

```
Top-up → available balance
         ↓
Invest (amount ≥ min_deposit, plan is_active)
         ↓
Contract: principal locked, APR = annual_profit_percent, term = duration_days
         ↓
Daily profit accrual (APR/365) → available balance
         ↓
Maturity → principal release → available balance
         ↓
Payout
```

---

## Особый случай: Enterprise

- `slug = enterprise`
- Если **Min investment** пуст — кнопка «Contact sales», покупка заблокирована.
- Если **Min investment** задан (например 800) — план можно купить как обычный.
- **Duration days** / **Annual profit %** могут быть пустыми → UI показывает «By agreement», «Estimated».

---

## Рекомендуемые значения (пример Node / Core / Cluster)

| План | min | price_amount | APR | duration | tflops (= старт слайдера) | max_tflops |
|---|---:|---:|---:|---:|---:|---:|
| Node | 250 | 250 | 12% | 90 | 250 | 600 |
| Core | 1100 | 1100 | 15% | 180 | 1200 | 2500 |
| Cluster | 3400 | 3400 | 18% | 365 | 4000 | 6000 |

---

## Что важно не путать

| Термин в UI | Поле | Это не… |
|---|---|---|
| Top-up | — | Investment (покупка плана) |
| Min investment | `min_deposit` | Top-up minimum |
| Price label | `price_label` | Сумма списания (списывается `power` / введённая сумма) |
| TFLOPS | `tflops` | Реальная вычислительная мощность (legacy UI) |
