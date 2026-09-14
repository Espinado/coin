# Coin — инвестиционная платформа (ТЗ v3)

**Дата:** 15.09.2026  
**Статус:** pivot с «AI Compute» на инвестиционную модель  
**Предыдущее ТЗ:** `docs/TZ.md` (устарело по бизнес-логике)

---

## 1. Новая бизнес-логика (простыми словами)

**Coin** — инвестиционная платформа. Пользователь пополняет счёт (**USDT / BTC**), покупает **план** (это депозит с фиксированным сроком и процентом), получает **ежедневную прибыль**, выводит средства по правилам ниже. Пригласивший получает **20% от суммы покупок планов** своих рефералов.

### 1.1. Деньги на счёте

| Тип средств | Откуда | Можно вывести |
|-------------|--------|---------------|
| **Свободный баланс** | Пополнение (deposit), реферальные, неиспользованное | ✅ в любой момент |
| **Прибыль по депозитам** | Ежедневное начисление % по активным планам | ✅ в любой момент |
| **Тело депозита (principal)** | Сумма, потраченная на покупку плана | ❌ только по окончании срока плана |

### 1.2. План = инвестиционный продукт

Админ создаёт план с параметрами:

- **Срок вклада** (дней)
- **Годовой % прибыли** (APR / profit %)
- **Минимальная сумма депозита**
- **Валюта** (USDT / BTC) — на первом этапе можно одна валюта на план
- Название, описание, порядок сортировки, active/hidden

Пользователь с **свободного баланса** покупает план → создаётся **сделка (contract/deposit deal)** на сумму ≥ min deposit.

### 1.3. Начисление прибыли

- **Каждый день** (cron): `daily_profit = principal × (annual_percent / 100) / 365`
- Начисляется на **available** (можно снять сразу)
- Тело депозита остаётся **locked** до `ends_at`

### 1.4. Пополнение и вывод (MVP — заглушки)

| Операция | MVP | Позже |
|----------|-----|-------|
| **Deposit** | Имитация: user указывает сумму → admin подтверждает / auto-approve | Payment gateway |
| **Withdraw** | Имитация: заявка → admin workflow (уже есть `WithdrawalService`) | Payout gateway |

### 1.5. Реферальная программа

- **Реферал** — пользователь, зарегистрировавшийся по ссылке пригласившего (уже есть L1)
- **Комиссия:** **20%** от суммы **покупки плана** рефералом (не от прибыли, не level 2)
- Начисляется на **available** пригласившего, можно вывести в любой момент
- Level 2 — **не используется**

### 1.6. Админ по клиенту

| Поле / блок | MVP | Позже |
|-------------|-----|-------|
| Дата и время регистрации | ✅ `users.created_at` | — |
| Страна | ❌ добавить | GeoIP / KYC |
| Последний вход | ❌ добавить | `last_login_at` |
| Email | ✅ | — |
| Telegram | ❌ добавить | — |
| Телефон | ❌ добавить | — |
| История выводов | ⚠️ есть модель, слабо в UI | полный список |
| История пополнений | ❌ нет таблицы | deposits |
| Комментарий о лиде | ❌ | `admin_notes` |
| Статус лида | ⏸️ позже | — |
| Рефералы: кол-во и сумма депозитов | ⚠️ частично | агрегация |

Пользователь видит **только свои** данные в Settings / Profile.

---

## 2. Маппинг: что уже есть → что станет

| Сейчас (AI Compute) | Станет (Investment) | Действие |
|---------------------|---------------------|----------|
| `plans` | Инвестиционные продукты | Расширить поля, переименовать UI |
| `contracts` | Активные депозиты / сделки | `principal_amount`, `profit_percent`, `locked_until` |
| `wallets` | Баланс пользователя | Разделить: `balance`, `available`, `locked` |
| `wallet_transactions` | Ledger | Добавить numeric `amount`, `currency`, FK |
| `withdrawals` | Заявки на вывод | + `currency`, тип (profit / principal) |
| `epochs` / `EpochService` | Ежедневное начисление | Заменить на `ProfitAccrualService` |
| `referral_profiles` | Реф. код и stats | Убрать L2, rate = 20% |
| `referral_accruals` | История комиссий | Привязать к `contract_id` |
| TFLOPS, infra, data centers | — | Убрать из UI (не удалять колонки сразу) |
| COIN token | USDT / BTC | Сменить `token_symbol` и labels |

**Хорошая новость:** каркас (auth, dashboard, admin CRUD plans, withdrawals workflow, referral signup) **переиспользуется**. Переделывать «с нуля» не нужно — нужен **domain pivot** в моделях и сервисах.

---

## 3. Оптимальный порядок рефакторинга (существующее)

Делать **слоями**, не ломая prod между этапами.

### Фаза R0 — Документация и терминология (1 день)
- [ ] Принять это ТЗ, заморозить старую compute-логику
- [ ] Переименовать в UI: Plans → «Investment plans», Contracts → «My deposits», TFLOPS → убрать

