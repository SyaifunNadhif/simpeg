<?php
// FILE: pages/ref-biaya-pendidikan/view-data-biaya-pendidikan.php
if (session_id() == '') session_start();
include "dist/koneksi.php"; 
$hak_akses = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : 'user';
$is_admin = ($hak_akses == 'admin' || $hak_akses == 'superadmin');
$qTahun = mysqli_query($conn, "SELECT DISTINCT YEAR(tgl_pengembangan_sdm) as th FROM tb_biaya_pendidikan WHERE tgl_pengembangan_sdm IS NOT NULL ORDER BY th DESC");
?>

<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

<style>
    .content-wrapper { background-color: #f8f9fa; }
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); background: #fff; margin-bottom: 25px; overflow: hidden; }
    .card-header-modern { padding: 25px 30px 20px 30px; background: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .toolbar-compact { padding: 15px 30px; background: #f9fafb; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .filter-group { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px; }
    .select2-container .select2-selection--single { height: 40px !important; border-radius: 10px !important; border: 1px solid #e5e7eb !important; background: #fff; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
    .search-pill { position: relative; width: 300px; }
    .search-pill input { width: 100%; height: 40px; padding-left: 40px; border-radius: 10px; border: 1px solid #e5e7eb; font-size: 0.9rem; transition: .2s; }
    .search-pill input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
    .search-pill i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 1rem; }
    
    /* Tombol */
    a:hover { text-decoration: none !important; }
    .btn-circle-home { width: 42px; height: 42px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #6b7280; background: #f3f4f6; border: none; transition: .2s; font-size: 1.1rem; text-decoration: none !important; }
    .btn-circle-home:hover { background: #e5e7eb; color: #374151; }
    .btn-modern-solid-green { background: #10b981; color: white; border: none; font-weight: 600; padding: 9px 24px; border-radius: 50px; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); display: inline-flex; align-items: center; transition: .2s; text-decoration: none !important; }
    .btn-modern-solid-purple { background: #6366f1; color: white; border: none; font-weight: 600; padding: 9px 24px; border-radius: 50px; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2); display: inline-flex; align-items: center; transition: .2s; text-decoration: none !important; }

    table.dataTable { width: 100% !important; margin: 0 !important; border-collapse: separate; border-spacing: 0; }
    table.dataTable thead th { background-color: #fff; color: #9ca3af; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; border-bottom: 2px solid #f3f4f6 !important; padding: 15px 20px; }
    table.dataTable tbody td { padding: 16px 20px; vertical-align: middle; border-bottom: 1px solid #f9fafb; color: #374151; font-size: 0.95rem; }
</style>

<section class="content pt-4 px-3">
    <div class="card card-modern">
        <div class="card-header-modern">
            <div>
                <h5 class="font-weight-bold text-dark"><i class="fas fa-graduation-cap text-primary mr-2"></i> Daftar Kegiatan Diklat</h5>
                <span class="text-muted small">Monitoring realisasi anggaran diklat</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="home-admin.php" class="btn-circle-home shadow-sm mr-2" title="Dashboard"><i class="fas fa-home"></i></a>
                <?php if($is_admin): ?>
                    <a href="home-admin.php?page=form-upload-data-biaya-pendidikan" class="btn-modern-solid-green mr-2"><i class="fas fa-file-excel mr-1"></i> Import</a>
                    <a href="home-admin.php?page=form-biaya-pendidikan" class="btn-modern-solid-purple"><i class="fas fa-plus mr-1"></i> Tambah</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="toolbar-compact">
            <div class="filter-group">
                <div style="flex: 1;"><select id="filter_tahun" class="form-control select2"><option value="">- Semua Tahun -</option><?php while ($t = mysqli_fetch_assoc($qTahun)) { echo "<option value='".$t['th']."'>".$t['th']."</option>"; } ?></select></div>
                <div style="flex: 1;"><select id="filter_kuartal" class="form-control select2"><option value="">- Semua Kuartal -</option><option value="1">Q1 (Jan-Mar)</option><option value="2">Q2 (Apr-Jun)</option><option value="3">Q3 (Jul-Sep)</option><option value="4">Q4 (Okt-Des)</option></select></div>
            </div>
            <div class="search-pill ml-auto"><i class="fas fa-search"></i><input type="text" id="customSearch" placeholder="Cari Kegiatan..."></div>
        </div>

        <div class="table-responsive">
            <table class="table w-100" id="tabelBiayaAjax">
                <thead><tr><th class="text-center">No</th><th>Detail Kegiatan</th><th>Pihak Pelaksana</th><th>Waktu</th><th class="text-right">Biaya & SDM</th><th class="text-center">Aksi</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modalPeserta" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-info-circle mr-2"></i> Rincian Peserta & Biaya</h5>
                <button type="button" class="close text-white btn-tutup-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0">
                
                <div class="p-4 bg-light border-bottom">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="small text-muted font-weight-bold text-uppercase mb-0">Nama Kegiatan</label>
                            <h5 class="font-weight-bold text-dark mt-1" id="detJudul">...</h5>
                            <span class="badge badge-info px-2" id="detKategori">...</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted font-weight-bold text-uppercase mb-0">Penyelenggara</label>
                            <div class="text-dark font-weight-500" id="detPihak">...</div>
                            <small class="text-muted font-italic" id="detJenis">...</small>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted font-weight-bold text-uppercase mb-0">Waktu & Durasi</label>
                            <div class="text-dark font-weight-500"><i class="far fa-calendar-alt mr-1"></i> <span id="detTgl">...</span></div>
                            <small class="text-muted">Durasi: <span id="detDurasi">...</span></small>
                        </div>
                    </div>
                    <div class="row mt-2 pt-2 border-top">
                        <div class="col-md-6">
                            <label class="small text-muted font-weight-bold text-uppercase mb-0">Anggaran Awal</label>
                            <div class="text-primary font-weight-bold h5 mb-0" id="detBiaya">Rp 0</div>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <label class="small text-muted font-weight-bold text-uppercase mb-0">Target Peserta</label>
                            <div class="text-dark font-weight-bold h5 mb-0" id="detJml">0 Org</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-white">
                            <tr>
                                <th width="5%" class="text-center border-top-0">No</th>
                                <th class="border-top-0">Nama Pegawai / NIP</th>
                                <th class="border-top-0 text-right">Biaya Satuan</th>
                            </tr>
                        </thead>
                        <tbody id="listPesertaBody"></tbody>
                        
                        <tfoot style="background-color: #e3f2fd;">
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold text-dark" style="vertical-align: middle;">
                                    TOTAL REALISASI (<span id="totalOrangReal">0 Orang</span>) :
                                </td>
                                <td class="text-right font-weight-bold text-success h5 mb-0" id="totalUangReal">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="p-3 d-flex justify-content-between align-items-center bg-white border-top">
                    <small class="text-muted" id="pageInfo">Menampilkan 0 data</small>
                    <nav><ul class="pagination pagination-sm mb-0" id="paginationControls"></ul></nav>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-tutup-modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white btn-tutup-hapus"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-dark">Apakah Anda yakin ingin menghapus data ini?</p>
                <div id="dataSummary" class="alert alert-secondary small p-3 rounded mb-3">Memuat info...</div>
                <textarea id="deleteReason" class="form-control" rows="2" placeholder="Alasan hapus..."></textarea>
                <input type="hidden" id="deleteId">
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link text-secondary font-weight-bold btn-tutup-hapus">Batal</button>
                <button type="button" class="btn btn-danger font-weight-bold shadow-sm px-4" id="btnConfirmDelete">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    var table = $('#tabelBiayaAjax').DataTable({
        "processing": true, "serverSide": true, "ordering": false, "autoWidth": false,
        "ajax": {
            "url": "pages/ref-biaya-pendidikan/ajax-data-biaya-pendidikan.php", 
            "type": "GET",
            "data": function (d) { d.tahun = $('#filter_tahun').val(); d.kuartal = $('#filter_kuartal').val(); }
        },
        "columns": [
            { "data": "no", "className": "text-center font-weight-bold" },
            { "data": "kegiatan" }, { "data": "pelaksana" }, { "data": "waktu" },
            { "data": "biaya", "className": "text-right" }, { "data": "aksi", "className": "text-center" }
        ],
        "dom": 't<"d-flex align-items-center justify-content-between flex-wrap p-3 bg-white border-top"lip>',
        "drawCallback": function(settings) {
            <?php if (!$is_admin) { echo "$('.btn-delete').remove();"; } ?>
        }
    });

    $('#filter_tahun, #filter_kuartal').change(function(){ table.ajax.reload(); });
    var timer; $('#customSearch').on('keyup', function(){ clearTimeout(timer); var val=this.value; timer=setTimeout(function(){ table.search(val).draw(); }, 500); });

    var currentModalParams = {}; 

    window.changeModalPage = function(page) { loadPesertaData(page); };

    function loadPesertaData(page) {
        $('#listPesertaBody').css('opacity', '0.5');
        $.ajax({
            url: 'pages/ref-biaya-pendidikan/ajax-get-detail-peserta.php',
            type: 'POST',
            data: { 
                diklat: currentModalParams.diklat, 
                penyelenggara: currentModalParams.peny, 
                tgl: currentModalParams.tgl,
                page: page
            },
            dataType: 'json',
            success: function(res) {
                $('#listPesertaBody').css('opacity', '1');
                var html = '';
                
                if (res.data.length > 0) {
                    $.each(res.data, function(i, item) {
                        html += '<tr><td class="text-center">'+item.no+'</td><td><div class="font-weight-bold text-dark">'+item.nama+'</div><small class="text-muted">'+item.nip+'</small></td><td class="text-right">'+item.biaya+'</td></tr>';
                    });
                    
                    // UPDATE FOOTER REALISASI
                    $('#totalOrangReal').text(res.summary.total_orang);
                    $('#totalUangReal').text(res.summary.total_biaya);

                } else { 
                    html = '<tr><td colspan="3" class="text-center text-muted py-4"><i>Belum ada data peserta.</i></td></tr>'; 
                    $('#totalOrangReal').text('0 Orang');
                    $('#totalUangReal').text('Rp 0');
                }
                
                $('#listPesertaBody').html(html);

                // Pagination
                var p = res.pagination;
                $('#pageInfo').text('Halaman ' + p.current_page + ' dari ' + p.total_pages + ' (Total ' + p.total_data + ')');
                
                var pagHtml = '';
                if(p.current_page > 1) { pagHtml += '<li class="page-item"><a class="page-link" href="#" onclick="changeModalPage('+(p.current_page - 1)+'); return false;">Prev</a></li>'; }
                else { pagHtml += '<li class="page-item disabled"><span class="page-link">Prev</span></li>'; }
                
                if(p.current_page < p.total_pages) { pagHtml += '<li class="page-item"><a class="page-link" href="#" onclick="changeModalPage('+(p.current_page + 1)+'); return false;">Next</a></li>'; }
                else { pagHtml += '<li class="page-item disabled"><span class="page-link">Next</span></li>'; }
                
                $('#paginationControls').html(pagHtml);
            },
            error: function() { $('#listPesertaBody').html('<tr><td colspan="3" class="text-danger text-center">Gagal load data.</td></tr>'); }
        });
    }

    $('body').on('click', '.btn-detail-peserta', function(e) {
        e.preventDefault();
        var d = $(this).data();
        
        // ISI HEADER KOMPLIT
        $('#detJudul').text(d.diklat);
        $('#detKategori').text(d.kategori);
        $('#detPihak').text(d.penyelenggara);
        $('#detJenis').text('('+d.jenis+')');
        $('#detTgl').text(d.tgl);
        $('#detDurasi').text(d.durasi);
        $('#detBiaya').text(d.biaya);
        $('#detJml').text(d.jml + ' Org');

        currentModalParams = { diklat: d.diklat, peny: d.penyelenggara, tgl: d.tglraw };

        $('#listPesertaBody').html('<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>');
        $('#modalPeserta').modal('show');
        loadPesertaData(1);
    });

    $('body').on('click', '.btn-tutup-modal', function() { $('#modalPeserta').modal('hide'); });
    $('body').on('click', '.btn-tutup-hapus', function() { $('#modalHapus').modal('hide'); });

    // Logic Delete (Sama)
    $('body').on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#deleteId').val(id);
        $('#deleteReason').val('');
        $('#dataSummary').html('<i class="fas fa-spinner fa-spin text-primary"></i> Info data...');
        $('#modalHapus').modal('show');

        $.ajax({
            url: 'pages/ref-biaya-pendidikan/prosess-delete-biaya.php',
            type: 'POST', data: { action: 'get_info', id: id }, dataType: 'json',
            success: function(res) {
                if (res.status == 'success') { $('#dataSummary').html('<strong>' + res.data.kegiatan + '</strong><br>Biaya: ' + res.data.biaya); } 
                else { $('#dataSummary').html('<span class="text-danger">' + res.message + '</span>'); }
            }
        });
    });

    $('#btnConfirmDelete').click(function() {
        var id = $('#deleteId').val();
        var reason = $.trim($('#deleteReason').val());
        if(reason == '') { Swal.fire('Wajib Diisi', 'Mohon isi alasan hapus', 'warning'); return; }
        var btn = $(this); btn.prop('disabled', true).text('...');
        $.ajax({
            url: 'pages/ref-biaya-pendidikan/prosess-delete-biaya.php',
            type: 'POST', data: { action: 'delete', id: id, reason: reason }, dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).text('Ya, Hapus');
                $('#modalHapus').modal('hide');
                if (res.status == 'success') { Swal.fire({icon: 'success', title: 'Terhapus', timer: 1000, showConfirmButton: false}); table.ajax.reload(null, false); } 
                else { Swal.fire('Gagal', res.message, 'error'); }
            }
        });
    });
});
</script>