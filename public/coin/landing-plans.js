(function () {
    let plans = [];
    let currency = 'USDT';
    let selectedId = null;
    let power = 0;

    function loadPayload() {
        const dataEl = document.getElementById('landing-plans-data');

        if (!dataEl) {
            return false;
        }

        try {
            const data = JSON.parse(dataEl.textContent || '{}');
            plans = Array.isArray(data.plans) ? data.plans : [];
            currency = data.currency || 'USDT';
            selectedId = data.defaultPlanId ?? plans[0]?.id ?? null;
            const plan = getPlan(selectedId);
            power = plan?.minAmount ?? 0;

            return plans.length > 0 && selectedId !== null;
        } catch (error) {
            console.error('[landing-plans] invalid payload', error);

            return false;
        }
    }

    function getRoot() {
        return document.getElementById('landing-plans');
    }

    function getPlan(id) {
        return plans.find(function (plan) {
            return plan.id === id;
        });
    }

    function queryElements() {
        const root = getRoot();

        if (!root) {
            return null;
        }

        return {
            root: root,
            slider: root.querySelector('[data-landing-slider]'),
            amountEl: root.querySelector('[data-landing-amount]'),
            dailyEl: root.querySelector('[data-landing-daily]'),
            monthlyEl: root.querySelector('[data-landing-monthly]'),
            planNameEl: root.querySelector('[data-landing-plan-name]'),
            sliderMinEl: root.querySelector('[data-landing-slider-min]'),
            sliderMidEl: root.querySelector('[data-landing-slider-mid]'),
            sliderMaxEl: root.querySelector('[data-landing-slider-max]'),
        };
    }

    function clampPower(plan, value) {
        const numeric = Number(value) || 0;

        return Math.max(plan.minAmount, Math.min(plan.maxAmount, numeric));
    }

    function dailyProfit(plan, amount) {
        if (!plan.apr || amount <= 0) {
            return 0;
        }

        return Math.round(amount * (plan.apr / 100) / 365 * 100) / 100;
    }

    function fmt(value, digits) {
        return Number(value).toLocaleString('en-US', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        });
    }

    function formatSliderLabel(value) {
        return fmt(value, 0).replace(/,/g, ' ');
    }

    function updateCardStyles() {
        const root = getRoot();

        if (!root) {
            return;
        }

        root.querySelectorAll('[data-landing-card]').forEach(function (card) {
            const planId = Number(card.dataset.landingCard);
            const isSelected = planId === selectedId;
            const isFeatured = card.dataset.landingFeatured === '1';
            const button = card.querySelector('[data-landing-select]');

            card.classList.toggle('landing-plan-card--selected', isSelected);

            if (button) {
                if (isSelected) {
                    button.style.border = '1px solid oklch(0.86 0.11 195 / 0.5)';
                    button.style.background = 'linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205))';
                    button.style.color = '#04121f';
                    button.style.fontWeight = '600';
                } else {
                    button.style.border = '1px solid rgba(150,235,250,0.2)';
                    button.style.background = 'rgba(150,235,250,0.06)';
                    button.style.color = '#e6f4fa';
                    button.style.fontWeight = '500';
                }
            }

            if (isFeatured || isSelected) {
                card.style.border = '1px solid oklch(0.86 0.11 195 / 0.36)';
                card.style.background = 'linear-gradient(170deg, oklch(0.6 0.13 200 / 0.22), rgba(150,235,250,0.03))';
                card.style.boxShadow = '0 30px 70px -44px oklch(0.7 0.14 195 / 0.9)';
            } else {
                card.style.border = '1px solid rgba(150,235,250,0.12)';
                card.style.background = 'rgba(150,235,250,0.04)';
                card.style.boxShadow = 'none';
            }
        });
    }

    function syncSliderToPlan() {
        const plan = getPlan(selectedId);
        const els = queryElements();

        if (!plan || !els?.slider) {
            return;
        }

        power = clampPower(plan, power);
        els.slider.min = String(plan.minAmount);
        els.slider.max = String(plan.maxAmount);
        els.slider.step = String(plan.step);
        els.slider.value = String(power);

        if (els.sliderMinEl) {
            els.sliderMinEl.textContent = formatSliderLabel(plan.minAmount);
        }

        if (els.sliderMaxEl) {
            els.sliderMaxEl.textContent = formatSliderLabel(plan.maxAmount);
        }

        if (els.sliderMidEl) {
            els.sliderMidEl.textContent = formatSliderLabel(Math.round((plan.minAmount + plan.maxAmount) / 2));
        }
    }

    function updateCalculator() {
        const plan = getPlan(selectedId);
        const els = queryElements();

        if (!plan || !els) {
            return;
        }

        power = clampPower(plan, power);
        const daily = dailyProfit(plan, power);

        if (els.amountEl) {
            els.amountEl.innerHTML = fmt(power, 0) + ' <span style="font-size: 12px; color: rgba(230,244,250,0.7);">' + currency + '</span>';
        }

        if (els.dailyEl) {
            els.dailyEl.textContent = fmt(daily, 2);
        }

        if (els.monthlyEl) {
            els.monthlyEl.textContent = fmt(daily * 30, 1);
        }

        if (els.planNameEl) {
            els.planNameEl.textContent = plan.name;
        }

        if (els.slider && els.slider.value !== String(power)) {
            els.slider.value = String(power);
        }

        updateCardStyles();
    }

    function bootLandingPlans() {
        if (!loadPayload()) {
            return;
        }

        syncSliderToPlan();
        updateCalculator();
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-landing-select]');
        const root = getRoot();

        if (!button || !root || !root.contains(button)) {
            return;
        }

        if (plans.length === 0 && !loadPayload()) {
            return;
        }

        selectedId = Number(button.dataset.landingSelect);
        const plan = getPlan(selectedId);

        if (plan) {
            power = plan.minAmount;
        }

        syncSliderToPlan();
        updateCalculator();
    });

    document.addEventListener('input', function (event) {
        const root = getRoot();

        if (!event.target.matches('[data-landing-slider]') || !root || !root.contains(event.target)) {
            return;
        }

        if (plans.length === 0 && !loadPayload()) {
            return;
        }

        power = Number(event.target.value);
        updateCalculator();
    });

    window.coinInitLandingPlans = bootLandingPlans;

    function scheduleBoot() {
        bootLandingPlans();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleBoot);
    } else {
        scheduleBoot();
    }

    const observer = new MutationObserver(function () {
        if (document.getElementById('landing-plans-data')) {
            scheduleBoot();
        }
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });

    function patchDcBoot() {
        const boot = window.__dcBoot;

        if (typeof boot !== 'function' || boot.__coinLandingPatched) {
            return;
        }

        window.__dcBoot = function () {
            const result = boot.apply(this, arguments);
            scheduleBoot();

            return result;
        };
        window.__dcBoot.__coinLandingPatched = true;
        scheduleBoot();
    }

    const bootPoll = window.setInterval(function () {
        if (typeof window.__dcBoot === 'function') {
            patchDcBoot();
            window.clearInterval(bootPoll);
        }
    }, 50);

    window.setTimeout(function () {
        window.clearInterval(bootPoll);
    }, 10000);
})();
