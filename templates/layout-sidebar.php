<?php
    // 1. Ambil parameter page
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

    // 2. Logic Menu Aktif
    $is_keluarga_active = (strpos($page, 'data-suami-istri') !== false || strpos($page, 'data-anak') !== false || strpos($page, 'data-ortu') !== false);
    
    $master_pages_other = ['data-jabatan', 'data-pendidikan', 'master-data-diklat', 'data-sertifikasi'];
    $is_master_other_active = false;
    foreach($master_pages_other as $mp) {
        if(strpos($page, $mp) !== false) { $is_master_other_active = true; break; }
    }
    $is_master_active = ($is_keluarga_active || $is_master_other_active);
    
    $is_laporan_active = ($page == 'nominatif' || $page == 'keadaan-pegawai' || $page == 'formasi' || $page == 'rekap-biaya-diklat');
    $is_pegawai_active = (strpos($page, 'data-pegawai') !== false || strpos($page, 'form-ubah-id-peg') !== false);

    // 3. LOGIC LOGO APLIKASI (DYNAMIC)
    // Mengambil dari variabel $set (tb_config)
    $logo_db = isset($set['logo']) ? $set['logo'] : '';
    $path_logo = "dist/img/" . $logo_db;

    // Cek apakah file ada di folder dist/img/
    if (!empty($logo_db) && file_exists($path_logo)) {
        $logo_src = $path_logo;
    } else {
        // Fallback jika tidak ada logo custom
        $logo_src = "dist/img/bkk.png"; 
    }
?>

