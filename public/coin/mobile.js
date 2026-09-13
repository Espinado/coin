(function () {
  function closeOnEscape(e) {
    if (e.key === 'Escape') {
      document.documentElement.classList.remove('coin-nav-open');
    }
  }

  document.addEventListener('keydown', closeOnEscape);

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      document.documentElement.classList.remove('coin-nav-open');
    }
  });
})();
