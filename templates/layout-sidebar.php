<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="home-admin.php" class="brand-link">
    <img src="dist/img/bkk.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light"><?php echo isset($set['nama_app']) ? $set['nama_app'] : 'SIMPEG'; ?></span>
  </a>
  <div class="sidebar text-sm">
    <div class="form-inline">
      <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
          </button>
        </div>
      </div>
    </div>
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-header">MENU APLIKASI</li>

        <?php if (aksesAdminKepala()): ?>
          <!-- Dashboard untuk Admin dan Kepala -->
          <li class="nav-item">
            <a href="home-admin.php<?php echo $_SESSION['hak_akses'] == 'admin' ? '' : '?page=dashboard-cabang'; ?>" class="nav-link">
              <i class="fas fa-tachometer-alt nav-icon"></i>
              <p>Dashboard</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['hak_akses'] == 'admin'): ?>
          <!-- Konfigurasi Aplikasi -->
          <li class="nav-item">
            <a href="home-admin.php?page=form-config-aplikasi" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p>Konfigurasi Aplikasi</p>
            </a>
          </li>

          <!-- Data Pegawai - Admin Full Access -->
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="far fas fa-circle nav-icon"></i>
              <p>
                Data Pegawai
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tampil Data Pegawai</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="home-admin.php?page=form-master-data-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Entry Data Pegawai</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="home-admin.php?page=form-upload-data-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Entry Data Pegawai Kolektif</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-circle"></i>
              <p>
                Data Referensi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <!-- Tambahkan sub menu keluarga, pendidikan, dll di sini -->
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Keluarga
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-suami-istri" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Suami Istri</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-anak" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Anak</p>
                    </a>
                  </li>              
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-ortu" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Orang Tua</p>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- Tambahan lainnya sesuai kebutuhan -->
            </ul>
            <ul class="nav nav-treeview">
              <!-- Tambahkan sub menu keluarga, pendidikan, dll di sini -->
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-jabatan" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Jabatan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-pendidikan" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pendidikan</p>
                </a>
              </li>  
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-diklat" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pelatihan</p>
                </a>
              </li>  
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-sertifikasi" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sertifikasi</p>
                </a>
              </li>                                          
              <!-- Tambahan lainnya sesuai kebutuhan -->
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="far fas fa-circle nav-icon"></i>
              <p>
                Daftar Laporan
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="home-admin.php?page=nominatif" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Nominatif Pegawai</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="home-admin.php?page=keadaan-pegawai" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Keadaan Pegawai</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="home-admin.php?page=formasi" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Laporan Formasi Pegawai</p>
                </a>
              </li>
            </ul>
          </li>          
          <li class="nav-item">
            <a href="home-admin.php?page=daftar-user" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p>Data User</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['hak_akses'] == 'kepala'): ?>
          <!-- Menu Kepala -->
          <li class="nav-item">
            <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link">
              <i class="far fa-circle nav-icon"></i>
              <p>Tampil Data Pegawai</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="home-admin.php?page=nominatif" class="nav-link">
              <i class="far fa-circle nav-icon"></i>
              <p>Daftar Kepegawaian</p>
            </a>
          </li>
        <?php endif; ?>

        <?php if ($_SESSION['hak_akses'] == 'user'): ?>
          <!-- Menu User -->
          <li class="nav-item">
            <a href="home-admin.php?page=profil-pegawai" class="nav-link">
              <i class="far fa-circle nav-icon"></i>
              <p>Profil Saya</p>
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>
</aside>
