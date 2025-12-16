<?php
/*********************************************************
 * FILE     : pages/pegawai/form-view-data-pegawai.php
 * UPDATE   : Select2 Search, Cascading Filter, Modern UI
 *********************************************************/

$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
if ($hak_akses_user === 'kepala') {
    $link_back = "home-admin.php?page=dashboard-cabang";
} else {
    $link_back = "home-admin.php";
}
?>

<link rel="stylesheet" href="plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

<style>
    /* HIDE HEADER BAWAAN */
    .content-header { display: none !important; }
    .content-wrapper { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    
    /* CARD STYLE */
    .card-modern {
        border: none; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        background: #fff; overflow: visible; /* Penting agar dropdown tidak terpotong */
        margin-bottom: 25px;
    }
    .card-header-modern {
        padding: 20px 30px; background: #fff; border-bottom: 1px solid #f1f5f9;
        border-radius: 16px 16px 0 0;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
    }

    /* TABS */
    .nav-pills-modern { background: #f1f5f9; padding: 4px; border-radius: 50px; display: inline-flex; }
    .nav-pills-modern .nav-link {
        border-radius: 50px; padding: 8px 24px; font-weight: 600; color: #64748b; font-size: 0.9rem; transition: all 0.2s;
    }
    .nav-pills-modern .nav-link.active { background-color: #fff; color: #0ea5e9; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

    /* --- SELECT2 CUSTOM STYLE (MODERN ROUNDED) --- */
    .select2-container .select2-selection--single {
        height: 45px !important; /* Tinggi fix */
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        padding: 8px 10px !important;
        background-color: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 45px !important; right: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important; color: #334155 !important; font-size: 0.9rem;
    }
    .filter-label {
        font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px;
    }

    /* TABLE */
    table.dataTable { border-collapse: separate; border-spacing: 0; width: 100% !important; margin-top: 0 !important; }
    table.dataTable thead th {
        background-color: #fff; color: #64748b; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9 !important; padding: 15px 20px; letter-spacing: 0.5px;
    }
    table.dataTable tbody td {
        padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; color: #334155; font-size: 0.95rem;
    }
    table.dataTable tbody tr:hover { background-color: #fcfdfe; }

    /* UTILITY */
    .avatar-wrapper { width: 45px; height: 45px; border-radius: 50%; overflow: hidden; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .avatar-img { width: 100%; height: 100%; object-fit: cover; }
    
    .text-pegawai-name { font-weight: 700; color: #1e293b; font-size: 0.95rem; display: block; }
    .text-pegawai-id { font-family: monospace; color: #64748b; font-size: 0.85rem; }
    
    .text-jabatan { font-weight: 700; color: #0f172a; font-size: 0.9rem; display: block; margin-bottom: 2px; }
    .text-kantor { color: #0ea5e9; font-weight: 600; font-size: 0.8rem; display: block; }
    .text-divisi { color: #94a3b8; font-size: 0.8rem; }

    /* CONTROLS */
    .dt-controls-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; gap: 10px; }
    .dataTables_filter input { border-radius: 50px !important; border: 1px solid #e2e8f0; padding: 8px 20px !important; outline: none; }
    .dataTables_length select { border-radius: 50px !important; border: 1px solid #e2e8f0; padding: 5px 15px; outline: none; }

    @media (max-width: 768px) {
        .card-header-modern { flex-direction: column; align-items: flex-start; }
        .dt-controls-wrapper { flex-direction: column; align-items: stretch; }
        .dataTables_filter { text-align: left !important; }
        .dataTables_filter input { width: 100% !important; margin-left: 0 !important; }
    }
</style>

<section class="content" style="padding-top: 30px; padding-bottom: 50px;">
  <div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 style="font-weight: 800; color: #1e293b; margin-bottom: 4px; font-size: 1.7rem;">Data Pegawai</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data pegawai, jabatan, dan status kepegawaian.</p>
        </div>
        <a href="<?= $link_back; ?>" class="btn btn-white border shadow-sm rounded-pill px-4 py-2 bg-white font-weight-bold text-secondary">
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
              <a href="home-admin.php?page=form-master-data-pegawai" class="btn btn-primary rounded-pill shadow-sm px-4 font-weight-bold" style="background:#0ea5e9; border:none;"><i class="fa fa-plus mr-2"></i> Tambah</a>
              <a href="home-admin.php?page=form-upload-data-pegawai" class="btn btn-outline-success rounded-pill px-4 font-weight-bold" style="border:1px solid #22c55e; color:#22c55e;"><i class="fa fa-file-excel mr-2"></i> Import</a>
          </div>
          <?php endif; ?>
      </div>

      <div class="card-body p-0">
        <div class="tab-content" id="pegawaiTabContent">

          <div class="tab-pane fade show active" id="aktif" role="tabpanel">
            
            <div class="p-4 border-bottom" style="background: #fff;">
                 <div class="row g-3">
                    
                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <label class="filter-label"><i class="fa fa-building mr-1"></i> Kantor / Cabang</label>
                        <select id="filter_kantor" class="form-control select2">
                            <option value="">-- Semua Kantor --</option>
                            <?php
                                if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala') {
                                    $kode_kantor = mysqli_real_escape_string($conn, isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '');
                                    $qUnit = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE kode_kantor_detail = '{$kode_kantor}'");
                                } else {
                                    $qUnit = mysqli_query($conn, "SELECT * FROM tb_kantor ORDER BY kode_kantor_detail ASC");
                                }
                                while ($u = mysqli_fetch_assoc($qUnit)) {
                                    echo "<option value='".$u['kode_kantor_detail']."'>".$u['nama_kantor']."</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <label class="filter-label"><i class="fa fa-sitemap mr-1"></i> Divisi / Bagian</label>
                        <select id="filter_divisi" class="form-control select2" disabled>
                            <option value="">-- Pilih Kantor Dulu --</option>
                        </select>
                    </div>

                    <div class="col-md-4 col-12">
                        <label class="filter-label"><i class="fa fa-id-badge mr-1"></i> Jabatan</label>
                        <select id="filter_jabatan" class="form-control select2" disabled>
                            <option value="">-- Pilih Divisi Dulu --</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tablePegawai" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th width="30%">Pegawai</th> 
                            <th>TTL</th>
                            <th width="30%">Jabatan & Unit</th> 
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
                 <div class="alert alert-warning border-0 shadow-sm rounded-lg d-flex align-items-center mb-0" style="background-color: #fffbeb; color: #92400e;">
                    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                    <div>
                        <h6 class="font-weight-bold mb-1">Data Pegawai Non-Jabatan</h6>
                        <span class="small">Pegawai ini berstatus <b>Aktif</b> tapi belum memiliki jabatan. Klik tombol aksi untuk mengatur.</span>
                    </div>
                 </div>
             </div>
             <div class="table-responsive">
                <table id="tableNonJob" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th width="40%">Pegawai</th>
                            <th>Status</th>
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
<script src="plugins/select2/js/select2.full.min.js"></script>

<script>
$(document).ready(function() {

  // --- INIT SELECT2 (SEARCHABLE DROPDOWN) ---
  $('.select2').select2({
      theme: 'bootstrap4',
      width: '100%' // Penting agar tidak gepeng/terpotong
  });

  // --- RENDERER HELPERS ---
  function renderPegawai(fotoHtml, namaHtml, idHtml) {
      return `<div class="d-flex align-items-center">
                <div class="mr-3">${fotoHtml}</div>
                <div>
                    <span class="text-pegawai-name">${namaHtml}</span>
                    <span class="text-pegawai-id">${idHtml}</span>
                </div>
              </div>`;
  }

  function renderJabatan(jabatan, kantor, divisi) {
      var j = jabatan ? jabatan : '<span class="text-danger font-italic small">Belum ada jabatan</span>';
      return `<div>
                <span class="text-jabatan">${j}</span>
                <span class="text-kantor"><i class="fas fa-building mr-1"></i> ${kantor}</span>
                <span class="text-divisi"><i class="fas fa-sitemap mr-1"></i> ${divisi}</span>
              </div>`;
  }

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

  // 1. TABEL PEGAWAI AKTIF
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
              data: 'nama_teks',
              render: function(data, type, row) {
                  return renderPegawai(row.nama_foto, row.nama_teks, row.id_peg);
              }
          },
          { data: 'ttl', render: function(d){ return `<span style="font-size:0.85rem; color:#64748b;">${d||'-'}</span>`; } },
          { 
              data: 'jabatan',
              render: function(d, t, r) {
                  return renderJabatan(r.jabatan, r.kantor, r.divisi);
              }
          },
          { data: 'tgl_masuk', className: 'text-nowrap', render: function(d){ return `<span class="badge badge-light border text-muted">${d||'-'}</span>`; } },
          { data: 'no_telp', render: function(d){ return `<span style="color:#64748b; font-size:0.9rem;">${d||'-'}</span>`; } },
          { data: 'action', orderable: false, className: "text-center" }
      ]
  }));

  // === CASCADING DROPDOWN LOGIC (KANTOR -> DIVISI -> JABATAN) ===
  
  // 1. Ganti Kantor
  $('#filter_kantor').on('change', function(){
      var kodeKantor = $(this).val();
      
      // Reset Select2 Divisi & Jabatan
      $('#filter_divisi').html('<option value="">-- Loading... --</option>').prop('disabled', true).trigger('change');
      $('#filter_jabatan').html('<option value="">-- Pilih Divisi Dulu --</option>').prop('disabled', true).trigger('change');
      
      tableAktif.ajax.reload(); // Reload tabel

      if(kodeKantor) {
          $.ajax({
              url: 'pages/pegawai/ajax-get-options.php', type: 'POST',
              data: { type: 'get_divisi', kode_kantor: kodeKantor },
              success: function(response){ 
                  $('#filter_divisi').html(response).prop('disabled', false).trigger('change'); // Refresh Select2
              }
          });
      } else {
          $('#filter_divisi').html('<option value="">-- Pilih Kantor Dulu --</option>').trigger('change');
      }
  });

  // 2. Ganti Divisi
  $('#filter_divisi').on('change', function(){
      var divisi = $(this).val();
      var kodeKantor = $('#filter_kantor').val();

      $('#filter_jabatan').html('<option value="">-- Loading... --</option>').prop('disabled', true).trigger('change');
      tableAktif.ajax.reload();

      if(divisi) {
          $.ajax({
              url: 'pages/pegawai/ajax-get-options.php', type: 'POST',
              data: { type: 'get_jabatan', kode_kantor: kodeKantor, divisi: divisi },
              success: function(response){ 
                  $('#filter_jabatan').html(response).prop('disabled', false).trigger('change');
              }
          });
      } else {
          $('#filter_jabatan').html('<option value="">-- Pilih Divisi Dulu --</option>').trigger('change');
      }
  });

  // 3. Ganti Jabatan
  $('#filter_jabatan').on('change', function(){ tableAktif.ajax.reload(); });

  // 2. TABEL NONJOB
  $('#nonjob-tab').on('click', function(){
      if ($.fn.DataTable.isDataTable('#tableNonJob')) return;
      $('#tableNonJob').DataTable($.extend({}, dtOptions, {
          ajax: { url: 'pages/pegawai/ajax-data-pegawai.php', type: 'GET', data: function(d){ d.filter_type = 'nonjob'; } },
          columns: [
              { data: 'nama_teks', render: function(d,t,r) { return renderPegawai(r.nama_foto, r.nama_teks, r.id_peg); } },
              { data: 'status_kepeg', render: function(d){ return `<span class="badge badge-danger px-3 py-2">${d||'Unknown'}</span>`; } },
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
              { data: 'nama', render: function(d,t,r) { 
                  var foto = '<div class="avatar-wrapper"><img src="https://ui-avatars.com/api/?name='+r.nama+'&background=random&color=fff" class="avatar-img"></div>';
                  return renderPegawai(foto, r.nama, r.id_peg);
              }},
              { data: 'ttl' }, { data: 'jabatan' }, 
              { data: 'status_kepeg', render: function(d){ return `<span class="badge badge-secondary">${d}</span>`; } }, 
              { data: 'tgl_pensiun' }
          ]
      }));
  });

});
</script>