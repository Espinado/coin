# CloudFlops — Source of Truth (SEO / AI / About / FAQ)

**Status:** phase A source material for production publication  
**Дата:** 27.09.2026 (обновлено: staging vs prod)  
**Канон позиционирования:** [POSITIONING.md](POSITIONING.md)  

**Канонический сайт (production):** `PUBLIC_ORIGIN` — *другой домен и хост, чем staging*  
**Staging (текущий стенд, не для бренда):** https://coin.arguss.lv  

Во всех публичных цитатах ниже используйте токены `PUBLIC_ORIGIN` / `PUBLIC_HOST`. Перед go-live заменить на финальный домен одним проходом (или генерировать из `APP_URL` / `COIN_USER_DOMAIN` в коде).

---

## A. Карточка фактов (machine-friendly)

| Key | RU | EN |
|-----|----|----|
| Brand | CloudFlops | CloudFlops |
| Legal name | CloudFlops LLC | CloudFlops LLC |
| Product type | Платформа инвестиционных планов | Investment-plan platform |
| Website (canon) | `PUBLIC_ORIGIN` | `PUBLIC_ORIGIN` |
| Staging (internal only) | https://coin.arguss.lv | https://coin.arguss.lv |
| Deposit assets | USDT, BTC | USDT, BTC |
| Accounting currency | USDT | USDT |
| Accrual | Ежедневно по APR плана | Daily based on plan APR |
| Referral | L1, % от покупки / доплаты upgrade | L1, % of purchase / upgrade top-up |
| Not | Не биржа, не банк | Not an exchange, not a bank |
| Risks URL | `PUBLIC_ORIGIN/legal/risks` | `PUBLIC_ORIGIN/legal/risks` |
| Terms URL | `PUBLIC_ORIGIN/legal/terms` | `PUBLIC_ORIGIN/legal/terms` |
| Privacy URL | `PUBLIC_ORIGIN/legal/privacy` | `PUBLIC_ORIGIN/legal/privacy` |
| FAQ URL | `PUBLIC_ORIGIN/legal/faq` | `PUBLIC_ORIGIN/legal/faq` |
| About URL | `PUBLIC_ORIGIN/about` | `PUBLIC_ORIGIN/about` |
| Support | Live chat на сайте + кабинет; email из настроек | Live chat on site + dashboard; email from settings |

---

## B. About — текст страницы `/about`

### B1. Русский

# О CloudFlops

**CloudFlops** — платформа инвестиционных планов с прозрачным личным кабинетом.

Вы пополняете баланс в **USDT** или **BTC** (BTC конвертируется в USDT по курсу платформы), выбираете план с параметрами минимальной суммы, **APR** и срока, открываете контракт и получаете **ежедневные начисления** на доступный баланс. По окончании срока тело инвестиции возвращается на доступный баланс. Доступные средства можно вывести на свой криптокошелёк. Есть реферальная программа первого уровня.

Официальный сайт: **`PUBLIC_HOST`** (`PUBLIC_ORIGIN`).

### Как это работает

1. Регистрация и подтверждение email.  
2. Пополнение кошелька (USDT / BTC).  
3. Покупка инвестиционного плана → создаётся контракт.  
4. Ежедневные начисления прибыли по правилам плана.  
5. По зрелости контракта тело возвращается в доступный баланс.  
6. Заявка на вывод на ваш внешний адрес.

### Чем CloudFlops не является

CloudFlops **не** криптобиржа, **не** банк и **не** гарантия доходности. Параметры планов и фактические начисления отображаются в кабинете; маркетинговые оценки на лендинге носят справочный характер.

### Риски

Инвестиции связаны с риском. Доходность в прошлом не гарантирует доходность в будущем. Перед участием ознакомьтесь с Раскрытием рисков (`PUBLIC_ORIGIN/legal/risks`) и Условиями использования (`PUBLIC_ORIGIN/legal/terms`).

### Компания и контакты

Оператор платформы: **CloudFlops LLC**.  
Поддержка: Live support на сайте и в кабинете; email — контакт платформы.

---

### B2. English

# About CloudFlops

**CloudFlops** is an investment-plan platform with a transparent user dashboard.

You top up in **USDT** or **BTC** (BTC is converted to USDT at the platform rate), choose a plan with a minimum amount, **APR**, and term, open a contract, and receive **daily accruals** to your available balance. At maturity, principal returns to available balance. You can withdraw available funds to your own crypto wallet. A Level-1 referral program is available.

