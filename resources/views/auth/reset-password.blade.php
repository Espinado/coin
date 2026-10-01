<x-coin-auth-layout :title="\App\Support\PlatformBrand::pageTitle(__('coin.auth.reset_title'))">
    <div class="coin-auth" style="margin: 0 auto; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 36px; padding: 80px 72px; box-sizing: border-box; background: #061423; color: #e6f4fa; font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif; overflow: hidden;">
        <div style="position: absolute; top: -300px; left: 50%; width: 900px; height: 680px; margin-left: -450px; border-radius: 50%; background: radial-gradient(closest-side, oklch(0.62 0.13 198 / 0.22), transparent 74%); filter: blur(30px); pointer-events: none;"></div>

        @include('partials.auth-brand')

        <div class="coin-auth-card" style="position: relative; padding: 36px 36px 30px; border-radius: 22px; border: 1px solid rgba(150,235,250,0.16); background: linear-gradient(170deg, rgba(13,38,58,0.92), rgba(5,16,27,0.96)); box-shadow: 0 50px 110px -50px #000;">
            <div style="font-size: 22px; font-weight: 600; letter-spacing: -0.025em; color: #f0fbff;">{{ __('coin.auth.reset_title') }}</div>
            <p style="margin: 10px 0 0; font-size: 13.5px; line-height: 1.6; color: rgba(230,244,250,0.74);">{{ __('coin.auth.reset_sub') }}</p>

            <form method="POST" action="{{ route('password.store') }}" data-no-page-spinner>
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div style="margin-top: 26px;">
                    <label for="email" style="display: block; font-size: 12.5px; color: rgba(230,244,250,0.78);">{{ __('coin.auth.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="{{ __('coin.auth.email_placeholder') }}" style="width: 100%; box-sizing: border-box; margin-top: 9px; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-family: inherit; font-size: 16px;" />
                    @error('email')<div class="coin-auth-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-top: 18px;">
                    <label for="password" style="display: block; font-size: 12.5px; color: rgba(230,244,250,0.78);">{{ __('coin.auth.password') }}</label>
                    <div style="position: relative; margin-top: 9px;">
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="js-password-input" placeholder="{{ __('coin.auth.password_new_placeholder') }}" style="width: 100%; box-sizing: border-box; padding: 14px 92px 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-family: inherit; font-size: 16px;" />
                        <button type="button" data-password-toggle data-show-label="{{ __('coin.auth.show') }}" data-hide-label="{{ __('coin.auth.hide') }}" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); padding: 7px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.18); background: rgba(150,235,250,0.07); color: rgba(230,244,250,0.85); font-family: inherit; font-size: 12px; cursor: pointer;">{{ __('coin.auth.show') }}</button>
                    </div>
                    @error('password')<div class="coin-auth-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-top: 18px;">
                    <label for="password_confirmation" style="display: block; font-size: 12.5px; color: rgba(230,244,250,0.78);">{{ __('coin.auth.password_confirm') }}</label>
                    <div style="position: relative; margin-top: 9px;">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="js-password-input" placeholder="{{ __('coin.auth.password_confirm_placeholder') }}" style="width: 100%; box-sizing: border-box; padding: 14px 92px 14px 16px; border-radius: 12px; border: 1px solid rgba(150,235,250,0.18); background: rgba(4,16,28,0.7); color: #f0fbff; font-family: inherit; font-size: 16px;" />
                        <button type="button" data-password-toggle data-show-label="{{ __('coin.auth.show') }}" data-hide-label="{{ __('coin.auth.hide') }}" style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); padding: 7px 12px; border-radius: 8px; border: 1px solid rgba(150,235,250,0.18); background: rgba(150,235,250,0.07); color: rgba(230,244,250,0.85); font-family: inherit; font-size: 12px; cursor: pointer;">{{ __('coin.auth.show') }}</button>
                    </div>
                    @error('password_confirmation')<div class="coin-auth-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" style="width: 100%; margin-top: 24px; padding: 15px; border-radius: 12px; border: 1px solid oklch(0.86 0.11 195 / 0.5); background: linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205)); color: #04121f; font-family: inherit; font-size: 15px; font-weight: 600; cursor: pointer; box-shadow: 0 20px 46px -22px oklch(0.8 0.13 195 / 0.85);">{{ __('coin.auth.reset_submit') }}</button>

                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(150,235,250,0.1); text-align: center; font-size: 13px;">
                    <a href="{{ route('login') }}">{{ __('coin.auth.back_to_login') }}</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrap = btn.closest('div');
                var input = wrap ? wrap.querySelector('.js-password-input') : null;
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.textContent = show ? (btn.getAttribute('data-hide-label') || 'Hide') : (btn.getAttribute('data-show-label') || 'Show');
            });
        });
    })();
    </script>
</x-coin-auth-layout>
