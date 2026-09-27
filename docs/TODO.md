# CloudFlops — TODO

## Hardening backlog (отложено, 2026-09-28)

Закрыто ранее: H1–H7, H11, H14–H15 (webhook, mock, concurrency, Viewer/Operator ACL, guest token, cabinet ownership, live toggle). Commit `34fedad`.

- [x] **Payout 2FA** — уже сделано: вывод требует пароль + email-код (`WithdrawalTwoFactorService`), независимо от флага login-2FA.
- [x] **H8 — Login/register 2FA** — login всегда через email-код; регистрация через email verification; toggle в профиле убран (только badge «Обязательно»).
- [ ] **H9 — Guest Turnstile** — на staging `TURNSTILE_ENABLED=false`, ключи пустые → спам guest tickets. Включить + `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` на staging/prod.
- [ ] **H10 — KYC на вывод** — `kyc_required_for_withdrawal=false` на staging. Включить до широкого запуска; проверить upload/approve flow в admin/users.
- [ ] **H12 — Staging SEO** — `public/robots.txt` Allow all; meta noindex только на maintenance. До PUBLIC_ORIGIN: noindex / Disallow.
- [ ] **H13 — Invite role** — invite всегда создаёт Operator; нет выбора Viewer. Добавить роль в invite UX; сверить DB default Superadmin vs invite Operator.

## Compliance / безопасность

- [ ] **Включить обязательный KYC для вывода** — см. H10 выше (`kyc_required_for_withdrawal = 0`, с 2026-09-24). Перед продакшен-запуском для широкой аудитории: включить в админке «Настройки платформы» или `PlatformSettingsSeeder`, убедиться что есть процесс верификации (upload flow или ручной approve в admin/users).

## Платежи / UX

- [ ] **DepositUpdated + фоновый poll** — доработка UI пополнения (toast/broadcast при закрытом модальном окне) — в коде, нужен deploy.
- [ ] **Проверить TOP-24 / TOP-25** — pending USDT пополнения без IPN от CCAPI (webhook-логов нет). Админка: Deposits → карточка депозита → «Журнал CCAPI webhook».
