<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __('coin.admin.admins.invite_mail_subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#071018;font-family:Arial,Helvetica,sans-serif;color:#e6f4fa;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#071018;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#0b1a28;border:1px solid rgba(255,180,84,0.22);border-radius:16px;overflow:hidden;">
          <tr>
            <td style="padding:28px 28px 18px;">
              <div style="font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,180,84,0.85);">{{ __('coin.admin.brand') }}</div>
              <h1 style="margin:14px 0 0;font-size:24px;line-height:1.3;color:#f0fbff;">
                {{ ($isPasswordReset ?? false) ? __('coin.admin.admins.reset_mail_title') : __('coin.admin.admins.invite_mail_title') }}
              </h1>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 18px;font-size:15px;line-height:1.65;color:rgba(214,238,248,0.86);">
              @if($isPasswordReset ?? false)
                <p style="margin:0 0 14px;">{{ __('coin.admin.admins.reset_mail_intro', ['email' => $email]) }}</p>
                <p style="margin:0 0 14px;">{{ __('coin.admin.admins.reset_mail_body', ['expires' => $expiresAt->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))->format('d.m.Y H:i')]) }}</p>
              @else
                <p style="margin:0 0 14px;">{{ __('coin.admin.admins.invite_mail_intro', ['name' => $inviterName, 'email' => $email]) }}</p>
                <p style="margin:0 0 14px;">{{ __('coin.admin.admins.invite_mail_body', ['expires' => $expiresAt->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))->format('d.m.Y H:i')]) }}</p>
              @endif
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 24px;" align="center">
              <a href="{{ $inviteUrl }}" style="display:inline-block;padding:14px 24px;border-radius:10px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;font-size:15px;font-weight:700;text-decoration:none;">
                {{ ($isPasswordReset ?? false) ? __('coin.admin.admins.reset_mail_cta') : __('coin.admin.admins.invite_mail_cta') }}
              </a>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 28px;font-size:13px;line-height:1.6;color:rgba(214,238,248,0.72);">
              <p style="margin:0 0 10px;">{{ __('coin.admin.admins.invite_mail_link_hint') }}</p>
              <p style="margin:0;word-break:break-all;font-family:Consolas,Monaco,monospace;color:#eafcff;">{{ $inviteUrl }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
