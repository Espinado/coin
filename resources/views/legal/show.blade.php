<x-coin-legal-layout :title="$page->title" :legal-nav="$legalNav" :current-page="$page">
    <div class="coin-legal-kicker">{{ mb_strtoupper(__('coin.admin.legal.title')) }}</div>
    <h1 class="coin-legal-title">{{ $page->title }}</h1>

    @if($page->isFaq())
        <div style="margin-top: 28px; display: flex; flex-direction: column; gap: 22px;">
            @foreach($page->faqItems() as $item)
                <div style="padding-bottom: 22px; border-bottom: 1px solid rgba(150,235,250,0.12);">
                    <h2 style="margin: 0; font-size: 20px; line-height: 1.35; font-weight: 600; color: #f0fbff;">{{ $item['question'] }}</h2>
                    <p style="margin: 12px 0 0; font-size: 16px; line-height: 1.72; color: rgba(230,244,250,0.82); white-space: pre-wrap;">{{ $item['answer'] }}</p>
                </div>
            @endforeach
        </div>
    @else
        <div class="coin-legal-body">{{ $page->body }}</div>
    @endif
</x-coin-legal-layout>
