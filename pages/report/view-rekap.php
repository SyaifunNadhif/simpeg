<?php
// FILE: pages/report/view-rekap.php

// 1. PANGGIL LOGIC DATA
// Pastikan file logic-rekap.php ada di folder yang sama
include 'pages/report/logic-rekap.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .modern-wrapper { font-family: 'Poppins', sans-serif; color: #444; font-size: 0.9rem; }
    
    /* Card & Layout */
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
    
    /* Table Styling (Halaman Utama) */
    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-modern thead th { 
        background: linear-gradient(45deg, #1e3c72, #2a5298); 
        color: white; padding: 15px; font-weight: 500; 
        text-transform: uppercase; font-size: 0.85rem; 
        border: none; vertical-align: middle; 
    }
    .table-modern tbody tr:hover { background-color: #f8f9fa; }
    .table-modern td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    /* --- BUTTONS --- */
    .btn-modern { 
        border-radius: 8px; padding: 8px 16px; font-weight: 500; border: none; 
        display: inline-flex; align-items: center; gap: 8px; color: white !important; 
        transition: transform 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        text-decoration: none !important; cursor: pointer;
    }
    
    .btn-excel { background-color: #1D6F42 !important; color: white !important; }
    .btn-excel:hover { background-color: #145c32 !important; transform: translateY(-2px); }

    .btn-pdf { background-color: #c62828 !important; color: white !important; }
    .btn-pdf:hover { background-color: #a92020 !important; transform: translateY(-2px); }

    .btn-peserta-count { 
        background: #e3f2fd; color: #1565c0; border: none; padding: 6px 14px; 
        border-radius: 6px; font-weight: 700; cursor: pointer; transition: 0.2s; 
    }
    .btn-peserta-count:hover { background: #1565c0; color: white; }
    
    .badge-tahun { background: #e9ecef; color: #333; padding: 5px 10px; border-radius: 6px; font-weight: 700; border: 1px solid #ced4da; }
    .nominal-font { font-family: 'Consolas', monospace; font-weight: 600; color: #333; }
    .form-control-modern { border-radius: 8px; border: 1px solid #ddd; padding: 8px 12px; }

    /* --- MODERN MODAL STYLING --- */
    .modal-content-modern {
        border: none;
        border-radius: 16px; /* Sudut Modal Melengkung */
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        overflow: hidden;
    }
    
    .modal-header-modern {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); /* Gradient Mewah */
        color: white;
        padding: 20px 25px;
        border-bottom: none;
    }

    .modal-info-box {
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
        border-left: 5px solid #1e3c72; /* Aksen Biru di Kiri */
    }

    /* Tombol Close Custom */
    .tutup-modal { cursor: pointer; opacity: 0.8; transition: 0.3s; }
    .tutup-modal:hover { opacity: 1; transform: scale(1.1); }
    
    /* Tombol X di Header */
    .close-modern {
        text-shadow: none; opacity: 0.8; color: white; background: none; border: none; font-size: 1.5rem; transition: 0.3s;
    }
    .close-modern:hover { opacity: 1; transform: rotate(90deg); color: #ffeb3b; }

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
                    <form method="GET" action="home-admin.php" id="formFilter" class="d-flex align-items-center justify-content-between flex-wrap">
                        
                        <input type="hidden" name="page" value="rekap-biaya-diklat">
                        
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <label class="mr-2 mb-0 font-weight-bold text-secondary">Periode:</label>
                            <select name="tahun" class="form-control form-control-modern" style="min-width: 150px;" onchange="this.form.submit()">
                                <option value="Semua" <?= $tahun_pilih == 'Semua' ? 'selected' : '' ?>>Semua</option>
                                <?php foreach($list_tahun as $thn) { ?>
                                    <option value="<?= $thn ?>" <?= $tahun_pilih == $thn ? 'selected' : '' ?>>
                                        <?= $thn ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <a href="pages/report/export-biaya.php?type=excel&<?= $params ?>" target="_blank" class="btn btn-modern btn-excel mr-1">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="pages/report/export-biaya.php?type=pdf&<?= $params ?>" class="btn btn-modern btn-pdf">
                                <i class="fas fa-file-pdf"></i> Download PDF
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
                            <?php if (!empty($data_rekap)): ?>
                                <?php $no = 1; foreach ($data_rekap as $row): ?>
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
                                        <button type="button" class="btn-peserta-count btn-detail-trigger" 
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
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                            <?php endif; ?>
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

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modal-content-modern">
            
            <div class="modal-header modal-header-modern d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title font-weight-bold" style="letter-spacing: 0.5px;">
                        <i class="fas fa-users mr-2"></i> Rincian Peserta
                    </h5>
                    <p class="mb-0 small text-white-50" style="font-weight: 300;">Detail biaya per pegawai untuk kegiatan ini</p>
                </div>
                <button type="button" class="close-modern tutup-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                
                <div class="modal-info-box d-flex align-items-start">
                    <i class="fas fa-info-circle text-primary mt-1 mr-3 fa-lg"></i>
                    <div>
                        <h6 class="font-weight-bold text-dark mb-1" id="detailJudul" style="line-height: 1.4;">...</h6>
                        <span class="badge badge-secondary text-uppercase" id="detailPenyelenggara">...</span>
                    </div>
                </div>
                
                <div id="loader" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p class="text-muted mt-3 font-weight-bold">Sedang memuat data...</p>
                </div>
                
                <div id="hasilDetail"></div>
            </div>

            <div class="modal-footer bg-white border-0 pt-0 pb-4 pr-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 font-weight-bold tutup-modal shadow-sm">
                    Tutup Tampilan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        console.log("View Rekap Ready.");

        // --- FUNGSI LOAD DATA (PAGINATION) ---
        function loadPagination(diklat, tahun, peny, page) {
            $('#loader').show();
            $('#hasilDetail').css('opacity', '0.3'); // Efek loading tipis
            
            $.ajax({
                url: 'pages/report/ajax-detail.php', 
                type: 'POST',
                data: { 
                    diklat: diklat, 
                    tahun: tahun, 
                    penyelenggara: peny,
                    page: page // Kirim parameter halaman
                },
                success: function(response) {
                    $('#loader').hide();
                    $('#hasilDetail').css('opacity', '1');
                    $('#hasilDetail').html(response);
                },
                error: function() {
                    $('#loader').hide();
                    $('#hasilDetail').html('<div class="alert alert-danger">Gagal memuat halaman.</div>');
                }
            });
        }

        // 1. EVENT KLIK TOMBOL ANGKA (BUKA MODAL PERTAMA KALI)
        $(document).on('click', '.btn-detail-trigger', function(e) {
            e.preventDefault();
            
            // Ambil Data
            var diklat = $(this).data('diklat');
            var tahun  = $(this).data('tahun');
            var peny   = $(this).data('penyelenggara');

            // Set Header Modal
            $('#detailJudul').text(diklat);
            $('#detailPenyelenggara').text(peny);
            
            // Bersihkan konten lama
            $('#hasilDetail').html('');
            
            // Tampilkan Modal
            $('#modalDetail').modal('show');
            
            // Load Halaman 1
            loadPagination(diklat, tahun, peny, 1);
        });

        // 2. EVENT KLIK TOMBOL PAGINATION (PREV/NEXT/NUMBER)
        $(document).on('click', '.page-nav', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            var diklat = $(this).data('diklat');
            var tahun = $(this).data('tahun');
            var peny = $(this).data('peny');
            loadPagination(diklat, tahun, peny, page);
        });

        // 3. EVENT KLIK TOMBOL CLOSE (MANUAL FIX)
        $(document).on('click', '.tutup-modal', function(e) {
            e.preventDefault();
            $('#modalDetail').modal('hide');
        });
    });
</script>