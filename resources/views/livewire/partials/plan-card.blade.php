@php
    $isCurrent = $plan->isCurrentFor($primaryPlan);
    $isSelected = (int) ($selectedPlanId ?? 0) === $plan->id;
    $isEnterprise = $plan->isEnterprise();
    $isCluster = $plan->slug === 'cluster';
    $actionLabel = $plan->actionLabel($primaryPlan);
    $anotherPlanSelected = $primaryPlan && (int) ($selectedPlanId ?? 0) !== (int) $primaryPlan->id;
    $currentMuted = $isCurrent && $anotherPlanSelected && ! $isSelected;
    $selectedStyle = $isSelected && ! $isCurrent
        ? 'border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.18), rgba(150,235,250,0.04)); box-shadow: 0 24px 60px -40px oklch(0.7 0.14 195 / 0.7);'
        : '';
    $currentCardStyle = $currentMuted
        ? 'border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);'
        : 'border: 1px solid oklch(0.86 0.11 195 / 0.36); background: linear-gradient(170deg, oklch(0.6 0.13 200 / 0.24), rgba(150,235,250,0.03)); box-shadow: 0 24px 60px -40px oklch(0.7 0.14 195 / 0.9);';
@endphp

@if($isCurrent)
<div style="position: relative; padding: 24px; border-radius: 18px; {{ $currentCardStyle }} display: flex; flex-direction: column;">
  <div style="position: absolute; top: -10px; left: 24px; padding: 4px 10px; border-radius: 7px; background: linear-gradient(140deg, oklch(0.88 0.12 192), oklch(0.66 0.13 205)); font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: #04121f;">CURRENT DEPOSIT</div>
  <div style="display: flex; align-items: center; justify-content: space-between;">
    <div style="width: 44px; height: 44px; border-radius: 13px; background: linear-gradient(150deg, oklch(0.72 0.13 198), oklch(0.44 0.12 215)); border: 1px solid rgba(190,250,255,0.4); display: grid; place-items: center; box-shadow: inset 0 1px 0 rgba(255,255,255,0.3);"><span style="width: 15px; height: 15px; border-radius: 4px; background: #eafcff;"></span></div>
    <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.72);">{{ $plan->tier_label }}</span>
  </div>
  <div style="margin-top: 20px; font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">{{ $plan->name }}</div>
  <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 7px;">
    <span style="font-size: 30px; font-weight: 600; letter-spacing: -0.03em;">{{ $plan->price_label }}</span>
    <span style="font-size: 12.5px; color: rgba(214,238,248,0.72);">{{ $plan->formattedDuration() }}</span>
  </div>
  <div style="height: 1px; background: rgba(150,235,250,0.16); margin: 20px 0;"></div>
  <div style="display: flex; flex-direction: column; gap: 13px; font-size: 13px;">
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.74); min-width: 0;">Min deposit</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.74); min-width: 0;">Term</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedDuration() }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.74); min-width: 0;">Annual profit</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedAnnualProfit() ?? ($plan->formattedDailyEstimate() ?? 'Estimated') }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.74); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->infra }}</span></div>
  </div>
  <div style="margin-top: 20px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.14);"><div style="width: {{ $plan->capacity_percent ?? 0 }}%; height: 100%; border-radius: 3px; background: oklch(0.86 0.12 192);"></div></div>
  <button type="button" wire:click="selectPlan({{ $plan->id }})" style="margin-top: 22px; padding: 11px; border-radius: 10px; border: 1px solid {{ $currentMuted ? 'rgba(150,235,250,0.2)' : 'oklch(0.86 0.11 195 / 0.5)' }}; background: {{ $currentMuted ? 'rgba(150,235,250,0.06)' : 'linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205))' }}; color: {{ $currentMuted ? '#e6f4fa' : '#04121f' }}; font-family: inherit; font-size: 13.5px; font-weight: {{ $currentMuted ? '500' : '600' }}; cursor: pointer;">Manage deposit</button>
</div>
@elseif($isEnterprise)
<div style="position: relative; padding: 24px; border-radius: 18px; {{ $selectedStyle ?: 'border: 1px solid rgba(180,180,255,0.16); background: linear-gradient(170deg, rgba(120,110,220,0.13), rgba(150,235,250,0.02));' }} display: flex; flex-direction: column;">
  @if($isSelected)
  <div style="position: absolute; top: -10px; left: 24px; padding: 4px 10px; border-radius: 7px; background: linear-gradient(140deg, oklch(0.88 0.12 192), oklch(0.66 0.13 205)); font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: #04121f;">SELECTED</div>
  @endif
  <div style="display: flex; align-items: center; justify-content: space-between;">
    <div style="width: 56px; height: 44px; border-radius: 11px; background: linear-gradient(160deg, #2a2a58, #0d1230); border: 1px solid rgba(180,180,255,0.3); padding: 7px; display: flex; flex-direction: column; gap: 5px;">
      <div style="display: flex; align-items: center; gap: 5px;"><span style="width: 4px; height: 4px; border-radius: 50%; background: oklch(0.85 0.15 160);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(190,190,255,0.5);"></span></div>
      <div style="display: flex; align-items: center; gap: 5px;"><span style="width: 4px; height: 4px; border-radius: 50%; background: oklch(0.78 0.15 292);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(190,190,255,0.4);"></span></div>
      <div style="display: flex; align-items: center; gap: 5px;"><span style="width: 4px; height: 4px; border-radius: 50%; background: rgba(190,190,255,0.4);"></span><span style="flex: 1; height: 3px; border-radius: 2px; background: rgba(190,190,255,0.28);"></span></div>
    </div>
    <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ $plan->tier_label }}</span>
  </div>
  <div style="margin-top: 20px; font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">{{ $plan->name }}</div>
  <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 7px;"><span style="font-size: 30px; font-weight: 600; letter-spacing: -0.03em;">{{ $plan->price_label }}</span></div>
  <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 20px 0;"></div>
  <div style="display: flex; flex-direction: column; gap: 13px; font-size: 13px;">
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Min deposit</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Term</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedDuration() }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Annual profit</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedAnnualProfit() ?? 'Estimated' }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->infra }}</span></div>
  </div>
  <div style="margin-top: 20px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $plan->capacity_percent ?? 100 }}%; height: 100%; border-radius: 3px; background: linear-gradient(90deg, oklch(0.78 0.15 292), oklch(0.88 0.12 192));"></div></div>
  <button type="button" wire:click="selectPlan({{ $plan->id }})" style="margin-top: 22px; padding: 11px; border-radius: 10px; border: 1px solid rgba(180,180,255,0.28); background: rgba(150,140,255,0.1); color: #e6f4fa; font-family: inherit; font-size: 13.5px; font-weight: 500; cursor: pointer;">Contact sales</button>
