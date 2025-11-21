<?php
/*********************************************************
 * FILE    : pages/pegawai/profil-pegawai.php
 * MODULE  : Profil Pegawai (Fixed Link Edit Pattern)
 * VERSION : v6.0 (Final Link Fix)
 *********************************************************/

if (session_id() === '') session_start();

// --- 1. CEK LOGIN ---
if (!isset($_SESSION['id_user'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

include "dist/koneksi.php";

// --- 2. LOGIKA ID PEGAWAI & HAK AKSES ---
$hak_akses_session = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';

// Admin DAN Kepala boleh edit. User biasa tidak boleh.
$can_edit = ($hak_akses_session == 'admin' || $hak_akses_session == 'kepala');

$id_peg = null;
if ($hak_akses_session == 'user') {
    $id_peg = $_SESSION['id_pegawai'];
} elseif (isset($_GET['id_peg'])) {
    $id_peg = $_GET['id_peg'];
} else {
    $id_peg = isset($_SESSION['id_pegawai']) ? $_SESSION['id_pegawai'] : '';
}

if (empty($id_peg)) {
    echo '<div class="alert alert-danger m-3">ID Pegawai tidak ditemukan.</div>';
    exit;
}

// --- 3. QUERY DATA UTAMA ---
$tampilPeg = mysqli_query($conn, "SELECT p.*, u.id_user, u.hak_akses FROM tb_pegawai p LEFT JOIN tb_user u ON u.id_pegawai = p.id_peg WHERE p.id_peg = '$id_peg'");
if (mysqli_num_rows($tampilPeg) == 0) {
    echo '<div class="alert alert-warning m-3">Data pegawai tidak ditemukan.</div>';
    exit;
}
$peg = mysqli_fetch_array($tampilPeg);

// --- 4. ASSETS FOTO ---
$foto_db    = isset($peg['foto']) ? trim($peg['foto']) : '';
$jk         = isset($peg['jk']) ? strtolower(trim($peg['jk'])) : '';
$avatar_def = ($jk == 'laki-laki' || $jk == 'l') ? 'dist/img/avatar5.png' : 'dist/img/avatar3.png';
$path_foto  = 'pages/assets/foto/' . $foto_db;
$src_foto   = (!empty($foto_db) && file_exists($path_foto)) ? $path_foto : $avatar_def;
?>

<style>
    .profile-header-cover {
        background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
        height: 130px;
        border-radius: 12px 12px 0 0;
    }
    .profile-user-img {
        width: 130px; height: 130px; margin-top: -65px;
        border: 5px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        background: #fff; object-fit: cover;
    }
    /* Tab Navigasi Custom */
    .nav-pills-custom { border-bottom: 1px solid #eee; margin-bottom: 20px; }
    .nav-pills-custom .nav-link {
        color: #6c757d; font-weight: 600; padding: 12px 20px;
        border-radius: 0; border-bottom: 3px solid transparent;
    }
    .nav-pills-custom .nav-link.active {
        background-color: transparent; color: #007bff; border-bottom: 3px solid #007bff;
    }
    /* Tombol Quick Menu */
    .btn-quick {
        display: flex; flex-direction: column; align-items: center; gap: 5px;
        padding: 10px; border-radius: 10px; border: 1px solid #eee;
        background: #fff; color: #555; transition: 0.2s; width: 100%; cursor: pointer;
    }
    .btn-quick i { font-size: 1.5rem; color: #007bff; }
    .btn-quick span { font-size: 0.8rem; font-weight: 600; }
    .btn-quick:hover { background: #f0f8ff; border-color: #007bff; text-decoration: none; color: #007bff; }
    
    /* Tabel Detail */
    .table-detail tr td { padding: 10px 15px; border-bottom: 1px solid #f4f4f4; }
    .table-detail tr td:first-child { width: 35%; color: #888; font-weight: 500; }
    .table-detail tr td:last-child { font-weight: 600; color: #333; }
    
    /* Responsive Fix */
    .table-responsive { display: block; width: 100%; overflow-x: auto; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0 font-weight-bold text-dark">Profil Pegawai</h1>
            <ol class="breadcrumb float-sm-right small bg-transparent p-0">
                <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
                <li class="breadcrumb-item active">Profil</li>
            </ol>
        </div>
    </div>
</section>

<section class="content pb-5">
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="profile-header-cover"></div>
                    <div class="card-body text-center pt-0">
                        <a href="<?= ($can_edit) ? "home-admin.php?page=form-ganti-foto&id_peg=".urlencode($peg['id_peg']) : '#' ?>">
                            <img class="profile-user-img img-fluid img-circle"
                                 src="<?php echo $src_foto; ?>?time=<?php echo time(); ?>"
                                 onerror="this.src='<?php echo $avatar_def; ?>';">
                        </a>
                        <h4 class="mt-3 mb-1 font-weight-bold"><?php echo $peg['nama']; ?></h4>
                        <p class="text-muted mb-2 small"><?php echo $peg['id_peg']; ?></p>
                        <span class="badge badge-primary px-3 py-1 rounded-pill mb-4"><?php echo $peg['status_kepeg']; ?></span>
                        
                        <div class="text-left border-top pt-3">
                            <p class="text-muted small mb-1"><i class="fas fa-phone mr-2"></i> Telepon</p>
                            <h6 class="mb-3 ml-4"><?php echo $peg['telp'] ? $peg['telp'] : '-'; ?></h6>
                            <p class="text-muted small mb-1"><i class="fas fa-envelope mr-2"></i> Email</p>
                            <h6 class="mb-0 ml-4 small text-truncate"><?php echo $peg['email'] ? $peg['email'] : '-'; ?></h6>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white font-weight-bold border-bottom-0">
                        <i class="fas fa-th mr-2 text-primary"></i> Menu Cepat
                    </div>
                    <div class="card-body p-2">
                        <div class="row no-gutters">
                            <div class="col-4 p-1"><button type="button" class="btn-quick" data-toggle="modal" data-target="#pensiun"><i class="fa fa-user-clock"></i> <span>Pensiun</span></button></div>
                            <div class="col-4 p-1"><button type="button" class="btn-quick" data-toggle="modal" data-target="#naikpkt"><i class="fa fa-layer-group"></i> <span>Pangkat</span></button></div>
                            <div class="col-4 p-1"><button type="button" class="btn-quick" data-toggle="modal" data-target="#naikgj"><i class="fa fa-money-bill"></i> <span>Gaji</span></button></div>
                            <div class="col-4 p-1"><button type="button" class="btn-quick" data-toggle="modal" data-target="#dp3"><i class="fa fa-chart-line"></i> <span>SKP</span></button></div>
                            <div class="col-4 p-1"><button type="button" class="btn-quick" data-toggle="modal" data-target="#bahasa"><i class="fa fa-language"></i> <span>Bahasa</span></button></div>
                            <div class="col-4 p-1"><button type="button" class="btn-quick" data-toggle="modal" data-target="#pendidikan"><i class="fa fa-graduation-cap"></i> <span>Sekolah</span></button></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-lg-9">
                <div class="card shadow-sm border-0" style="border-radius: 12px; min-height: 600px;">
                    <div class="card-header p-0 border-bottom-0 bg-white rounded-top">
                        <ul class="nav nav-pills nav-pills-custom" id="custom-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="tab-bio" data-toggle="pill" href="#bio" role="tab">Biodata</a></li>
                            <li class="nav-item"><a class="nav-link" id="tab-keluarga" data-toggle="pill" href="#keluarga" role="tab">Keluarga</a></li>
                            <li class="nav-item"><a class="nav-link" id="tab-riwayat" data-toggle="pill" href="#riwayat" role="tab">Riwayat</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="bio" role="tabpanel">
                                <table class="table-detail w-100">
                                    <tr><td>NIK</td><td>: <?php echo $peg['nip']; ?></td></tr>
                                    <tr><td>Nama Lengkap</td><td>: <?php echo $peg['nama']; ?></td></tr>
                                    <tr><td>TTL</td><td>: <?php echo $peg['tempat_lhr'] . ', ' . date('d-m-Y', strtotime($peg['tgl_lhr'])); ?></td></tr>
                                    <tr><td>Jenis Kelamin</td><td>: <?php echo $peg['jk']; ?></td></tr>
                                    <tr><td>Agama</td><td>: <?php echo $peg['agama']; ?></td></tr>
                                    <tr><td>Golongan Darah</td><td>: <?php echo $peg['gol_darah']; ?></td></tr>
                                    <tr><td>Status Nikah</td><td>: <?php echo $peg['status_nikah']; ?></td></tr>
                                    <tr><td>Alamat</td><td>: <?php echo $peg['alamat']; ?></td></tr>
                                </table>
                                <?php if($can_edit): ?>
                                <div class="mt-4 text-right">
                                    <a href="home-admin.php?page=form-master-data-pegawai&mode=edit&id=<?= $peg['id_peg']; ?>" class="btn btn-warning shadow-sm"><i class="fa fa-edit"></i> Edit Biodata</a>
                                    <a href="./pages/report/print-biodata-pegawai.php?id_peg=<?= $id_peg ?>" target="_blank" class="btn btn-primary shadow-sm ml-2"><i class="fas fa-print"></i> Cetak CV</a>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="tab-pane fade" id="keluarga" role="tabpanel">
                                
                                <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3">Pasangan (Suami/Istri)</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light"><tr><th>Nama</th><th>TTL</th><th>Pekerjaan</th><th>Status</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead>
                                        <tbody>
                                            <?php $qSi = mysqli_query($conn,"SELECT a.*, (SELECT desc_pekerjaan FROM tb_master_pekerjaan WHERE id_pekerjaan=a.id_pekerjaan) as nm_kerja FROM tb_suamiistri a WHERE id_peg='$id_peg'");
                                            if(mysqli_num_rows($qSi)>0) {
                                                while($si=mysqli_fetch_array($qSi)){ 
                                                    // FIX ID_SI
                                                    $id_si = isset($si['id_si']) ? $si['id_si'] : (isset($si['id']) ? $si['id'] : 0);
                                                ?>
                                                <tr>
                                                    <td><?=$si['nama']?></td><td><?=$si['tmp_lhr']?>, <?=$si['tgl_lhr']?></td><td><?=$si['nm_kerja']?></td><td><?=$si['status_hub']?></td>
                                                    <?php if($can_edit): ?>
                                                    <td class="text-center">
                                                        <a href="home-admin.php?page=form-edit-data-suami-istri&id_si=<?=$id_si?>" class="btn btn-xs btn-info" title="Edit"><i class="fa fa-edit"></i></a>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php } } else { echo "<tr><td colspan='5' class='text-center text-muted small'>Tidak ada data</td></tr>"; } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3">Anak</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light"><tr><th>Nama</th><th>TTL</th><th>Pendidikan</th><th>Anak Ke</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead>
                                        <tbody>
                                            <?php $qAnak = mysqli_query($conn,"SELECT * FROM tb_anak WHERE id_peg='$id_peg' ORDER BY anak_ke");
                                            if(mysqli_num_rows($qAnak)>0) {
                                                while($ak=mysqli_fetch_array($qAnak)){ 
                                                    // FIX ID_ANAK
                                                    $id_ak = isset($ak['id_anak']) ? $ak['id_anak'] : (isset($ak['id']) ? $ak['id'] : 0);
                                                ?>
                                                <tr>
                                                    <td><?=$ak['nama']?></td><td><?=$ak['tmp_lhr']?>, <?=$ak['tgl_lhr']?></td><td><?=$ak['pendidikan']?></td><td><?=$ak['anak_ke']?></td>
                                                    <?php if($can_edit): ?>
                                                    <td class="text-center">
                                                        <a href="home-admin.php?page=form-edit-data-anak&id_anak=<?=$id_ak?>" class="btn btn-xs btn-info" title="Edit"><i class="fa fa-edit"></i></a>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php } } else { echo "<tr><td colspan='5' class='text-center text-muted small'>Tidak ada data</td></tr>"; } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <h6 class="font-weight-bold text-primary border-bottom pb-2 mb-3">Orang Tua</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light"><tr><th>Nama</th><th>TTL</th><th>Hubungan</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead>
                                        <tbody>
                                            <?php $qOrtu = mysqli_query($conn,"SELECT * FROM tb_ortu WHERE id_peg='$id_peg'");
                                            if(mysqli_num_rows($qOrtu)>0) {
                                                while($or=mysqli_fetch_array($qOrtu)){ 
                                                    // FIX ID_ORTU
                                                    $id_or = isset($or['id_ortu']) ? $or['id_ortu'] : (isset($or['id']) ? $or['id'] : 0);
                                                ?>
                                                <tr>
                                                    <td><?=$or['nama']?></td><td><?=$or['tmp_lhr']?>, <?=$or['tgl_lhr']?></td><td><?=$or['status_hub']?></td>
                                                    <?php if($can_edit): ?>
                                                    <td class="text-center">
                                                        <a href="home-admin.php?page=form-edit-data-ortu&id_ortu=<?=$id_or?>" class="btn btn-xs btn-info" title="Edit"><i class="fa fa-edit"></i></a>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php } } else { echo "<tr><td colspan='4' class='text-center text-muted small'>Tidak ada data</td></tr>"; } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="riwayat" role="tabpanel">
                                <div class="row">
                                    <div class="col-12"><h6 class="font-weight-bold mb-3">Data Riwayat</h6></div>
                                    <div class="col-md-4 mb-2"><button class="btn btn-outline-secondary btn-block text-left" data-toggle="modal" data-target="#pengangkatan"><i class="fa fa-file-contract mr-2"></i> Pengangkatan</button></div>
                                    <div class="col-md-4 mb-2"><button class="btn btn-outline-secondary btn-block text-left" data-toggle="modal" data-target="#mutasi"><i class="fa fa-exchange-alt mr-2"></i> Mutasi</button></div>
                                    <div class="col-md-4 mb-2"><button class="btn btn-outline-secondary btn-block text-left" data-toggle="modal" data-target="#diklat"><i class="fa fa-chalkboard-teacher mr-2"></i> Diklat</button></div>
                                    <div class="col-md-4 mb-2"><button class="btn btn-outline-secondary btn-block text-left" data-toggle="modal" data-target="#sertifikasi"><i class="fa fa-certificate mr-2"></i> Sertifikasi</button></div>
                                    <div class="col-md-4 mb-2"><button class="btn btn-outline-secondary btn-block text-left" data-toggle="modal" data-target="#hukum"><i class="fa fa-gavel mr-2"></i> Pelanggaran</button></div>
                                    <div class="col-md-4 mb-2"><button class="btn btn-outline-secondary btn-block text-left" data-toggle="modal" data-target="#jabatan"><i class="fa fa-briefcase mr-2"></i> Jabatan</button></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="pensiun" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Info Pensiun</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body"><table class="table"><tr><td>Tgl Lahir</td><td class="font-weight-bold"><?=$peg['tgl_lhr']?></td></tr><tr><td>Jatuh Tempo</td><td class="font-weight-bold text-danger"><?=$peg['tgl_pensiun']?></td></tr></table></div>
        </div>
    </div>
</div>

<div id="naikgj" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title">Proyeksi Kenaikan Gaji</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Periode</th><th>Estimasi Tanggal</th></tr></thead>
                    <tbody>
                        <?php if($peg['tgl_naikgaji'] && $peg['tgl_pensiun']){
                            $begin = new DateTime($peg['tgl_naikgaji']); $end = new DateTime($peg['tgl_pensiun']); $no=0;
                            for($i = $begin; $begin <= $end; $i->modify('+2 year')){ $no++; if($no > 5) break; echo "<tr><td>Ke-$no</td><td>".$i->format("d-m-Y")."</td></tr>"; }
                        } else { echo "<tr><td colspan='2'>Data tanggal tidak lengkap.</td></tr>"; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="naikpkt" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Pangkat</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="alert alert-info py-2"><strong>Estimasi Naik:</strong> <?php if($peg['tgl_naikpangkat']){ $next = new DateTime($peg['tgl_naikpangkat']); $next->modify('+4 year'); echo $next->format('d-m-Y'); } else { echo "-"; } ?></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light"><tr><th>Pangkat</th><th>Gol</th><th>TMT</th><th>SK</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead>
                        <tbody>
                            <?php $qPan = mysqli_query($conn,"SELECT * FROM tb_pangkat WHERE id_peg='$id_peg' ORDER BY tgl_sk DESC"); 
                            while($p=mysqli_fetch_array($qPan)){ $id_p = isset($p['id_pangkat'])?$p['id_pangkat']:$p['id']; ?>
                            <tr><td><?=$p['pangkat']?></td><td><?=$p['gol']?></td><td><?=$p['tmt_pangkat']?></td><td><?=$p['no_sk']?></td>
                            <?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-pangkat&id_pangkat=<?=$id_p?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="bahasa" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Bahasa</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <table class="table table-bordered"><thead><tr><th>Bahasa</th><th>Kemampuan</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody>
                    <?php $qBhs = mysqli_query($conn,"SELECT * FROM tb_bahasa WHERE id_peg='$id_peg'"); while($b=mysqli_fetch_array($qBhs)){ $id_b = isset($b['id_bahasa'])?$b['id_bahasa']:$b['id']; ?>
                    <tr><td><?=$b['bahasa']?></td><td><?=$b['kemampuan']?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-bahasa&id_bhs=<?=$id_b?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr>
                    <?php } ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<div id="pendidikan" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Pendidikan</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body table-responsive">
                <table class="table table-bordered table-hover"><thead><tr><th>Jenjang</th><th>Nama Sekolah</th><th>Jurusan</th><th>Lulus</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody>
                    <?php $qSek = mysqli_query($conn,"SELECT * FROM tb_pendidikan WHERE id_peg='$id_peg' ORDER BY tgl_ijazah DESC"); while($s=mysqli_fetch_array($qSek)){ $id_s = isset($s['id_sekolah'])?$s['id_sekolah']:$s['id']; ?>
                    <tr><td><?=$s['jenjang']?></td><td><?=$s['nama_sekolah']?></td><td><?=$s['jurusan']?></td><td><?=$s['tgl_ijazah']?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-sekolah&id_sekolah=<?=$id_s?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr>
                    <?php } ?>
                </tbody></table>
            </div>
        </div>
    </div>
</div>

<div id="jabatan" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Jabatan</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body"><table class="table table-bordered table-striped"><thead><tr><th>Jabatan</th><th>TMT</th><th>Status</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody>
                <?php $qJab = mysqli_query($conn,"SELECT id_jab, tmt_jabatan, status_jab, (SELECT jabatan FROM tb_ref_jabatan WHERE kode_jabatan=tb_jabatan.kode_jabatan) nm_jab FROM tb_jabatan WHERE id_peg='$id_peg' ORDER BY tmt_jabatan DESC"); while($j=mysqli_fetch_array($qJab)){ ?>
                <tr><td><?=$j['nm_jab']?></td><td><?=$j['tmt_jabatan']?></td><td><?=$j['status_jab']?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-jabatan&id_jab=<?=$j['id_jab']?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr>
                <?php } ?>
            </tbody></table></div>
        </div>
    </div>
</div>

<div id="dp3" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white"><h5 class="modal-title">Sasaran Kerja (SKP)</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <div class="modal-body table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>Periode</th><th>Nilai</th><th>Mutu</th><th>Aksi</th></tr></thead><tbody>
                <?php $qDp3 = mysqli_query($conn,"SELECT * FROM tb_dp3 WHERE id_peg='$id_peg' ORDER BY periode_akhir DESC"); while($d=mysqli_fetch_array($qDp3)){ $jml = $d['nilai_kesetiaan']+$d['nilai_prestasi']+$d['nilai_tgjwb']+$d['nilai_ketaatan']+$d['nilai_kejujuran']+$d['nilai_kerjasama']+$d['nilai_prakarsa']+$d['nilai_kepemimpinan']; ?>
                <tr><td><?=$d['periode_akhir']?></td><td><?=$jml?></td><td><?=$d['hasil_penilaian']?></td><td><a href="home-admin.php?page=view-detail-data-dp3&id_dp3=<?=$d['id_dp3']?>" class="btn btn-xs btn-info">Detail</a> <?php if($can_edit): ?><a href="home-admin.php?page=form-edit-data-dp3&id_dp3=<?=$d['id_dp3']?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a><?php endif; ?></td></tr>
                <?php } ?>
            </tbody></table></div>
        </div>
    </div>
</div>

<div id="pengangkatan" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Pengangkatan</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div><div class="modal-body"><table class="table table-bordered"><thead><tr><th>Status</th><th>Tgl</th><th>No SK</th><th>File</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody><?php $qAng = mysqli_query($conn,"SELECT * FROM tb_angkat WHERE id_peg_baru='$id_peg'"); while($a=mysqli_fetch_array($qAng)){ $id_a = isset($a['id_angkat'])?$a['id_angkat']:$a['id']; ?><tr><td><?=$a['jns_mutasi']?></td><td><?=$a['tgl_mutasi']?></td><td><?=$a['no_mutasi']?></td><td><a href="home-admin.php?page=view-pengangkatan&id_angkat=<?=$id_a?>" target="_blank"><i class="fa fa-file-pdf"></i></a></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-angkat&id_angkat=<?=$id_a?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr><?php } ?></tbody></table></div></div></div></div>
<div id="mutasi" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Mutasi</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div><div class="modal-body"><table class="table table-bordered"><thead><tr><th>Jenis</th><th>Tgl</th><th>No SK</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody><?php $qMut = mysqli_query($conn,"SELECT * FROM tb_mutasi WHERE id_peg='$id_peg'"); while($m=mysqli_fetch_array($qMut)){ $id_m = isset($m['id_mutasi'])?$m['id_mutasi']:$m['id']; ?><tr><td><?=$m['jns_mutasi']?></td><td><?=$m['tgl_mutasi']?></td><td><?=$m['no_mutasi']?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-mutasi&id_mutasi=<?=$id_m?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr><?php } ?></tbody></table></div></div></div></div>
<div id="diklat" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Diklat</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div><div class="modal-body"><table class="table table-bordered"><thead><tr><th>Nama</th><th>Penyelenggara</th><th>Tahun</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody><?php $qDik = mysqli_query($conn,"SELECT * FROM tb_diklat WHERE id_peg='$id_peg'"); while($d=mysqli_fetch_array($qDik)){ $id_d = isset($d['id_diklat'])?$d['id_diklat']:$d['id']; ?><tr><td><?=$d['diklat']?></td><td><?=$d['penyelenggara']?></td><td><?=$d['tahun']?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-diklat&id_diklat=<?=$id_d?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr><?php } ?></tbody></table></div></div></div></div>
<div id="sertifikasi" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Sertifikasi</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div><div class="modal-body"><table class="table table-bordered"><thead><tr><th>Sertifikasi</th><th>Exp</th><th>Status</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody><?php $qSer = mysqli_query($conn,"SELECT *, DATEDIFF(tgl_expired, CURDATE()) AS selisih FROM tb_sertifikasi WHERE id_peg='$id_peg'"); while($s=mysqli_fetch_array($qSer)){ $id_s = isset($s['id_sertif'])?$s['id_sertif']:$s['id']; ?><tr><td><?=$s['sertifikasi']?></td><td><?=$s['tgl_expired']?></td><td><?= ($s['selisih'] < 0) ? 'Exp' : 'Aktif' ?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-penugasan&id_penugasan=<?=$id_s?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr><?php } ?></tbody></table></div></div></div></div>
<div id="hukum" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Riwayat Pelanggaran</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div><div class="modal-body"><table class="table table-bordered"><thead><tr><th>Hukuman</th><th>Tgl SK</th><?php if($can_edit) echo '<th>Aksi</th>'; ?></tr></thead><tbody><?php $qHuk = mysqli_query($conn,"SELECT * FROM tb_hukuman WHERE id_peg='$id_peg'"); while($h=mysqli_fetch_array($qHuk)){ $id_h = isset($h['id_hukum'])?$h['id_hukum']:$h['id']; ?><tr><td><?=$h['hukuman']?></td><td><?=$h['tgl_sk']?></td><?php if($can_edit): ?><td><a href="home-admin.php?page=form-edit-data-hukuman&id_hukum=<?=$id_h?>" class="btn btn-xs btn-success"><i class="fa fa-edit"></i></a></td><?php endif; ?></tr><?php } ?></tbody></table></div></div></div></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>