<?php
    // Ambil parameter page dari URL, jika tidak ada anggap sebagai 'dashboard'
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<aside class="main-sidebar sidebar-dark-navy elevation-4">
    <?php if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) == 'ketua') : ?>
    <a href="home-admin.php" class="brand-link">
    <?php else: ?>
    <a href="#" class="brand-link" onclick="return false;">
    <?php endif; ?>
        <img src="dist/img/bkk.png" alt="Logo" class="brand-image img-circle elevation-3"> 
        <span class="brand-text"><?php echo isset($set['nama_app']) ? $set['nama_app'] : 'SIMPEG'; ?></span> 
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
            <ul class="nav nav-pills nav-sidebar flex-column nav-compact nav-flat nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">MENU APLIKASI</li>
                
                <?php if (aksesAdminKepala()): ?>
                <li class="nav-item">
                    <a href="home-admin.php<?php echo $_SESSION['hak_akses'] == 'admin' ? '' : '?page=dashboard-cabang'; ?>" 
                       class="nav-link <?php echo ($page == 'dashboard' || $page == 'dashboard-cabang' || !isset($_GET['page'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-desktop"></i> <p>Dashboard</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['hak_akses'] == 'admin'): ?>
                
                <li class="nav-item">
                    <a href="home-admin.php?page=form-config-aplikasi" class="nav-link <?php echo ($page == 'form-config-aplikasi') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>Pengaturan Aplikasi</p>
                    </a>
                </li>

                <li class="nav-item has-treeview <?php echo (strpos($page, 'data-pegawai') !== false) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (strpos($page, 'data-pegawai') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Data Pegawai
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link <?php echo ($page == 'form-view-data-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Daftar Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-master-data-pegawai" class="nav-link <?php echo ($page == 'form-master-data-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Tambah Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-upload-data-pegawai" class="nav-link <?php echo ($page == 'form-upload-data-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Import Data Pegawai</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <?php 
                    // Cek apakah halaman aktif adalah bagian dari master data untuk membuka menu
                    $master_pages = ['data-suami-istri', 'data-anak', 'data-ortu', 'data-jabatan', 'data-pendidikan', 'data-diklat', 'data-sertifikasi'];
                    $is_master_active = false;
                    foreach($master_pages as $mp) {
                        if(strpos($page, $mp) !== false) { $is_master_active = true; break; }
                    }
                ?>
                <li class="nav-item has-treeview <?php echo $is_master_active ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $is_master_active ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            Master Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    
                    <ul class="nav nav-treeview">
                        <li class="nav-item has-treeview <?php echo (strpos($page, 'data-suami-istri') !== false || strpos($page, 'data-anak') !== false || strpos($page, 'data-ortu') !== false) ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>
                                    Keluarga
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="home-admin.php?page=form-view-data-suami-istri" class="nav-link <?php echo ($page == 'form-view-data-suami-istri') ? 'active' : ''; ?>">
                                        <i class="far fa-circle nav-icon"></i> <p>Suami Istri</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="home-admin.php?page=form-view-data-anak" class="nav-link <?php echo ($page == 'form-view-data-anak') ? 'active' : ''; ?>">
                                        <i class="far fa-circle nav-icon"></i> 
                                        <p>Anak</p>
                                    </a>
                                </li> 
                                <li class="nav-item">
                                    <a href="home-admin.php?page=form-view-data-ortu" class="nav-link <?php echo ($page == 'form-view-data-ortu') ? 'active' : ''; ?>">
                                        <i class="far fa-circle nav-icon"></i> 
                                        <p>Orang Tua</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-jabatan" class="nav-link <?php echo ($page == 'form-view-data-jabatan') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Jabatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-pendidikan" class="nav-link <?php echo ($page == 'form-view-data-pendidikan') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Pendidikan</p>
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-diklat" class="nav-link <?php echo ($page == 'form-view-data-diklat') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Pelatihan</p>
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-sertifikasi" class="nav-link <?php echo ($page == 'form-view-data-sertifikasi') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Sertifikasi</p>
                            </a>
                        </li> 
                    </ul>
                </li>
                
                <li class="nav-item has-treeview <?php echo ($page == 'nominatif' || $page == 'keadaan-pegawai' || $page == 'formasi') ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo ($page == 'nominatif' || $page == 'keadaan-pegawai' || $page == 'formasi') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Laporan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="home-admin.php?page=nominatif" class="nav-link <?php echo ($page == 'nominatif') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Laporan Nominatif</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=keadaan-pegawai" class="nav-link <?php echo ($page == 'keadaan-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Laporan Keadaan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=formasi" class="nav-link <?php echo ($page == 'formasi') ? 'active' : ''; ?>">
                                <i class="far fa-dot-circle nav-icon"></i> 
                                <p>Laporan Formasi</p>
                            </a>
                        </li>
                    </ul>
                </li>          
                
                <li class="nav-item">
                    <a href="home-admin.php?page=daftar-user" class="nav-link <?php echo ($page == 'daftar-user') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-lock"></i>
                        <p>Data User</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['hak_akses'] == 'kepala'): ?>
                <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link <?php echo ($page == 'form-view-data-pegawai') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>Data Pegawai</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="home-admin.php?page=nominatif" class="nav-link <?php echo ($page == 'nominatif') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Laporan Kepegawaian</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['hak_akses'] == 'user'): ?>
                <li class="nav-item">
                    <a href="home-admin.php?page=profil-pegawai" class="nav-link <?php echo ($page == 'profil-pegawai') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
    </div>
</aside>

<style>
/* -------------------- KUSTOMISASI SIDEBAR BKK SIMPEG -------------------- */

/* 1. Kustomisasi Background Sidebar Utama */
.main-sidebar, .sidebar-dark-navy {
    background-color: #2c3e50 !important; 
}

/* 2. Kustomisasi Brand Link */
.brand-link {
    padding: 1rem .5rem !important; 
    border-bottom: none !important; 
    background-color: #2c3e50 !important; 
}

.brand-link .brand-text {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
    font-size: 1.15rem; 
    font-weight: 700 !important; 
    color: #fff !important; 
}

/* 3. Kustomisasi Search Bar */
.sidebar .form-inline {
    margin: 10px 0; 
    padding: 0 .5rem; 
}

/* 4. Kustomisasi Menu Aktif */
.nav-sidebar > .nav-item > .nav-link.active {
    background: linear-gradient(90deg, #f39c12, #e67e22) !important; 
    color: #fff !important; 
    font-weight: bold;
    border-radius: 4px; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.2); /* Menambah sedikit bayangan agar lebih pop */
}

/* Penyesuaian sub-menu aktif */
.nav-sidebar .nav-treeview .nav-item > .nav-link.active {
    background-color: rgba(255, 255, 255, 0.15) !important; 
    color: #fff !important;
}

/* Ikon menu aktif menjadi silver/putih agar kontras */
.nav-sidebar .nav-item .nav-link.active i {
    color: #ecf0f1 !important; 
}

/* Hover Effect agar lebih interaktif */
.nav-sidebar .nav-item .nav-link:hover {
    background-color: rgba(255,255,255,0.05);
}
</style>