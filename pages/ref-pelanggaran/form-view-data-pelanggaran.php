<?php
/*********************************************************
 * FILE    : pages/pelanggaran/form-view-data-hukuman.php
 * MODULE  : Data Pelanggaran (Fix Filter Action 404)
 * VERSION : v4.0
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// ==========================================
// 1. LOGIKA FILTER (PHP)
// ==========================================

// --- A. FILTER TAHUN ---
// Default ke tahun sekarang jika tidak ada filter
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// --- B. FILTER KANTOR ---
$hak_akses      = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$session_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$filter_kantor  = isset($_GET['kantor']) ? $_GET['kantor'] : '';

// Logic Kunci Kantor (Admin Bebas, User Terkunci)
$kantor_locked = false;
if ($hak_akses !== 'admin') {
    $filter_kantor = $session_kantor; 
    $kantor_locked = true;
}

// ==========================================
// 2. QUERY DATA UTAMA
// ==========================================
$sql = "SELECT h.*, p.nama, p.nip, k.nama_kantor 
        FROM tb_hukuman h
        JOIN tb_pegawai p ON h.id_peg = p.id_peg
        LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg AND j.status_jab = 'Aktif'
        LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
        WHERE 1=1"; 

// Append Filter Tahun (Jika user memilih tahun tertentu)
if (!empty($filter_tahun)) {
    $sql .= " AND YEAR(h.tgl_sk) = '$filter_tahun'";
}

// Append Filter Kantor (Jika user memilih kantor tertentu)
if (!empty($filter_kantor)) {
    $sql .= " AND j.unit_kerja = '$filter_kantor'";
}

$sql .= " ORDER BY h.tgl_sk DESC";

$tampilJudge = mysqli_query($conn, $sql);
?>

<style>
    .content-wrapper { background-color: #f4f6f9; }
    .card-modern { 
        border: none; border-radius: 15px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
        background: #fff; overflow: hidden; 
    }
    .card-header-filter { 
        padding: 20px 25px; 
        background: #fff; 
        border-bottom: 1px solid #f4f4f4; 
    }
    .input-modern { 
        border-radius: 50px; border: 1px solid #ddd; 
        padding: 5px 20px; height: 40px; width: 100%; 
        background-color: #fff;
    }
    .input-modern:focus { border-color: #007bff; box-shadow: none; }
    .btn-modern { 
        border-radius: 50px; padding: 8px 20px; font-weight: 600; 
        border: none; transition: 0.3s; 
    }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    /* Tabel */
    table.dataTable { margin-top: 0 !important; border-collapse: separate; border-spacing: 0 5px; }
    table.dataTable thead th { 
        background-color: #f8f9fa; border-bottom: 2px solid #e9ecef !important; 
        color: #495057; font-size: 0.85rem; text-transform: uppercase; 
        padding: 15px !important; 
    }
    table.dataTable tbody td { 
        padding: 12px 15px !important; vertical-align: middle; 
        font-size: 0.9rem; background: #fff;
    }
    table.dataTable tbody tr:hover td { background: #fdfdfd; }
    .label-filter { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 5px; display: block; }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Data Pelanggaran</h1>
                <p class="text-muted mb-0">Rekap data hukuman disiplin pegawai</p>
            </div>
            <div>
                <a href="home-admin.php" class="btn btn-light rounded-pill shadow-sm px-4" style="border:1px solid #eee;">
                    <i class="fa fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="card card-modern">
            
            <div class="card-header-filter">
                
                <form method="GET" action="home-admin.php">
                    
                    <input type="hidden" name="page" value="form-view-data-pelanggaran"> 
                    
                    <div class="row align-items-end">
                        
                        <div class="col-md-2 mb-3 mb-md-0">
                            <span class="label-filter">Tahun</span>
                            <select name="tahun" class="form-control input-modern">
                                <option value="">-- Semua Tahun --</option>
                                <?php
                                $thn_skr = date('Y');
                                for ($x = $thn_skr; $x >= 2019; $x--) {
                                    $sel = ($filter_tahun == $x) ? 'selected' : '';
                                    echo "<option value='$x' $sel>$x</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <span class="label-filter">Unit Kerja</span>
                            <select name="kantor" class="form-control input-modern" <?= $kantor_locked ? 'disabled' : '' ?>>
                                <option value="">-- Semua Unit Kerja --</option>
                                <?php
                                $qK = mysqli_query($conn, "SELECT * FROM tb_kantor ORDER BY nama_kantor ASC");
                                while ($k = mysqli_fetch_array($qK)) {
                                    $sel = ($filter_kantor == $k['kode_kantor_detail']) ? 'selected' : '';
                                    echo "<option value='{$k['kode_kantor_detail']}' $sel>{$k['nama_kantor']}</option>";
                                }
                                ?>
                            </select>
                            <?php if($kantor_locked): ?>
                                <input type="hidden" name="kantor" value="<?= $filter_kantor ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-2 mb-3 mb-md-0">
                            <button type="submit" class="btn btn-primary btn-modern btn-block">
                                <i class="fa fa-filter mr-1"></i> Terapkan
                            </button>
                        </div>

                        <div class="col-md-5 text-md-right">
                            <?php if($hak_akses == 'admin' || $hak_akses == 'kepala'): ?>
                                <a href="home-admin.php?page=form-master-data-hukuman" class="btn btn-info btn-modern mr-1">
                                    <i class="fa fa-plus-circle mr-1"></i> Tambah
                                </a>
                                <a href="#" class="btn btn-danger btn-modern">
                                    <i class="fa fa-file-import mr-1"></i> Kolektif
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="pelanggaran" class="table w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Pegawai</th>
                                <th>Jenis Pelanggaran</th>
                                <th>Keterangan</th>
                                <th>Tgl Surat</th>
                                <th>Kantor</th>
                                <th class="text-center" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($tampilJudge) > 0) {
                                while($peg = mysqli_fetch_array($tampilJudge)) { 
                            ?>  
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($peg['nama']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($peg['id_peg']) ?></div>
                                </td>
                                <td><span class="badge badge-warning p-2"><?= htmlspecialchars($peg['hukuman']) ?></span></td>
                                <td><?= htmlspecialchars($peg['keterangan']) ?></td>
                                <td><?= date('d M Y', strtotime($peg['tgl_sk'])) ?></td>
                                <td><?= htmlspecialchars($peg['nama_kantor']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$peg['id_peg']?>" class="btn btn-sm btn-light text-primary" title="Detail"><i class="fa fa-eye"></i></a>
                                        
                                        <?php if($hak_akses == 'admin' || $hak_akses == 'kepala'): ?>
                                            <a href="home-admin.php?page=form-edit-data-hukuman&id_hukum=<?=$peg['id_hukum']?>" class="btn btn-sm btn-light text-warning" title="Edit"><i class="fa fa-edit"></i></a>
                                            <a href="home-admin.php?page=delete-data-hukuman&id_hukum=<?=$peg['id_hukum']?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Yakin hapus?')" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                } 
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    var table = $("#pelanggaran").DataTable({
        "responsive": false,
        "scrollX": true,     
        "autoWidth": false,
        "lengthChange": false, 
        "pageLength": 10,
        "order": [[ 3, "desc" ]], 
        "language": {
            "search": "",
            "searchPlaceholder": "Cari data...",
            "zeroRecords": "Tidak ada data pelanggaran.",
            "info": "Hal _PAGE_ dari _PAGES_",
            "infoEmpty": "Kosong",
            "paginate": { "previous": "<", "next": ">" }
        }
    });

    $('.dataTables_filter input').addClass('form-control input-modern').css('width', '250px');
    $('.dataTables_wrapper').css('padding', '20px');
});
</script>