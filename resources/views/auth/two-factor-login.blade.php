<x-coin-auth-layout :title="\App\Support\PlatformBrand::pageTitle(__('coin.auth.two_factor_title'))">
    <div class="coin-auth" style="width: 1440px; min-height: 900px; margin: 0 auto; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 36px; padding: 80px 72px; box-sizing: border-box; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; overflow: hidden;">
        <div style="position: absolute; top: -300px; left: 50%; width: 900px; height: 680px; margin-left: -450px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.62 0.13 198 / 0.22), transparent 74%); filter: blur(30px); pointer-events: none;"></div>

        @include('partials.auth-brand', ['height' => 40])

        <div class="coin-auth-card" style="position: relative; width: 420px; padding: 36px 36px 30px; border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(13,38,58,0.92), rgba(5,16,27,0.96)); box-shadow: 0 50px 110px -50px #000;">
            <div style="font-size: 22px; font-weight: 600; letter-spacing: -0.025em; color: #f0fbff;">{{ __('coin.auth.two_factor_title') }}</div>
            <p style="margin: 10px 0 0; font-size: 13.5px; line-height: 1.55; color: rgba(230,244,250,0.72);">{{ __('coin.auth.two_factor_sub', ['email' => $email]) }}</p>

            <x-auth-session-status class="coin-auth-status" :status="session('status')" />

            <form method="POST" action="{{ route('login.two-factor') }}">
                @csrf

                <div style="margin-top: 26px;">
                    <label for="code" style="display: block; font-size: 12.5px; color: rgba(230,244,250,0.78);">{{ __('coin.auth.two_factor_code') }}</label>
                    <input id="code" type="text" name="code" value="{{ old('code') }}" required autofocus autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" style="width: 100%; box-sizing: border-box; margin-top: 9px; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-family: 'JetBrains Mono', monospace; font-size: 24px; letter-spacing: 0.28em; text-align: center;" />
                    @error('code')<div class="coin-auth-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" style="width: 100%; margin-top: 24px; padding: 15px; border-radius: 12px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 15px; font-weight: 600; cursor: pointer; box-shadow: 0 20px 46px -22px oklch(0.8 0.13 195 / 0.85);">{{ __('coin.auth.two_factor_confirm') }}</button>
            </form>

            <form method="POST" action="{{ route('login.two-factor.resend') }}" style="margin-top: 16px; text-align: center;">
                @csrf
                <button type="submit" style="border: 0; background: transparent; color: rgba(230,244,250,0.78); font-family: inherit; font-size: 13px; cursor: pointer; text-decoration: underline;">{{ __('coin.auth.two_factor_resend') }}</button>
            </form>

            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(150,235,250,0.1); text-align: center; font-size: 13px; color: rgba(230,244,250,0.72);">
                <a href="{{ route('login', ['cancel' => 1]) }}">{{ __('coin.auth.two_factor_back') }}</a>
            </div>
        </div>
    </div>
</x-coin-auth-layout>
