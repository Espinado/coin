@php
    $name = trim($companyLegal['company_name'] ?? '');
    $legalAddress = trim($companyLegal['company_legal_address'] ?? '');
    $physicalAddress = trim($companyLegal['company_physical_address'] ?? '');
    $registrationNumber = trim($companyLegal['company_registration_number'] ?? '');
    $licenseNumber = trim($companyLegal['company_license_number'] ?? '');
    $hasDetails = $name !== ''
        || $legalAddress !== ''
        || $physicalAddress !== ''
        || $registrationNumber !== ''
        || $licenseNumber !== '';
@endphp

@if($hasDetails)
    <address class="coin-landing-footer__company">
        @if($name !== '')
            <div style="font-size: 13px; font-weight: 600; color: rgba(230,244,250,0.82);">{{ $name }}</div>
        @endif

        @if($registrationNumber !== '')
            <div style="margin-top: 10px;">{{ __('coin.footer.registration_number') }}: {{ $registrationNumber }}</div>
        @endif

        @if($licenseNumber !== '')
            <div style="margin-top: 6px;">{{ __('coin.footer.license_number') }}: {{ $licenseNumber }}</div>
        @endif

        @if($legalAddress !== '' && $physicalAddress !== '' && $legalAddress === $physicalAddress)
            <div style="margin-top: 10px;">{{ __('coin.footer.address') }}:<br>{!! nl2br(e($legalAddress)) !!}</div>
        @else
            @if($legalAddress !== '')
                <div style="margin-top: 10px;">{{ __('coin.footer.legal_address') }}:<br>{!! nl2br(e($legalAddress)) !!}</div>
            @endif
            @if($physicalAddress !== '')
                <div style="margin-top: 10px;">{{ __('coin.footer.physical_address') }}:<br>{!! nl2br(e($physicalAddress)) !!}</div>
            @endif
        @endif
    </address>
@endif
