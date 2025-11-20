<!-- Content Header (Page header) -->
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
	</div><!-- /.container-fluid -->
</section>
<?php
// Cek session login

// === wajib di paling atas file sebelum output apapun ===
if (session_status() === PHP_SESSION_NONE) session_start();

// cek session login
if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

include "dist/koneksi.php";

// ambil hak akses (aman)
$hak_akses = strtolower(trim($_SESSION['hak_akses'] ?? ''));

// ambil id_peg dengan prioritas:
// - kalau user biasa -> pakai session id_pegawai
// - else kalau ada ?id_peg= -> pakai itu (admin melihat profile tertentu)
// - else fallback ke session id_pegawai (mis. admin melihat profil sendiri)
$id_peg = null;

if ($hak_akses === 'user') {
    // user hanya boleh melihat profil sendiri
    $id_peg = $_SESSION['id_pegawai'] ?? null;
} elseif (!empty($_GET['id_peg'])) {
    // admin/kepala melihat profil via parameter GET
    $id_peg = $_GET['id_peg'];
} else {
    // fallback: mungkin admin ingin lihat profil sendiri
    $id_peg = $_SESSION['id_pegawai'] ?? null;
}

$id_peg = trim((string)$id_peg);

// jika masih kosong -> kasih pesan (atau redirect)
if ($id_peg === '') {
    die("Error. ID Pegawai tidak ditemukan.");
}

// sanitasi sebelum query
$id_peg_safe = mysqli_real_escape_string($conn, $id_peg);

// ambil data pegawai
$tampilPeg = mysqli_query($conn, "
    SELECT p.*,
           u.id_user, u.hak_akses
    FROM tb_pegawai p
    LEFT JOIN tb_user u ON u.id_pegawai = p.id_peg
    WHERE p.id_peg = '{$id_peg_safe}'
");

if (!$tampilPeg) {
    // debugging query error
    die("Query error: " . mysqli_error($conn));
}

if (mysqli_num_rows($tampilPeg) == 0) {
    die("Data pegawai tidak ditemukan.");
}

$peg = mysqli_fetch_array($tampilPeg);
?>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
					<a href="home-admin.php?page=form-ganti-foto&id_peg=<?= htmlspecialchars($peg['id_peg']) ?>">
<!-- <a href="home-admin.php?page=form-ganti-foto&id_peg=<?= htmlspecialchars($peg['id_peg']) ?>"> -->
<?php
// PHP 5.6 compatible robust foto resolver (debug enabled, hapus komentar debug jika sudah OK)

$raw = isset($peg['foto']) ? trim((string)$peg['foto']) : '';
$jk  = isset($peg['jk']) ? strtolower(trim((string)$peg['jk'])) : '';

$FS_ROOT = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_FILENAME'])), '/');
$URL_BASE = ''; // kalau src harus relatif dari root aplikasimu

// candidates yang mungkin (relatif dari root app)
$candidates = array();
if ($raw !== '') {
    $rawClean = ltrim($raw, '/');
    $candidates[] = $rawClean;
    $fileOnly = basename($rawClean);
    $candidates[] = "uploads/foto/".$fileOnly;
    $candidates[] = "pages/assets/foto/".$fileOnly;
    $candidates[] = "pages/asset/foto/".$fileOnly;
}

// fallback berdasarkan jenis kelamin
$fallback = ($jk === 'laki-laki') ? 'no-foto-male.png' : 'no-foto-female.png';
$candidates[] = "pages/assets/foto/".$fallback;
$candidates[] = "pages/asset/foto/".$fallback;
$candidates[] = "uploads/foto/".$fallback;
$candidates[] = "pages/assets/foto/no-foto.png";
$candidates[] = "pages/asset/foto/no-foto.png";

$src = null;
$debug_lines = array();

// 1) coba langsung file_exists pada tiap kandidat
foreach ($candidates as $rel) {
    $fs = $FS_ROOT . '/' . $rel;
    $debug_lines[] = "check: $fs => " . (file_exists($fs) ? "FOUND" : "MISSING");
    if (file_exists($fs)) {
        $src = $rel;
        break;
    }
}

// 2) kalau belum ketemu: coba case-insensitive search pada folder-folder kandidat
if ($src === null) {
    // kumpulkan folder yang mungkin
    $folders = array();
    foreach ($candidates as $rel) {
        $folder = $FS_ROOT . '/' . dirname($rel);
        if (!in_array($folder, $folders)) $folders[] = $folder;
    }

    $targetFile = $fileOnly = ($raw !== '' ? basename($raw) : $fallback);

    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            $debug_lines[] = "dir missing: $folder";
            continue;
        }
        $debug_lines[] = "scanning dir: $folder";
        $entries = scandir($folder);
        foreach ($entries as $e) {
            if ($e === '.' || $e === '..') continue;
            if (strtolower($e) === strtolower($fileOnly)) {
                // gunakan file actual dari filesystem (preserve case)
                $relDir = str_replace($FS_ROOT.'/', '', $folder);
                $src = $relDir . '/' . $e;
                $debug_lines[] = "ci-match: $folder/$e";
                break 2;
            }
        }
    }
}

// 3) kalau masih null gunakan default paling dasar
if ($src === null) {
    $src = "pages/assets/foto/no-foto.png";
    $debug_lines[] = "fallback to default: $src";
}

// cache-buster
$cb = time();
?>
<!-- debug (hapus setelah OK):
FS_ROOT=<?= htmlspecialchars($FS_ROOT) ?> 
picked src=<?= htmlspecialchars($src) ?> 
<?php foreach ($debug_lines as $dl) { echo "<!-- ".htmlspecialchars($dl)." -->\n"; } ?>
-->