</div>
@else
<div style="position: relative; padding: 24px; border-radius: 18px; {{ $selectedStyle ?: 'border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.04);' }} display: flex; flex-direction: column;">
  @if($isSelected)
  <div style="position: absolute; top: -10px; left: 24px; padding: 4px 10px; border-radius: 7px; background: linear-gradient(140deg, oklch(0.88 0.12 192), oklch(0.66 0.13 205)); font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.12em; color: #04121f;">SELECTED</div>
  @endif
  <div style="display: flex; align-items: center; justify-content: space-between;">
    @if($isCluster)
    <div style="position: relative; width: 58px; height: 44px;">
      <div style="position: absolute; left: 0; top: 6px; width: 22px; height: 32px; border-radius: 8px; background: linear-gradient(150deg, #1e4a60, #0b2030); border: 1px solid rgba(150,235,250,0.22);"></div>
      <div style="position: absolute; left: 18px; top: 0; width: 23px; height: 36px; border-radius: 9px; background: linear-gradient(150deg, #276076, #0d2637); border: 1px solid rgba(150,235,250,0.3); box-shadow: inset 0 1px 0 rgba(255,255,255,0.16);"></div>
      <div style="position: absolute; left: 37px; top: 7px; width: 21px; height: 30px; border-radius: 8px; background: linear-gradient(150deg, #1e4a60, #0b2030); border: 1px solid rgba(150,235,250,0.22);"></div>
    </div>
    @else
    <div style="width: 44px; height: 44px; border-radius: 13px; background: linear-gradient(150deg, #1a4055, #0b2030); border: 1px solid rgba(150,235,250,0.22); display: grid; place-items: center;"><span style="width: 15px; height: 15px; border-radius: 4px; background: oklch(0.7 0.1 200);"></span></div>
    @endif
    <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: rgba(214,238,248,0.66);">{{ $plan->tier_label }}</span>
  </div>
  <div style="margin-top: 20px; font-size: 19px; font-weight: 600; letter-spacing: -0.02em;">{{ $plan->name }}</div>
  <div style="margin-top: 14px; display: flex; align-items: baseline; gap: 7px;">
    <span style="font-size: 30px; font-weight: 600; letter-spacing: -0.03em;">{{ $plan->price_label }}</span>
    <span style="font-size: 12.5px; color: rgba(214,238,248,0.7);">{{ $plan->formattedDuration() }}</span>
  </div>
  <div style="height: 1px; background: rgba(150,235,250,0.12); margin: 20px 0;"></div>
  <div style="display: flex; flex-direction: column; gap: 13px; font-size: 13px;">
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Min deposit</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedMinDeposit() ?? $plan->formattedComputeLabel() }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Term</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedDuration() }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Annual profit</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->formattedAnnualProfit() ?? ($plan->formattedDailyEstimate() ?? 'Estimated') }}</span></div>
    <div style="display: flex; justify-content: space-between; gap: 14px;"><span style="color: rgba(214,238,248,0.72); min-width: 0;">Infrastructure</span><span style="font-family: 'JetBrains Mono', monospace; flex: none; text-align: right;">{{ $plan->infra }}</span></div>
  </div>
  <div style="margin-top: 20px; height: 5px; border-radius: 3px; background: rgba(150,235,250,0.12);"><div style="width: {{ $plan->capacity_percent ?? 0 }}%; height: 100%; border-radius: 3px; background: {{ $isCluster ? 'oklch(0.8 0.12 198)' : 'oklch(0.7 0.11 205)' }};"></div></div>
  <button type="button" wire:click="selectPlan({{ $plan->id }})" style="margin-top: 22px; padding: 11px; border-radius: 10px; border: 1px solid {{ $isSelected ? 'oklch(0.86 0.11 195 / 0.5)' : 'rgba(150,235,250,0.2)' }}; background: {{ $isSelected ? 'linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205))' : 'rgba(150,235,250,0.06)' }}; color: {{ $isSelected ? '#04121f' : '#e6f4fa' }}; font-family: inherit; font-size: 13.5px; font-weight: {{ $isSelected ? '600' : '500' }}; cursor: pointer;">{{ $actionLabel }}</button>
</div>
@endif
