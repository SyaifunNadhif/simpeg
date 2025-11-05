<footer class="main-footer text-sm">
  <strong>
    &copy; <?php echo date('Y'); ?> 
    <a href="<?php echo isset($set['url_app']) ? $set['url_app'] : '#'; ?>" target="_blank">
      <?php echo isset($set['nama_app']) ? $set['nama_app'] : 'SIMPEG'; ?>
    </a>.
  </strong>
  All rights reserved.
  <div class="float-right d-none d-sm-inline-block">
    <b>Version</b> 2.0
  </div>
</footer>
</div>

<!-- JS Libraries -->
<!-- jQuery (tidak wajib di Bootstrap 5, tapi bisa tetap dipakai jika masih digunakan di komponen tertentu) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 5 Bundle -->
<script src="plugins/bootstrap5/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE and dependencies -->
<script src="dist/js/adminlte.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

<!-- DataTables Core & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>

<!-- Select2 -->
<script src="plugins/select2/js/select2.full.min.js"></script>

<!-- Enter to next input -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const inputs = Array.from(document.querySelectorAll('input, select, textarea'))
      .filter(el => !el.disabled && el.type !== 'hidden');

    inputs.forEach((input, index) => {
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          const next = inputs[index + 1];
          if (next) next.focus();
        }
      });
    });
  });
</script>

<!-- Atur Sesi User Aktif -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let timer;
let sessionTimeout = 15 * 60 * 1000; // 15 menit
let keepAliveInterval = 5 * 60 * 1000; // ping tiap 5 menit

function resetSession() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    Swal.fire({
      title: 'Sesi Habis',
      text: 'Sesi Anda telah habis karena tidak ada aktivitas.',
      icon: 'warning',
      confirmButtonText: 'OK',
      allowOutsideClick: false
    }).then((result) => {
      if (result.isConfirmed) {
        window.location = 'pages/login/act-logout.php';
      }
    });
  }, sessionTimeout);
}

function sendKeepAlive() {
  fetch('dist/keepalive.php');
}

['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
  document.addEventListener(evt, () => {
    resetSession();
    sendKeepAlive();
  });
});

resetSession();
setInterval(sendKeepAlive, keepAliveInterval);
</script>



</body>
</html>
