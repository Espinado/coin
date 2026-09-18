@php
    $contactEmail = trim($companyLegal['company_email'] ?? '') ?: config('coin.contact_email');
    $contactPhone = trim($companyLegal['company_phone'] ?? '');
@endphp
@if($contactPhone !== '')
    <a href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}" style="color: rgba(230,244,250,0.78);">{{ $contactPhone }}</a>
@endif
<a href="mailto:{{ $contactEmail }}" style="color: rgba(230,244,250,0.78);">{{ $contactEmail }}</a>
