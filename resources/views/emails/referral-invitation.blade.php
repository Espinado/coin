<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __('coin.referrals.invite_mail_subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#071018;font-family:Arial,Helvetica,sans-serif;color:#e6f4fa;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#071018;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#0b1a28;border:1px solid rgba(150,235,250,0.18);border-radius:16px;overflow:hidden;">
          @include('emails.partials.brand-header', ['title' => __('coin.referrals.invite_mail_title')])
          <tr>
            <td style="padding:0 28px 18px;font-size:15px;line-height:1.65;color:rgba(214,238,248,0.86);">
              <p style="margin:0 0 14px;">{{ __('coin.referrals.invite_mail_intro', ['name' => $referrerName]) }}</p>
              <p style="margin:0 0 14px;">{{ __('coin.referrals.invite_mail_body', ['commission' => $commissionLabel]) }}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 24px;" align="center">
              <a href="{{ $shareUrl }}" style="display:inline-block;padding:14px 24px;border-radius:10px;background:linear-gradient(140deg,#7cecf8,#3db9d6);color:#04121f;font-size:15px;font-weight:700;text-decoration:none;">{{ __('coin.referrals.invite_mail_cta') }}</a>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 28px;font-size:13px;line-height:1.6;color:rgba(214,238,248,0.72);">
              <p style="margin:0 0 10px;">{{ __('coin.referrals.invite_mail_link_hint') }}</p>
              <p style="margin:0;word-break:break-all;font-family:Consolas,Monaco,monospace;color:#eafcff;">{{ $shareUrl }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
