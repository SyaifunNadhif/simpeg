<?php
// FILE: pages/report/view-rekap-biaya.php

if (session_id() == '') session_start();
include "dist/koneksi.php"; 

// Ambil List Tahun
$optTahun = [];
$qT = mysqli_query($conn, "SELECT DISTINCT YEAR(tgl_pengembangan_sdm) as th FROM tb_biaya_pendidikan WHERE tgl_pengembangan_sdm IS NOT NULL AND tgl_pengembangan_sdm != '0000-00-00' ORDER BY th DESC");
while($r = mysqli_fetch_assoc($qT)){ $optTahun[] = $r['th']; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    .modern-wrapper { font-family: 'Poppins', sans-serif; color: #444; font-size: 0.9rem; }
    
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
    
    /* Table Styling */
    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-modern thead th { 
        background: linear-gradient(45deg, #1e3c72, #2a5298); 
        color: white; padding: 15px; font-weight: 600; 
        text-transform: uppercase; font-size: 0.8rem; 
        border: none; vertical-align: middle; 
    }
    .table-modern tbody tr:hover { background-color: #f8f9fa; }
    .table-modern td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    /* Buttons */
    .btn-modern { border-radius: 8px; padding: 8px 16px; font-weight: 500; border: none; color: white !important; cursor: pointer; text-decoration: none !important; display: inline-flex; align-items: center; gap: 5px;}
    .btn-excel { background-color: #1D6F42; } .btn-excel:hover { background-color: #145c32; }
    .btn-pdf { background-color: #c62828; } .btn-pdf:hover { background-color: #a92020; }

    .badge-tahun { background: #e9ecef; color: #333; padding: 5px 10px; border-radius: 6px; font-weight: 700; border: 1px solid #ced4da; }
    .nominal-font { font-family: 'Consolas', monospace; font-weight: 600; color: #333; }
    .form-control-modern { border-radius: 8px; border: 1px solid #ddd; padding: 8px 12px; }
    
    /* Label Filter */
    .filter-label { font-size: 0.8rem; font-weight: 700; color: #6c757d; margin-right: 8px; }
</style>

<div class="modern-wrapper">
    <section class="content-header pt-3 pb-3">
        <div class="container-fluid">
            <h1 style="font-weight: 700; color: #2c3e50; font-size: 1.5rem;">
                <i class="fas fa-money-check-alt mr-2 text-primary"></i> Rekap Biaya Pendidikan
            </h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <div class="card-modern">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <label class="filter-label">TAHUN:</label>
                                <select id="filterTahun" class="form-control form-control-modern" style="min-width: 140px;">
                                    <option value="Semua">Semua Tahun</option>
                                    <?php foreach($optTahun as $th) { echo "<option value='$th'>$th</option>"; } ?>
                                </select>
                            </div>

                            <div class="d-flex align-items-center">
                                <label class="filter-label">KUARTAL:</label>
                                <select id="filterKuartal" class="form-control form-control-modern" style="min-width: 160px;">
                                    <option value="Semua">Semua Kuartal</option>
                                    <option value="1">Q1 (Jan - Mar)</option>
                                    <option value="2">Q2 (Apr - Jun)</option>
                                    <option value="3">Q3 (Jul - Sep)</option>
                                    <option value="4">Q4 (Okt - Des)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <a href="#" id="btnExcel" target="_blank" class="btn btn-modern btn-excel mr-1">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="#" id="btnPdf" target="_blank" class="btn btn-modern btn-pdf">
                                <i class="fas fa-print"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-body p-0 table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="20%">Kategori Pengembangan</th> <th>Detail Kegiatan</th>
                                <th>Penyelenggara</th>
                                <th width="8%" class="text-center">Tahun</th>
                                <th width="8%" class="text-center">Peserta</th>
                                <th width="15%" class="text-right">Biaya</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div> Memuat data...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #eef2ff; font-weight: 700; color: #1e3c72; border-top: 2px solid #aec4e8;">
                                <td colspan="5" class="text-right text-uppercase pt-3 pb-3">Grand Total</td>
                                <td class="text-center pt-3 pb-3" id="grandTotalPeserta">0</td>
                                <td class="text-right text-success nominal-font pt-3 pb-3" id="grandTotalBiaya" style="font-size: 1.1rem;">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    
    function loadData() {
        var tahun   = $('#filterTahun').val();
        var kuartal = $('#filterKuartal').val();
        
        // Update Link Download (Include Kuartal)
        var params = 'type=excel&tahun=' + tahun + '&kuartal=' + kuartal;
        $('#btnExcel').attr('href', 'pages/report/export-biaya-pendidikan.php?' + params);
        
        params = 'type=pdf&tahun=' + tahun + '&kuartal=' + kuartal;
        $('#btnPdf').attr('href', 'pages/report/export-biaya-pendidikan.php?' + params);

        $('#tableBody').html('<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm"></div> Memuat data...</td></tr>');

        $.ajax({
            url: 'pages/report/ajax-rekap-biaya.php',
            type: 'GET',
            data: { tahun: tahun, kuartal: kuartal },
            dataType: 'json',
            success: function(res) {
                var html = '';
                if(res.data.length > 0) {
                    $.each(res.data, function(i, item){
                        var no = i + 1;
                        html += `<tr>
                                    <td class="text-center" style="font-weight: 600; color:#888;">${no}.</td>
                                    <td style="color:#1e3c72; font-weight:500;">${item.kategori_display}</td>
                                    <td style="font-weight: 500; color: #2c3e50;">
                                        ${item.kegiatan}
                                        <div class="small text-muted mt-1"><i class="far fa-calendar-alt mr-1"></i> ${item.tgl_lengkap}</div>
                                    </td>
                                    <td class="text-secondary small font-weight-bold text-uppercase">${item.penyelenggara}</td>
                                    <td class="text-center"><span class="badge-tahun">${item.tahun}</span></td>
                                    <td class="text-center"><span class="badge badge-info px-3 py-2">${item.peserta}</span></td>
                                    <td class="text-right nominal-font">Rp ${item.biaya_rp}</td>
                                 </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada data untuk periode ini.</td></tr>';
                }

                $('#tableBody').html(html);
                $('#grandTotalPeserta').text(res.total.peserta + ' Org');
                $('#grandTotalBiaya').text('Rp ' + res.total.biaya);
            },
            error: function() {
                $('#tableBody').html('<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>');
            }
        });
    }

    // Load Pertama Kali
    loadData();

    // Event Listener (Tahun & Kuartal)
    $('#filterTahun, #filterKuartal').change(function(){
        loadData();
    });
});
</script>