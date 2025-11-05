<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>404 Not Found</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="javascript:history.back()">Home</a></li>
            <li class="breadcrumb-item active">404 Error</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="error-page">
      <h2 class="headline text-warning"> 404</h2>

      <div class="error-content">
        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Halaman tidak ditemukan.</h3>

        <p>
          Halaman yang Anda cari tidak tersedia atau telah dipindahkan.<br>
          Silakan kembali ke <a href="javascript:history.back()">Dashboard</a> atau gunakan pencarian di bawah ini.
        </p>

        <form class="search-form" action="home-admin.php" method="get">
          <div class="input-group">
            <input type="text" name="page" class="form-control" placeholder="Cari halaman...">
            <div class="input-group-append">
              <button type="submit" name="submit" class="btn btn-warning">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
      <!-- /.error-content -->
    </div>
    <!-- /.error-page -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
