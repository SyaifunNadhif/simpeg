<?php
/*********************************************************
 * FILE    : pages/diklat/rekap-biaya-diklat.php
 * MODULE  : Laporan Rekap Biaya (Modern UI Design)
 * VERSION : v3.0
 *********************************************************/

if (session_id() == '') session_start();
include 'dist/koneksi.php';
include 'dist/library.php';

// --- 1. INISIALISASI & FILTER ---
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$where_akses = "WHERE 1=1";
if ($hak_akses !== 'admin') {
    $where_akses .= " AND id_peg IN (SELECT id_peg FROM tb_jabatan WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif')";
}
$where_tahun = ($tahun_pilih == 'Semua') ? "" : " AND tahun = '$tahun_pilih'";

// --- 2. QUERY DATA ---
$query = "SELECT 
            diklat, 
            penyelenggara, 
            tahun,
            COUNT(id_peg) as jumlah_peserta, 
            SUM(biaya) as total_biaya_kegiatan
          FROM tb_diklat 
          $where_akses $where_tahun
          GROUP BY diklat, penyelenggara, tahun
          ORDER BY total_biaya_kegiatan DESC";

$result = mysqli_query($conn, $query);

// Hitung Grand Total
$grand_total_biaya = 0;
$total_semua_peserta = 0;
$data_rekap = [];
while($row = mysqli_fetch_assoc($result)){
    $grand_total_biaya += $row['total_biaya_kegiatan'];
    $total_semua_peserta += $row['jumlah_peserta'];
    $data_rekap[] = $row;
}

$qTahun = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat ORDER BY tahun DESC");
?>

