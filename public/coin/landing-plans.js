(function () {
    const root = document.getElementById('landing-plans');

    if (!root) {
        return;
    }

    const dataEl = document.getElementById('landing-plans-data');

    if (!dataEl) {
        return;
    }

    let data;

    try {
        data = JSON.parse(dataEl.textContent || '{}');
    } catch (error) {
        console.error('[landing-plans] invalid payload', error);

        return;
    }

    const plans = Array.isArray(data.plans) ? data.plans : [];

    if (plans.length === 0) {
        return;
    }

    const currency = data.currency || 'USDT';
    let selectedId = data.defaultPlanId ?? plans[0].id;
    let power = plans.find(function (plan) {
        return plan.id === selectedId;
    })?.minAmount ?? plans[0].minAmount;

    const slider = root.querySelector('[data-landing-slider]');
    const amountEl = root.querySelector('[data-landing-amount]');
    const dailyEl = root.querySelector('[data-landing-daily]');
    const monthlyEl = root.querySelector('[data-landing-monthly]');
    const planNameEl = root.querySelector('[data-landing-plan-name]');
    const sliderMinEl = root.querySelector('[data-landing-slider-min]');
    const sliderMidEl = root.querySelector('[data-landing-slider-mid]');
    const sliderMaxEl = root.querySelector('[data-landing-slider-max]');

    function getPlan(id) {
        return plans.find(function (plan) {
            return plan.id === id;
        });
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
        root.querySelectorAll('[data-landing-card]').forEach(function (card) {
            const planId = Number(card.dataset.landingCard);
            const isSelected = planId === selectedId;
            const isFeatured = card.dataset.landingFeatured === '1';

            card.classList.toggle('landing-plan-card--selected', isSelected);

            const button = card.querySelector('[data-landing-select]');

            if (!button) {
                return;
            }

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

            if (isFeatured && !isSelected) {
                card.style.border = '1px solid oklch(0.86 0.11 195 / 0.36)';
                card.style.background = 'linear-gradient(170deg, oklch(0.6 0.13 200 / 0.22), rgba(150,235,250,0.03))';
                card.style.boxShadow = '0 30px 70px -44px oklch(0.7 0.14 195 / 0.9)';
            } else if (isSelected) {
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

        if (!plan || !slider) {
            return;
        }

        power = clampPower(plan, power);
        slider.min = String(plan.minAmount);
        slider.max = String(plan.maxAmount);
        slider.step = String(plan.step);
        slider.value = String(power);

        if (sliderMinEl) {
            sliderMinEl.textContent = formatSliderLabel(plan.minAmount);
        }

        if (sliderMaxEl) {
            sliderMaxEl.textContent = formatSliderLabel(plan.maxAmount);
        }

        if (sliderMidEl) {
            sliderMidEl.textContent = formatSliderLabel(Math.round((plan.minAmount + plan.maxAmount) / 2));
        }
    }

    function updateCalculator() {
        const plan = getPlan(selectedId);

        if (!plan) {
            return;
        }

        power = clampPower(plan, power);
        const daily = dailyProfit(plan, power);

        if (amountEl) {
            amountEl.innerHTML = fmt(power, 0) + ' <span style="font-size: 12px; color: rgba(230,244,250,0.7);">' + currency + '</span>';
        }

        if (dailyEl) {
            dailyEl.textContent = fmt(daily, 2);
        }

        if (monthlyEl) {
            monthlyEl.textContent = fmt(daily * 30, 1);
        }

        if (planNameEl) {
            planNameEl.textContent = plan.name;
        }

        updateCardStyles();
    }

    root.addEventListener('click', function (event) {
        const button = event.target.closest('[data-landing-select]');

        if (!button) {
            return;
        }

        selectedId = Number(button.dataset.landingSelect);
        syncSliderToPlan();
        updateCalculator();
    });

    if (slider) {
        slider.addEventListener('input', function (event) {
            power = Number(event.target.value);
            updateCalculator();
        });
    }

    syncSliderToPlan();
    updateCalculator();
})();