### Фаза R1 — Схема данных (2–3 дня)
**Приоритет: без этого нельзя писать логику**

1. **`plans`** — добавить:
   - `min_deposit` (decimal)
   - `annual_profit_percent` (decimal) — вместо `reward_multiplier`
   - `currency` enum (`USDT`, `BTC`)
   - `price_amount` (decimal, optional) — вместо только `price_label`
   - Deprecate: `tflops`, `infra`, `max_tflops`, `daily_estimate` (оставить nullable, не использовать)

2. **`contracts`** — добавить:
   - `principal_amount`, `currency`
   - `annual_profit_percent` (snapshot)
   - `locked_amount` (= principal пока active)
   - `accrued_profit` (rename concept from `accrued_amount`)
   - `started_at`, `ends_at` (datetime)
   - Deprecate: `tflops`, `location_label`

3. **`wallets`** — добавить:
   - `currency`
   - `locked_balance` (сумма в активных депозитах)
   - Переосмыслить: `balance` = total, `available` = можно вывести, `locked` = в депозитах

4. **`wallet_transactions`** — добавить:
   - `amount` (decimal), `currency`
   - `reference_type`, `reference_id` (polymorphic: deposit, contract, withdrawal, referral)
   - `occurred_at`

5. **Новая таблица `deposits`**:
   - `user_id`, `amount`, `currency`, `status` (pending/confirmed/rejected)
   - `method` (mock/gateway), `external_reference`, `confirmed_by`, `confirmed_at`

6. **`withdrawals`** — добавить:
   - `currency`
   - `withdrawal_type` enum: `available_profit`, `available_balance`, `matured_principal`

7. **`users`** — добавить:
   - `phone`, `telegram`, `country_code`
   - `last_login_at`
   - `admin_lead_note` (text, admin-only)

8. **`referral_commissions`** (новая):
   - `referrer_user_id`, `referral_user_id`, `contract_id`
   - `purchase_amount`, `commission_percent` (20), `commission_amount`, `currency`

9. **Settings:** `referral_level1_percent` → **20**, убрать L2 из UI

### Фаза R2 — Сервисы (3–4 дня)
1. `DepositService` — mock пополнение (create pending → admin confirm → credit available)
2. `PlanPurchaseService` — покупка плана (available → locked, create contract, trigger referral 20%)
3. `ProfitAccrualService` — заменить `EpochService` (daily cron по active contracts)
4. `ReferralCommissionService` — 20% on purchase (idempotent по contract_id)
5. Refactor `WithdrawalService` — проверка типа вывода (profit vs matured principal)

### Фаза R3 — Admin CRUD (2 дня)
1. Plans form: min_deposit, annual_profit_percent, currency (убрать TFLOPS/multiplier)
2. Deposits module: список, confirm/reject mock deposits
3. User card: registration, last login, country, phone, tg, lead note, deposits list, withdrawals list, referral stats

### Фаза R4 — Dashboard UI (3–4 дня)
1. **Plans** — показать min deposit, APR, term; кнопка «Invest» → purchase flow
2. **Contracts → My deposits** — principal, profit/day, maturity date, status
3. **Wallet** — wire deposit/withdraw mock forms; split available vs locked
4. **Overview** — убрать compute; показать portfolio summary
5. **Statistics** — profit history вместо epochs/data centers
6. **Referrals** — 20%, history from real commissions

### Фаза R5 — Cleanup (1–2 дня)
- Удалить/скрыть: epochs admin (или rename), compute labels, landing «AI Compute» copy
- Миграция demo seeder под investment plans
- Feature tests end-to-end

---

## 4. Оптимальный порядок дальнейшей разработки (новое)

После рефакторинга — **MVP investment loop**:

```
Этап 1: Mock deposit → баланс available
Этап 2: Plan purchase → locked principal + contract
Этап 3: Daily profit accrual (cron)
Этап 4: Withdraw available (profit + free balance) — mock
Этап 5: Matured principal withdrawal (after term)
Этап 6: Referral 20% on purchase
Этап 7: Admin client card (full CRM-lite)
```

### MVP Definition of Done

- [ ] User: mock deposit → buy plan → видит daily profit → withdraw profit
- [ ] User: по окончании срока — withdraw principal
- [ ] Referrer: +20% при покупке реферала, видит в referrals
- [ ] Admin: CRUD plans (term, %, min), confirm deposits, process withdrawals
- [ ] Admin: карточка клиента с историей и заметкой

### После MVP (backlog)

1. Payment gateway (USDT TRC-20 / BTC)
2. Payout gateway
3. KYC с документами
4. Lead status pipeline
5. GeoIP country auto-fill
6. Multi-currency wallet (USDT + BTC одновременно)
7. Email/TG notifications

---

## 5. Проверка шаблонов: хватает ли полей

### 5.1. Dashboard — Plans (`plan-card.blade.php`)

