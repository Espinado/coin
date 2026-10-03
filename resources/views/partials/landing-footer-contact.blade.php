@php
    $contactEmail = trim($companyLegal['company_email'] ?? '') ?: trim((string) config('coin.contact_email'));
    $contactPhone = trim($companyLegal['company_phone'] ?? '');
@endphp
@if($contactPhone !== '')
    <a href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
@endif
@if($contactEmail !== '')
    <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
@endif
