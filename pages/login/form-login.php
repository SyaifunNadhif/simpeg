<?php
include "dist/koneksi.php";
  $App=mysqli_query($conn, "SELECT * FROM tb_config WHERE id_app='1'");
  $set=mysqli_fetch_array($App);
  $alias  = $set['nama_app'];
  list($als,$app) = explode (" ",$alias);
?>
<body class="hold-transition login-page">
  <div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <div class="image">
          <img src="dist/img/bkk.png" alt="User Image">
        </div>
        <a href="index.php" class="h3"><b>BKK</b> SimPeg</a>
      </div>
      <div class="card-body">
        <p class="login-box-msg">Login Untuk Masuk ke Aplikasi</p>

        <form action="index.php?page=act-login&op=in" method="post">
          <div class="input-group mb-3">
            <input type="text" name="id_user" class="form-control" placeholder="User ID">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember">
                  Ingat Saya
                </label>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block">Log In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>

        <br>
        <p class="mb-1">
          <a href="#">Lupa Password?</a>
        </p>
        <p class="mb-0">
          <a href="#" class="text-center" data-toggle="modal" data-target="#register">Belum Punya Akun BKK SimPeg?</a>
        </p>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <div class="modal fade" id="register">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Pendaftaran User</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Silakan Hubungi Bagian SDM Kantor Pusat Untuk Menjadi User&hellip;</p>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-primary float-right" data-dismiss="modal">Close</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
</body>

