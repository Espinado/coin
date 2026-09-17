@props([
    'sectionId',
    'label',
    'currentSection',
    'badge' => null,
])

<button
    type="button"
    wire:click="setSection({{ $sectionId }})"
    class="coin-nav-item {{ (int) $currentSection === (int) $sectionId ? 'coin-nav-item--active' : '' }}"
>
    @if((int) $currentSection === (int) $sectionId)
        <span class="coin-nav-item__bg" aria-hidden="true"></span>
    @endif
    <span class="coin-nav-dot"></span>
    <span class="coin-nav-item__label">{{ $label }}</span>
    @if($badge !== null)
        <span class="coin-nav-item__badge">{{ $badge }}</span>
    @endif
</button>
