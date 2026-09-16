<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.js-swal-confirm-form')) {
            return;
        }

        event.preventDefault();

        Swal.fire({
            title: form.dataset.swalTitle || '',
            text: form.dataset.swalText || '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.swalConfirm || @json(__('coin.confirm')),
            cancelButtonText: form.dataset.swalCancel || @json(__('coin.cancel')),
            confirmButtonColor: '#e8872e',
            cancelButtonColor: '#3a4454',
            background: '#0b121a',
            color: '#e8edf5',
        }).then(function (result) {
            if (result.isConfirmed) {
                form.classList.remove('js-swal-confirm-form');
                form.submit();
            }
        });
    });
</script>
