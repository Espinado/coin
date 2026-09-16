<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __('coin.auth.two_factor_mail_subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#071018;font-family:Arial,Helvetica,sans-serif;color:#e6f4fa;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#071018;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#0b1a28;border:1px solid rgba(150,235,250,0.18);border-radius:16px;overflow:hidden;">
          @include('emails.partials.brand-header', ['title' => __('coin.auth.two_factor_mail_title')])
          <tr>
            <td style="padding:0 28px 18px;font-size:15px;line-height:1.65;color:rgba(214,238,248,0.86);">
              <p style="margin:0 0 14px;">{{ __('coin.auth.two_factor_mail_intro', ['name' => $userName]) }}</p>
              <p style="margin:0;">{{ __('coin.auth.two_factor_mail_body') }}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 24px;" align="center">
              <div style="display:inline-block;padding:16px 28px;border-radius:12px;border:1px dashed rgba(150,235,250,0.35);background:rgba(4,16,28,0.55);font-family:Consolas,Monaco,monospace;font-size:32px;letter-spacing:0.35em;color:#eafcff;">{{ $code }}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 28px;font-size:13px;line-height:1.6;color:rgba(214,238,248,0.72);">
              <p style="margin:0;">{{ __('coin.auth.two_factor_mail_footer') }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
