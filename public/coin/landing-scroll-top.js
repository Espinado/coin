(function () {
    var button = document.getElementById('coin-scroll-top');

    if (!button) {
        return;
    }

    var threshold = 320;
    var ticking = false;

    function updateVisibility() {
        button.classList.toggle('is-visible', window.scrollY > threshold);
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(updateVisibility);
        }
    }, { passive: true });

    button.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    updateVisibility();
})();
