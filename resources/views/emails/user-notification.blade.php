<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background:#071018;font-family:Arial,Helvetica,sans-serif;color:#e6f4fa;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#071018;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#0b1a28;border:1px solid rgba(150,235,250,0.18);border-radius:16px;overflow:hidden;">
          <tr>
            <td style="padding:28px 28px 18px;">
              <div style="font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(214,238,248,0.65);">Coin</div>
              <h1 style="margin:14px 0 0;font-size:24px;line-height:1.3;color:#f0fbff;">{{ $subjectLine }}</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 18px;font-size:15px;line-height:1.65;color:rgba(214,238,248,0.86);">
              <p style="margin:0 0 14px;">{{ $intro }}</p>
              @foreach($lines as $line)
              <p style="margin:0 0 10px;">{{ $line }}</p>
              @endforeach
            </td>
          </tr>
          <tr>
            <td style="padding:0 28px 28px;font-size:13px;line-height:1.6;color:rgba(214,238,248,0.72);">
              <p style="margin:0;">{{ $footer ?? __('coin.notifications.mail.footer') }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