<style>
    /* --- 1. SIDEBAR GENERAL --- */
    .main-sidebar { background-color: #1e293b !important; box-shadow: 4px 0 25px rgba(0,0,0,0.05); }
    .brand-link { background-color: #0f172a !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important; padding: 1.2rem 1rem !important; }
    .brand-link .brand-text { color: #fff !important; font-family: 'Segoe UI', sans-serif; font-weight: 800 !important; text-transform: uppercase; letter-spacing: 0.5px; }

    /* --- 2. SEARCH BAR LOGIC (TEGAS/BINER) --- */
    
    /* Reset Container */
    .sidebar .form-inline {
        padding: 0 8px !important;
        margin: 15px 0 !important;
        overflow: visible !important;
    }

    /* Container Input Group */
    .sidebar .form-inline .input-group {
        display: flex !important;
        flex-wrap: nowrap !important; /* Wajib satu baris */
        width: 100% !important;
    }

    /* Style Input (Normal/Terbuka) */
    .sidebar-form .form-control { 
        background-color: rgba(255,255,255,0.05) !important; 
        border: 1px solid transparent; 
        color: #cbd5e1; 
        height: 38px !important;
        /* Radius kiri untuk mode terbuka */
        border-radius: 8px 0 0 8px !important; 
        transition: none !important; /* Matikan animasi biar gak peyot */
    }

    /* Style Tombol (Normal/Terbuka) */
    .sidebar-form .btn { 
        background-color: rgba(255,255,255,0.05) !important; 
        border: 1px solid transparent; 
        color: #94a3b8; 
        height: 38px !important;
        /* Radius kanan untuk mode terbuka */
        border-radius: 0 8px 8px 0 !important; 
        width: 40px !important; 
        padding: 0 !important;
        display: flex; align-items: center; justify-content: center;
        transition: none !important;
    }

    /* --- MODE 1: TERTUTUP TOTAL (Sidebar Collapse & Mouse Jauh) --- */
    /* Input di-hide, Tombol jadi kotak penuh */
    body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-form .form-control {
        display: none !important; /* Hilang total */
    }
    
    body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-form .input-group-append {
        width: 100% !important; /* Container tombol ambil full width */
    }
    
    body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-form .btn {
        width: 100% !important; /* Tombol melebar */
        border-radius: 8px !important; /* Jadi kotak/bulat penuh (tidak ada sisi rata) */
    }

    /* --- MODE 2: TRANSISI & TERBUKA (Hover atau Sidebar Open) --- */
    /* Saat mouse nempel (hover), LANGSUNG paksa ke bentuk normal */
    body.sidebar-collapse .main-sidebar:hover .sidebar-form .form-control {
        display: block !important; /* Munculkan Input */
        width: 100% !important;
        border-radius: 8px 0 0 8px !important; /* Radius kiri */
    }
    
    body.sidebar-collapse .main-sidebar:hover .sidebar-form .btn {
        width: 40px !important; /* Kecilkan tombol */
        border-radius: 0 8px 8px 0 !important; /* Radius kanan saja */
    }
    
    /* Paksa container tombol menyesuaikan isi */
    body.sidebar-collapse .main-sidebar:hover .sidebar-form .input-group-append {
        width: auto !important; 
    }
    /* ---------------------------------------------------- */

    /* Nav Items */
    .nav-sidebar .nav-item { margin-bottom: 4px; }
    .nav-sidebar .nav-link { border-radius: 12px !important; color: #94a3b8 !important; font-weight: 600; transition: all 0.2s ease; white-space: nowrap; }
    .nav-sidebar .nav-link:hover { background-color: rgba(255,255,255,0.05) !important; color: #fff !important; transform: translateX(5px); }
    
    .nav-sidebar > .nav-item > .nav-link.active {
        background: linear-gradient(135deg, #f39c12 0%, #d35400 100%) !important;
        color: #fff !important;
        box-shadow: 0 6px 15px rgba(243, 156, 18, 0.4) !important;
    }
    
    .nav-treeview { background-color: rgba(0,0,0,0.15) !important; border-radius: 12px; margin-top: 5px; }
    .nav-treeview > .nav-item > .nav-link { padding-left: 45px !important; font-size: 0.9rem; }
    .nav-treeview > .nav-item > .nav-link.active { background: rgba(255,255,255,0.08) !important; color: #f39c12 !important; }
    
    .nav-sidebar .nav-link i { color: #64748b; margin-right: 10px; transition: 0.3s; }
    .nav-sidebar .nav-link.active i { color: #fff !important; }
    .nav-treeview > .nav-item > .nav-link.active i { color: #f39c12 !important; }
    
    .nav-header { color: #64748b !important; font-weight: 800; padding-left: 15px; margin-top: 10px; }
    .sidebar::-webkit-scrollbar { width: 5px; }
    .sidebar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>

<aside class="main-sidebar sidebar-dark-navy elevation-4">
    <?php if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) == 'ketua') : ?>
        <a href="home-admin.php" class="brand-link">
    <?php else: ?>
        <a href="#" class="brand-link" onclick="return false;">
    <?php endif; ?>
        <img src="<?php echo $logo_src; ?>?t=<?php echo time(); ?>" 
             alt="App Logo" 
             class="brand-image img-circle elevation-3" 
             style="opacity: .9; width: 33px; height: 33px; object-fit: cover;"> 
        
        <span class="brand-text"><?php echo isset($set['nama_app']) ? $set['nama_app'] : 'SIMPEG'; ?></span> 
    </a>
    
    <div class="sidebar text-sm">
        <div class="form-inline">
            <div class="input-group sidebar-form" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Cari Menu..." aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar"><i class="fas fa-search fa-fw"></i></button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">MENU UTAMA</li>
                
                <?php 
                if (!function_exists('aksesAdminKepala')) {
                    function aksesAdminKepala() {
                        return isset($_SESSION['hak_akses']) && ($_SESSION['hak_akses'] == 'admin' || $_SESSION['hak_akses'] == 'kepala');
                    }
                }
                
                if (aksesAdminKepala()): 
                ?>
                <li class="nav-item">
                    <a href="home-admin.php<?php echo $_SESSION['hak_akses'] == 'admin' ? '' : '?page=dashboard-cabang'; ?>" 
                       class="nav-link <?php echo ($page == 'dashboard' || $page == 'dashboard-cabang' || !isset($_GET['page'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i> <p>Dashboard</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['hak_akses'] == 'admin'): ?>
                


                <li class="nav-item has-treeview <?php echo $is_pegawai_active ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $is_pegawai_active ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Pegawai <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link <?php echo ($page == 'form-view-data-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Daftar Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-master-data-pegawai" class="nav-link <?php echo ($page == 'form-master-data-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Tambah Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-ubah-id-peg" class="nav-link <?php echo ($page == 'form-ubah-id-peg') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Ubah ID Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-upload-data-pegawai" class="nav-link <?php echo ($page == 'form-upload-data-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Import Excel</p>
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-mutasi" class="nav-link <?php echo ($page == 'form-view-data-mutasi') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Penonaktifan</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item has-treeview <?php echo $is_master_active ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $is_master_active ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-database"></i>
                        <p>Master Data <i class="right fas fa-angle-left"></i></p>
                    </a>
                    
                    <ul class="nav nav-treeview">
                        <li class="nav-item has-treeview <?php echo $is_keluarga_active ? 'menu-open' : ''; ?>">
                            <a href="#" class="nav-link <?php echo $is_keluarga_active ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-user-friends"></i> 
                                <p>Keluarga <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="home-admin.php?page=form-view-data-suami-istri" class="nav-link <?php echo ($page == 'form-view-data-suami-istri') ? 'active' : ''; ?>">
                                        <i class="far fa-circle nav-icon"></i> <p>Suami Istri</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="home-admin.php?page=form-view-data-anak" class="nav-link <?php echo ($page == 'form-view-data-anak') ? 'active' : ''; ?>">
                                        <i class="far fa-circle nav-icon"></i> <p>Anak</p>
                                    </a>
                                </li> 
                                <li class="nav-item">
                                    <a href="home-admin.php?page=form-view-data-ortu" class="nav-link <?php echo ($page == 'form-view-data-ortu') ? 'active' : ''; ?>">
                                        <i class="far fa-circle nav-icon"></i> <p>Orang Tua</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-jabatan" class="nav-link <?php echo ($page == 'form-view-data-jabatan') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Jabatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-pendidikan" class="nav-link <?php echo ($page == 'form-view-data-pendidikan') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Pendidikan</p>
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a href="home-admin.php?page=view-data-biaya-pendidikan" class="nav-link <?php echo ($page == 'view-data-biaya-pendidikan') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Biaya Pendidikan</p>
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a href="home-admin.php?page=master-data-diklat" class="nav-link <?php echo ($page == 'master-data-diklat') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Pelatihan</p>
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a href="home-admin.php?page=form-view-data-sertifikasi" class="nav-link <?php echo ($page == 'form-view-data-sertifikasi') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Sertifikasi</p>
                            </a>
                        </li> 
                    </ul>
                </li>
                
                <li class="nav-item has-treeview <?php echo $is_laporan_active ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $is_laporan_active ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Laporan <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="home-admin.php?page=nominatif" class="nav-link <?php echo ($page == 'nominatif') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Kepegawaian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=keadaan-pegawai" class="nav-link <?php echo ($page == 'keadaan-pegawai') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Keadaan Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=formasi" class="nav-link <?php echo ($page == 'formasi') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Formasi Jabatan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=rekap-biaya-diklat" class="nav-link <?php echo ($page == 'rekap-biaya-diklat') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Diklat</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="home-admin.php?page=view-rekap-biaya" class="nav-link <?php echo ($page == 'rekap-biaya-pendidikan') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i> <p>Biaya Pendidikan</p>
                            </a>
                        </li>
                    </ul>
                </li>          
                
                <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-user" class="nav-link <?php echo ($page == 'daftar-user') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-lock"></i> <p>Data User</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['hak_akses'] == 'kepala'): ?>
                <li class="nav-item">
                    <a href="home-admin.php?page=form-view-data-pegawai" class="nav-link <?php echo ($page == 'form-view-data-pegawai') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-friends"></i> <p>Data Pegawai</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="home-admin.php?page=nominatif" class="nav-link <?php echo ($page == 'nominatif') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i> <p>Laporan Kepegawaian</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['hak_akses'] == 'user'): ?>
                <li class="nav-item">
                    <a href="home-admin.php?page=profil-pegawai" class="nav-link <?php echo ($page == 'profil-pegawai') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-circle"></i> <p>Profil Saya</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($_SESSION['hak_akses'] == 'admin'): ?>
                <li class="nav-item">
                    <a href="home-admin.php?page=form-config-aplikasi" class="nav-link <?php echo ($page == 'form-config-aplikasi') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cogs"></i> <p>Pengaturan Aplikasi</p>
                    </a>
                </li>
                <?php endif; ?>



            </ul>
        </nav>
    </div>
</aside>