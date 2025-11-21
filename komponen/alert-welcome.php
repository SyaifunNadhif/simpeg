<div class="alert alert-info alert-dismissible fade show auto-dismiss-alert" role="alert">
  <h5><i class="icon fas fa-info"></i> Informasi</h5>
  Selamat Datang, <strong><?php echo $_SESSION['nama_user']; ?></strong>!
  &nbsp;&nbsp;Di Sistem Informasi Kepegawaian <strong>BPR BKK Jateng</strong>.
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
      var alertNode = document.querySelector(".auto-dismiss-alert");
      if (alertNode) {
        var alert = new bootstrap.Alert(alertNode);
        alert.close();
      }
    }, 5000);
  });
</script>
