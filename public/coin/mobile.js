(function () {
  function closeNav() {
    document.documentElement.classList.remove('coin-nav-open');
  }

  function closeOnEscape(e) {
    if (e.key === 'Escape') {
      closeNav();
    }
  }

  document.addEventListener('keydown', closeOnEscape);

  document.addEventListener('click', function (e) {
    var burger = e.target.closest('.coin-burger');
    if (burger) {
      if (burger.hasAttribute('wire:click') || burger.closest('[wire\\:id]')) {
        return;
      }
      document.documentElement.classList.toggle('coin-nav-open');
      return;
    }

    if (
      e.target.closest('.coin-nav-overlay')
      || e.target.closest('.coin-nav-mobile-close')
      || e.target.closest('.coin-nav-mobile a')
      || e.target.closest('.coin-landing-drawer__link')
      || e.target.closest('.coin-landing-drawer__cta')
    ) {
      closeNav();
    }
  });

  function isCompactViewport() {
    return window.innerWidth <= 768
      || (window.matchMedia('(orientation: landscape)').matches && window.innerHeight <= 500);
  }

  window.addEventListener('resize', function () {
    if (! isCompactViewport()) {
      closeNav();
    }
  });
})();
