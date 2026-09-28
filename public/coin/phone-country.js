(function () {
    function closePicker(root) {
        var menu = root.querySelector('[data-phone-country-menu]');
        var trigger = root.querySelector('[data-phone-country-trigger]');

        if (! menu || ! trigger) {
            return;
        }

        menu.hidden = true;
        root.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    }

    function openPicker(root) {
        document.querySelectorAll('[data-phone-country].is-open').forEach(function (other) {
            if (other !== root) {
                closePicker(other);
            }
        });

        var menu = root.querySelector('[data-phone-country-menu]');
        var trigger = root.querySelector('[data-phone-country-trigger]');

        if (! menu || ! trigger) {
            return;
        }

        menu.hidden = false;
        root.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');

        var selected = menu.querySelector('.is-selected');
        if (selected && typeof selected.scrollIntoView === 'function') {
            selected.scrollIntoView({ block: 'nearest' });
        }
    }

    function selectOption(root, option) {
        var input = root.querySelector('[data-phone-country-input]');
        var flag = root.querySelector('[data-phone-country-flag]');
        var dial = root.querySelector('[data-phone-country-dial]');

        if (! input || ! option) {
            return;
        }

        input.value = option.getAttribute('data-iso') || '';

        if (flag) {
            flag.src = option.getAttribute('data-flag') || flag.src;
        }

        if (dial) {
            dial.textContent = option.getAttribute('data-dial') || '';
        }

        root.querySelectorAll('[data-phone-country-option]').forEach(function (item) {
            var active = item === option;
            item.classList.toggle('is-selected', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        closePicker(root);
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-phone-country-trigger]');
        var option = event.target.closest('[data-phone-country-option]');
        var root = event.target.closest('[data-phone-country]');

        if (trigger && root) {
            event.preventDefault();
            if (root.classList.contains('is-open')) {
                closePicker(root);
            } else {
                openPicker(root);
            }
            return;
        }

        if (option && root) {
            event.preventDefault();
            selectOption(root, option);
            return;
        }

        document.querySelectorAll('[data-phone-country].is-open').forEach(closePicker);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-phone-country].is-open').forEach(closePicker);
    });
})();
