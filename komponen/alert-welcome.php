<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
  <div id="welcomeToast" class="toast align-items-center text-white bg-primary border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center">
        <i class="fas fa-info-circle fa-lg me-3"></i>
        <div>
            <strong>Selamat Datang!</strong><br>
            Halo, <?php echo isset($_SESSION['nama_user']) ? htmlspecialchars($_SESSION['nama_user']) : 'User'; ?> di SIMPEG.
        </div>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi Toast Bootstrap 5
    var toastEl = document.getElementById('welcomeToast');
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl, {
            delay: 3000, // Hilang dalam 5 detik
            animation: true
        });
        toast.show();
    }
  });
</script>

<style>
    /* Styling tambahan agar toast terlihat rounded & modern */
    .toast {
        border-radius: 12px;
        font-size: 0.95rem;
    }
</style>