| Нужно для сделки | Есть в шаблоне | Есть в БД |
|------------------|----------------|-----------|
| Название плана | ✅ name | ✅ |
| Мин. депозит | ❌ | ❌ `min_deposit` |
| Годовой % | ❌ (daily estimate) | ❌ `annual_profit_percent` |
| Срок | ✅ duration | ✅ `duration_days` |
| Валюта USDT/BTC | ❌ | ❌ `currency` |
| TFLOPS, infra | ✅ (лишнее) | ✅ (убрать) |
| Кнопка Invest | ✅ Activate (без action) | — |

**Вердикт:** шаблон **переделать labels**, добавить 3 поля в карточку. Структура grid подходит.

### 5.2. Dashboard — Contracts (`contract-active-card.blade.php`)

| Нужно | Есть | БД |
|-------|------|-----|
| Сумма депозита | ❌ | ❌ `principal_amount` |
| Валюта | ❌ | ❌ |
| % годовых | ❌ | ❌ |
| Начислено profit | ⚠️ accrued (COIN) | ⚠️ `accrued_amount` |
| Дата окончания | ⚠️ ends_label (string) | ❌ `ends_at` |
| Прогресс срока | ✅ progress % | ✅ |
| TFLOPS, location | ✅ (лишнее) | ✅ |

**Вердикт:** карточка **по структуре OK**, заменить содержимое. 4 новых поля в БД.

### 5.3. Dashboard — Wallet (section 4)

| Нужно | Есть | Работает |
|-------|------|----------|
| Total balance | ✅ | demo |
| Available to withdraw | ✅ | demo |
| Locked in deposits | ⚠️ pending (другое значение) | ❌ |
| Deposit form + currency | ⚠️ UI static | ❌ |
| Withdraw form | ⚠️ UI static | ❌ |
| Transaction history | ✅ type/source/amount | demo strings |
| Payout address | ✅ | ✅ |

**Вердикт:** блок **Deposit/Withdraw** — вёрстка есть, нужно wire + `locked` card. История — добавить numeric amount и currency в колонки таблицы.

### 5.4. Dashboard — Referrals (section 5)

| Нужно | Есть |
|-------|------|
| Referral link | ✅ |
| Invited count | ✅ |
| 20% commission label | ❌ (5%/2%) |
| Commission history (real) | ❌ demo rows |
| Referral deposit volume | ❌ |

**Вердикт:** layout OK, **тексты и data source** заменить.

### 5.5. Admin — Plans form

| Нужно | Есть |
|-------|------|
| term (days) | ✅ duration_days |
| profit % | ❌ (reward_multiplier) |
| min deposit | ❌ |
| currency | ❌ |
| TFLOPS, infra, max_tflops | ✅ (убрать) |

**Вердикт:** форма **2×6 grid** — места хватает, заменить поля.

### 5.6. Admin — User card

| Нужно | Есть |
|-------|------|
| Registration datetime | ⚠️ only in list index |
| Country | ❌ |
| Last login | ❌ |
| Email | ✅ |
| Telegram, phone | ❌ |
| Withdrawal history | ❌ (model exists) |
| Deposit history | ❌ |
| Lead comment | ❌ |
| Referral count + volume | ❌ |
| Contracts list | ✅ (compute fields) |

**Вердикт:** layout 2-col **расширить** — добавить блок «Profile», «Deposits», «Withdrawals», «Referrals». Contracts block — retitle to «Deposits».

### 5.7. Settings (user profile)

| Нужно | Есть |
|-------|------|
| phone, telegram | ❌ |
| country | ❌ |
| payout address | ✅ in wallet section |

**Вердикт:** добавить поля в Settings + migration.

---

## 6. Сводка: что НЕ нужно переделывать

| Компонент | Статус |
|-----------|--------|
| Auth (register/login) | ✅ оставить |
| Referral link `/r/{code}` + signup | ✅ оставить |
| Support chat (Reverb) | ✅ оставить |
| Admin withdrawals workflow | ✅ расширить |
| Dashboard 8-section navigation | ✅ переименовать секции |
| Livewire Dashboard architecture | ✅ оставить |
| Deploy / Reverb | ✅ оставить |

---

## 7. Риски и решения

| Риск | Решение |
|------|---------|
| `wallet_transactions` только display strings | Миграция: добавить numeric, постепенно заполнять из сервисов |
| Epochs привязаны к TFLOPS | Новый `ProfitAccrualService`, epochs deprecated |
| Demo seeder с compute data | Новый `InvestmentDemoSeeder` после R1 |
| Два смысла `pending` в wallet | Rename: `pending` → settlement hold; новое `locked_balance` для депозитов |

---

## 8. Резюме одной строкой

> **Платформа уже имеет скелет кошелька, планов, сделок, выводов и рефералов. Pivot = сменить формулы и поля с TFLOPS/epochs на deposit/APR/daily profit, добавить mock deposit и 20% referral on purchase. UI в основном готов — не хватает полей в БД и wiring форм.**

---

*Следующий шаг: Фаза R1 — миграции `plans`, `contracts`, `wallets`, `deposits`, `users`.*
