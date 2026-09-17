@php
    /** @var \App\Models\LegalPage $page */
    $faqItems = old('faq_items', $page->decodedFaqItems());
    if ($faqItems === []) {
        $faqItems = [['question' => '', 'answer' => '']];
    }
@endphp

<div id="admin-faq-items" style="display:flex;flex-direction:column;gap:14px;">
    @foreach($faqItems as $index => $item)
        <div class="admin-faq-item" data-faq-item style="padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.02);display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.faq_item')) }} #{{ $index + 1 }}</div>
                <button type="button" class="admin-btn" data-faq-remove>{{ __('coin.admin.legal.faq_remove') }}</button>
            </div>
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.faq_question')) }}</label>
                <input type="text" name="faq_items[{{ $index }}][question]" value="{{ $item['question'] ?? '' }}" maxlength="500"
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
            </div>
            <div>
                <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.faq_answer')) }}</label>
                <textarea name="faq_items[{{ $index }}][answer]" rows="4" maxlength="5000"
                    style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;font-family:inherit;line-height:1.55;">{{ $item['answer'] ?? '' }}</textarea>
            </div>
        </div>
    @endforeach
</div>

<button type="button" class="admin-btn" id="admin-faq-add" style="margin-top:4px;">{{ __('coin.admin.legal.faq_add') }}</button>
<p style="margin:0;font-size:13px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.legal.faq_hint') }}</p>

<template id="admin-faq-item-template">
    <div class="admin-faq-item" data-faq-item style="padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.02);display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div class="admin-faq-item-label" style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);"></div>
            <button type="button" class="admin-btn" data-faq-remove>{{ __('coin.admin.legal.faq_remove') }}</button>
        </div>
        <div>
            <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.faq_question')) }}</label>
            <input type="text" data-faq-question maxlength="500"
                style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;">
        </div>
        <div>
            <label style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(232,237,245,0.65);">{{ mb_strtoupper(__('coin.admin.legal.faq_answer')) }}</label>
            <textarea rows="4" maxlength="5000" data-faq-answer
                style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;resize:vertical;font-family:inherit;line-height:1.55;"></textarea>
        </div>
    </div>
</template>

<script>
(function () {
    var root = document.getElementById('admin-faq-items');
    var addButton = document.getElementById('admin-faq-add');
    var template = document.getElementById('admin-faq-item-template');

    if (!root || !addButton || !template) {
        return;
    }

    function renumberItems() {
        root.querySelectorAll('[data-faq-item]').forEach(function (item, index) {
            var label = item.querySelector('.admin-faq-item-label');
            var question = item.querySelector('[data-faq-question]') || item.querySelector('input[name*="[question]"]');
            var answer = item.querySelector('[data-faq-answer]') || item.querySelector('textarea[name*="[answer]"]');

            if (label) {
                label.textContent = '{{ mb_strtoupper(__('coin.admin.legal.faq_item')) }} #' + (index + 1);
            }

            if (question) {
                question.setAttribute('name', 'faq_items[' + index + '][question]');
            }

            if (answer) {
                answer.setAttribute('name', 'faq_items[' + index + '][answer]');
            }
        });
    }

    addButton.addEventListener('click', function () {
        var clone = template.content.firstElementChild.cloneNode(true);
        root.appendChild(clone);
        renumberItems();
    });

    root.addEventListener('click', function (event) {
        var button = event.target.closest('[data-faq-remove]');

        if (!button) {
            return;
        }

        var items = root.querySelectorAll('[data-faq-item]');

        if (items.length <= 1) {
            items[0].querySelectorAll('input, textarea').forEach(function (field) {
                field.value = '';
            });

            return;
        }

        button.closest('[data-faq-item]').remove();
        renumberItems();
    });
})();
</script>
