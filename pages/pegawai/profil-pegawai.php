<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Biodata Pegawai</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item active">Biodata Pegawai</li>
                </ol>
            </div>
        </div>
    </div></section>

<?php
// Cek session login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

include "dist/koneksi.php";

// Ambil id pegawai berdasarkan hak akses
if (strtolower($_SESSION['hak_akses']) == 'user') {
    // Ambil dari session untuk user biasa
    $id_peg = $_SESSION['id_pegawai'];
} elseif (isset($_GET['id_peg'])) {
    // Admin/kepala bisa akses bebas
    $id_peg = $_GET['id_peg'];
} else {
    die("Error. ID Pegawai tidak ditemukan.");
}

// Cek apakah data pegawai tersedia
$tampilPeg = mysqli_query($conn, "
    SELECT p.*,
    u.id_user, u.hak_akses
    FROM tb_pegawai p
    LEFT JOIN tb_user u ON u.id_pegawai = p.id_peg
    WHERE p.id_peg = '$id_peg'
    ");

if (mysqli_num_rows($tampilPeg) == 0) {
    die("Data pegawai tidak ditemukan.");
}
$peg = mysqli_fetch_array($tampilPeg);

// ==========================================
// LOGIKA FOTO (PHP 5.6 Compatible)
// ==========================================
$foto_db = isset($peg['foto']) ? trim($peg['foto']) : '';
$jk      = isset($peg['jk']) ? strtolower(trim($peg['jk'])) : '';

// Tentukan Default Avatar berdasarkan JK
if ($jk == 'laki-laki' || $jk == 'l') {
    $avatar_def = 'dist/img/avatar5.png'; // Sesuaikan path avatar cowok AdminLTE
} else {
    $avatar_def = 'dist/img/avatar3.png'; // Sesuaikan path avatar cewek AdminLTE
}

// Path Foto User
$path_foto = 'pages/assets/foto/' . $foto_db;

// Cek apakah file ada di server
if (!empty($foto_db) && file_exists($path_foto)) {
    $src_foto = $path_foto;
} else {
    $src_foto = $avatar_def;
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">

                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <a href="home-admin.php?page=form-ganti-foto&id_peg=<?= urlencode($peg['id_peg']); ?>">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo $src_foto; ?>?time=<?php echo time(); ?>"
                                     alt="User profile picture"
                                     style="width: 140px; height: 140px; object-fit: cover; border: 3px solid #adb5bd;"
                                     onerror="this.onerror=null;this.src='<?php echo $avatar_def; ?>';">
                            </a>
                        </div>

                        <h3 class="profile-username text-center mt-2"><?php echo $peg['nama']; ?></h3>
                        <p class="text-muted text-center"><?php echo $peg['id_peg']; ?></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <i class="fas fa-phone mr-1"></i> <b>Telp</b> <a class="float-right"><?php echo $peg['telp']; ?></a>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-envelope mr-1"></i> <b>Email</b> <a class="float-right" style="font-size: 12px;"><?php echo $peg['email']; ?></a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header-custom">
                        <i class="fa fa-calendar"></i> Schedule
                    </div>
                    <div class="card-body-custom btn-row">
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#pensiun">Pensiun</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#naikpkt">Pangkat</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#naikgj">Gaji</button>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header-custom">
                        <i class="fa fa-book"></i> Education
                    </div>
                    <div class="card-body-custom">
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#bahasa">Bahasa</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#pendidikan">Pendidikan</button>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header-custom">
                        <i class="fa fa-list"></i> Sasaran Kerja Pegawai
                    </div>
                    <div class="card-body-custom">
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#dp3">SKP</button>
                    </div>
                </div>

                <style>
                    /* Header card seragam */
                    .card-header-custom {
                        border-bottom: 1px solid #e5e5e5 !important;
                        padding: 10px 14px !important;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        background: #fff;
                        border-radius: 8px 8px 0 0;
                        color: #333;
                    }

                    /* Body card rapi */
                    .card-body-custom {
                        padding: 12px 14px 14px !important;
                    }

                    /* Tombol tag-style */
                    .tag-btn {
                        background: #6c757d;
                        border: none;
                        color: white;
                        padding: 4px 14px;
                        border-radius: 8px;
                        font-size: 13px;
                        font-weight: 600;
                        cursor: pointer;
                        margin-right: 6px;
                        margin-bottom: 6px;
                        transition: 0.2s;
                    }

                    .tag-btn:hover {
                        background: #5a6268;
                    }
                </style>

            </div>
            
            <div class="col-md-9">
                <div class="card card-primary card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="suamiistri-tab" data-bs-toggle="tab" href="#suamiistri" role="tab" aria-controls="suamiistri" aria-selected="false">Suami Istri</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="anak-tab" data-bs-toggle="tab" href="#anak" role="tab" aria-controls="anak" aria-selected="false">Anak</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="ortu-tab" data-bs-toggle="tab" href="#ortu" role="tab" aria-controls="ortu" aria-selected="false">Orang Tua</a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            
                            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr><td width="30%">NIK</td><td>: <?php echo $peg['nip']; ?></td></tr>
                                        <tr><td>Nama</td><td>: <?php echo $peg['nama']; ?></td></tr>
                                        <tr><td>Tempat, Tanggal Lahir</td><td>: <?php echo $peg['tempat_lhr']; ?>, <?php echo $peg['tgl_lhr']; ?></td></tr>
                                        <tr><td>Agama</td><td>: <?php echo $peg['agama']; ?></td></tr>
                                        <tr><td>Jenis Kelamin</td><td>: <?php echo $peg['jk']; ?></td></tr>
                                        <tr><td>Golongan Darah</td><td>: <?php echo $peg['gol_darah']; ?></td></tr>
                                        <tr><td>Status Pernikahan</td><td>: <?php echo $peg['status_nikah']; ?></td></tr>
                                        <tr><td>Status Kepegawaian</td><td>: <?php echo $peg['status_kepeg']; ?></td></tr>
                                        <tr><td>Alamat</td><td>: <?php echo $peg['alamat']; ?></td></tr>
                                        <tr><td>No. Telp</td><td>: <?php echo $peg['telp']; ?></td></tr>
                                        <tr><td>Email</td><td>: <?php echo $peg['email']; ?></td></tr>
                                        <tr><td>No BPJS Tenaga Kerja</td><td>: <?php echo $peg['bpjstk']; ?></td></tr>
                                    </table>
                                </div>
                                </div>

                            <div class="tab-pane fade" id="suamiistri" role="tabpanel" aria-labelledby="suamiistri-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap table-bordered">
                                        <thead><tr><th>NIK</th><th>Nama</th><th>TTL</th><th>Pendidikan</th><th>Pekerjaan</th><th>Hubungan</th></tr></thead> <tbody>
                                            <?php $tampilSi = mysqli_query($conn,"SELECT a.*, (SELECT desc_pekerjaan FROM tb_master_pekerjaan WHERE id_pekerjaan=a.id_pekerjaan) pekerjaan FROM tb_suamiistri a WHERE id_peg='$id_peg'");
                                            while($si=mysqli_fetch_array($tampilSi)){ ?>
                                            <tr>
                                                <td><?php echo $si['nik'];?></td>
                                                <td><?php echo $si['nama'];?></td>
                                                <td><?php echo $si['tmp_lhr'];?>, <?php echo date('d-m-Y',strtotime($si['tgl_lhr']));?></td>
                                                <td><?php echo $si['pendidikan'];?></td>
                                                <td><?php echo $si['pekerjaan'];?></td>
                                                <td><?php echo $si['status_hub'];?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="anak" role="tabpanel" aria-labelledby="anak-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap table-bordered">
                                        <thead><tr><th>No</th><th>NIK</th><th>Nama</th><th>TTL</th><th>Pendidikan</th><th>Pekerjaan</th><th>Anak Ke</th></tr></thead> <tbody>
                                            <?php $no=0; $tampilAnak = mysqli_query($conn,"SELECT * FROM tb_anak WHERE id_peg='$id_peg' ORDER BY anak_ke ASC");
                                            while($anak=mysqli_fetch_array($tampilAnak)){ $no++; ?>
                                            <tr>
                                                <td><?=$no?></td>
                                                <td><?php echo $anak['nik'];?></td>
                                                <td><?php echo $anak['nama'];?></td>
                                                <td><?php echo $anak['tmp_lhr'];?>, <?php echo $anak['tgl_lhr'];?></td>
                                                <td><?php echo $anak['pendidikan'];?></td>
                                                <td><?php echo $anak['pekerjaan'];?></td>
                                                <td align="center"><?php echo $anak['anak_ke'];?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="ortu" role="tabpanel" aria-labelledby="ortu-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover text-nowrap table-bordered">
                                        <thead><tr><th>NIK</th><th>Nama</th><th>TTL</th><th>Pendidikan</th><th>Pekerjaan</th><th>Hubungan</th></tr></thead> <tbody>
                                            <?php $tampilOrtu = mysqli_query($conn,"SELECT * FROM tb_ortu WHERE id_peg='$id_peg'");
                                            while($ortu=mysqli_fetch_array($tampilOrtu)){ ?>
                                            <tr>
                                                <td><?php echo $ortu['nik'];?></td>
                                                <td><?php echo $ortu['nama'];?></td>
                                                <td><?php echo $ortu['tmp_lhr'];?>, <?php echo $ortu['tgl_lhr'];?></td>
                                                <td><?php echo $ortu['pendidikan'];?></td>
                                                <td><?php echo $ortu['pekerjaan'];?></td>
                                                <td><?php echo $ortu['status_hub'];?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="card card-outline card-primary">
                    <div class="card-header-custom">
                        <i class="fas fa-history mr-1"></i> Riwayat
                    </div>
                    <div class="card-body-custom">
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#pengangkatan">Pengangkatan</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#jabatan">Jabatan</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#mutasi">Mutasi</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#hukum">Pelanggaran</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#diklat">Diklat</button>
                        <button class="tag-btn" data-bs-toggle="modal" data-bs-target="#sertifikasi">Sertifikasi</button>
                    </div>
                </div>

            </div>
        </div>
        
        <div id="bahasa" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Kemampuan Bahasa</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>No</th><th>Jenis</th><th>Bahasa</th><th>Kemampuan</th></tr></thead> <tbody>
                                <?php $no=0; $tampilBhs = mysqli_query($conn,"SELECT * FROM tb_bahasa WHERE id_peg='$id_peg'"); while($bhs=mysqli_fetch_array($tampilBhs)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$bhs['jns_bhs']?></td><td><?=$bhs['bahasa']?></td><td><?=$bhs['kemampuan']?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="pendidikan" class="modal fade" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Pendidikan</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>Jenjang</th><th>Nama</th><th>Lokasi</th><th>Jurusan</th><th>No. Ijazah</th><th>Tgl</th><th>Kepala</th><th>Status</th></tr></thead> <tbody>
                                <?php $tampilSek = mysqli_query($conn,"SELECT * FROM tb_pendidikan WHERE id_peg='$id_peg' ORDER BY tgl_ijazah DESC"); while($sek=mysqli_fetch_array($tampilSek)){ ?>
                                    <tr>
                                        <td><?=$sek['jenjang']?></td><td><?=$sek['nama_sekolah']?></td><td><?=$sek['lokasi']?></td><td><?=$sek['jurusan']?></td><td><?=$sek['no_ijazah']?></td><td><?=$sek['tgl_ijazah']?></td><td><?=$sek['kepala']?></td>
                                        <td><?= ($sek['status'] == "") ? "-" : "Pend ".$sek['status']; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="jabatan" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Jabatan</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>No</th><th>Jabatan</th><th>TMT</th><th>Sampai</th><th>Status</th></tr></thead> <tbody>
                                <?php $no=0; $tampilJab = mysqli_query($conn,"SELECT id_peg, tmt_jabatan, sampai_tgl, status_jab, kode_jabatan, (SELECT jabatan FROM tb_ref_jabatan WHERE kode_jabatan=tb_jabatan.kode_jabatan) nama_jabatan, id_jab FROM tb_jabatan WHERE id_peg = '$id_peg' ORDER BY tmt_jabatan DESC");
                                while($jab=mysqli_fetch_array($tampilJab)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$jab['nama_jabatan']?></td><td><?=$jab['tmt_jabatan']?></td><td><?=$jab['sampai_tgl']?></td>
                                        <td><?= ($jab['status_jab'] == "") ? "-" : $jab['status_jab']; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="pangkat" class="modal fade" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Pangkat</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead>
                                    <tr><th rowspan="2">No</th><th rowspan="2">Pangkat</th><th rowspan="2">Gol</th><th rowspan="2">Jenis</th><th rowspan="2">TMT</th><th colspan="3" class="text-center">SK</th><th rowspan="2">Status</th></tr>
                                    <tr><th>Pejabat</th><th>Nomor</th><th>Tgl</th></tr>
                                </thead>
                                <tbody>
                                <?php $no=0; $tampilPan = mysqli_query($conn,"SELECT * FROM tb_pangkat WHERE id_peg='$id_peg' ORDER BY tgl_sk"); while($pangkat=mysqli_fetch_array($tampilPan)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$pangkat['pangkat']?></td><td><?=$pangkat['gol']?></td><td><?=$pangkat['jns_pangkat']?></td><td><?=$pangkat['tmt_pangkat']?></td>
                                        <td><?=$pangkat['pejabat_sk']?></td><td><?=$pangkat['no_sk']?></td><td><?=$pangkat['tgl_sk']?></td>
                                        <td><?= ($pangkat['status_pan'] == "") ? "-" : $pangkat['status_pan']; ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="hukum" class="modal fade" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Hukuman</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead>
                                    <tr><th rowspan="2">No</th><th rowspan="2">Hukuman</th><th colspan="3" class="text-center">SK</th><th colspan="3" class="text-center">Pemulihan</th></tr>
                                    <tr><th>Pejabat</th><th>Nomor</th><th>Tgl</th><th>Pejabat</th><th>Nomor</th><th>Tgl</th></tr>
                                </thead>
                                <tbody>
                                <?php $no=0; $tampilHuk = mysqli_query($conn,"SELECT * FROM tb_hukuman WHERE id_peg='$id_peg' ORDER BY tgl_sk"); while($hukum=mysqli_fetch_array($tampilHuk)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$hukum['hukuman']?></td>
                                        <td><?=$hukum['pejabat_sk']?></td><td><?=$hukum['no_sk']?></td><td><?=$hukum['tgl_sk']?></td>
                                        <td><?=$hukum['pejabat_pulih']?></td><td><?=$hukum['no_pulih']?></td><td><?=$hukum['tgl_pulih']?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="diklat" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Diklat</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>No</th><th>Nama</th><th>Penyelenggara</th><th>Tempat</th><th>Angkatan</th><th>Tahun</th></tr></thead>
                                <tbody>
                                <?php $no=0; $tampilDik = mysqli_query($conn,"SELECT * FROM tb_diklat WHERE id_peg='$id_peg' ORDER BY tahun"); while($dik=mysqli_fetch_array($tampilDik)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$dik['diklat']?></td><td><?=$dik['penyelenggara']?></td><td><?=$dik['tempat']?></td><td><?=$dik['angkatan']?></td><td><?=$dik['tahun']?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="sertifikasi" class="modal fade" role="dialog">
             <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Sertifikasi</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                             <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>No</th><th>Sertifikasi</th><th>Penyelenggara</th><th>Tgl Sertifikat</th><th>Tgl Expired</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php $no=0; $tampilSert = mysqli_query($conn,"SELECT tb_sertifikasi.*, DATEDIFF(tgl_expired, CURDATE()) AS 'selisih' FROM tb_sertifikasi WHERE id_peg='$id_peg' ORDER BY tgl_sertifikat"); while($tug=mysqli_fetch_array($tampilSert)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td>
                                        <td><?=$tug['sertifikasi']?></td><td><?=$tug['penyelenggara']?></td>
                                        <td><?=date('d-m-Y',strtotime($tug['tgl_sertifikat']))?></td>
                                        <td><?=date('d-m-Y',strtotime($tug['tgl_expired']))?></td>
                                        <td align="center">
                                            <?= ($tug['selisih'] < 0) ? "<small class='badge badge-danger'>Expired</small>" : "<small class='badge badge-info'>Aktif</small>" ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                             </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
             </div>
        </div>

        <div id="pengangkatan" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Pengangkatan</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>No</th><th>Status</th><th>Tgl</th><th>No SK</th><th>Lampiran</th></tr></thead>
                                <tbody>
                                <?php $no=0; $tampilAngkat = mysqli_query($conn,"SELECT * FROM tb_angkat WHERE id_peg_baru='$id_peg' ORDER BY tgl_mutasi DESC"); while($Angkat=mysqli_fetch_array($tampilAngkat)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$Angkat['jns_mutasi']?></td><td><?=$Angkat['tgl_mutasi']?></td><td><?=$Angkat['no_mutasi']?></td>
                                        <td><a href="home-admin.php?page=view-pengangkatan&id_angkat=<?=$Angkat['id_angkat']?>" class="text-info"><i class="fa fa-file-pdf"></i> PDF</a></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="mutasi" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Riwayat Mutasi</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead><tr><th>No</th><th>Jenis</th><th>Tanggal</th><th>No SK</th></tr></thead>
                                <tbody>
                                <?php $no=0; $tampilMut = mysqli_query($conn,"SELECT * FROM tb_mutasi WHERE id_peg='$id_peg'"); while($mut=mysqli_fetch_array($tampilMut)){ $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$mut['jns_mutasi']?></td><td><?=$mut['tgl_mutasi']?></td><td><?=$mut['no_mutasi']?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="dp3" class="modal fade" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Sasaran Kerja Pegawai</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th><th colspan="2">Periode</th><th colspan="2">Penilai</th><th rowspan="2">Total</th><th rowspan="2">Rata2</th><th rowspan="2">Mutu</th><th rowspan="2">Detail</th>
                                    </tr>
                                    <tr><th>Awal</th><th>Akhir</th><th>Pejabat</th><th>Atasan</th></tr>
                                </thead>
                                <tbody>
                                <?php $no=0; $tampilDp3 = mysqli_query($conn,"SELECT * FROM tb_dp3 WHERE id_peg='$id_peg' ORDER BY periode_akhir"); while($dp3=mysqli_fetch_array($tampilDp3)){ $id_dp3 =$dp3['id_dp3']; $no++; ?>
                                    <tr>
                                        <td><?=$no?></td><td><?=$dp3['periode_awal']?></td><td><?=$dp3['periode_akhir']?></td><td><?=$dp3['pejabat_penilai']?></td><td><?=$dp3['atasan_pejabat_penilai']?></td>
                                        <td>
                                        <?php
                                            $nilai = mysqli_query($conn,"SELECT * FROM tb_dp3 WHERE id_dp3='$id_dp3'");
                                            $ndp3 = mysqli_fetch_assoc($nilai);
                                            $jml_nilai = $ndp3['nilai_kesetiaan']+$ndp3['nilai_prestasi']+$ndp3['nilai_tgjwb']+$ndp3['nilai_ketaatan']+$ndp3['nilai_kejujuran']+$ndp3['nilai_kerjasama']+$ndp3['nilai_prakarsa']+$ndp3['nilai_kepemimpinan'];
                                            echo $jml_nilai;
                                        ?>
                                        </td>
                                        <td><?= round($jml_nilai/8, 2) ?></td>
                                        <td><?=$dp3['hasil_penilaian']?></td>
                                        <td><a href="home-admin.php?page=view-detail-data-dp3&id_dp3=<?=$dp3['id_dp3']?>" class="btn btn-xs btn-warning">Detail</a></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

        <div id="pensiun" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Info Pensiun</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <tr><th>Tanggal Kelahiran</th><td><?=$peg['tgl_lhr']?></td></tr>
                            <tr><th>Jatuh Tempo Pensiun</th><td><?=$peg['tgl_pensiun']?></td></tr>
                        </table>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

		<!-- id="naikpkt" -->
        <div id="#" class="modal fade" role="dialog"> 
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Estimasi Kenaikan Pangkat</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-hover table-bordered">
                            <thead><tr><th>Periode</th><th>Tanggal</th></tr></thead>
                            <tbody>
                                <?php
                                $begin = new DateTime($peg['tgl_naikpangkat']);
                                $end = new DateTime($peg['tgl_pensiun']);
                                $no=0;
                                for($i = $begin; $begin <= $end; $i->modify('+4 year')){ $no++; ?>
                                <tr><td>Periode <?=$no?></td><td><?=$i->format("Y-m-d")?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

		<!-- id="naikgj" -->
        <div id="#" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title">Estimasi Kenaikan Gaji Berkala</h4>
                        <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-hover table-bordered">
                            <thead><tr><th>Periode</th><th>Tanggal</th></tr></thead>
                            <tbody>
                                <?php
                                $begin = new DateTime($peg['tgl_naikgaji']);
                                $end = new DateTime($peg['tgl_pensiun']);
                                $no=0;
                                for($i = $begin; $begin <= $end; $i->modify('+2 year')){ $no++; ?>
                                <tr><td>Periode <?=$no?></td><td><?=$i->format("Y-m-d")?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
                </div>
            </div>
        </div>

    </div></section>