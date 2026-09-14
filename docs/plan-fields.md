# Поля CRUD инвестиционного плана

Справочник для формы **Admin → Plans → Create / Edit**.  
Колонка **DB** — имя поля в таблице `plans` (исторические имена сохранены).

---

## Идентификация и отображение

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **Name** | `name` | Человекочитаемое название плана («Node», «Core»). | Карточки планов в dashboard, landing, admin-список, контракты, транзакции «Investment». |
| **Slug** | `slug` | Уникальный машинный ключ (`node`, `core`, `enterprise`). Не менять на prod без необходимости. | `PlanSeeder`, логика Enterprise (`slug === 'enterprise'` → кнопка «Contact sales», блокировка покупки без min). |
| **Tier label** | `tier_label` | Короткий бейдж уровня (`START`, `POPULAR`, `CUSTOM`). | Верхний правый угол карточки плана в dashboard. |
| **Price label** | `price_label` | Маркетинговая цена для UI (`$1,100`, `Custom`). Не списывается с баланса. | Крупная цифра на карточке, калькулятор «Estimated price», admin index (fallback если нет min). |
| **Infrastructure** | `infra` | Описание «где размещён» капитал (`Shared pool`, `Reserved racks`). | Карточки планов, sidebar калькулятора, `location_label` нового контракта. |

---

## Финансовые параметры (бизнес-логика)

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **Min investment** | `min_deposit` | Минимальная сумма покупки плана в USDT. | Валидация `PlanPurchaseService` — сумма инвестиции не может быть ниже. Карточки: «Min investment». Enterprise: если `null` — покупка только через sales. |
| **Default investment amount** | `price_amount` | Сумма по умолчанию, если пользователь не указал другую. | `PlanPurchaseService::purchase()` когда amount не передан. Fallback principal в отображении контрактов. |
| **Annual profit %** | `annual_profit_percent` | Годовая ставка (APR). **Главный параметр начисления прибыли.** | Daily profit = `principal × APR / 365`. Копируется в контракт при покупке. `ProfitAccrualService` начисляет по APR контракта. Калькулятор dashboard (per day / month / year). |
| **Currency** | `currency` | Валюта плана (обычно `USDT`). | Форматирование сумм, wallet-транзакции, контракты. |
| **Duration days** | `duration_days` | Срок контракта в днях. Principal locked до этой даты. | При покупке: `ends_at = started_at + duration_days`. Maturity release в `ProfitAccrualService`. UI: «90 days», «By agreement» если пусто. |

---

## Калькулятор и legacy (наследие AI Compute)

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **TFLOPS (legacy calculator)** | `tflops` | Историческое поле; сейчас **прокси для суммы инвестиции на слайдере**. При выборе плана `power` слайдера = `tflops`. | `Dashboard::selectPlan()`, поле `contracts.tflops`. **Не связано с реальными TFLOPS.** Для Node 250 → слайдер стартует с 250 USDT. |
| **Max TFLOPS (calculator)** | `max_tflops` | Верхняя граница диапазона слайдера для автоподбора плана. | `DashboardDataService::planForPower()` — при движении слайдера выбирается план, где `power <= max_tflops`. |
| **Reward multiplier** | `reward_multiplier` | Коэффициент из эпохи AI Compute. **Не участвует в daily profit accrual.** | Legacy `EpochService`, `calculatorTiers()` (не используется в UI dashboard). Оставить ≈1.0 для совместимости. |
| **Daily estimate** | `daily_estimate` | Статичная подсказка «~X / day» на карточке. | `Plan::formattedDailyEstimate()` — только display, если заполнено. Реальный расчёт идёт через APR. |

---

## Сортировка, витрина, ёмкость

| Поле (форма) | DB | Назначение | Где используется |
|---|---|---|---|
| **Sort order** | `sort_order` | Порядок планов (меньше = выше). | Admin index, dashboard список планов, сравнение upgrade (`sort_order > activePlan`). |
| **Capacity %** | `capacity_percent` | Декоративная полоска «заполненности» плана (0–100). | Progress bar внизу карточки плана. **Не лимитирует покупки.** |
| **Active** | `is_active` | План доступен для покупки. | `DashboardDataService` — только `is_active = true`. Скрытые планы не показываются пользователю. |
| **Featured** | `is_featured` | Метка «рекомендуемый» в admin-списке. | Admin index (`Active · Featured`). На landing/dashboard пока не выделает отдельно. |

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