Official website: **`PUBLIC_HOST`** (`PUBLIC_ORIGIN`).

### How it works

1. Register and verify your email.  
2. Top up your wallet (USDT / BTC).  
3. Buy an investment plan → a contract is created.  
4. Receive daily profit accruals per plan rules.  
5. At maturity, principal returns to available balance.  
6. Request a withdrawal to your external address.

### What CloudFlops is not

CloudFlops is **not** a crypto exchange, **not** a bank, and **does not** guarantee returns. Live plan parameters and accruals are shown in the dashboard; landing-page estimates are indicative only.

### Risks

Investing involves risk. Past performance does not guarantee future results. Please read the Risk Disclosure (`PUBLIC_ORIGIN/legal/risks`) and Terms of Use (`PUBLIC_ORIGIN/legal/terms`) before participating.

### Company and contact

Platform operator: **CloudFlops LLC**.  
Support: live chat on the website and in the dashboard; email — platform contact address.

---

## C. Расширенный FAQ (для `/legal/faq` и лендинга)

Формат совместим с админкой Legal FAQ (question / answer).  
В ответах — `PUBLIC_HOST`, не staging-домен.

### C1. English (ready to paste)

**Q: What is CloudFlops?**  
A: CloudFlops is an investment-plan platform at PUBLIC_HOST. You top up USDT (or BTC converted to USDT), buy a plan with a stated APR and term, receive daily profit to your available balance, and can withdraw to your own wallet. Investing involves risk — see the Risk Disclosure.

**Q: How do investment plans work?**  
A: A plan defines minimum investment, APR, and contract duration. When you buy a plan, principal is locked in a contract until maturity. Daily profit is credited to your available balance according to platform rules.

**Q: How is daily profit calculated?**  
A: Accruals follow the plan’s APR and the platform’s daily accrual schedule (shown in your dashboard). Landing-page calculators are estimates only and are not a guarantee of returns.

**Q: What happens when a contract matures?**  
A: When the contract term ends, principal returns to your available balance (subject to Terms). Accrued profit already credited remains in available balance unless otherwise stated in Terms.

**Q: Which currencies can I deposit?**  
A: USDT and BTC. Accounting is in USDT; BTC deposits are converted using the platform exchange rate.

**Q: How do withdrawals work?**  
A: Open Wallet in the dashboard, submit a withdrawal request to your payout address, and confirm any required checks (for example verification or fees shown before confirm). Processing status is visible in the dashboard and admin review may apply.

**Q: How does the referral program work?**  
A: Share your referral link. When someone you invited buys a plan, you may receive a Level-1 commission (default percentage is configured by the platform, commonly 20% of the purchase). On an approved plan upgrade, commission may apply to the top-up difference only. Level-2 is not used.

**Q: Is CloudFlops an exchange or a bank?**  
A: No. CloudFlops is not a crypto exchange and not a bank. It offers investment plans through a user dashboard under its Terms and Risk Disclosure.

**Q: Are returns guaranteed?**  
A: No. Returns are not guaranteed. Past performance does not guarantee future results. Read /legal/risks before investing.

**Q: How do I contact support?**  
A: Use Live support on the landing page (guest) or inside the dashboard after sign-in. You can also use the platform contact email published on the site.

**Q: Where are the legal documents?**  
A: Terms: /legal/terms · Privacy: /legal/privacy · Risks: /legal/risks · FAQ: /legal/faq (on PUBLIC_ORIGIN)

---

### C2. Русский (готово к вставке)

**В: Что такое CloudFlops?**  
О: CloudFlops — платформа инвестиционных планов на PUBLIC_HOST. Вы пополняете баланс в USDT (или BTC с конвертацией в USDT), покупаете план с указанным APR и сроком, получаете ежедневную прибыль на доступный баланс и можете вывести средства на свой кошелёк. Инвестиции связаны с риском — см. Раскрытие рисков.

**В: Как работают инвестиционные планы?**  
О: План задаёт минимальную сумму, APR и срок контракта. При покупке тело инвестиции блокируется в контракте до зрелости. Ежедневная прибыль зачисляется на доступный баланс по правилам платформы.

**В: Как считается ежедневная прибыль?**  
О: Начисления идут по APR плана и расписанию платформы (видно в кабинете). Калькулятор на лендинге — оценочный и не является гарантией доходности.

