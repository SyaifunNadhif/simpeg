<div style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;">
  
  <div id="welcomeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="true" data-delay="3000" style="min-width: 300px; border-radius: 8px;">
    
    <div class="toast-header bg-primary text-white" style="border-radius: 8px 8px 0 0;">
      <i class="fas fa-info-circle mr-2"></i>
      <strong class="mr-auto">Selamat Datang!</strong>
      <small>Baru saja</small>
      
      <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <div class="toast-body bg-white text-dark" style="border-radius: 0 0 8px 8px;">
      Halo, <strong><?php echo isset($_SESSION['nama_user']) ? htmlspecialchars($_SESSION['nama_user']) : 'User'; ?></strong><br>
      Selamat bekerja di aplikasi SIMPEG.
    </div>

  </div>
</div>

<script>
window.addEventListener('load', function() {
    if (typeof $ !== 'undefined') {
        var $toast = $('#welcomeToast');

        // 1. Inisialisasi Tegas (3 Detik & Auto Hide)
        $toast.toast({
            delay: 3000,
            autohide: true
        });

        // 2. Tampilkan Toast
        $toast.toast('show');

        // 3. FIX TOMBOL SILANG (CLOSE)
        // Kita paksa event click manual biar pasti nutup
        $toast.find('.close').on('click', function() {
            $toast.toast('hide');
        });

    } else {
        console.error("jQuery belum diload, Toast tidak bisa muncul.");
    }
});
</script>

<style>
/* Style Visual */
.toast {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: none;
    opacity: 1; /* Pastikan opacity 1 agar terlihat jelas */
}
.toast-header .close {
    color: #ffffff;
    opacity: 0.8;
    text-shadow: none;
    outline: none;
    cursor: pointer; /* Pastikan kursor berubah jadi jari */
}
.toast-header .close:hover {
    opacity: 1;
}
</style>