<style>
    /* Card Summary Style */
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s;
        overflow: hidden;
        position: relative;
    }
    .card-modern:hover { transform: translateY(-3px); }
    
    .bg-gradient-blue { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; }
    .bg-gradient-teal { background: linear-gradient(135deg, #20c997, #198754); color: white; }
    
    .card-icon-bg {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.15;
        transform: rotate(-15deg);
    }

    /* Table Style */
    .table-responsive { border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); background: white; }
    .table thead th { 
        background-color: #f8f9fa; 
        color: #6c757d; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.5px;
        border-top: none;
        border-bottom: 2px solid #e9ecef;
        padding: 15px;
    }
    .table tbody td { 
        vertical-align: middle; 
        padding: 12px 15px; 
        border-color: #f1f3f5;
        color: #495057;
    }
    
    /* Tombol Peserta Modern */
    .btn-participant {
        background-color: #e7f1ff;
        color: #0d6efd;
        border: none;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 6px 15px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-participant:hover {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    /* Font Rupiah Monospace (Agar angka sejajar) */
    .font-currency { font-family: 'Consolas', 'Monaco', monospace; font-weight: 600; letter-spacing: -0.5px; }
    
    /* Header Section */
    .filter-select {
        border-radius: 20px;
        border: 2px solid #e9ecef;
        padding: 5px 15px;
        font-weight: 600;
        color: #495057;
    }
    .filter-select:focus { border-color: #0d6efd; box-shadow: none; }
</style>

<section class="content-header pt-4 pb-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.5rem;">Rekapitulasi Biaya Diklat</h1>
                <p class="text-muted mb-0">Monitor pengeluaran dan partisipasi pelatihan pegawai.</p>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                    <i class="fa fa-print mr-2"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card card-modern bg-gradient-blue h-100">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase mb-2" style="opacity: 0.8; font-size: 0.85rem; letter-spacing: 1px;">Total Pengeluaran (Tahun <?= $tahun_pilih ?>)</h6>
                        <h2 class="font-weight-bold mb-0">Rp <?= number_format($grand_total_biaya, 0, ',', '.') ?></h2>
                        <i class="fas fa-wallet card-icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-modern bg-gradient-teal h-100">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase mb-2" style="opacity: 0.8; font-size: 0.85rem; letter-spacing: 1px;">Total Pegawai Mengikuti</h6>
                        <h2 class="font-weight-bold mb-0"><?= $total_semua_peserta ?> <span style="font-size: 1.2rem; font-weight: 400;">Orang</span></h2>
                        <i class="fas fa-users card-icon-bg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-modern bg-white">
            <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom-0">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-alt mr-2"></i> Rincian Per Judul Kegiatan</h6>
                
                <form method="GET" action="home-admin.php" class="form-inline mt-2 mt-md-0">
                    <input type="hidden" name="page" value="rekap-biaya-diklat">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 font-weight-bold text-muted">Tahun:</span>
                        </div>
                        <select name="tahun" class="form-control filter-select" onchange="this.form.submit()">
                            <option value="Semua" <?= $tahun_pilih == 'Semua' ? 'selected' : '' ?>>Semua</option>
                            <?php while($t = mysqli_fetch_assoc($qTahun)) { ?>
                                <option value="<?= $t['tahun'] ?>" <?= $tahun_pilih == $t['tahun'] ? 'selected' : '' ?>>
                                    <?= $t['tahun'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" width="100%">
                        <thead>
                            <tr>
                                <th class="pl-4" width="50">No</th>
                                <th>Nama Diklat</th>
                                <th>Penyelenggara</th>
                                <th class="text-center">Tahun</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-right pr-4">Biaya (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($data_rekap)) {
                                $no = 1;
                                foreach ($data_rekap as $row) { 
                            ?>
                                <tr>
                                    <td class="pl-4 text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark mb-1"><?= $row['diklat'] ?></div>
                                    </td>
                                    <td class="text-muted small text-uppercase font-weight-bold">
                                        <?= $row['penyelenggara'] ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light border text-muted"><?= $row['tahun'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-participant btn-detail" 
                                                data-diklat="<?= htmlspecialchars($row['diklat']) ?>"
                                                data-penyelenggara="<?= htmlspecialchars($row['penyelenggara']) ?>"
                                                data-tahun="<?= $row['tahun'] ?>">
                                            <i class="fas fa-eye mr-1"></i> <?= $row['jumlah_peserta'] ?>
                                        </button>
                                    </td>
                                    <td class="text-right pr-4">
                                        <span class="font-currency text-primary"><?= number_format($row['total_biaya_kegiatan'], 0, ',', '.') ?></span>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <img src="https://img.icons8.com/ios/100/e0e0e0/opened-folder.png" class="mb-3" width="60"><br>
                                        Belum ada data diklat di tahun <?= $tahun_pilih ?>.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-right font-weight-bold pt-3 text-uppercase text-muted">Grand Total:</td>
                                <td class="text-center font-weight-bold pt-3"><?= $total_semua_peserta ?> Org</td>
                                <td class="text-right pr-4 font-weight-bold text-success pt-3" style="font-size: 0.8rem;">
                                    <?= number_format($grand_total_biaya, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-gradient-blue text-white" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-users mr-2"></i> Rincian Peserta</h5>
                <button type="button" class="close text-white opacity-1" onclick="$('#modalDetail').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 pb-3 border-bottom">
                    <h5 class="font-weight-bold text-dark mb-1" id="detailJudul">...</h5>
                    <div class="text-muted small text-uppercase font-weight-bold">
                        <i class="fas fa-building mr-1"></i> <span id="detailPenyelenggara">-</span>
                    </div>
                </div>
                
                <div id="loader" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted small">Sedang memuat data...</p>
                </div>

                <div id="hasilDetail"></div>
            </div>
            <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" onclick="$('#modalDetail').modal('hide')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    console.log("Modern UI Loaded."); 

    $(document).on('click', '.btn-detail', function(e) {
        e.preventDefault();
        var diklat = $(this).data('diklat');
        var tahun  = $(this).data('tahun');
        var peny   = $(this).data('penyelenggara');

        $('#detailJudul').text(diklat);
        $('#detailPenyelenggara').text(peny);
        $('#hasilDetail').html('');
        $('#loader').show();
        $('#modalDetail').modal('show');

        $.ajax({
            url: 'pages/ref-diklat/ajax-detail-diklat.php',
            type: 'POST',
            data: { diklat: diklat, tahun: tahun, penyelenggara: peny },
            success: function(response) {
                $('#loader').hide();
                $('#hasilDetail').html(response);
            },
            error: function() {
                $('#loader').hide();
                $('#hasilDetail').html('<div class="alert alert-danger">Gagal memuat data.</div>');
            }
        });
    });
});
</script>