<img
    class="profile-user-img img-fluid"
    src="<?= htmlspecialchars($URL_BASE . $src) ?>?cb=<?= $cb ?>"
    alt="Foto Pegawai"
    style="width:140px;height:140px;object-fit:cover;border-radius:50%"
    onerror="this.onerror=null;this.src='pages/assets/foto/no-foto.png'">
</a>



					<br />
									<h3 class="profile-username text-center"><?php echo $peg['nama']; ?></h3>
									<p class="text-muted text-center"><?php echo $peg['id_peg']; ?></p>
									<ul class="list-group list-group-unbordered mb-3">
										<li class="list-group-item">
											<i class="fa fa-phone float-left"></i> <a class="float-right"><?php echo $peg['telp']; ?></a>
										</li>
										<li class="list-group-item">
											<i class="fa fa-envelope  float-left"></i> <a class="float-right"><?php echo $peg['email']; ?></a>
										</li>
									</ul>
								</div>
							</div>
							<!-- /.card-body -->
						</div>
						<!-- /.card -->

						<div class="card card-primary card-outline">
							<div class="card-header">
								<strong><i class="fa fa-calendar margin-r-5"></i> Schedule</strong>
							</div>
							<div class="card-body">
								<button type="button" class="btn bg-orange btn-sm" data-bs-toggle="modal" data-bs-target="#pensiun">Pensiun</button>
								<button type="button" class="btn bg-purple btn-sm" data-bs-toggle="modal" data-bs-target="#naikpkt">Pangkat</button>
								<button type="button" class="btn bg-navy btn-sm" data-bs-toggle="modal" data-bs-target="#naikgj">Gaji</button>
							</div>
						</div>
						<div class="card card-primary card-outline">
							<div class="card-header">
								<strong><i class="fa fa-book margin-r-5"></i> Education</strong>
							</div>
							<div class="card-body">
								<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bahasa">Bahasa</button>
								<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pendidikan">Pendidikan</button>
							</div>
						</div>
						<div class="card card-primary card-outline">
							<div class="card-header">
								<strong><i class="fa fa-list-ul margin-r-5"></i> Sasaran Kerja Pegawai</strong>
							</div>
							<div class="card-body">
								<button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#dp3">SKP</button>
							</div>
						</div>
					</div>
					<!-- /.col -->
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
              			<a class="nav-link" id="ortu-tab" data-bs-toggle="tab" href="#ortu" role="tab" aria-controls="ortu" aria-selected="false">Ortu</a>
              		</li>
              	</ul>
							</div>
							<!-- /.card-header -->
							<div class="card-body">	
								<div class="tab-content" id="custom-tabs-one-tabContent">
									<div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
										<div class="box-body no-padding">
											<table class="col-sm-12 table-condensed">
												<tr>
													<td class="col-sm-3">NIK</td>
													<td class="col-sm-9">: <?php echo $peg['nip']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Nama</td>
													<td class="col-sm-9">: <?php echo $peg['nama']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Tempat, Tanggal Lahir</td>
													<td class="col-sm-9">: <?php echo $peg['tempat_lhr']; ?>, <?php echo $peg['tgl_lhr']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Agama</td>
													<td class="col-sm-9">: <?php echo $peg['agama']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Jenis Kelamin</td>
													<td class="col-sm-9">: <?php echo $peg['jk']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Golongan Darah</td>
													<td class="col-sm-9">: <?php echo $peg['gol_darah']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Status Pernikahan</td>
													<td class="col-sm-9">: <?php echo $peg['status_nikah']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Status Kepegawaian</td>
													<td class="col-sm-9">: <?php echo $peg['status_kepeg']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Alamat</td>
													<td class="col-sm-9">: <?php echo $peg['alamat']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">No. Telp</td>
													<td class="col-sm-9">: <?php echo $peg['telp']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">Email</td>
													<td class="col-sm-9">: <?php echo $peg['email']; ?></td>
												</tr>
												<tr>
													<td class="col-sm-3">No BPJS Tenaga Kerja</td>
													<td class="col-sm-9">: <?php echo $peg['bpjstk']; ?></td>
												</tr>								
											</table>
										</div>
										<p>
										<div class="col-sm-offset-3 col-sm-7">
											<a href="home-admin.php?page=form-master-data-pegawai&mode=edit&id=<?= $peg['id_peg'] ?>" class="btn btn-warning btn-sm">
												<i class="fas fa-edit"></i> Edit Data
											</a>
											<!-- <a href="./pages/report/print-biodata-pegawai.php?id_peg=<?= $id_peg ?>" target="_blank" class="btn btn-success btn-sm"> -->
											<a href="#"  class="btn btn-success btn-sm">
												<i class="fas fa-print"></i> Cetak CV
											</a>
										</div>	
									</div>

									<!-- START: improved tables for Suami/Istri, Anak, Ortu -->
<style>
/* local styles untuk memperkecil kolom & membiarkan teks wrap */
.profile-tbl .table {
  font-size: 13px;
}
.profile-tbl th {
  white-space: nowrap;
  vertical-align: middle;
}
.profile-tbl td {
  white-space: normal; /* izinkan wrap */
  word-wrap: break-word;
  vertical-align: middle;
}

