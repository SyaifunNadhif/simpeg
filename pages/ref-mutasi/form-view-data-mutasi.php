<?php
/*********************************************************
 * FILE    : pages/mutasi/form-view-data-mutasi.php
 * MODULE  : Data Mutasi (Single Line Toolbar & Soft UI)
 * VERSION : v2.5
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// --- LOGIKA LINK KEMBALI ---
$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$link_back = ($hak_akses_user == 'kepala') ? "home-admin.php?page=dashboard-cabang" : "home-admin.php?page=form-view-data-pegawai";


// --- QUERY DATA ---
$sql = "SELECT tb_mutasi.*, 
        (SELECT nama FROM tb_pegawai WHERE id_peg=tb_mutasi.id_peg) as nama_peg
        FROM tb_mutasi 
        ORDER BY tgl_mutasi DESC";
        
$tampilMutasi = mysqli_query($conn, $sql);
?>

<style>
    .content-wrapper { background-color: #f4f6f9; }
    
    /* Card Style */
    .card-modern {
        border: none; border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: #fff; overflow: hidden; margin-bottom: 20px;
    }

    /* Table Style */
    table.dataTable thead th {
        background-color: #f8f9fa; border-bottom: 2px solid #e9ecef !important;
        color: #495057; font-size: 0.85rem; text-transform: uppercase;
        padding: 15px !important; white-space: nowrap;
    }
    table.dataTable tbody td { padding: 12px 15px !important; vertical-align: middle; font-size: 0.9rem; }
    
    /* Custom Toolbar (Search + Add) */
    .toolbar-container {
        display: flex;
        justify-content: space-between; /* Judul kiri, Tools kanan */
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .tools-right {
        display: flex;
        align-items: center;
        gap: 10px; /* Jarak antar elemen */
    }
    
    /* Modern Search Input */
    .search-box {
        position: relative;
    }
    .search-input {
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        padding: 8px 20px 8px 40px; /* Padding kiri utk icon */
        font-size: 0.9rem;
        width: 250px;
        transition: all 0.3s;
    }
    .search-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        outline: none;
        width: 300px; /* Efek melebar saat fokus */
    }
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
    }

    /* Tombol Aksi Lembut (Soft Buttons) */
    .btn-action {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.2s;
        background-color: #f8f9fa; /* Warna dasar lembut */
    }
    .btn-action:hover { transform: translateY(-2px); }
    
    /* Warna Icon Spesifik */
    .btn-view { color: #007bff; } /* Biru */
    .btn-view:hover { background-color: #e7f1ff; border-color: #007bff; }
    
    .btn-download { color: #28a745; } /* Hijau */
    .btn-download:hover { background-color: #e6fffa; border-color: #28a745; }
    
    .btn-edit { color: #ffc107; } /* Kuning */
    .btn-edit:hover { background-color: #fffbf0; border-color: #ffc107; }

    /* Tombol Tambah Modern */
    .btn-add-modern {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
    }
    .btn-add-modern:hover { transform: translateY(-1px); box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08); }

</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Data Mutasi</h1>
                <p class="text-muted mb-0">Rekapitulasi mutasi jabatan dan unit kerja</p>
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
        <div class="row">
            <div class="col-12">
                
                <div class="card card-modern">
                    <div class="card-body">
                        
                        <div class="toolbar-container">
                            <div class="tools-left">
                                </div>

                            <div class="tools-right">
                                <div class="search-box">
                                    <i class="fa fa-search search-icon"></i>
                                    <input type="text" id="customSearch" class="search-input" placeholder="Cari Nama / Jabatan...">
                                </div>

                                <a href="home-admin.php?page=form-master-data-mutasi" class="btn btn-primary btn-add-modern">
                                    <i class="fa fa-plus mr-1"></i> Tambah Data
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="mutasi" class="table w-100">
                                <thead>
                                    <tr>
                                        <th>Nama Pegawai</th>
                                        <th>Jabatan Baru</th>
                                        <th>Jenis Mutasi</th>
                                        <th>Tanggal SK</th>
                                        <th>No SK</th>
                                        <th>TMT</th>
                                        <th class="text-center" width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($Mutasi = mysqli_fetch_array($tampilMutasi)): 
                                        // Logic File
                                        $file_name_raw = $Mutasi['sk_mutasi'];
                                        $path_server = "pages/assets/sk_mutasi/" . $file_name_raw;
                                        $file_exists = (!empty($file_name_raw) && file_exists($path_server));
                                        $url_browser = "pages/assets/sk_mutasi/" . rawurlencode($file_name_raw);
                                    ?>  
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-dark"><?= htmlspecialchars($Mutasi['nama_peg']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($Mutasi['id_peg']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($Mutasi['jabatan']) ?></td>
                                        <td>
                                            <span class="badge badge-light border text-secondary px-2 py-1 rounded">
                                                <?= htmlspecialchars($Mutasi['jns_mutasi']) ?>
                                            </span>
                                        </td>
                                        <td><?= Indonesia2Tgl($Mutasi['tgl_mutasi']) ?></td>
                                        <td><?= htmlspecialchars($Mutasi['no_mutasi']) ?></td>
                                        <td><?= Indonesia2Tgl($Mutasi['tmt']) ?></td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center" style="gap: 5px;">
                                                
                                                <?php if($file_exists): ?>
                                                    <button type="button" class="btn-action btn-view view-pdf" 
                                                            title="Lihat SK"
                                                            data-toggle="modal" data-target="#modalPDF" 
                                                            data-url="<?= $url_browser ?>" 
                                                            data-title="<?= htmlspecialchars($Mutasi['no_mutasi']) ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    
                                                    <a href="<?= $url_browser ?>" target="_blank" class="btn-action btn-download" title="Download SK">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="home-admin.php?page=form-edit-data-mutasi&id_mutasi=<?=$Mutasi['id_mutasi']?>" class="btn-action btn-edit" title="Edit Data">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalPDF" tabindex="-1" role="dialog" aria-labelledby="modalPDFLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="height: 90vh;">
        <div class="modal-content h-100">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title" id="modalPDFLabel" style="font-size: 1rem;"><i class="fa fa-file-pdf mr-2"></i> Preview Dokumen</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 d-flex flex-column" style="background: #525659;">
                <iframe id="pdfFrame" src="" style="width: 100%; flex-grow: 1; border: none;"></iframe>
                <div class="text-center p-2 bg-light border-top">
                    <a id="btnDownload" href="#" target="_blank" class="btn btn-sm btn-primary px-4">
                        <i class="fa fa-external-link-alt mr-2"></i> Buka di Tab Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    // Init DataTable
    var table = $("#mutasi").DataTable({
        "responsive": false, 
        "scrollX": true,     
        "autoWidth": false,
        "lengthChange": false, 
        "pageLength": 10,
        "order": [[ 5, "desc" ]], 
        "dom": 'rtip', // Sembunyikan search box bawaan (f) karena kita buat custom
        "language": {
            "search": "",
            "zeroRecords": "Tidak ada data mutasi.",
            "info": "Hal _PAGE_ dari _PAGES_",
            "infoEmpty": "Kosong",
            "paginate": { "previous": "<", "next": ">" }
        }
    });

    // Bind Custom Search Input ke DataTables
    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Logic View PDF
    $(document).on('click', '.view-pdf', function() {
        var fileUrl = $(this).data('url');
        var title = $(this).data('title');
        $('#modalPDFLabel').html('<i class="fa fa-file-pdf mr-2"></i> ' + title);
        $('#pdfFrame').attr('src', fileUrl);
        $('#btnDownload').attr('href', fileUrl);
    });

    $('#modalPDF').on('hidden.bs.modal', function () {
        $('#pdfFrame').attr('src', 'about:blank');
    });
});
</script>