@php($brandName = \App\Support\PlatformBrand::name())
<tr>
  <td style="padding:28px 28px 18px;">
    <table role="presentation" cellspacing="0" cellpadding="0">
      <tr>
        <td style="padding-right:14px;vertical-align:middle;">
          <img src="{{ \App\Support\PlatformBrand::logoUrl('mark') }}" alt="" height="44" width="44" style="height:44px;width:44px;display:block;border:0;" />
        </td>
        <td style="vertical-align:middle;">
          <div style="font-family:Arial,Helvetica,sans-serif;font-size:24px;font-weight:700;letter-spacing:-0.02em;color:#f0fbff;line-height:1.1;">{{ $brandName }}</div>
        </td>
      </tr>
    </table>
    @if(! empty($title))
    <h1 style="margin:18px 0 0;font-size:22px;line-height:1.35;font-weight:600;color:rgba(214,238,248,0.92);">{{ $title }}</h1>
    @endif
  </td>
</tr>
