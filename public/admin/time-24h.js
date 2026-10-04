(function () {
  function pad(value) {
    return String(value).padStart(2, '0');
  }

  function notify(target) {
    target.dispatchEvent(new Event('input', { bubbles: true }));
    target.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function syncTime(root, emit) {
    var targetId = root.getAttribute('data-target');
    var target = targetId ? document.getElementById(targetId) : null;
    var hour = root.querySelector('[data-time-24h-hour]');
    var minute = root.querySelector('[data-time-24h-minute]');
    if (! target || ! hour || ! minute) {
      return;
    }
    target.value = pad(hour.value) + ':' + pad(minute.value);
    if (emit) {
      notify(target);
    }
  }

  function syncDateTime(root, emit) {
    var targetId = root.getAttribute('data-target');
    var target = targetId ? document.getElementById(targetId) : null;
    var date = root.querySelector('[data-datetime-local-24h-date]');
    var hour = root.querySelector('[data-datetime-local-24h-hour]');
    var minute = root.querySelector('[data-datetime-local-24h-minute]');
    if (! target || ! date || ! hour || ! minute) {
      return;
    }
    if (! date.value) {
      target.value = '';
    } else {
      target.value = date.value + 'T' + pad(hour.value) + ':' + pad(minute.value);
    }
    if (emit) {
      notify(target);
    }
  }

  function bind() {
    document.querySelectorAll('[data-time-24h]').forEach(function (root) {
      if (root.dataset.bound === '1') {
        return;
      }
      root.dataset.bound = '1';
      root.addEventListener('change', function () {
        syncTime(root, true);
      });
      syncTime(root, false);
    });

    document.querySelectorAll('[data-datetime-local-24h]').forEach(function (root) {
      if (root.dataset.bound === '1') {
        return;
      }
      root.dataset.bound = '1';
      root.addEventListener('change', function () {
        syncDateTime(root, true);
      });
      root.addEventListener('input', function () {
        syncDateTime(root, true);
      });
      syncDateTime(root, false);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();
