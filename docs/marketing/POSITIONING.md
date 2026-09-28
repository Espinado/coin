# CloudFlops — утверждённое позиционирование (фаза A)

**Статус:** утверждено как рабочая база для SEO / ИИ / копирайта  
**Дата:** 27.09.2026 (обновлено: staging vs prod)  
**Языки витрины (фаза 1):** RU + EN  
**Язык фазы 2:** LV (после стабилизации RU/EN)

Связанные документы: [SOURCE-OF-TRUTH.md](SOURCE-OF-TRUTH.md), [SEO-AI-BACKLOG.md](SEO-AI-BACKLOG.md).

---

## 0. Среды: staging сейчас, production позже

| Среда | User URL | Admin URL | Для SEO / ИИ |
|-------|----------|-----------|--------------|
| **Staging (сейчас)** | https://coin.arguss.lv | https://admin.coin.arguss.lv | Только отладка. **Не** канон бренда. Желателен `noindex` / закрытие от индекса. |
| **Production (будет)** | `PUBLIC_ORIGIN` — другой домен и хост | `ADMIN_ORIGIN` — другой домен | **Единственный** канон для sitemap, OG, schema, llms.txt, прессы, Search Console |

Плейсхолдеры в документах маркетинга:

| Токен | Смысл | Пример после cutover |
|-------|--------|----------------------|
| `PUBLIC_ORIGIN` | Базовый HTTPS URL пользовательского сайта | `https://www.cloudflops.example` |
| `ADMIN_ORIGIN` | Базовый HTTPS URL админки | `https://admin.cloudflops.example` |
| `PUBLIC_HOST` | Хост без схемы | `www.cloudflops.example` |

Правила:

1. В **публичных** текстах для запуска (About, FAQ, press, llms.txt) писать **`PUBLIC_ORIGIN` / `PUBLIC_HOST`**, не `coin.arguss.lv`.
2. Staging-URL допустимы только в внутренних runbook / HANDOVER как «текущий стенд».
3. При cutover: подставить реальный домен в SoT, env (`APP_URL`, `COIN_USER_DOMAIN`, `COIN_ADMIN_DOMAIN`), пересобрать canonical/sitemap/llms, зарегистрировать **prod** в Search Console.
4. Не смешивать staging и prod в одном sitemap и не продвигать staging как бренд.

---

## 1. Кто мы

| Поле | Значение |
|------|----------|
| Бренд | **CloudFlops** |
| Юрлицо (по умолчанию в конфиге) | CloudFlops LLC |
| Публичный сайт (канон) | `PUBLIC_ORIGIN` *(заполнить при выборе домена)* |
| Админ (не для SEO) | `ADMIN_ORIGIN` |
| Staging (временно) | https://coin.arguss.lv · https://admin.coin.arguss.lv |
| Контакт (по умолчанию) | из `COIN_CONTACT_EMAIL` / config |

> Юр. реквизиты (рег. номер, адрес) при публикации About/пресс-кита сверить с актуальными данными LLC и юристом.

---

## 2. Позиционирование одной фразой

**RU:** CloudFlops — платформа инвестиционных планов с прозрачным кабинетом: пополнение в USDT/BTC, ежедневные начисления по APR выбранного плана, вывод на свой кошелёк и реферальная программа.

**EN:** CloudFlops is an investment-plan platform with a transparent dashboard: top up in USDT/BTC, receive daily accruals based on your plan’s APR, withdraw to your own wallet, and earn Level-1 referral commissions.

**Сайт в каждом каноническом описании:** `PUBLIC_HOST` (после cutover; до этого — не публиковать staging как «официальный»).  
**Риск в каждом публичном материале:** инвестиции связаны с риском — см. Risk Disclosure (`/legal/risks` на `PUBLIC_ORIGIN`).

---

## 3. Гео и аудитория

| Параметр | Решение |
|----------|---------|
| Гео | EU + русскоязычная аудитория |
| Продуктовый акцент | USDT, планы, прозрачность кабинета — **не** «гарантированный доход» |
| Цель для ИИ | Ответ на: кто вы, что предлагаете, где сайт (`PUBLIC_ORIGIN`), какие риски, как связаться |

---

## 4. Что мы есть / чего мы не есть

### Есть

- Инвестиционные планы с параметрами (min deposit, APR, срок)
- Пополнение USDT / BTC (конвертация BTC → USDT)
- Контракт: тело lock → ежедневная прибыль → maturity
- Вывод на внешний кошелёк пользователя
- Рефералы L1 (% от покупки плана и от доплаты при upgrade)
- Live support (гость + кабинет)

### Не есть (запрещено заявлять)

- Не биржа и не брокер с ордерами
- Не банк и не банковский депозит
- Не гарантия доходности / «без риска»
- Не «пассивный доход навсегда»
- Не сравнение с банковским вкладом без оговорок
- Не P2P-обменник

---

## 5. Обязательные дисклеймеры

Использовать на лендинге, About, FAQ, пресс-китах, соцсетях, SEO meta (кратко), `llms.txt`:

**RU (короткий):**  
Инвестиции связаны с риском. Доходность в прошлом не гарантирует доходность в будущем. Подробнее — в Раскрытии рисков: `PUBLIC_ORIGIN/legal/risks`.

**EN (short):**  
Investing involves risk. Past performance does not guarantee future results. See Risk Disclosure: `PUBLIC_ORIGIN/legal/risks`.

**RU (калькулятор / APR):**  
Цифры на лендинге и в калькуляторе — оценочные. Актуальные параметры планов и начислений — в личном кабинете после входа.

**EN (calculator / APR):**  
Figures on the landing page and calculator are estimates. Live plan parameters and accruals are shown in the dashboard after sign-in.

---

## 6. Голос бренда

| Делать | Не делать |
|--------|-----------|
| Короткие фактические предложения | Туманный hype («революция», «гарантия») |
| Один канонический URL: `PUBLIC_ORIGIN` | Рекламировать staging `*.arguss.lv` как бренд |
| Прозрачность: кабинет, статусы, legal | Обещания конкретной месячной доходности в ads |
| RU и EN как равноправные витрины | Машинный перевод без вычитки |

---

## 7. Известный разрыв с текущим UI (к закрытию в фазе B/C)

В `lang/*/coin.php` блок landing hero всё ещё использует legacy-формулировки **AI Compute / compute power**, тогда как продукт и meta_title — **инвестиционная платформа**, а FAQ в сидере уже про APR/планы.

**Решение концепции:** канон для SEO/ИИ = **инвестиционные планы** (этот документ + SOURCE-OF-TRUTH).  
При внедрении кода (фаза B) hero и связанные тексты лендинга нужно привести к этому канону, иначе поисковики и ИИ получат противоречивые сигналы.

---

## 8. Критерий «позиционирование утверждено»

- [x] Одна фраза RU + EN зафиксирована
- [x] Языки фазы 1: RU + EN; LV отложен
- [x] Список анти-сообщений задан
- [x] Дисклеймеры рисков обязательны
- [x] Staging ≠ production: канон через `PUBLIC_ORIGIN`
- [x] Разрыв AI Compute vs investment отмечен для бэклога контента

Дальше: наполнение [SOURCE-OF-TRUTH.md](SOURCE-OF-TRUTH.md).
