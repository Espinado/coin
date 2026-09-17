<x-coin-legal-layout :title="$page->title" :legal-nav="$legalNav" :current-page="$page">
    <div class="coin-legal-kicker">{{ mb_strtoupper(__('coin.admin.legal.title')) }}</div>
    <h1 class="coin-legal-title">{{ $page->title }}</h1>
    <div class="coin-legal-body">{{ $page->body }}</div>
</x-coin-legal-layout>
