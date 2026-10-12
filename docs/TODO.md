# CloudFlops — TODO

## Hardening backlog (отложено, 2026-09-28)

Закрыто ранее: H1–H7, H11, H14–H15 (webhook, mock, concurrency, Viewer/Operator ACL, guest token, cabinet ownership, live toggle). Commit `34fedad`.

- [x] **Payout 2FA** — уже сделано: вывод требует пароль + email-код (`WithdrawalTwoFactorService`), независимо от флага login-2FA.
- [x] **H8 — Login/register 2FA** — login всегда через email-код; регистрация через email verification; toggle в профиле убран (только badge «Обязательно»).
- [x] **H9 — Guest Turnstile** — `TURNSTILE_ENABLED=true` + keys на staging/prod (виджет Cloudflare: добавить hostname `coin.arguss.lv`, если ещё нет).
- [x] **H10 — KYC на вывод** — `kyc_required_for_withdrawal=1` на staging и prod.
- [x] **H12 — Staging SEO** — `SeoVisibility` + пустой allowlist → staging `Disallow: /` и meta `noindex`; prod только `cudaflops.com`.
- [x] **H13 — Invite role** — в invite UX выбор Operator / Viewer; Superadmin через invite недоступен.

## Compliance / безопасность

- [x] **Включить обязательный KYC для вывода** — см. H10 (`kyc_required_for_withdrawal = 1`). Upload/approve — admin/users + chat KYC photos.

## Платежи / UX

- [ ] **DepositUpdated + фоновый poll** — доработка UI пополнения (toast/broadcast при закрытом модальном окне) — в коде, нужен deploy.
- [ ] **Проверить TOP-24 / TOP-25** — pending USDT пополнения без IPN от CCAPI (webhook-логов нет). Админка: Deposits → карточка депозита → «Журнал CCAPI webhook».
