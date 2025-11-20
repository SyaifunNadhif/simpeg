<?php
// Pastikan session dimulai (biasanya sudah di header.php)
$page_title = "Data";
$page_subtitle = "Diklat Pegawai";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Data Diklat Pegawai"]
];
include "komponen/header.php";
include 'dist/koneksi.php';
include 'dist/library.php';

// --- LOGIKA FILTER & HAK AKSES ---
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_kantor_session = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// 1. Ambil nilai filter dari GET
$diklat_filter = isset($_GET['diklat']) ? $_GET['diklat'] : '';

if (!isset($_GET['filter'])) {
    $tahun = date('Y');
    if ($hak_akses == 'kepala') {
        $kantor = $kode_kantor_session;
    } else {
        $kantor = '';
    }
} else {
    $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
    
    if ($hak_akses == 'kepala') {
        $kantor = $kode_kantor_session; 
    } else {
        $kantor = isset($_GET['kantor']) ? $_GET['kantor'] : '';
    }
}

// 2. Buat Base Query WHERE untuk FILTER UTAMA & DROPDOWN DIKLAT
$where_base = "1=1";

if ($tahun != '') {
    $where_base .= " AND d.tahun = '$tahun'";
}

if ($kantor != '') {
    // Filter unit kerja menggunakan kode kantor
    $where_base .= " AND j.unit_kerja = '$kantor'";
}

