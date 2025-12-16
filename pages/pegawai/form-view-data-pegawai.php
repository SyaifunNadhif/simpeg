<?php
// ... (Bagian Header PHP tetap sama) ...
$page_title    = "Data";
$page_subtitle = "Pegawai";
$breadcrumbs   = [ ["label" => "Dashboard", "url" => "home-admin.php"], ["label" => "Data Pegawai"] ];
include "komponen/header.php";

function esc($c,$s){ return mysqli_real_escape_string($c, $s); }

$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
if ($hak_akses_user === 'kepala') {
    $link_back = "home-admin.php?page=dashboard-cabang";
} else {
    $link_back = "home-admin.php";
}
?>

<style>
    .content-header { display: none !important; }
    .content-wrapper { background-color: #f4f6f9; font-family: 'Inter', sans-serif; }
    .card-modern { border: none; border-radius: 16px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); background: #fff; overflow: hidden; margin-bottom: 20px; }
    .card-header-modern { padding: 20px 30px; background: #fff; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .nav-pills-modern { background: #f8fafc; padding: 5px; border-radius: 50px; display: inline-flex; border: 1px solid #e2e8f0; }
    .nav-pills-modern .nav-link { border-radius: 50px; padding: 8px 25px; font-weight: 600; color: #64748b; background: transparent; transition: all 0.2s; font-size: 0.9rem; }
    .nav-pills-modern .nav-link:hover { color: #0ea5e9; }
    .nav-pills-modern .nav-link.active { background-color: #fff; color: #0ea5e9; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .input-modern { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; font-size: 0.85rem; width: 100%; color: #334155; }
    .input-modern:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); outline: none; }
    .label-filter { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    /* ... (Style Table & Avatar tetap sama seperti sebelumnya) ... */
    table.dataTable { border-collapse: collapse !important; width: 100% !important; margin-top: 0 !important; }
    table.dataTable thead th { background-color: #fff; color: #64748b; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9 !important; padding: 15px 20px; }
    table.dataTable tbody td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; color: #334155; font-size: 0.95rem; }
    table.dataTable tbody tr:hover { background-color: #fcfdfe; }
    .avatar-wrapper { width: 45px; height: 45px; min-width: 45px; border-radius: 50%; overflow: hidden; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .avatar-img { width: 100%; height: 100%; object-fit: cover; }
    .text-pegawai-name { font-weight: 700; color: #1e293b; font-size: 0.95rem; display: block; }
    .text-pegawai-id { font-family: monospace; color: #94a3b8; font-size: 0.8rem; }
    .badge-soft { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
    .badge-soft-blue { background: #e0f2fe; color: #0284c7; }
    .badge-soft-red { background: #fee2e2; color: #dc2626; }
    .badge-soft-dark { background: #f1f5f9; color: #475569; }
    @media (max-width: 768px) {
        .card-header-modern { flex-direction: column; align-items: flex-start; }
        .dt-controls-wrapper { flex-direction: column; align-items: stretch; }
    }
    .dt-controls-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; gap: 10px; }
    .dataTables_filter input { border-radius: 50px !important; border: 1px solid #e2e8f0; padding: 6px 15px !important; outline: none; }
    .dataTables_length select { border-radius: 50px !important; border: 1px solid #e2e8f0; padding: 5px 10px; outline: none; }
</style>

<section class="content" style="padding-top: 30px; padding-bottom: 50px;">
  <div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 style="font-weight: 800; color: #1e293b; margin-bottom: 4px; font-size: 1.7rem;">Data Pegawai</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Manajemen data aktif, jabatan, dan purna tugas.</p>
        </div>
        <a href="<?= $link_back; ?>" class="btn btn-white border shadow-sm rounded-pill px-4 py-2" style="color: #64748b; font-weight: 600; background: #fff;">
            <i class="fa fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="card card-modern">
      
      <div class="card-header-modern">
          <ul class="nav nav-pills nav-pills-modern" id="pegawaiTab" role="tablist">
              <li class="nav-item"><a class="nav-link active" id="aktif-tab" data-toggle="pill" href="#aktif" role="tab"><i class="fa fa-users mr-1"></i> Aktif</a></li>
              <li class="nav-item"><a class="nav-link" id="nonjob-tab" data-toggle="pill" href="#nonjob" role="tab"><i class="fa fa-exclamation-circle mr-1"></i> Belum Ada Jabatan</a></li>
              <li class="nav-item"><a class="nav-link" id="purna-tab" data-toggle="pill" href="#purna" role="tab"><i class="fa fa-history mr-1"></i> Purna</a></li>
          </ul>

          <?php if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'admin'): ?>
          <div class="d-flex gap-2">
              <a href="home-admin.php?page=form-master-data-pegawai" class="btn btn-primary rounded-pill shadow-sm px-3 font-weight-bold" style="background: #0ea5e9; border:none;"><i class="fa fa-plus mr-1"></i> Tambah</a>
              <a href="home-admin.php?page=form-upload-data-pegawai" class="btn btn-outline-success rounded-pill px-3 font-weight-bold" style="border: 1px solid #22c55e; color: #22c55e;"><i class="fa fa-file-excel mr-1"></i> Import</a>
          </div>
          <?php endif; ?>
      </div>

      <div class="card-body p-0">
        <div class="tab-content" id="pegawaiTabContent">

          <div class="tab-pane fade show active" id="aktif" role="tabpanel">
            
            <div class="p-4 border-bottom" style="background: #fff;">
                 <div class="row g-3">
                    
                    <div class="col-md-4 col-12 mb-2 mb-md-0">
                        <label class="label-filter"><i class="fa fa-building mr-1"></i> Kantor / Cabang</label>
                        <select id="filter_kantor" class="form-control input-modern">
                            <option value="">-- Pilih Kantor --</option>
                            <?php
                                // Ambil data kantor
                                if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala') {
                                    $kode_kantor = mysqli_real_escape_string($conn, isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '');
                                    $qUnit = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE kode_kantor_detail = '{$kode_kantor}'");
                                } else {
                                    $qUnit = mysqli_query($conn, "SELECT * FROM tb_kantor ORDER BY nama_kantor ASC");
                                }
                                while ($u = mysqli_fetch_assoc($qUnit)) {
                                    echo "<option value='".$u['kode_kantor_detail']."'>".$u['nama_kantor']."</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-12 mb-2 mb-md-0">
                        <label class="label-filter"><i class="fa fa-sitemap mr-1"></i> Divisi / Bagian</label>
                        <select id="filter_divisi" class="form-control input-modern" disabled>
                            <option value="">-- Pilih Kantor Dulu --</option>
                            </select>
                    </div>

                    <div class="col-md-4 col-12">
                        <label class="label-filter"><i class="fa fa-id-badge mr-1"></i> Jabatan</label>
                        <select id="filter_jabatan" class="form-control input-modern" disabled>
                            <option value="">-- Pilih Divisi Dulu --</option>
                            </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tablePegawai" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th width="35%">Pegawai</th> 
                            <th>TTL</th>
                            <th>Jabatan & Unit</th> 
                            <th>Mulai</th>
                            <th>Kontak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
          </div>
          
          <div class="tab-pane fade" id="nonjob" role="tabpanel">
             <div class="p-4">
                 <div class="alert alert-light border shadow-sm rounded-lg d-flex align-items-center mb-0" role="alert">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h6 class="font-weight-bold mb-1">Perhatian</h6>
                        <span class="text-muted">Daftar pegawai ini <b>belum memiliki jabatan aktif</b>.</span>
                    </div>
                 </div>
             </div>
             <div class="table-responsive">
                <table id="tableNonJob" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th width="40%">Pegawai</th>
                            <th>Status Kepegawaian</th>
                            <th>Kontak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
             </div>
          </div>

          <div class="tab-pane fade" id="purna" role="tabpanel">
            <div class="table-responsive">
                <table id="tablePurna" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th width="35%">Pegawai</th>
                            <th>TTL</th>
                            <th>Jabatan Terakhir</th>
                            <th>Status</th>
                            <th>Tgl Pensiun</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
          </div>

        </div> 
      </div> 
    </div> 
  </div>
</section>

<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {

  // --- RENDERER PEGAWAI ---
  function renderPegawai(fotoHtml, namaHtml) {
      return `<div class="d-flex align-items-center">
                <div class="mr-3">${fotoHtml}</div>
                <div>${namaHtml}</div>
              </div>`;
  }

  // --- DATA TABLE OPTIONS ---
  var dtOptions = {
      processing: true, serverSide: true, autoWidth: false,
      lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
      dom: '<"dt-controls-wrapper"lf>rtip',
      language: {
          search: "", searchPlaceholder: "Cari nama/NIP...", lengthMenu: "Tampil _MENU_",
          zeroRecords: "Data tidak ditemukan",
          info: "Menampilkan _START_ - _END_ dari _TOTAL_",
          paginate: { next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
      }
  };

  // 1. TABEL PEGAWAI AKTIF (Dengan 3 Filter Terkait)
  var tableAktif = $('#tablePegawai').DataTable($.extend({}, dtOptions, {
      ajax: {
          url: 'pages/pegawai/ajax-data-pegawai.php', 
          type: 'GET',
          data: function(d){ 
              d.kantor  = $('#filter_kantor').val();
              d.divisi  = $('#filter_divisi').val();
              d.jabatan = $('#filter_jabatan').val();
          }
      },
      columns: [
          { 
              data: 'nama',
              render: function(data, type, row) {
                  return renderPegawai(row.nama, row.raw_nama, row.raw_id); // row.nama = foto html
              }
          },
          { data: 'ttl' },
          { data: 'unit_kerja', render: function(d){ return d; } },
          { data: 'tgl_masuk', className: 'text-nowrap', render: function(d){ return `<span class="badge badge-soft-blue">${d||'-'}</span>`; } },
          { data: 'no_telp', render: function(d){ return `<span style="color:#64748b;">${d||'-'}</span>`; } },
          { data: 'action', orderable: false, className: "text-center" }
      ]
  }));

  // =================================================================
  // === LOGIKA CASCADING DROPDOWN (KANTOR -> DIVISI -> JABATAN) ===
  // =================================================================

  // 1. Ketika KANTOR Berubah
  $('#filter_kantor').on('change', function(){
      var kodeKantor = $(this).val();
      
      // Reset Divisi & Jabatan
      $('#filter_divisi').html('<option value="">-- Loading... --</option>').prop('disabled', true);
      $('#filter_jabatan').html('<option value="">-- Pilih Divisi Dulu --</option>').prop('disabled', true);
      
      // Reload Tabel (Hanya Filter Kantor)
      tableAktif.ajax.reload();

      if(kodeKantor) {
          $.ajax({
              url: 'pages/pegawai/ajax-get-options.php',
              type: 'POST',
              data: { type: 'get_divisi', kode_kantor: kodeKantor },
              success: function(response){
                  $('#filter_divisi').html(response).prop('disabled', false);
              }
          });
      } else {
          $('#filter_divisi').html('<option value="">-- Pilih Kantor Dulu --</option>');
      }
  });

  // 2. Ketika DIVISI Berubah
  $('#filter_divisi').on('change', function(){
      var divisi = $(this).val();
      var kodeKantor = $('#filter_kantor').val();

      // Reset Jabatan
      $('#filter_jabatan').html('<option value="">-- Loading... --</option>').prop('disabled', true);
      
      // Reload Tabel (Filter Kantor + Divisi)
      tableAktif.ajax.reload();

      if(divisi) {
          $.ajax({
              url: 'pages/pegawai/ajax-get-options.php',
              type: 'POST',
              data: { type: 'get_jabatan', kode_kantor: kodeKantor, divisi: divisi },
              success: function(response){
                  $('#filter_jabatan').html(response).prop('disabled', false);
              }
          });
      } else {
          $('#filter_jabatan').html('<option value="">-- Pilih Divisi Dulu --</option>');
      }
  });

  // 3. Ketika JABATAN Berubah
  $('#filter_jabatan').on('change', function(){
      // Reload Tabel (Filter Kantor + Divisi + Jabatan)
      tableAktif.ajax.reload();
  });

  // =================================================================

  // 2. TABEL NONJOB
  $('#nonjob-tab').on('click', function(){
      if ($.fn.DataTable.isDataTable('#tableNonJob')) return;
      $('#tableNonJob').DataTable($.extend({}, dtOptions, {
          ajax: { url: 'pages/pegawai/ajax-data-pegawai.php', type: 'GET', data: function(d){ d.filter_type = 'nonjob'; } },
          columns: [
              { data: 'nama', render: function(data, type, row) { return renderPegawai(row.nama, row.raw_nama, row.raw_id); } },
              { data: 'status_kepeg', render: function(d){ return `<span class="badge badge-soft-red">${d||'Unknown'}</span>`; } },
              { data: 'no_telp' },
              { data: 'action', orderable: false, className: "text-center" }
          ]
      }));
  });

  // 3. TABEL PURNA
  $('#purna-tab').on('click', function(){
      if ($.fn.DataTable.isDataTable('#tablePurna')) return;
      $('#tablePurna').DataTable($.extend({}, dtOptions, {
          ajax: { url: 'pages/pegawai/ajax-pegawai-purna.php', type: 'GET' },
          columns: [
              { data: 'nama', render: function(data, type, row) { 
                  var foto = '<div class="avatar-wrapper"><img src="https://ui-avatars.com/api/?name='+row.nama+'&background=random&color=fff" class="avatar-img"></div>';
                  return renderPegawai(foto, row.nama, row.id_peg); 
              }},
              { data: 'ttl' }, { data: 'jabatan' }, 
              { data: 'status_kepeg', render: function(d){ return `<span class="badge badge-soft-dark">${d}</span>`; } }, 
              { data: 'tgl_pensiun' }
          ]
      }));
  });

});
</script>