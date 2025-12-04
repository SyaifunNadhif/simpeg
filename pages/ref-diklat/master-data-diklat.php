<?php
/*********************************************************
 * FILE    : pages/diklat/master-data-diklat.php
 * MODULE  : Data Diklat (Dropdown Diklat Dynamic by Tahun)
 * VERSION : v3.3
 *********************************************************/

// Session & Koneksi
if (session_id() == '') session_start();
include "dist/koneksi.php";
include "dist/library.php";

// --- 1. LOGIKA HAK AKSES ---
$hak_akses      = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor    = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala      = ($hak_akses == 'kepala');

// --- LOGIKA LINK KEMBALI ---
$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$link_back = ($hak_akses_user == 'kepala') ? "home-admin.php?page=dashboard-cabang" : "home-admin.php?page=form-view-data-pegawai";

// --- 2. LOGIKA FILTER ---

// A. Filter Tahun (Default 2025 / Tahun Ini)
if (isset($_GET['tahun'])) {
    $filter_tahun = $_GET['tahun'];
} else {
    $filter_tahun = date('Y'); 
}

$filter_diklat  = isset($_GET['diklat']) ? $_GET['diklat'] : '';
$filter_kantor  = isset($_GET['kantor']) ? $_GET['kantor'] : '';

// Jika KEPALA, paksa filter kantor
if ($is_kepala) {
    $filter_kantor = $kode_kantor;
}

// --- 3. QUERY UTAMA (TABEL DATA) ---
$sql = "SELECT d.*, p.nama, j.jabatan, j.unit_kerja, k.nama_kantor
        FROM tb_diklat d
        JOIN tb_pegawai p ON d.id_peg = p.id_peg
        LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
        LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
        WHERE 1=1";

// Append Filter Tahun
if (!empty($filter_tahun)) {
    $sql .= " AND d.tahun = '$filter_tahun'";
}
// Append Filter Jenis Diklat
if (!empty($filter_diklat)) {
    $sql .= " AND d.diklat = '" . mysqli_real_escape_string($conn, $filter_diklat) . "'";
}
// Append Filter Kantor
if (!empty($filter_kantor)) {
    $sql .= " AND j.unit_kerja = '$filter_kantor'";
}

$sql .= " ORDER BY d.date_reg DESC";
$result = mysqli_query($conn, $sql);


// --- 4. QUERY DROPDOWN (LOGIKA DINAMIS) ---

// A. List Tahun
$qTahun  = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat WHERE tahun != '' ORDER BY tahun DESC");

// B. List Jenis Diklat (DIPERBAIKI: Filter by TAHUN JUGA)
$sqlDiklat = "SELECT DISTINCT d.diklat FROM tb_diklat d LEFT JOIN tb_jabatan j ON d.id_peg = j.id_peg WHERE 1=1";

// Jika User Memilih Tahun Tertentu, Filter Diklat Sesuai Tahun Tersebut
if (!empty($filter_tahun)) {
    $sqlDiklat .= " AND d.tahun = '$filter_tahun'";
}

// Jika Kepala, Filter Sesuai Unit Kerja
if ($is_kepala) { 
    $sqlDiklat .= " AND j.unit_kerja = '$kode_kantor'"; 
}

$sqlDiklat .= " ORDER BY d.diklat ASC";
$qDiklat = mysqli_query($conn, $sqlDiklat);

// C. List Kantor (Admin Only)
$qKantor = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE level IN ('KC','KP') ORDER BY nama_kantor ASC");
?>

