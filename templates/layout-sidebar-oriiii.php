<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="home-admin.php" class="brand-link">
    <img src="dist/img/bkk.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light"><?php echo isset($set['nama_app']) ? $set['nama_app'] : 'SIMPEG'; ?></span>
  </a>
  <div class="sidebar text-sm">
    <!-- <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="assets/img/<?php echo isset($_SESSION['foto']) ? $_SESSION['foto'] : 'avatar.png'; ?>" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block"><?php echo isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : 'User'; ?></a>
      </div>
    </div> -->
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
           <li class="nav-item">
            <a href="home-admin.php" class="nav-link">
              <i class="fas fa-tachometer-alt nav-icon"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <!--
          <li class="nav-item">
            <a href="home-admin.php?page=dashboard-cabang" class="nav-link">
              <i class="fas fa-tachometer-alt nav-icon"></i>
              <p>Dashboard Cabang</p>
            </a>
          </li>
          -->
          <li class="nav-item">
            <a href="home-admin.php?page=form-config-aplikasi" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p>Konfigurasi Aplikasi</p>
            </a>
          </li>
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
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Pendidikan
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-sekolah" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pendidikan Formal</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-diklat" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pendidikan Informal</p>
                    </a>
                  </li>
                </ul>
              </li> 
              <li class="nav-item">
                <a href="home-admin.php?page=form-view-data-sertifikasi" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Sertifikasi
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
              </li>  
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Mutasi Pegawai
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-angkat" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pegawai Masuk</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-mutasi" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pegawai Keluar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-jabatan" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Mutasi</p>
                    </a>
                  </li>                  
                </ul>
              </li>                            
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Riwayat Kepegawaian
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-angkat" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pengangkatan</p>
                    </a>
                  </li>                    
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-cuti" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Cuti</p>
                    </a>
                  </li>                                  
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-penghargaan" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Penghargaan</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-hukuman" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Pelanggaran</p>
                    </a>
                  </li>                  
                </ul>
              </li>
            </ul>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="far fas fa-circle nav-icon"></i>
                <p>
                  Laporan-laporan
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="home-admin.php?page=nominatif" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Daftar Kepegawaian</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="home-admin.php?page=formasi" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Laporan Formasi Pegawai</p>
                  </a>
                </li>                  
                <li class="nav-item">
                  <a href="home-admin.php?page=keadaan-pegawai" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Keadaan Pegawai</p>
                  </a>
                </li>                 
              </ul>
            </li>  
          </li>
        <!-- Tambahkan menu lain sesuai kebutuhan -->
      </ul>
    </nav>
  </div>
</aside>
