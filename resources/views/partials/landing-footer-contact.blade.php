@php
    $contactEmail = trim($companyLegal['company_email'] ?? '') ?: config('coin.contact_email');
    $contactPhone = trim($companyLegal['company_phone'] ?? '');
@endphp
@if($contactPhone !== '')
    <a href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
@endif
<a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
