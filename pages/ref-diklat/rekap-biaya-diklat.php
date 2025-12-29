<?php
/*********************************************************
 * FILE    : pages/diklat/rekap-biaya-diklat.php
 * MODULE  : Laporan Rekap Biaya (Final Version)
 *********************************************************/

if (session_id() == '') session_start();
include 'dist/koneksi.php';
include 'dist/library.php';

// --- FILTER LOGIC ---
$hak_akses   = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$kode_kantor = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$where_akses = "WHERE 1=1";
if ($hak_akses !== 'admin') {
    $where_akses .= " AND id_peg IN (SELECT id_peg FROM tb_jabatan WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif')";
}
$where_tahun = ($tahun_pilih == 'Semua') ? "" : " AND tahun = '$tahun_pilih'";

// --- QUERY DATA ---
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

// Parameter untuk link export
$params = "tahun=" . $tahun_pilih;
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .modern-wrapper { font-family: 'Poppins', sans-serif; color: #444; font-size: 0.9rem; }

    /* Card Styling */
    .card-modern {
        border: none; border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        background: #fff; margin-bottom: 20px;
    }

    /* Table Styling */
    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-modern thead th {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: white; padding: 15px; font-weight: 500; text-transform: uppercase;
        font-size: 0.85rem; border: none; vertical-align: middle;
    }
    .table-modern tbody tr:hover { background-color: #f8f9fa; }
    .table-modern td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }

    /* Badge Tahun (Warna Abu Gelap agar tulisan terbaca) */
    .badge-tahun {
        background: #e9ecef; color: #333; padding: 5px 10px;
        border-radius: 6px; font-weight: 700; font-size: 0.8rem; border: 1px solid #ced4da;
    }

    /* Buttons */
    .btn-modern {
        border-radius: 8px; padding: 8px 16px; font-weight: 500; border: none;
        display: inline-flex; align-items: center; gap: 8px; color: white;
        transition: transform 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-modern:hover { transform: translateY(-2px); color: white; text-decoration: none; }
    
    .btn-excel { background: #1D6F42; }
    .btn-pdf { background: #c62828; }
    .btn-filter { background: #2a5298; }

    .btn-peserta-count {
        background: #e3f2fd; color: #1565c0; border: none; padding: 6px 14px;
        border-radius: 6px; font-weight: 700; cursor: pointer; transition: 0.2s;
    }
    .btn-peserta-count:hover { background: #1565c0; color: white; }

    .form-control-modern { border-radius: 8px; border: 1px solid #ddd; padding: 8px 12px; }
    .nominal-font { font-family: 'Consolas', monospace; font-weight: 600; color: #333; }
</style>

<div class="modern-wrapper">
    <section class="content-header pt-3 pb-3">
        <div class="container-fluid">
            <h1 style="font-weight: 700; color: #2c3e50; font-size: 1.5rem;">
                <i class="fas fa-money-check-alt mr-2 text-primary"></i> Rekapitulasi Biaya Diklat
            </h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card-modern">
                <div class="card-body py-3">
                    <form method="GET" action="home-admin.php" class="d-flex align-items-center justify-content-between flex-wrap">
                        <input type="hidden" name="page" value="rekap-biaya-diklat">
                        
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <label class="mr-2 mb-0 font-weight-bold text-secondary">Periode:</label>
                            <div class="input-group">
                                <select name="tahun" class="form-control form-control-modern" style="min-width: 120px;">
                                    <option value="Semua" <?= $tahun_pilih == 'Semua' ? 'selected' : '' ?>>Semua</option>
                                    <?php while($t = mysqli_fetch_assoc($qTahun)) { ?>
                                        <option value="<?= $t['tahun'] ?>" <?= $tahun_pilih == $t['tahun'] ? 'selected' : '' ?>>
                                            <?= $t['tahun'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-filter btn-modern rounded-right" style="border-radius: 0 8px 8px 0;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="pages/diklat/export-biaya.php?type=excel&<?= $params ?>" target="_blank" class="btn btn-modern btn-excel mr-1">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="pages/diklat/export-biaya.php?type=print&<?= $params ?>" target="_blank" class="btn btn-modern btn-pdf">
                                <i class="fas fa-print"></i> PDF / Print
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-body p-0 table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Nama Kegiatan / Diklat</th>
                                <th>Penyelenggara</th>
                                <th width="10%" class="text-center">Tahun</th>
                                <th width="10%" class="text-center">Peserta</th>
                                <th width="20%" class="text-right">Realisasi Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($data_rekap)) {
                                $no = 1;
                                foreach ($data_rekap as $row) { 
                            ?>
                                <tr>
                                    <td align="center" style="font-weight: 600; color:#888;"><?= $no++ ?>.</td>
                                    <td style="font-weight: 500; color: #2c3e50;">
                                        <?= $row['diklat'] ?>
                                    </td>
                                    <td class="text-secondary small font-weight-bold text-uppercase">
                                        <?= $row['penyelenggara'] ?>
                                    </td>
                                    <td align="center">
                                        <span class="badge-tahun"><?= $row['tahun'] ?></span>
                                    </td>
                                    <td align="center">
                                        <button type="button" class="btn-peserta-count btn-detail" 
                                                data-diklat="<?= htmlspecialchars($row['diklat']) ?>"
                                                data-penyelenggara="<?= htmlspecialchars($row['penyelenggara']) ?>"
                                                data-tahun="<?= $row['tahun'] ?>">
                                            <?= $row['jumlah_peserta'] ?>
                                        </button>
                                    </td>
                                    <td align="right" class="nominal-font">
                                        <?= number_format($row['total_biaya_kegiatan'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else { 
                                echo "<tr><td colspan='6' class='text-center py-5 text-muted'>Data tidak ditemukan.</td></tr>";
                            } 
                            ?>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #eef2ff; font-weight: 700; color: #1e3c72;">
                                <td colspan="4" class="text-right text-uppercase">Grand Total</td>
                                <td class="text-center"><?= number_format($total_semua_peserta) ?></td>
                                <td class="text-right text-success nominal-font" style="font-size: 1.1rem;">
                                    <?= number_format($grand_total_biaya, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header text-white" style="background: linear-gradient(45deg, #1e3c72, #2a5298); border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-list-ul mr-2"></i> Rincian Peserta</h5>
                <button type="button" class="close text-white opacity-1" onclick="$('#modalDetail').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 border-bottom pb-2">
                    <h5 class="font-weight-bold text-dark mb-1" id="detailJudul">...</h5>
                    <small class="text-uppercase text-muted font-weight-bold" id="detailPenyelenggara">...</small>
                </div>
                <div id="loader" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="hasilDetail"></div>
            </div>
            <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" onclick="$('#modalDetail').modal('hide')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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

        // Pastikan path ini benar: pages/diklat/ajax-detail-diklat.php
        $.ajax({
            url: 'pages/diklat/ajax-detail-diklat.php', 
            type: 'POST',
            data: { diklat: diklat, tahun: tahun, penyelenggara: peny },
            success: function(response) {
                $('#loader').hide();
                $('#hasilDetail').html(response);
            },
            error: function() {
                $('#loader').hide();
                $('#hasilDetail').html('<div class="alert alert-danger">Gagal memuat data (Check File Path).</div>');
            }
        });
    });
});
</script>