<style>
    .content-wrapper { background-color: #f4f6f9; }
    .card-modern { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: #fff; overflow: hidden; }
    .card-header-modern { background: #fff; border-bottom: 1px solid #f1f1f1; padding: 20px; }
    
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; height: 40px; font-size: 0.9rem; width: 100%; padding: 5px 15px; }
    .input-modern:focus { border-color: #007bff; box-shadow: none; }
    .btn-modern { border-radius: 50px; padding: 8px 20px; font-weight: 600; transition: 0.3s; font-size: 0.9rem; }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    .table-responsive { border-radius: 0 0 15px 15px; }
    table.dataTable thead th { background-color: #f8f9fa; color: #495057; border-bottom: 2px solid #e9ecef !important; font-size: 0.85rem; text-transform: uppercase; padding: 15px !important; white-space: nowrap; }
    table.dataTable tbody td { padding: 12px 15px !important; vertical-align: middle; font-size: 0.9rem; color: #333; }
    .label-filter { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 5px; display: block; }
    
    .badge-tahun { background-color: #007bff; color: white; font-size: 0.85rem; padding: 5px 10px; border-radius: 6px; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Riwayat Diklat</h1>
                <p class="text-muted mb-0">Data pendidikan dan pelatihan pegawai</p>
            </div>
            <div>
                <a href="<?= $link_back ?>" class="btn btn-light rounded-pill shadow-sm px-4 border">
                    <i class="fa fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        
        <div class="card card-modern">
            <div class="card-header-modern">
                <form method="GET" action="home-admin.php">
                    <input type="hidden" name="page" value="master-data-diklat">
                    
                    <div class="row align-items-end">
                        
                        <div class="col-lg-2 col-md-4 mb-3 mb-lg-0">
                            <span class="label-filter">Tahun</span>
                            <select name="tahun" class="form-control input-modern" onchange="this.form.submit()">
                                <option value="">-- Semua Tahun --</option>
                                <?php while ($row = mysqli_fetch_assoc($qTahun)) { ?>
                                    <option value="<?= $row['tahun'] ?>" <?= ($filter_tahun == $row['tahun']) ? 'selected' : '' ?>>
                                        <?= $row['tahun'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 mb-3 mb-lg-0">
                            <span class="label-filter">Jenis Diklat</span>
                            <select name="diklat" class="form-control input-modern select2bs4">
                                <option value="">-- Semua Jenis (<?= empty($filter_tahun) ? 'Semua Thn' : $filter_tahun ?>) --</option>
                                <?php 
                                mysqli_data_seek($qDiklat, 0); 
                                while ($row = mysqli_fetch_assoc($qDiklat)) { 
                                ?>
                                    <option value="<?= $row['diklat'] ?>" <?= ($filter_diklat == $row['diklat']) ? 'selected' : '' ?>>
                                        <?= $row['diklat'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 mb-3 mb-lg-0">
                            <span class="label-filter">Unit Kerja</span>
                            <select name="kantor" class="form-control input-modern" <?= $is_kepala ? 'disabled' : '' ?>>
                                <option value="">-- Semua Kantor --</option>
                                <?php while ($row = mysqli_fetch_assoc($qKantor)) { ?>
                                    <option value="<?= $row['kode_kantor_detail'] ?>" <?= ($filter_kantor == $row['kode_kantor_detail']) ? 'selected' : '' ?>>
                                        <?= $row['nama_kantor'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php if($is_kepala): ?><input type="hidden" name="kantor" value="<?= $kode_kantor ?>"><?php endif; ?>
                        </div>

                        <div class="col-lg-4 col-md-12 mt-3 mt-lg-0 text-right">
                            <button type="submit" class="btn btn-primary btn-modern shadow-sm mr-1">
                                <i class="fa fa-filter mr-1"></i> Terapkan
                            </button>
                            <a href="home-admin.php?page=master-data-diklat" class="btn btn-light btn-modern border">
                                <i class="fa fa-sync-alt text-muted"></i>
                            </a>
                        </div>

                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-list mr-2"></i> Daftar Peserta Diklat</h6>
                    <div class="btn-group">
                        <a href="home-admin.php?page=form-diklat" class="btn btn-success btn-sm shadow-sm"><i class="fa fa-plus mr-1"></i> Tambah Data</a>
                        <a href="home-admin.php?page=form-import-data-diklat" class="btn btn-info btn-sm shadow-sm"><i class="fa fa-file-upload mr-1"></i> Import</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover w-100 mb-0" id="tabelDiklat">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Nama Pegawai</th>
                                <th>Jenis Diklat</th>
                                <th width="10%" class="text-center">Tahun</th>
                                <th>Penyelenggara & Lokasi</th>
                                <th>Unit Kerja</th>
                                <th class="text-center" width="8%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($result) > 0){
                                while ($row = mysqli_fetch_assoc($result)) { 
                            ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= $no++ ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= $row['nama'] ?></div>
                                        <div class="small text-muted">ID: <?= $row['id_peg'] ?></div>
                                    </td>
                                    <td><?= $row['diklat'] ?></td>
                                    <td class="text-center"><span class="badge-tahun"><?= $row['tahun'] ?></span></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= $row['penyelenggara'] ?></div>
                                        <div class="small text-muted"><i class="fa fa-map-marker-alt mr-1 text-danger"></i> <?= $row['tempat'] ?></div>
                                    </td>
                                    <td><?= $row['nama_kantor'] ?></td>
                                    <td class="text-center">
                                        <a href="home-admin.php?page=form-diklat&id=<?= $row['id_diklat'] ?>" class="btn btn-sm btn-warning shadow-sm" title="Edit Data">
                                            <i class="fa fa-pen text-white"></i>
                                        </a>
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
        </div>

    </div>
</section>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    // Init DataTable
    $('#tabelDiklat').DataTable({
        "responsive": false,
        "scrollX": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "language": {
            "search": "",
            "searchPlaceholder": "Cari data...",
            "zeroRecords": "Data tidak ditemukan.",
            "info": "Hal _PAGE_ dari _PAGES_",
            "paginate": { "next": ">", "previous": "<" }
        }
    });

    // Styling Search Box
    $('.dataTables_filter input').addClass('form-control input-modern').css('width', '200px');

    // Init Select2
    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
});
</script>