/* Batasi lebar kolom tertentu agar tidak melebar */
.profile-tbl th.col-nik, .profile-tbl td.col-nik { max-width: 140px; }
.profile-tbl th.col-nama, .profile-tbl td.col-nama { max-width: 260px; }
.profile-tbl th.col-ttl, .profile-tbl td.col-ttl { max-width: 200px; }
.profile-tbl th.col-pendidikan, .profile-tbl td.col-pendidikan { max-width: 120px; }
.profile-tbl th.col-pekerjaan, .profile-tbl td.col-pekerjaan { max-width: 160px; }
.profile-tbl th.col-hub, .profile-tbl td.col-hub { max-width: 100px; }

/* Small screens: tampilkan tabel sebagai blok list agar mudah dibaca */
@media (max-width: 768px) {
  .profile-tbl table, .profile-tbl thead, .profile-tbl tbody, .profile-tbl th, .profile-tbl td, .profile-tbl tr {
    display: block;
    width: 100%;
  }
  .profile-tbl thead { display: none; }
  .profile-tbl tr { margin-bottom: 12px; border: 1px solid #eee; padding: 8px; border-radius: 4px; }
  .profile-tbl td {
    display: flex;
    justify-content: space-between;
    padding: 6px 8px;
  }
  .profile-tbl td:before {
    content: attr(data-label);
    font-weight: 600;
    margin-right: 8px;
    flex: 0 0 40%;
    color: #444;
  }
  .profile-tbl td .tools { text-align: right; }
}
</style>

<!-- Suami / Istri -->
<div class="tab-pane fade" id="suamiistri" role="tabpanel" aria-labelledby="suamiistri-tab">
  <div class="table-responsive profile-tbl">
    <table class="table table-hover table-bordered">
      <thead>
        <tr>
          <th class="col-nik">NIK</th>
          <th class="col-nama">Nama</th>
          <th class="col-ttl">TTL</th>
          <th class="col-pendidikan">Pendidikan</th>
          <th class="col-pekerjaan">Pekerjaan</th>
          <th class="col-hub">Hubungan</th>
          <th style="width:80px">More</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $q = mysqli_query($conn, "
          SELECT a.*,
                 (SELECT desc_pekerjaan FROM tb_master_pekerjaan WHERE id_pekerjaan=a.id_pekerjaan) AS pekerjaan
          FROM tb_suamiistri a
          WHERE id_peg = '".mysqli_real_escape_string($conn, $id_peg)."'
          ORDER BY a.nama ASC
        ");
        if ($q) {
          while ($si = mysqli_fetch_assoc($q)) {
            $ttl = trim($si['tmp_lhr'] . ', ' . ($si['tgl_lhr'] ? date('d-m-Y', strtotime($si['tgl_lhr'])) : ''));
        ?>
        <tr>
          <td class="col-nik" data-label="NIK"><?= htmlspecialchars($si['nik']); ?></td>
          <td class="col-nama" data-label="Nama"><?= htmlspecialchars($si['nama']); ?></td>
          <td class="col-ttl" data-label="TTL"><?= htmlspecialchars($ttl); ?></td>
          <td class="col-pendidikan" data-label="Pendidikan"><?= htmlspecialchars($si['pendidikan']); ?></td>
          <td class="col-pekerjaan" data-label="Pekerjaan"><?= htmlspecialchars($si['pekerjaan']); ?></td>
          <td class="col-hub" data-label="Hubungan"><?= htmlspecialchars($si['status_hub']); ?></td>
          <td class="tools" data-label="More" align="center">
            <a href="home-admin.php?page=form-edit-data-suami-istri&id_si=<?= urlencode($si['id_si']); ?>" title="edit"><i class="fa fa-edit"></i></a>
            &nbsp;&nbsp;
            <a href="home-admin.php?page=delete-data-suami-istri&id_si=<?= urlencode($si['id_si']); ?>" title="delete" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa fa-trash-o"></i></a>
          </td>
        </tr>
        <?php
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Anak -->
<div class="tab-pane fade" id="anak" role="tabpanel" aria-labelledby="anak-tab">
  <div class="table-responsive profile-tbl">
    <table class="table table-hover table-bordered">
      <thead>
        <tr>
          <th style="width:40px">No</th>
          <th class="col-nik">NIK</th>
          <th class="col-nama">Nama</th>
          <th class="col-ttl">TTL</th>
          <th class="col-pendidikan">Pendidikan</th>
          <th class="col-pekerjaan">Pekerjaan</th>
          <th class="col-hub">Hubungan</th>
          <th style="width:60px">Anak Ke</th>
          <th style="width:120px">No. BPJS</th>
          <th style="width:80px">More</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 0;
        $q = mysqli_query($conn, "SELECT * FROM tb_anak WHERE id_peg = '".mysqli_real_escape_string($conn,$id_peg)."' ORDER BY anak_ke ASC");
        if ($q) {
          while ($anak = mysqli_fetch_assoc($q)) {
            $no++;
            $ttl = trim($anak['tmp_lhr'] . ', ' . ($anak['tgl_lhr'] ? date('d-m-Y', strtotime($anak['tgl_lhr'])) : ''));
        ?>
        <tr>
          <td data-label="No"><?= $no ?></td>
          <td class="col-nik" data-label="NIK"><?= htmlspecialchars($anak['nik']); ?></td>
          <td class="col-nama" data-label="Nama"><?= htmlspecialchars($anak['nama']); ?></td>
          <td class="col-ttl" data-label="TTL"><?= htmlspecialchars($ttl); ?></td>
          <td class="col-pendidikan" data-label="Pendidikan"><?= htmlspecialchars($anak['pendidikan']); ?></td>
          <td class="col-pekerjaan" data-label="Pekerjaan"><?= htmlspecialchars($anak['pekerjaan']); ?></td>
          <td class="col-hub" data-label="Hubungan"><?= htmlspecialchars($anak['status_hub']); ?></td>
          <td align="center" data-label="Anak Ke"><?= htmlspecialchars($anak['anak_ke']); ?></td>
          <td data-label="No. BPJS"><?= htmlspecialchars($anak['bpjs_anak']); ?></td>
          <td class="tools" data-label="More" align="center">
            <a href="home-admin.php?page=form-edit-data-anak&id_anak=<?= urlencode($anak['id_anak']); ?>" title="edit"><i class="fa fa-edit"></i></a>
            &nbsp;&nbsp;
            <a href="home-admin.php?page=delete-data-anak&id_anak=<?= urlencode($anak['id_anak']); ?>" title="delete" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa fa-trash-o"></i></a>
          </td>
        </tr>
        <?php
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Ortu -->
<div class="tab-pane fade" id="ortu" role="tabpanel" aria-labelledby="ortu-tab">
  <div class="table-responsive profile-tbl">
    <table class="table table-hover table-bordered">
      <thead>
        <tr>
          <th class="col-nik">NIK</th>
          <th class="col-nama">Nama</th>
          <th class="col-ttl">TTL</th>
          <th class="col-pendidikan">Pendidikan</th>
          <th class="col-pekerjaan">Pekerjaan</th>
          <th class="col-hub">Hubungan</th>
          <th style="width:80px">More</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $q = mysqli_query($conn, "SELECT * FROM tb_ortu WHERE id_peg = '".mysqli_real_escape_string($conn,$id_peg)."' ORDER BY nama ASC");
        if ($q) {
          while ($ortu = mysqli_fetch_assoc($q)) {
            $ttl = trim($ortu['tmp_lhr'] . ', ' . ($ortu['tgl_lhr'] ? date('d-m-Y', strtotime($ortu['tgl_lhr'])) : ''));
        ?>
        <tr>
          <td class="col-nik" data-label="NIK"><?= htmlspecialchars($ortu['nik']); ?></td>
          <td class="col-nama" data-label="Nama"><?= htmlspecialchars($ortu['nama']); ?></td>
          <td class="col-ttl" data-label="TTL"><?= htmlspecialchars($ttl); ?></td>
          <td class="col-pendidikan" data-label="Pendidikan"><?= htmlspecialchars($ortu['pendidikan']); ?></td>
          <td class="col-pekerjaan" data-label="Pekerjaan"><?= htmlspecialchars($ortu['pekerjaan']); ?></td>
          <td class="col-hub" data-label="Hubungan"><?= htmlspecialchars($ortu['status_hub']); ?></td>
          <td class="tools" data-label="More" align="center">
            <a href="home-admin.php?page=form-edit-data-ortu&id_ortu=<?= urlencode($ortu['id_ortu']); ?>" title="edit"><i class="fa fa-edit"></i></a>
            &nbsp;&nbsp;
            <a href="home-admin.php?page=delete-data-ortu&id_ortu=<?= urlencode($ortu['id_ortu']); ?>" title="delete" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa fa-trash-o"></i></a>
          </td>
        </tr>
        <?php
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
<!-- END: improved tables -->

									
									
								</div>
							</div>
						</div>
							<h5><i class="fa fa-bars margin-r-5"></i>Riwayat</h5>
							<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pengangkatan">Pengangkatan</button>							
							<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#jabatan">Jabatan</button>
							<button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#mutasi">Mutasi</button>	
							<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hukum">Pelanggaran</button>	
							<button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#diklat">Diklat</button>		
							<button type="button" class="btn bg-purple btn-sm" data-bs-toggle="modal" data-bs-target="#sertifikasi">Sertifikasi</button>
							<!-- Sementara belum digunakan
							<button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#pangkat">Kepangkatan</button>
							<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#harga">Penghargaan</button>
							<button type="button" class="btn bg-maroon btn-sm" data-bs-toggle="modal" data-bs-target="#seminar">Seminar</button>
							<button type="button" class="btn bg-olive btn-sm" data-bs-toggle="modal" data-bs-target="#cuti">Cuti</button>
							-->
						<!-- Modal -->
						<div id="bahasa" class="modal fade" role="dialog">
							<div class="modal-dialog modal-lg">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
										<h4 class="modal-title">Kemampuan Bahasa</h4>
									</div>
									<div class="modal-body  table-responsive p-0">
										<table class="table table-hover table-bordered">
											<thead>
												<tr>
													<th>No</th>
													<th>Jenis Bahasa</th>
													<th>Bahasa</th>
													<th>Kemampuan Bicara</th>
													<th>More</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$no=0;
												$tampilBhs	=mysqli_query($conn,"SELECT * FROM tb_bahasa WHERE id_peg='$id_peg'");
												while($bhs=mysqli_fetch_array($tampilBhs)){
													$no++;
													?>	
													<tr>
														<td><?=$no?></td>
														<td><?php echo $bhs['jns_bhs'];?></td>
														<td><?php echo $bhs['bahasa'];?></td>
														<td><?php echo $bhs['kemampuan'];?></td>
														<td class="tools" align="center"><a href="home-admin.php?page=form-edit-data-bahasa&id_bhs=<?=$bhs['id_bhs'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-bahasa&id_bhs=<?=$bhs['id_bhs'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
													</tr>
													<?php
												}
												?>
											</tbody>
										</table>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
						<div id="pendidikan" class="modal fade" role="dialog">
							<div class="modal-dialog modal-lg">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
										<h4 class="modal-title">Pendidikan</h4>
									</div>
									<div class="modal-body  table-responsive p-0">
										<table class="table table-hover table-bordered">
											<thead>
												<tr>
													<th>Jenjang</th>
													<th>Nama</th>
													<th>Lokasi</th>
													<th>Jurusan</th>
													<th>No. Ijazah</th>
													<th>Tgl. Ijazah</th>
													<th>Kepala / Rektor</th>
													<th>Status</th>
													<th>More</th>
													<th>Set Sbg Pend Akhir</th>
												</tr>
											</thead>
											<tbody>
												<?php
												$tampilSek	=mysqli_query($conn,"SELECT * FROM tb_pendidikan WHERE id_peg='$id_peg' ORDER BY tgl_ijazah DESC");
												while($sek=mysqli_fetch_array($tampilSek)){
													?>	
													<tr>
														<td><?php echo $sek['jenjang'];?></td>
														<td><?php echo $sek['nama_sekolah'];?></td>
														<td><?php echo $sek['lokasi'];?></td>
														<td><?php echo $sek['jurusan'];?></td>
														<td><?php echo $sek['no_ijazah'];?></td>
														<td><?php echo $sek['tgl_ijazah'];?></td>
														<td><?php echo $sek['kepala'];?></td>
														<td><?php 
														if ($sek['status'] ==""){
															echo "-";
														}
														else{
															echo "Pend $sek[status];";
														}
														?>
													</td>
													<td class="tools"><a href="home-admin.php?page=form-edit-data-sekolah&id_sekolah=<?=$sek['id_sekolah'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-sekolah&id_sekolah=<?=$sek['id_sekolah'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>	
													<td class="tools"><a href="home-admin.php?page=set-pendidikan-akhir&id_sekolah=<?=$sek['id_sekolah'];?>&id_peg=<?=$peg['id_peg'];?>" type="button" class="btn bg-orange btn-xs">Setup</a></td>
												</tr>
												<?php
											}
											?>
										</tbody>
									</table>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
					<div id="jabatan" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
							<h4 class="modal-title">Riwayat Jabatan</h4>
						</div>
						<div class="modal-body table-responsive p-0">
							<table class="table table-hover text-nowrap table-bordered">
							<thead>
								<tr>
								<th>No</th>
								<th>Jabatan</th>
								<th>TMT</th>
								<th>Sampai</th>
								<th>Status</th>
								<th>More</th>
								<th>Set Sbg Jabatan Sekarang</th>
								</tr>
							</thead>
							<tbody>
							<?php
								// (opsional) matikan tampilan warning agar tidak merusak HTML
								// error_reporting(E_ALL & ~E_NOTICE); ini_set('display_errors', 0);

								$no = 0;
								$id_peg_safe = mysqli_real_escape_string($conn, $id_peg);
								$sql = "
								SELECT
									j.id_jab,               -- << penting!
									j.id_peg,
									j.tmt_jabatan,
									j.sampai_tgl,
									j.status_jab,
									j.kode_jabatan,
									r.jabatan AS nama_jabatan
								FROM tb_jabatan j
								LEFT JOIN tb_ref_jabatan r ON r.kode_jabatan = j.kode_jabatan
								WHERE j.id_peg = '{$id_peg_safe}'
								ORDER BY j.tmt_jabatan DESC
								";
								$tampilJab = mysqli_query($conn, $sql);

								while ($jab = mysqli_fetch_assoc($tampilJab)) {
								$no++;
								$id_jab   = htmlspecialchars($jab['id_jab'] ?? '', ENT_QUOTES, 'UTF-8');
								$nama_jab = htmlspecialchars($jab['nama_jabatan'] ?? '-', ENT_QUOTES, 'UTF-8');
								$tmt      = $jab['tmt_jabatan']  ? date('Y-m-d', strtotime($jab['tmt_jabatan'])) : '-';
								$sampai   = $jab['sampai_tgl']    ? date('Y-m-d', strtotime($jab['sampai_tgl']))   : '-';
								$status   = trim((string)($jab['status_jab'] ?? '')) !== '' ? htmlspecialchars($jab['status_jab'], ENT_QUOTES, 'UTF-8') : '-';
							?>
								<tr>
								<td><?= $no ?></td>
								<td><?= $nama_jab ?></td>
								<td><?= $tmt ?></td>
								<td><?= $sampai ?></td>
								<td><?= $status ?></td>
								<td class="tools" align="center">
									<a href="home-admin.php?page=form-edit-data-jabatan&id_jab=<?= $id_jab ?>" title="edit"><i class="fa fa-edit"></i></a>
									&nbsp;&nbsp;&nbsp;&nbsp;
									<a href="home-admin.php?page=delete-data-jabatan&id_jab=<?= $id_jab ?>" title="delete"><i class="fa fa-trash-o"></i></a>
								</td>
								<td class="tools">
									<a href="home-admin.php?page=set-jabatan-sekarang&id_jab=<?= $id_jab ?>&id_peg=<?= htmlspecialchars($peg['id_peg'], ENT_QUOTES, 'UTF-8') ?>" type="button" class="btn bg-orange btn-xs">Setup</a>
								</td>
								</tr>
							<?php } ?>
							</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
						</div>
						</div>
					</div>
					</div>

					<div id="pangkat" class="modal fade" role="dialog">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
									<h4 class="modal-title">Riwayat Pangkat</h4>
								</div>
								<div class="modal-body  table-responsive p-0">
									<table class="table table-hover table-bordered">
										<thead>
											<tr>
												<th rowspan="2">No</th>
												<th rowspan="2">Pangkat</th>
												<th rowspan="2">Gol</th>
												<th rowspan="2">Jenis</th>
												<th rowspan="2">TMT</th>
												<th colspan="3">Surat Keputusan</th>
												<th rowspan="2">Status</th>
												<th rowspan="2">More</th>
												<th rowspan="2">Set Sbg Pangkat Sekarang</th>
											</tr>
											<tr>
												<th>Pejabat Pengesah</th>
												<th>Nomor</th>
												<th>Tgl Pengesahan</th>																		
											</tr>								
										</thead>
										<tbody>
											<?php
											$no=0;
											$tampilPan	=mysqli_query($conn,"SELECT * FROM tb_pangkat WHERE id_peg='$id_peg' ORDER BY tgl_sk");
											while($pangkat=mysqli_fetch_array($tampilPan)){
												$no++;
												?>	
												<tr>
													<td><?=$no?></td>
													<td><?php echo $pangkat['pangkat'];?></td>
													<td><?php echo $pangkat['gol'];?></td>
													<td><?php echo $pangkat['jns_pangkat'];?></td>
													<td><?php echo $pangkat['tmt_pangkat'];?></td>
													<td><?php echo $pangkat['pejabat_sk'];?></td>
													<td><?php echo $pangkat['no_sk'];?></td>
													<td><?php echo $pangkat['tgl_sk'];?></td>
													<td><?php 
													if ($pangkat['status_pan'] ==""){
														echo "-";
													}
													else{
														echo "$pangkat[status_pan]";
													}
												?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-pangkat&id_pangkat=<?=$pangkat['id_pangkat'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-pangkat&id_pangkat=<?=$pangkat['id_pangkat'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
												<td class="tools"><a href="home-admin.php?page=set-pangkat-sekarang&id_pangkat=<?=$pangkat['id_pangkat'];?>&gol=<?=$pangkat['gol'];?>&id_peg=<?=$peg['id_peg'];?>" type="button" class="btn bg-orange btn-xs">Setup</a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="hukum" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Hukuman</h4>
							</div>
							<div class="modal-body  table-responsive p-0">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th rowspan="2">No</th>
											<th rowspan="2">Jenis Hukuman</th>
											<th colspan="3">Surat Keputusan</th>
											<th colspan="4">Pemulihan</th>
										</tr>
										<tr>
											<th scope="col">Pejabat Pengesah</th>
											<th scope="col">Nomor</th>
											<th scope="col">Tgl Pengesahan</th>
											<th scope="col">Pejabat Pemulih</th>
											<th scope="col">Nomor Pemulihan</th>
											<th scope="col">Tgl Pemulihan</th>											
											<th scope="col">More</th>									
										</tr>								
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilHuk	=mysqli_query($conn,"SELECT * FROM tb_hukuman WHERE id_peg='$id_peg' ORDER BY tgl_sk");
										while($hukum=mysqli_fetch_array($tampilHuk)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $hukum['hukuman'];?></td>
												<td><?php echo $hukum['pejabat_sk'];?></td>
												<td><?php echo $hukum['no_sk'];?></td>
												<td><?php echo $hukum['tgl_sk'];?></td>
												<td><?php echo $hukum['pejabat_pulih'];?></td>
												<td><?php echo $hukum['no_pulih'];?></td>
												<td><?php echo $hukum['tgl_pulih'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-hukuman&id_hukum=<?=$hukum['id_hukum'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-hukuman&id_hukum=<?=$hukum['id_hukum'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="diklat" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Diklat</h4>
							</div>
							<div class="modal-body table-responsive">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th>Nama Diklat</th>
											<th>Penyelenggara</th>
											<th>Tempat</th>
											<th>Angkatan</th>
											<th>Tahun</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilDik	=mysqli_query($conn,"SELECT * FROM tb_diklat WHERE id_peg='$id_peg' ORDER BY tahun");
										while($dik=mysqli_fetch_array($tampilDik)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $dik['diklat'];?></td>
												<td><?php echo $dik['penyelenggara'];?></td>
												<td><?php echo $dik['tempat'];?></td>
												<td><?php echo $dik['angkatan'];?></td>
												<td><?php echo $dik['tahun'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-diklat&id_diklat=<?=$dik['id_diklat'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-diklat&id_diklat=<?=$dik['id_diklat'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="harga" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Penghargaan</h4>
							</div>
							<div class="modal-body  table-responsive p-0">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th>Nama Penghargaan</th>
											<th>Tahun</th>
											<th>Negara / Instansi Pemberi</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilHar	=mysqli_query($conn,"SELECT * FROM tb_penghargaan WHERE id_peg='$id_peg' ORDER BY tahun");
										while($har=mysqli_fetch_array($tampilHar)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $har['penghargaan'];?></td>
												<td><?php echo $har['tahun'];?></td>
												<td><?php echo $har['pemberi'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-penghargaan&id_penghargaan=<?=$har['id_penghargaan'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-penghargaan&id_penghargaan=<?=$har['id_penghargaan'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="sertifikasi" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Sertifikasi</h4>
							</div>
							<div class="modal-body table-responsive p-0">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th width="5%">No</th>
											<th width="25%">Jenis Sertifikasi</th>
											<th width="25%">Lembaga Penyedia</th>
											<th width="10%">Tgl Sertifikat</th>
											<th width="10%">Tgl Berakhir Sertifikat</th>
											<th width="15%">Status Sertifikat</th>
											<?php if (strtolower($hak_akses) != 'user'): ?>
												<th width="10%">More</th>
											<?php endif; ?>
										</tr>
									</thead>
									<tbody>
										<?php
										$no = 0;
										$tampilSert = mysqli_query($conn, "SELECT tb_sertifikasi.*, DATEDIFF(tgl_expired, CURDATE()) AS 'selisih' FROM tb_sertifikasi WHERE id_peg='$id_peg' ORDER BY tgl_sertifikat");
										while ($tug = mysqli_fetch_array($tampilSert)) {
											$no++;
										?>
											<tr>
												<td><?= $no ?></td>
												<td><?= $tug['sertifikasi']; ?></td>
												<td><?= $tug['penyelenggara']; ?></td>
												<td><?= date('d-m-Y', strtotime($tug['tgl_sertifikat'])); ?></td>
												<td><?= date('d-m-Y', strtotime($tug['tgl_expired'])); ?></td>
												<td align="center">
													<?php
													$selisih = $tug['selisih'];
													if ($selisih < 0) {
														echo "<small class='badge badge-danger'>Expired</small>";
													} else {
														echo "<small class='badge badge-info'>Aktif</small>";
													}
													?>
												</td>
												<?php if (strtolower($hak_akses) != 'user'): ?>
													<td class="tools">
														<a href="home-admin.php?page=form-edit-data-penugasan&id_penugasan=<?= $tug['id_sertif']; ?>" title="edit"><i class="fa fa-edit"></i></a>
														&nbsp;&nbsp;&nbsp;&nbsp;
														<a href="home-admin.php?page=delete-data-penugasan&id_penugasan=<?= $tug['id_sertif']; ?>" title="delete" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fa fa-trash-o"></i></a>
													</td>
												<?php endif; ?>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="seminar" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Seminar</h4>
							</div>
							<div class="modal-body  table-responsive p-0">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th>Nama Seminar</th>
											<th>Tempat</th>
											<th>Penyelenggara</th>
											<th>Tanggal Mulai</th>
											<th>Tanggal Selesai</th>
											<th>No. Piagam</th>
											<th>Tanggal Piagam</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilSem	=mysqli_query($conn,"SELECT * FROM tb_seminar WHERE id_peg='$id_peg' ORDER BY tgl_selesai");
										while($sem=mysqli_fetch_array($tampilSem)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $sem['seminar'];?></td>
												<td><?php echo $sem['tempat'];?></td>
												<td><?php echo $sem['penyelenggara'];?></td>
												<td><?php echo $sem['tgl_mulai'];?></td>
												<td><?php echo $sem['tgl_selesai'];?></td>
												<td><?php echo $sem['no_piagam'];?></td>
												<td><?php echo $sem['tgl_piagam'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-seminar&id_seminar=<?=$sem['id_seminar'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-seminar&id_seminar=<?=$sem['id_seminar'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="cuti" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Cuti</h4>
							</div>
							<div class="modal-body">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th>Jenis Cuti</th>
											<th>No. Surat Cuti</th>
											<th>Tgl Surat Cuti</th>
											<th>Tanggal Mulai</th>
											<th>Tanggal Selesai</th>
											<th>Keterangan</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilCut	=mysqli_query($conn,"SELECT * FROM tb_cuti WHERE id_peg='$id_peg' ORDER BY tgl_suratcuti");
										while($cut=mysqli_fetch_array($tampilCut)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $cut['jns_cuti'];?></td>
												<td><?php echo $cut['no_suratcuti'];?></td>
												<td><?php echo $cut['tgl_suratcuti'];?></td>
												<td><?php echo $cut['tgl_mulai'];?></td>
												<td><?php echo $cut['tgl_selesai'];?></td>
												<td><?php echo $cut['ket'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-cuti&id_cuti=<?=$cut['id_cuti'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-cuti&id_cuti=<?=$cut['id_cuti'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="pengangkatan" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Pengangkatan Pegawai</h4>
							</div>
							<div class="modal-body">
								<script>
									function basicPopup(url) {
										popupWindow = window.open(url, 'popUpWindow','height=300,width=700,left=50,resizeable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
									}
								</script>
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th>Status Pegawai</th>
											<th>Tgl Pengangkatan</th>
											<th>Nomor SK Pengangkatan</th>
											<th>Lampiran SK</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilAngkat	=mysqli_query($conn,"SELECT * FROM tb_angkat WHERE id_peg_baru='$id_peg' ORDER BY tgl_mutasi DESC");
										while($Angkat=mysqli_fetch_array($tampilAngkat)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $Angkat['jns_mutasi'];?></td>
												<td><?php echo $Angkat['tgl_mutasi'];?></td>
												<td><?php echo $Angkat['no_mutasi'];?></td>
												<td><a href="home-admin.php?page=view-pengangkatan&id_angkat=<?=$Angkat['id_angkat'];?>" title="lampiran"><i class="fa fa-file-pdf">&nbsp;&nbsp;</i></a><?php echo $Angkat['sk_mutasi'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-angkat&id_angkat=<?=$Angkat['id_angkat'];?>" title="edit"><i class="fa fa-edit"></i></a>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="mutasi" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Riwayat Mutasi</h4>
							</div>
							<div class="modal-body table-responsive p-0">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>No</th>
											<th>Jenis Mutasi</th>
											<th>Tanggal</th>
											<th>No. SK</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilMut	=mysqli_query($conn,"SELECT * FROM tb_mutasi WHERE id_peg='$id_peg'");
										while($mut=mysqli_fetch_array($tampilMut)){
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $mut['jns_mutasi'];?></td>
												<td><?php echo $mut['tgl_mutasi'];?></td>
												<td><?php echo $mut['no_mutasi'];?></td>
												<td class="tools"><a href="home-admin.php?page=form-edit-data-mutasi&id_mutasi=<?=$mut['id_mutasi'];?>" title="edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="home-admin.php?page=delete-data-mutasi&id_mutasi=<?=$mut['id_mutasi'];?>" title="delete"><i class="fa fa-trash-o"></i></a></td>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="dp3" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
								<h4 class="modal-title">Sasaran Kerja Pegawai</h4>
							</div>
							<div class="modal-body">
								<table class="table table-hover table-bordered">
									<thead>
										<tr>
											<th rowspan="2">No</th>
											<th colspan="2">Periode Penilaian</th>
											<th colspan="2">Penilai</th>
											<th rowspan="2">Nilai Total</th>
											<th rowspan="2">Rata-Rata</th>
											<th rowspan="2">Mutu</th>
										</tr>
										<tr>
											<th scope="col">Awal</th>
											<th scope="col">Akhir</th>
											<th scope="col">Pejabat</th>
											<th scope="col">Atasan Pejabat</th>
											<th>More</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no=0;
										$tampilDp3	=mysqli_query($conn,"SELECT * FROM tb_dp3 WHERE id_peg='$id_peg' ORDER BY periode_akhir");
										while($dp3=mysqli_fetch_array($tampilDp3)){
											$id_dp3	=$dp3['id_dp3'];									
											$no++;
											?>	
											<tr>
												<td><?=$no?></td>
												<td><?php echo $dp3['periode_awal'];?></td>
												<td><?php echo $dp3['periode_akhir'];?></td>
												<td><?php echo $dp3['pejabat_penilai'];?></td>
												<td><?php echo $dp3['atasan_pejabat_penilai'];?></td>
												<td><?php
												$nilai	=mysqli_query($conn,"SELECT * FROM tb_dp3 WHERE id_dp3='$id_dp3'");
												while($ndp3=mysqli_fetch_array($nilai)){
													$kesetiaan		=$ndp3['nilai_kesetiaan'];
													$prestasi		=$ndp3['nilai_prestasi'];
													$tgjwb			=$ndp3['nilai_tgjwb'];
													$ketaatan		=$ndp3['nilai_ketaatan'];
													$kejujuran		=$ndp3['nilai_kejujuran'];
													$kerjasama		=$ndp3['nilai_kerjasama'];
													$prakarsa		=$ndp3['nilai_prakarsa'];
													$kepemimpinan	=$ndp3['nilai_kepemimpinan'];
												}
												$jml_nilai	=$kesetiaan+$prestasi+$tgjwb+$ketaatan+$kejujuran+$kerjasama+$prakarsa+$kepemimpinan;
												$rata		=$jml_nilai/8;
												echo $jml_nilai;
												?>
											</td>
											<td><?=$rata?></td>
											<td><?php echo $dp3['hasil_penilaian'];?></td>
											<td class="tools"><a href="home-admin.php?page=view-detail-data-dp3&id_dp3=<?=$dp3['id_dp3'];?>" type="button" class="btn bg-orange btn-xs">Detail</a></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<div id="pensiun" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
							<h4 class="modal-title">Tanggal Pensiun</h4>
						</div>
						<div class="modal-body table-responsive p-0">
							<table class="table table-hover table-bordered">
								<thead>
									<tr>
										<th>Tanggal Kelahiran</th>
										<th>Tanggal Jatuh Tempo Pensiun</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$tampilPens	=mysqli_query($conn,"SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
									$pens	=mysqli_fetch_array($tampilPens);
									$lahir	=$pens['tgl_lhr'];
									$pensiun=$pens['tgl_pensiun'];							
									?>	
									<tr>
										<td><?=$lahir?></td>
										<td><?=$pensiun?></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<div id="naikpkt" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
							<h4 class="modal-title">Periode Kenaikan Pangkat</h4>
						</div>
						<div class="modal-body">
							<?php
							$tampilNp	=mysqli_query($conn,"SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
							$np	=mysqli_fetch_array($tampilNp);
							$naikpangkat	=$np['tgl_naikpangkat'];																	
							$naikpensiun	=$np['tgl_pensiun'];																	
							?>
							<table class="table table-hover table-bordered">
								<thead>								
									<tr>
										<th>Periode</th>
										<th>Tanggal Kenaikan Pangkat</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$begin = new DateTime($naikpangkat);
									$end = new DateTime($naikpensiun);
									$no=0;
									for($i = $begin; $begin <= $end; $i->modify('+4 year')){	
										$no++;
										?>
										<tr>
											<td>Periode <?=$no?></td>
											<td><?php echo $i->format("Y-m-d");?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<div id="naikgj" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
							<h4 class="modal-title">Periode Kenaikan Gaji</h4>
						</div>
						<div class="modal-body">
							<?php
							$tampilGj	=mysqli_query($conn,"SELECT * FROM tb_pegawai WHERE id_peg='$id_peg'");
							$ng	=mysqli_fetch_array($tampilGj);
							$naikgaji	=$ng['tgl_naikgaji'];																	
							$naikpens	=$ng['tgl_pensiun'];																	
							?>
							<table class="table table-hover table-bordered">
								<thead>								
									<tr>
										<th>Periode</th>
										<th>Tanggal Kenaikan Gaji</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$beging = new DateTime($naikgaji);
									$endg = new DateTime($naikpens);
									$nog=0;
									for($ig = $beging; $beging <= $endg; $ig->modify('+2 year')){	
										$nog++;
										?>
										<tr>
											<td>Periode <?=$nog?></td>
											<td><?php echo $ig->format("Y-m-d");?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
</div>
</div>
</div>

</section>