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
    if (e.target.closest('.coin-burger')) {
      document.documentElement.classList.toggle('coin-nav-open');
      return;
    }

    if (e.target.closest('.coin-nav-overlay') || e.target.closest('.coin-nav-mobile a')) {
      closeNav();
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      closeNav();
    }
  });
})();
