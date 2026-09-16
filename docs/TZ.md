# CloudFlops — техническое задание (актуальная версия)

**Версия:** 3.0  
**Дата:** 17.09.2026  
**Prod:** [coin.arguss.lv](https://coin.arguss.lv) · [admin.coin.arguss.lv](https://admin.coin.arguss.lv)  
**Бренд в UI:** CloudFlops <span style="color:#c0392b">*(ранее «Coin» — переименовано, логотип CloudFlops, favicon, письма, auth)*</span>  
**Кодовое имя репозитория / env:** `coin` (домены и префиксы таблиц не менялись)

> <span style="color:#c0392b">**Этот документ заменяет устаревший `TZ.md` v2.0 (AI Compute / TFLOPS / epochs) и дополняет `TZ-investment.md`.**  
> Красным отмечены фактические изменения относительно старого ТЗ и то, что реализовано в коде на 17.09.2026.</span>

---

## 0. Легенда пометок

| Пометка | Значение |
|---------|----------|
| <span style="color:#c0392b">красный текст</span> | Изменено / реализовано / отличается от старого ТЗ |
| ✅ | Работает в prod-коде |
| ⚠️ | Частично / mock / legacy |
| ❌ | Не реализовано |

---

## 1. Что мы строим (актуально)

<span style="color:#c0392b">**CloudFlops** — инвестиционная платформа (pivot с «AI Compute»).</span> Пользователь:

1. Регистрируется (в т.ч. по реферальной ссылке)
2. Пополняет баланс (**USDT**, опционально **BTC** с конвертацией в USDT)
3. Покупает **инвестиционный план** → создаётся **контракт** (депозит на срок)
4. Получает **ежедневную прибыль** по APR плана
5. Выводит доступные средства на внешний кошелёк
6. Приглашает рефералов и получает **20% от покупок планов** рефералов и **20% от доплаты**, если реферал повышает план на более дорогой

<span style="color:#c0392b">Это **не** биржа, **не** epoch-майнинг и **не** начисление TFLOPS. Старые термины (epoch, compute, COIN-токен) в UI убраны или оставлены как legacy в БД.</span>

Параллельно — **админ-панель** на отдельном домене для операционной работы.

---

## 2. Бизнес-логика и алгоритмы

### 2.1. Типы средств на кошельке (`wallets`)

| Поле | Смысл | Алгоритм изменения |
|------|-------|-------------------|
| `balance` | Общий учётный баланс | ↑ пополнение, прибыль, реферал, возврат тела; ↓ при одобрении вывода |
| `available` | Свободно: пополнения, прибыль, реферал, возвращённый principal | ↑ credit; ↓ покупка плана, доплата при смене плана, заявка на вывод |
| `locked_balance` | Тело активных инвестиций (principal) | ↑ покупка / upgrade; ↓ maturity контракта |
| `pending` | Заморожено под заявку на вывод | ↑ создание withdrawal; ↓ approve/reject |

<span style="color:#c0392b">**Реализовано.** Сервисы: `DepositService`, `PlanPurchaseService`, `ProfitAccrualService`, `WithdrawalService`.</span>

---

### 2.2. План (`plans`) и контракт (`contracts`)

**План** — продукт админки: название, slug, `min_deposit`, `annual_profit_percent`, `duration_days`, `sort_order`, `is_active`, infra/tflops (legacy-колонки для UI-калькулятора).

**Контракт** — активная инвестиция пользователя:
- `principal_amount`, `annual_profit_percent` (snapshot), `started_at`, `ends_at`, `duration_days`
- `days_elapsed`, `last_accrued_on`, `accrued_amount`, `progress_percent`
- `status`: `active` → `completed` (по maturity)

<span style="color:#c0392b">**Покупка плана реализована** (`PlanPurchaseService::purchase`). Старый пункт «Не сделано: покупка тарифа» — **закрыт**.</span>

#### Алгоритм покупки плана

```
1. Проверки:
   - plan.is_active
   - не Enterprise без min_deposit
   - amount >= min_deposit
   - wallet.available >= amount
2. Транзакция БД:
   - available -= amount
   - locked_balance += amount
   - Contract.create(status=active, code=CTR-XXXXXX, dates, APR snapshot)
   - WalletTransaction: type=Investment, amount=-amount
   - ReferralCommissionService::onContractPurchased (20% от суммы покупки)
   - user.expected_daily_reward пересчёт
```

---

### 2.3. Смена плана (из «Мои инвестиции») — с одобрением админа

<span style="color:#c0392b">**Реализовано 17.09.2026.** Кнопка «Изменить план». Это **не** новая покупка — обновляется **тот же** контракт.  
**Изменение применяется только после одобрения администратором** (раздел Admin → «Изменения планов»).</span>

#### UX: нехватка средств

<span style="color:#c0392b">Вместо отказа с ошибкой:</span> «Не хватает средств. [Пополнить баланс]» — ссылка переводит в секцию Wallet (пополнение). Кнопка «Изменить план» disabled, пока доплата не покрыта.

#### Алгоритм запроса пользователя (`PlanChangeRequestService::createRequest`)

```
1. Проверки:
   - contract active, принадлежит user
   - newPlan active, ≠ current plan
   - нет другого pending-запроса по этому contract
   - topUp = max(0, newPlan.min_deposit - principal)
   - если topUp > 0 и available < topUp → отказ (UI: «Пополнить баланс»)

2. Транзакция:
   - если topUp > 0: available -= topUp; pending += topUp (резерв под доплату)
   - PlanChangeRequest.create(status=pending, reference=PCR-XXXXXXXX)
   - event PlanChangeRequestUpdated → admin.plan-changes + wallet.user.{id}
```

#### Алгоритм одобрения админом (`approve`)

```
1. Admin → Plan Changes → запрос → «Одобрить»
2. Проверки: status=pending, contract still active, plan still active
3. PlanPurchaseService::changePlan(user, contract, newPlan, topUpHeld=true):
   - pending -= topUp; locked_balance += topUp (если topUp > 0)
   - principal, plan_id, APR, duration, ends_at — как в changePlan
   - referral 20% от topUp
4. request.status = approved; processed_by, processed_at
5. event PlanChangeRequestUpdated:
   - Admin toast: «План пользователя {имя} изменён»
   - User realtime: toast «Изменение плана подтверждено» + reload контрактов
```

#### Алгоритм отклонения (`reject`)

```
1. Admin → «Отклонить» (+ необязательная заметка)
2. если topUp > 0: pending -= topUp; available += topUp (возврат резерва)
3. request.status = rejected
4. event PlanChangeRequestUpdated → user toast «Запрос отклонён»
```

#### Применение плана (`PlanPurchaseService::changePlan`) — вызывается только при approve

```
topUp = max(0, newPlan.min_deposit - contract.principal_amount)

Если topUp > 0 (повышение):
  - topUpHeld=true → списание из pending (уже зарезервировано)
  - topUpHeld=false → available -= topUp (legacy/direct)
  - locked_balance += topUp
  - principal_amount = old_principal + topUp
  - WalletTransaction: Plan upgrade
  - ReferralCommissionService::onContractUpgradeTopUp (20% от topUp)

Если topUp = 0 (понижение):
  - principal_amount не меняется; разница НЕ возвращается

В обоих случаях:
  - plan_id, APR, duration_days, tflops, location_label ← новый план
  - started_at, days_elapsed, accrued — без сброса
  - ends_at = started_at + duration_days
```

#### Realtime (Reverb)

| Канал | Событие | Кто |
|-------|---------|-----|
| `admin.plan-changes` | `.PlanChangeRequestUpdated` | badge в nav + toast |
| `wallet.user.{id}` | `.PlanChangeRequestUpdated` | reload dashboard + toast |

---

### 2.4. Ежедневное начисление прибыли

<span style="color:#c0392b">**Заменяет старую epoch-логику.** Сервис: `ProfitAccrualService`. Cron: `coin:accrue-daily-profits` (09:00 Europe/Riga).</span>

#### Формула

```
daily_profit = principal_amount × (annual_profit_percent / 100) / 365
```

#### Алгоритм `accrueDaily()` (по каждому active contract)

```
1. Если last_accrued_on == today → skip (идемпотентность)
2. available += daily_profit; balance += daily_profit
3. contract.accrued_amount += daily_profit
4. contract.days_elapsed += 1; progress_percent обновить
5. WalletTransaction: Daily profit (+)
6. Email-уведомление (если включено у user)
7. Если ends_at наступил ИЛИ days_elapsed >= duration_days:
   - status = completed
   - locked_balance -= principal
   - available += principal
   - WalletTransaction: Principal release (+)
```

<span style="color:#c0392b">**Lazy accrual:** при открытии Dashboard вызывается `settleMatureContractsForUser()` — догоняет maturity без ожидания cron.</span>

---

### 2.5. Пополнение (deposit)

<span style="color:#c0392b">**Реализовано (mock).** UI: mock payment gateway (банк-заглушка). `DepositService`.</span>

```
createPending(user, amount, currency):
  - currency ∈ {USDT, BTC}
  - status = pending, method = mock
  - если COIN_DEPOSITS_AUTO_CONFIRM_MOCK=true → confirm сразу

confirm(deposit):
  - BTC → USDT через ExchangeRateService (btc_per_usdt из settings)
  - available += credited; balance += credited
  - WalletTransaction: Top-up
```

Админ: модуль **Deposits** — confirm/reject вручную, если auto-confirm выключен.

---

### 2.6. Вывод (withdrawal)

<span style="color:#c0392b">**Реализовано.** Старый пункт «кнопка withdrawal не отправляет заявку» — **закрыт**.</span>

#### Создание заявки (`WithdrawalService::createForUser`)

```
Проверки:
  - user не blocked
  - kyc_required_for_withdrawal → kyc_status == approved
  - amount >= min_withdrawal (settings)
  - amount <= available
  - payout_address заполнен

Действия:
  - available -= amount; pending += amount
  - Withdrawal(status=pending, ref=WD-XXXXXXXX)
  - event WithdrawalUpdated (Reverb → dashboard)
```

#### Статусы (админ)

| Переход | Деньги |
|---------|--------|
| pending → approved/processing/paid | pending ↓, balance ↓ |
| pending → rejected | pending ↓, available ↑ |
| paid | WalletTransaction Payout (net = amount - network_fee) |

---

### 2.7. Реферальная программа

<span style="color:#c0392b">**L1 реализован полностью.** Комиссия **20% от суммы покупки плана** и **20% от доплаты при повышении плана**, **не** от daily profit. Level 2 — только поле в settings, **не используется**.</span>

#### Бизнес-правила комиссии

| Событие | База для расчёта | Когда начисляется | Кому |
|---------|------------------|-------------------|------|
| Первая покупка плана рефералом | `principal_amount` контракта | Сразу при `PlanPurchaseService::purchase()` | Прямой пригласивший (`users.referred_by_user_id`) |
| Повышение плана (upgrade) | **Разница (доплата)** = `max(0, min_deposit нового плана − текущий principal)` | После **одобрения админом** смены плана (`PlanChangeRequestService::approve` → `changePlan`) | Тот же пригласивший |
| Понижение плана (downgrade) | — | **Не начисляется** (доплаты нет) | — |
| Отклонённая заявка на смену | — | **Не начисляется** | — |
| Повторная покупка того же плана (новый контракт) | Полная сумма нового контракта | Сразу при покупке | Тот же пригласивший |

**Формулы** (процент из `referral_level1_percent`, по умолчанию **20**):

```
purchase_commission = principal × referral_level1_percent / 100

topUp = max(0, newPlan.requiredDepositAmount() - contract.principal_amount)
upgrade_commission = topUp × referral_level1_percent / 100
```

**Пример upgrade:** реферал на Core (1 100 USDT) → Cluster (min 3 400 USDT):
- `topUp = 2 300 USDT`
- `upgrade_commission = 460 USDT` (20%)
- Если при покупке Core уже была комиссия 220 USDT, итого по контракту в `referral_commissions`: purchase_amount 3 400, commission_amount 680.

**Код:**
- Покупка: `ReferralCommissionService::onContractPurchased()`
- Доплата при смене: `ReferralCommissionService::onContractUpgradeTopUp()` ← вызывается из `PlanPurchaseService::changePlan()` при `topUp > 0`
- Зачисление: `referrer.wallet.available` и `balance` ↑; запись `wallet_transactions` тип «Referral credit»; e-mail при включённых уведомлениях.

```
GET /r/{code} → cookie coin_referral_code (30d) + session
POST /register → referred_by_user_id, invited_count++

При purchase(contract):
  commission = principal × referral_level1_percent / 100
  → referrer.available += commission
  → ReferralCommission (unique contract_id)

При upgrade topUp (после approve):
  commission = topUp × referral_level1_percent / 100
  → referrer.available += commission
  → ReferralCommission по contract_id: purchase_amount и commission_amount накапливаются
```

---

### 2.8. Устаревшая epoch-модель

| Было (TZ v2) | Сейчас |
|--------------|--------|
| Epoch settlement, TFLOPS × rate | ⚠️ Таблицы `epochs` есть, UI редиректит на Profit accrual |
| `reward_rate`, `epochs_per_day` в settings | ⚠️ Legacy keys, скрыты из admin UI |
| Statistics «за эпоху» | ✅ За день / неделю / месяц по wallet transactions |

---

## 3. Роли и домены

| Роль | Домен | Guard | Сессия |
|------|-------|-------|--------|
| Guest | user domain, лендинг | — | guest support token |
| User | `COIN_USER_DOMAIN` | `web` | изолирована |
| Admin | `COIN_ADMIN_DOMAIN` | `admin` | изолирована |

<span style="color:#c0392b">`SESSION_DOMAIN` должен быть **null** — cookies не текут между поддоменами.</span>

---

## 4. Регистрация и авторизация

### 4.1. Пользователь — регистрация

**URL:** `GET/POST /register` (только user domain)

| Шаг | Поле | Валидация | Действие |
|-----|------|-----------|----------|
| 1 | `name` | required, string, max:255 | — |
| 2 | `email` | required, lowercase, email, unique:users | — |
| 3 | `password` | required, confirmed, `Password::defaults()` | hash |
| 4 | — | — | `ReferralService::attributeReferrerOnSignup()` (cookie/session) |
| 5 | — | — | `Registered` event, `Auth::login`, redirect `/dashboard` |

<span style="color:#c0392b">Кошелёк **не** создаётся при регистрации — лениво при первой финансовой операции (`WalletService::ensureWallet`).</span>

**Реферальный вход:**

```
GET /r/{code}
  → ReferralInviteController
  → cookie + session (30 дней)
  → redirect /register
```

---

### 4.2. Пользователь — вход

**URL:** `GET/POST /login`

| Шаг | Проверка |
|-----|----------|
| 1 | email lowercase trim |
| 2 | Rate limit 5 попыток |
| 3 | credentials valid |
| 4 | `is_blocked` → ошибка «Аккаунт заблокирован» |
| 5a | если `email_two_factor_enabled` → redirect `/login/two-factor` |
| 5b | иначе → login, `last_login_at = now()`, session regenerate |

**2FA (опционально, включается в Settings):**

| Шаг | URL | Валидация |
|-----|-----|-----------|
| 1 | POST login | — |
| 2 | GET `/login/two-factor` | session challenge |
| 3 | POST code | 6 цифр, TTL 10 мин, throttle 6/min |
| 4 | Resend | throttle 3/min, email `login-verification` |

<span style="color:#c0392b">Чекбокс «Запомнить меня» **убран** из UI login (17.09.2026).</span>

**Восстановление пароля:** стандартный Breeze flow (`forgot-password` → email → `reset-password/{token}`).

---

### 4.3. Администратор — только по приглашению

<span style="color:#c0392b">Публичная регистрация **404**. Reset-password route **404**. Только invite + forgot → invite-link.</span>

#### Первичное создание admin (seeder)

`admin@coin.local` / пароль из seeder — только dev.

#### Приглашение нового admin

```
Admin → Admins → Invite (email)
  → AdminInvitation (token_hash, TTL 72h)
  → email admin-invitation

GET /invite/{token} → форма name + password
POST → Admin created / password set
  → mandatory 2FA challenge
  → login
```

#### Вход admin (каждый раз)

```
POST /login (email + password)
  → AdminLoginTwoFactorService::beginChallenge (всегда)
  → GET /login/two-factor
  → POST 6-digit code (throttle)
  → session admin guard
```

#### Forgot password admin

```
POST /forgot-password (email)
  → если admin exists → invitation-style reset link (не Laravel reset)
```

---

## 5. Клиентская часть — Dashboard (Livewire)

**Компонент:** `App\Livewire\Dashboard`  
**URL:** `/dashboard`, deep-link `?section=0..7`  
**Layout:** `layouts.coin-dashboard`, бренд CloudFlops

### 5.1. Секция 0 — Overview (Обзор)

| Элемент | Источник данных |
|---------|-----------------|
| Активные инвестиции, прогресс | `activeContracts` |
| Распределение по планам | `planAllocation` chart |
| Lifetime profit | sum wallet tx profit types |
| Следующее окончание | nearest `ends_at` |
| Быстрые действия | `setSection(n)` |

Валидация: нет (read-only).

---

### 5.2. Секция 1 — Plans (Планы)

| Действие | Метод | Валидация |
|----------|-------|-----------|
| Выбор плана | `selectPlan(id)` | plan exists |
| Калькулятор суммы | `power` (slider) | clamp min..max по plan |
| **Новая покупка** | `openInvestmentPaymentModal` → `confirmInvestmentPayment` | min_deposit, balance, not enterprise |
| **Смена плана** | `openChangePlan` → `openPlanChangeModal` → `confirmPlanChange` → **pending admin** | topUp, insufficient → link top-up, one pending/contract |

<span style="color:#c0392b">Режим смены плана: баннер + `changingContractId`, кнопка «Изменить план», модал с доплатой.</span>

**Порядок заполнения (покупка):**
1. Выбрать план (клик по карточке)
2. Слайдер суммы ≥ min_deposit
3. «Купить план» → модал review → confirm
4. Processing 2s (demo) → success → «Мои инвестиции»

**Порядок (смена плана):**
1. Мои инвестиции → «Изменить план»
2. Выбрать целевой план
3. Если не хватает доплаты → «Не хватает средств. Пополнить баланс»
4. «Изменить план» → модал → Confirm → **запрос PCR-… отправлен админу**
5. На карточке контракта badge «Ожидает → {план}»
6. После approve админом → realtime toast + контракт обновлён

---

### 5.3. Секция 2 — My Investments (Мои инвестиции)

| Действие | Метод |
|----------|-------|
| Список active / completed | `DashboardDataService` |
| **Подробнее** | `openContractDetails` → modal (plan + contract fields) |
| **Изменить план** | `openChangePlan` |
| Upgrade (legacy redirect) | заменён на change plan |

Карточка контракта: principal, APR, daily profit, accrued, maturity, progress bar.

---

### 5.4. Секция 3 — Statistics (Статистика)

| Элемент | Описание |
|---------|----------|
| Period toggle | day / week / month |
| Profit total | `RewardPeriodTotal` + transactions |
| Chart 24H/14D/30D | accrual chart |
| Investment breakdown | по active contracts |

Read-only.

**Отдельная страница:** `/dashboard/profit-history` — Livewire `ProfitHistory`, pagination.

---

### 5.5. Секция 4 — Wallet (Кошелёк)

| Блок | Методы | Валидация |
|------|--------|-----------|
| Балансы | display | — |
| Top-up USDT/BTC | `depositAmount`, `depositCurrency` | required, numeric, min:1 |
| Mock gateway | `proceedToTopUpBank`, `confirmTopUpBankPayment` | step machine |
| Withdraw | `withdrawAmount` | required, numeric, min:1; service rules |
| Max withdraw | `setWithdrawMax` | = available |
| Payout address | в wallet model | required для withdraw |
| Transactions | paginated, sort | search |

<span style="color:#c0392b">Все суммы в UI — **USDT** (labels через `MoneyFormat`).</span>

**Порядок пополнения:**
1. Ввести сумму / preset
2. Выбрать валюту (USDT/BTC)
3. «Пополнить» → gateway → bank mock → confirm
4. Auto-confirm или admin confirm → available ↑

**Порядок вывода:**
1. Указать payout address (Settings/Wallet)
2. Сумма ≤ available
3. «Запросить вывод» → modал → confirm
4. Admin меняет статус → paid

---

### 5.6. Секция 5 — Referrals (Рефералы)

| Элемент | Метод |
|---------|-------|
| Ссылка `/r/{code}` | copy |
| Invite email | `sendReferralInvite` — email required, not self |
| Stats | invited_count, commissions sum |
| History | `referralCommissions` real data |

<span style="color:#c0392b">Demo referral accruals **заменены** реальными commissions.</span>

---

### 5.7. Секция 6 — Settings (Настройки)

| Блок | Метод | Валидация |
|------|-------|-----------|
| Email | `saveProfileEmail` | required, email, unique, current_password |
| Password | `saveProfilePassword` | current + Password::defaults() + confirmed |
| Phone, Telegram, Country | `saveProfile` | phone max:32, telegram max:64, country size:2 |
| 2FA toggle | enable/disable | password on disable |
| Notifications | toggles | boolean prefs |

---

### 5.8. Секция 7 — Support (Поддержка)

| Действие | Валидация |
|----------|-----------|
| Create ticket | subject min:3 max:120; category enum; body min:10 max:5000 |
| Reply | body min:2 max:5000 |
| Realtime | Reverb `support.user.{id}` |

**Guest support (лендинг):** `GuestSupportChat` — email + subject + body, session token, channel `support.guest.{ticketId}`.

---

## 6. Админ-панель — модули

**Base URL:** admin domain, middleware `auth:admin`

### 6.1. Dashboard

- Метрики: users, contracts, deposits, withdrawals, tickets (`AdminOverviewService`)
- Read-only

---

### 6.2. Users (Пользователи)

| Экран | Функции |
|-------|---------|
| Index | search email/name, sort, paginate |
| Show | wallet, contracts, deposits, withdrawals, referral, support, transactions |
| Update | PATCH |

**Порядок / валидация (PATCH user):**

| Поле | Правила |
|------|---------|
| `kyc_status` | required, in: none/pending/approved/rejected |
| `is_blocked` | required boolean |
| `admin_lead_note` | nullable, max:5000 |
| `phone` | nullable, max:32 |
| `telegram` | nullable, max:64 |
| `country_code` | nullable, size:2 |

<span style="color:#c0392b">**Реализовано из ТГ-комментария:** дата регистрации, last_login_at, контакты, lead note, история deposits/withdrawals, реферальная статистика. **Статус лида (pipeline)** — ❌ позже.</span>

---

### 6.3. Plan Changes (Изменения планов)

| Экран | Функции |
|-------|---------|
| Index | pending/approved/rejected, search, badge в nav |
| Show | from/to plan, topUp, contract, user context |
| Approve | `PlanChangeRequestService::approve` → changePlan |
| Reject | возврат резерва topUp на available |

<span style="color:#c0392b">**Realtime:** канал `admin.plan-changes`, toast при новом запросе и при обработке.</span>

---

### 6.4. Plans (Планы) — CRUD

| Действие | Ограничения |
|----------|-------------|
| Create/Update | см. validation ниже |
| Delete | запрещено если есть contracts |

**Валидация формы плана:**

| Поле | Правила |
|------|---------|
| name | required, max:120 |
| slug | required, alpha_dash, unique |
| price_label | required, max:80 |
| min_deposit | nullable, numeric, min:0 |
| annual_profit_percent | nullable, numeric, min:0 |
| duration_days | nullable, integer, min:1 |
| tflops | required, integer, min:1 |
| infra | required, max:120 |
| reward_multiplier | required, numeric, min:0 |
| sort_order | required, integer, min:0 |
| is_active, is_featured | boolean |

<span style="color:#c0392b">currency принудительно USDT из config. UI labels — USDT (`formattedPriceLabel`).</span>

---

### 6.5. Deposits (Пополнения)

| Действие | Сервис |
|----------|--------|
| Index/Show | filters by status |
| Confirm | `DepositService::confirm` |
| Reject | `DepositService::reject` |

---

### 6.6. Withdrawals (Выводы)

| Действие | Валидация статуса |
|----------|-------------------|
| Index/Show | filter status |
| PATCH status | in: pending/approved/processing/paid/rejected |

`WithdrawalService::updateStatus` — см. §2.6

---

### 6.7. Profit accrual (Начисления)

- Список wallet transactions type = Daily profit
- <span style="color:#c0392b">Заменяет admin Epochs list (redirect)</span>

---

### 6.8. Epochs (Legacy)

- `GET /epochs` → redirect profit-accrual
- Detail view остаётся для старых записей

---

### 6.9. Settings (Настройки платформы)

| Key | Default | Назначение |
|-----|---------|------------|
| token_symbol | USDT | Display |
| min_withdrawal | 10 | Min payout |
| network_fee | 0.50 | Fee on paid |
| referral_level1_percent | 20 | Commission |
| referral_level2_percent | 0 | Unused |
| kyc_required_for_withdrawal | 0 | Gate withdraw |
| maintenance_mode | 0 | ⚠️ **не enforced** |
| btc_per_usdt | 2 | Mock BTC rate |

---

### 6.10. Admins (Персонал)

| Действие | Описание |
|----------|----------|
| Invite | email → invitation |
| Resend / Revoke | invitation TTL 72h |
| Delete admin | нельзя self / last admin |
| Forgot | reset via invite link |

---

### 6.11. Support

| Действие | Валидация |
|----------|-----------|
| Reply | message required |
| Status | open / pending / closed |
| Realtime | `support.admin`, ticket channels |

---

## 7. Лендинг

- Маркeting page `home.blade.php`
- Guest support widget
- <span style="color:#c0392b">Бренд CloudFlops, logo horizontal, favicon</span>
- CTA в dashboard/register

---

## 8. Email-шаблоны

| Шаблон | Когда |
|--------|-------|
| login-verification | User 2FA |
| admin-login-verification | Admin 2FA |
| admin-invitation | Invite / reset admin |
| referral-invitation | Referral invite |
| user-notification | Profit, expiry, payout, etc. |

<span style="color:#c0392b">Шапка писем — logo CloudFlops (`PlatformBrand::logoUrl`).</span>

---

## 9. Cron и фоновые задачи

| Command | Schedule | Описание |
|---------|----------|----------|
| `coin:accrue-daily-profits` | daily 09:00 Europe/Riga | Daily profit + maturity |
| `schedule:run` | * * * * * (server cron) | Laravel scheduler |

Log: `storage/logs/profit-accrual.log`

---

## 10. Статус относительно TZ v2.0 (14.09.2026)

### Было «Не сделано» → сейчас

| Пункт v2 | Статус |
|----------|--------|
| Покупка тарифа | ✅ `PlanPurchaseService` |
| User withdrawal | ✅ `WithdrawalService` + UI |
| Referral L1 accrual | ✅ 20% on purchase |
| Auto epochs | ✅ Заменено daily cron |
| Deposit mock | ✅ `DepositService` |
| Email notifications | ✅ profit, payout, 2FA, referral |

### Было «Частично» → сейчас

| Пункт | Статус |
|-------|--------|
| Dashboard цифры demo | ⚠️ seeder demo user; real users — live data |
| Withdrawal button | ✅ wired |
| Referral demo rows | ✅ real commissions |

### Всё ещё не сделано

| Пункт | Статус |
|-------|--------|
| Real blockchain deposit/payout | ❌ mock only |
| KYC document upload | ❌ status manual in admin |
| Maintenance mode enforcement | ❌ setting only |
| PWA | ❌ |
| Multi-language (full) | ⚠️ ru primary, lang files exist |
| Referral Level 2 | ❌ by design |
| Lead status pipeline | ❌ |
| GeoIP auto country | ❌ manual country_code |

### Новое после v2

| Функция | Статус |
|---------|--------|
| Investment pivot (APR, principal) | ✅ |
| Plan change (upgrade/downgrade) | ✅ |
| Plan change admin approval + realtime | ✅ |
| Insufficient funds → top-up link | ✅ |
| Contract details modal | ✅ |
| CloudFlops rebrand | ✅ |
| USDT labels everywhere | ✅ |
| Admin deposits module | ✅ |
| Profit accrual admin view | ✅ |
| BTC→USDT deposit conversion | ✅ mock rate |

---

## 11. Definition of Done (актуальный)

### Must have — ✅ выполнено

- [x] Mock deposit → available balance
- [x] Plan purchase → contract + locked principal
- [x] Daily profit accrual (cron + lazy)
- [x] Withdraw available funds (mock admin payout)
- [x] Matured principal release
- [x] Referral 20% on plan purchase
- [x] Admin: plans CRUD, deposits, withdrawals, users
- [x] End-to-end без demo seeder (см. `InvestmentFlowTest`, `PlanChangeTest`)

### Should have

- [ ] Real payment gateway
- [ ] KYC upload flow
- [ ] Maintenance middleware
- [ ] Lead pipeline

---

## 12. Техническая справка

**Стек:** Laravel 11, Livewire 3, Reverb, Vite, MySQL

**Ключевые сервисы:**
- `PlanPurchaseService` — purchase, changePlan, topUpRequired
- `ProfitAccrualService` — daily accrual, maturity
- `DepositService`, `WithdrawalService`
- `ReferralService`, `ReferralCommissionService`
- `DashboardDataService`, `SupportTicketService`, `PlatformSettingsService`

**Брендинг:** `App\Support\PlatformBrand`, `config/coin.php` → brand, `public/cloudflops/*.png`

**Deploy:**
```bash
ssh argussl@arguss.lv "cd ~/coin.arguss.lv && bash deploy-prod.sh"
```

**Demo (после seed):**
- User: `test@test.lv` / `test1234`
- Admin: `admin@coin.local` / `admin1234`

---

## 13. Резюме одной строкой

> <span style="color:#c0392b">**CloudFlops** — инвестиционная платформа: пополнение → покупка/смена плана → ежедневная прибыль → вывод → реферальные 20%. Полный цикл реализован на mock-платежах; prod-домены coin.arguss.lv; UI rebrand CloudFlops.</span>

---

*Состояние на 17.09.2026. Документ синхронизирован с кодом в `C:\laragon\www\coin`.*
