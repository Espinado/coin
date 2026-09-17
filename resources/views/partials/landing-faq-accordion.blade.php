@if(count($faqItems) > 0)
    <div id="landing-faq" style="border-top: 1px solid rgba(150,235,250,0.12);">
        @foreach($faqItems as $index => $item)
            <div class="landing-faq-item{{ $index === 0 ? ' is-open' : '' }}" data-faq-item style="border-top: {{ $index === 0 ? '0' : '1px solid rgba(150,235,250,0.12)' }}; {{ $loop->last ? 'border-bottom: 1px solid rgba(150,235,250,0.12);' : '' }}">
                <button type="button" data-faq-toggle style="width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 2px; background: none; border: 0; text-align: left; font-family: inherit; font-size: 17.5px; font-weight: 500; letter-spacing: -0.02em; color: #f0fbff; cursor: pointer;">
                    <span>{{ $item['question'] }}</span>
                    <span data-faq-icon-minus style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: oklch(0.88 0.11 195); flex: none;">−</span>
                    <span data-faq-icon-plus style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: rgba(230,244,250,0.6); flex: none;">+</span>
                </button>
                <div data-faq-panel>
                    <p style="margin: 0; padding: 0 80px 24px 2px; font-size: 15px; line-height: 1.65; color: rgba(230,244,250,0.72);">{{ $item['answer'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
@endif
