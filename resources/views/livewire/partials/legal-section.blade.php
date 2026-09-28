@php
    /** @var \App\Models\LegalPage|null $page */
    $page = $this->legalPage;
    $card = 'padding: 28px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035); box-sizing: border-box;';
@endphp

<section data-screen-label="{{ __('coin.nav.legal_information') }}" class="coin-dash-section">
  <div style="{{ $card }}">
    @if($page)
      <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: oklch(0.88 0.11 195);">{{ mb_strtoupper($page->slugLabel()) }}</div>
      <h2 style="margin: 14px 0 0; font-size: clamp(24px, 3vw, 32px); line-height: 1.15; letter-spacing: -0.03em; font-weight: 600; color: #f0fbff;">{{ $page->title }}</h2>

      @if($page->isFaq())
        <div style="margin-top: 28px; display: flex; flex-direction: column; gap: 22px;">
          @foreach($page->faqItems() as $item)
            <div style="padding-bottom: 22px; border-bottom: 1px solid rgba(150,235,250,0.12);">
              <h3 style="margin: 0; font-size: 18px; line-height: 1.35; font-weight: 600; color: #f0fbff;">{{ $item['question'] }}</h3>
              <p style="margin: 12px 0 0; font-size: 15px; line-height: 1.72; color: rgba(230,244,250,0.82); white-space: pre-wrap;">{{ $item['answer'] }}</p>
            </div>
          @endforeach
        </div>
      @else
        <div style="margin-top: 24px; font-size: 15px; line-height: 1.72; color: rgba(230,244,250,0.82); white-space: pre-wrap; word-break: break-word;">{{ $page->body }}</div>
      @endif
    @else
      <p style="margin: 0; font-size: 14px; color: rgba(214,238,248,0.72);">{{ __('coin.sections.legal_empty') }}</p>
    @endif
  </div>
</section>