**В: Что происходит при завершении контракта?**  
О: По окончании срока тело возвращается на доступный баланс (согласно Условиям). Уже начисленная прибыль остаётся в доступном балансе, если иное не указано в Условиях.

**В: Какими валютами можно пополнить?**  
О: USDT и BTC. Учёт ведётся в USDT; депозиты в BTC конвертируются по курсу платформы.

**В: Как работает вывод?**  
О: В кабинете откройте Кошелёк, создайте заявку на вывод на ваш payout-адрес и подтвердите проверки/комиссии, показанные перед отправкой. Статус заявки виден в кабинете; может потребоваться обработка администратором.

**В: Как работает реферальная программа?**  
О: Поделитесь реферальной ссылкой. Когда приглашённый покупает план, вы можете получить комиссию L1 (процент задаётся платформой, часто 20% от суммы покупки). При одобренном повышении плана комиссия может считаться только с доплаты. Уровень 2 не используется.

**В: CloudFlops — это биржа или банк?**  
О: Нет. CloudFlops не криптобиржа и не банк. Платформа предлагает инвестиционные планы через личный кабинет на условиях Terms и Risk Disclosure.

**В: Гарантирована ли доходность?**  
О: Нет. Доходность не гарантируется. Прошлые результаты не гарантируют будущие. Перед участием прочитайте /legal/risks.

**В: Как связаться с поддержкой?**  
О: Live support на лендинге (гость) или в кабинете после входа. Также доступен контактный email платформы.

**В: Где юридические документы?**  
О: Условия: /legal/terms · Конфиденциальность: /legal/privacy · Риски: /legal/risks · FAQ: /legal/faq (на PUBLIC_ORIGIN)

---

## D. One-liner для meta / Open Graph / schema

**EN title:** CloudFlops — Investment plans with daily APR accruals  
**EN description:** Top up USDT or BTC, buy an investment plan, track daily accruals in your dashboard, and withdraw to your wallet. Investing involves risk.

**RU title:** CloudFlops — инвестиционные планы с ежедневными начислениями  
**RU description:** Пополнение USDT или BTC, покупка плана, ежедневные начисления в кабинете и вывод на свой кошелёк. Инвестиции связаны с риском.

Canonical / `og:url` всегда = страницы на **`PUBLIC_ORIGIN`**, не staging.

---

## E. Содержимое `/llms.txt` (текст; файл на сайте — фаза C)

Генерировать с подстановкой `PUBLIC_ORIGIN` из env на **production**. На staging либо не публиковать, либо добавить пометку «staging — not canonical».

```
# CloudFlops

> CloudFlops is an investment-plan platform. Official site: PUBLIC_ORIGIN

CloudFlops is not an exchange and not a bank. Returns are not guaranteed.
Investing involves risk: PUBLIC_ORIGIN/legal/risks

## Canonical pages
- PUBLIC_ORIGIN/
- PUBLIC_ORIGIN/about
- PUBLIC_ORIGIN/legal/faq
- PUBLIC_ORIGIN/legal/terms
- PUBLIC_ORIGIN/legal/privacy
- PUBLIC_ORIGIN/legal/risks

## Product summary
- Deposits: USDT, BTC (accounted in USDT)
- Buy a plan → contract with APR and term
- Daily profit accruals to available balance
- Withdrawals to user-owned wallet
- Level-1 referrals on plan purchase / upgrade top-up

## Contact
- Live support on the website and in the user dashboard
```

---

## F. Чеклист перед публикацией (юрист / продукт / cutover)

- [ ] Выбран и зафиксирован финальный `PUBLIC_ORIGIN` / `ADMIN_ORIGIN` / хост  
- [ ] Юр. имя, адрес, рег. номер LLC вписаны точно  
- [ ] Контактный email актуален  
- [ ] % рефералки не зафиксирован «навсегда» без оговорки «может меняться»  
- [ ] Формула APR не обещает конкретный месячный доход в ads  
- [ ] RU и EN не противоречат друг другу  
- [ ] Hero лендинга приведён к investment-канону (сейчас возможен legacy AI Compute)  
- [ ] Staging `*.arguss.lv` не числится как официальный URL в press / llms / schema  
- [ ] После cutover: 301 со старого стенда (если нужен) или `noindex` на staging  

После approve → внедрение по [SEO-AI-BACKLOG.md](SEO-AI-BACKLOG.md).