// 3. Query untuk Dropdown Filter Diklat (MENGGUNAKAN WHERE_BASE)
$qDiklat = mysqli_query($conn, "
    SELECT DISTINCT d.diklat 
    FROM tb_diklat d
    LEFT JOIN tb_pegawai p ON d.id_peg = p.id_peg
    LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg 
    WHERE $where_base
    ORDER BY d.diklat ASC
");

// 4. Query Utama (Menambahkan filter Jenis Diklat)
$where_final = $where_base;

if ($diklat_filter != '') {
    $where_final .= " AND d.diklat = '" . mysqli_real_escape_string($conn, $diklat_filter) . "'";
}

$query = "
    SELECT d.*, p.nama, j.jabatan, j.unit_kerja, k.nama_kantor
    FROM tb_diklat d
    JOIN tb_pegawai p ON d.id_peg = p.id_peg
    LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg
    LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
    WHERE $where_final
    ORDER BY d.date_reg DESC
";

$result = mysqli_query($conn, $query);

// 5. Query Dropdown Tahun & Kantor (Tanpa filter tambahan)
$qTahun = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat ORDER BY tahun DESC");
$qKantor = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE level IN ('KC','KP') ORDER BY nama_kantor ASC");

// Ambil Nama Kantor untuk tampilan Kepala
$nama_kantor_kepala = $kode_kantor_session; // Default jika query gagal
if ($hak_akses == 'kepala') {
    $qNamaKantor = mysqli_query($conn, "SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = '$kode_kantor_session'");
    $rNamaKantor = mysqli_fetch_assoc($qNamaKantor);
    if ($rNamaKantor) {
        $nama_kantor_kepala = $rNamaKantor['nama_kantor'];
    }
}
?>

<style>
    /* Styling tambahan biar tabel padat tapi lega */
    .table-sm td, .table-sm th { font-size: 0.9rem; vertical-align: middle; }
    .badge-custom { font-size: 0.8rem; padding: 5px 8px; }
</style>

<section class="content" style="margin-top: 20px;">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-graduation-cap"></i> Data Riwayat Diklat Pegawai</h3>
            </div>
            <div class="card-body">

                <form method="GET" action="home-admin.php" class="mb-4 p-3 bg-light rounded border">
                    <input type="hidden" name="page" value="master-data-diklat">
                    <input type="hidden" name="filter" value="yes">
                    
                    <div class="row align-items-end">
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control form-control-sm">
                                <option value="">-- Semua --</option>
                                <?php 
                                mysqli_data_seek($qTahun, 0); 
                                while ($row = mysqli_fetch_assoc($qTahun)) { 
                                ?>
                                    <option value="<?= $row['tahun'] ?>" <?= ($tahun == $row['tahun']) ? 'selected' : '' ?>>
                                        <?= $row['tahun'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 col-sm-12 mb-2">
                            <label>Jenis Diklat</label>
                            <select name="diklat" class="form-control form-control-sm">
                                <option value="">-- Semua Jenis Diklat --</option>
                                <?php 
                                while ($row = mysqli_fetch_assoc($qDiklat)) { 
                                    $diklat_name = htmlspecialchars($row['diklat'], ENT_QUOTES, 'UTF-8');
                                ?>
                                    <option value="<?= $diklat_name ?>" <?= ($diklat_filter == $diklat_name) ? 'selected' : '' ?>>
                                        <?= $diklat_name ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <label>Unit Kerja</label>
                            <?php if ($hak_akses == 'kepala'): ?>
                                <input type="text" class="form-control form-control-sm" value="<?= $nama_kantor_kepala ?>" readonly>
                                <input type="hidden" name="kantor" value="<?= $kantor ?>">
                                
                            <?php else: ?>
                                <select name="kantor" class="form-control form-control-sm">
                                    <option value="">-- Semua Kantor --</option>
                                    <?php 
                                    while ($row = mysqli_fetch_assoc($qKantor)) { 
                                    ?>
                                        <option value="<?= $row['kode_kantor_detail'] ?>" <?= ($kantor == $row['kode_kantor_detail']) ? 'selected' : '' ?>>
                                            <?= $row['nama_kantor'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3 col-sm-12 mt-2 mt-md-0 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Terapkan Filter</button>
                            <a href="home-admin.php?page=master-data-diklat" class="btn btn-secondary btn-sm"><i class="fa fa-sync"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <hr>

                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <a href="home-admin.php?page=form-diklat" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Data</a>
                        <a href="home-admin.php?page=import-diklat" class="btn btn-outline-primary btn-sm"><i class="fa fa-file-upload"></i> Upload Excel</a>
                    </div>
                    <div>
                        <a href="export-diklat.php?tahun=<?= $tahun ?>&kantor=<?= $kantor ?>&diklat=<?= urlencode($diklat_filter) ?>" class="btn btn-outline-success btn-sm" target="_blank">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped table-sm" id="tabelDiklat">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th>Nama Pegawai</th>
                                <th>Jenis Diklat / Pelatihan</th>
                                <th width="10%">Tahun</th>
                                <th>Penyelenggara & Tempat</th>
                                <th>Unit Kerja</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($result) > 0){
                                while ($row = mysqli_fetch_assoc($result)) { 
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td style="font-weight: 500;"><?= $row['nama'] ?></td>
                                    <td><?= $row['diklat'] ?></td>
                                    <td class="text-center"><span class="badge badge-info"><?= $row['tahun'] ?></span></td>
                                    <td>
                                        <strong><?= $row['penyelenggara'] ?></strong><br>
                                        <small class="text-muted"><i class="fa fa-map-marker-alt"></i> <?= $row['tempat'] ?></small>
                                    </td>
                                    <td><?= $row['nama_kantor'] ?></td>
                                    <td class="text-center">
                                        <a href="home-admin.php?page=form-diklat&id=<?= $row['id_diklat'] ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fa fa-edit"></i></a>
                                        <a href="proses-diklat.php?act=hapus&id=<?= $row['id_diklat'] ?>" class="btn btn-xs btn-danger btn-hapus" title="Hapus"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else {
                                echo '<tr><td colspan="7" class="text-center py-4">Data tidak ditemukan untuk filter ini.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
  // Inisialisasi DataTable
  $('#tabelDiklat').DataTable({
    "responsive": true,
    "lengthChange": true,
    "autoWidth": false,
    "pageLength": 10,
    "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
    }
  });

  // SweetAlert Hapus
  $(document).on('click', '.btn-hapus', function(e) {
    e.preventDefault();
    let link = $(this).attr('href');
    Swal.fire({
      title: 'Hapus Data?',
      text: "Data diklat ini akan dihapus permanen!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = link;
      }
    });
  });
});
</script>