(function () {
    function closePicker(root) {
        var menu = root.querySelector('[data-phone-country-menu]');
        var trigger = root.querySelector('[data-phone-country-trigger]');
        var search = root.querySelector('[data-phone-country-search]');

        if (! menu || ! trigger) {
            return;
        }

        menu.hidden = true;
        root.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');

        if (search) {
            search.value = '';
            filterOptions(root, '');
        }
    }

    function filterOptions(root, query) {
        var needle = String(query || '').trim().toLowerCase();
        var options = root.querySelectorAll('[data-phone-country-option]');
        var empty = root.querySelector('[data-phone-country-empty]');
        var visibleCount = 0;

        options.forEach(function (option) {
            var name = (option.getAttribute('data-name') || '').toLowerCase();
            var dial = (option.getAttribute('data-dial') || '').toLowerCase();
            var iso = (option.getAttribute('data-iso') || '').toLowerCase();
            var match = ! needle
                || name.indexOf(needle) === 0
                || name.indexOf(needle) !== -1
                || dial.replace('+', '').indexOf(needle.replace('+', '')) === 0
                || iso.indexOf(needle) === 0;

            option.hidden = ! match;
            if (match) {
                visibleCount += 1;
            }
        });

        if (empty) {
            empty.hidden = visibleCount > 0;
        }
    }

    function openPicker(root) {
        document.querySelectorAll('[data-phone-country].is-open').forEach(function (other) {
            if (other !== root) {
                closePicker(other);
            }
        });

        var menu = root.querySelector('[data-phone-country-menu]');
        var trigger = root.querySelector('[data-phone-country-trigger]');
        var search = root.querySelector('[data-phone-country-search]');

        if (! menu || ! trigger) {
            return;
        }

        menu.hidden = false;
        root.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');

        if (search) {
            search.value = '';
            filterOptions(root, '');
            window.setTimeout(function () {
                search.focus();
                search.select();
            }, 0);
        }

        var selected = menu.querySelector('.is-selected:not([hidden])');
        if (selected && typeof selected.scrollIntoView === 'function') {
            selected.scrollIntoView({ block: 'nearest' });
        }
    }

    function selectOption(root, option) {
        var input = root.querySelector('[data-phone-country-input]');
        var flag = root.querySelector('[data-phone-country-flag]');
        var dial = root.querySelector('[data-phone-country-dial]');
        var name = root.querySelector('[data-phone-country-name]');

        if (! input || ! option) {
            return;
        }

        input.value = option.getAttribute('data-iso') || '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        if (flag) {
            flag.src = option.getAttribute('data-flag') || flag.src;
        }

        if (dial) {
            dial.textContent = option.getAttribute('data-dial') || '';
        }

        if (name) {
            name.textContent = option.getAttribute('data-name') || '';
        }

        root.querySelectorAll('[data-phone-country-option]').forEach(function (item) {
            var active = item === option;
            item.classList.toggle('is-selected', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        closePicker(root);
    }

    function firstVisibleOption(root) {
        return root.querySelector('[data-phone-country-option]:not([hidden])');
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-phone-country-trigger]');
        var option = event.target.closest('[data-phone-country-option]');
        var search = event.target.closest('[data-phone-country-search]');
        var root = event.target.closest('[data-phone-country]');

        if (search) {
            return;
        }

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

    document.addEventListener('input', function (event) {
        var search = event.target.closest('[data-phone-country-search]');
        if (! search) {
            return;
        }

        var root = search.closest('[data-phone-country]');
        if (! root) {
            return;
        }

        filterOptions(root, search.value);
    });

    document.addEventListener('keydown', function (event) {
        var openRoot = document.querySelector('[data-phone-country].is-open');
        if (! openRoot) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closePicker(openRoot);
            var trigger = openRoot.querySelector('[data-phone-country-trigger]');
            if (trigger) {
                trigger.focus();
            }
            return;
        }

        if (event.key === 'Enter') {
            var searchFocused = event.target.closest('[data-phone-country-search]');
            if (! searchFocused) {
                return;
            }

            event.preventDefault();
            var first = firstVisibleOption(openRoot);
            if (first) {
                selectOption(openRoot, first);
            }
        }
    });
})();
