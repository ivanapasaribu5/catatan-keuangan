<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Keuangan</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    @livewireStyles
</head>
<body class="bg-light">
    {{ $slot }}

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toast-title">Notifikasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body">
                Hello, world! This is a toast message.
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts

    <script>
        document.addEventListener('livewire:init', () => {
            const toastEl = document.getElementById('liveToast');
            const toast = new bootstrap.Toast(toastEl);
            const toastBody = document.getElementById('toast-body');
            const toastTitle = document.getElementById('toast-title');

            Livewire.on('show-toast', (event) => {
                toastBody.textContent = event.message;
                if (event.error) {
                    toastTitle.textContent = 'Gagal!';
                    toastEl.classList.add('bg-danger', 'text-white');
                } else {
                    toastTitle.textContent = 'Berhasil!';
                    toastEl.classList.remove('bg-danger', 'text-white');
                }
                toast.show();
            });
        });
    </script>
</body>
</html>