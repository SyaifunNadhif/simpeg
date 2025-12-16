<?php
/*********************************************************
 * FILE     : pages/pegawai/form-view-data-pegawai.php
 * MODULE   : SIMPEG — Data Pegawai (Modern UI & Responsive)
 * VERSION  : v2.3 (Fixed Mobile 1-Line Layout)
 * DATE     : 2025-11-20
 * AUTHOR   : EWS/SIMPEG BKK Jateng
 *********************************************************/

// Header & breadcrumbs
$page_title    = "Data";
$page_subtitle = "Pegawai";
$breadcrumbs   = [ ["label" => "Dashboard", "url" => "home-admin.php"], ["label" => "Data Pegawai"] ];
include "komponen/header.php";

// Helper Escape
function esc($c,$s){ return mysqli_real_escape_string($c, $s); }

// --- LOGIKA LINK KEMBALI ---
$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
if ($hak_akses_user === 'kepala') {
    $link_back = "home-admin.php?page=dashboard-cabang";
} else {
    $link_back = "home-admin.php";
}
?>

<style>
    /* --- General Layout --- */
    .content-wrapper { background-color: #f4f6f9; }
    
    /* --- Card Style --- */
    .card-modern {
        border: none;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .card-header-modern {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid #f4f4f4;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* --- Tabs Style --- */
    .nav-pills-modern .nav-link {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 600;
        color: #adb5bd;
        background: #f8f9fa;
        transition: all 0.3s;
        margin-right: 5px;
        font-size: 0.9rem;
    }
    .nav-pills-modern .nav-link:hover {
        background-color: #e9ecef;
        color: #007bff;
    }
    .nav-pills-modern .nav-link.active {
        background-color: #007bff;
        color: #fff;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    /* --- Input & Filter Modern --- */
    .input-modern {
        border-radius: 50px;
        border: 1px solid #e0e0e0;
        padding: 8px 20px;
        background-color: #fff;
        transition: all 0.3s;
        width: 100%;
    }
    .input-modern:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 4px rgba(0,123,255,0.1);
        outline: none;
    }

    /* --- Table Style --- */
    .table-responsive {
        border-radius: 0 0 20px 20px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table.dataTable {
        border-collapse: separate !important;
        border-spacing: 0 10px !important;
        width: 100% !important;
        margin-top: 0 !important;
    }
    table.dataTable thead th {
        border-bottom: none !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        padding: 15px;
        background: #fff;
        white-space: nowrap;
    }
    table.dataTable tbody tr {
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }
    table.dataTable tbody td {
        border: none !important;
        padding: 15px;
        vertical-align: middle;
    }
    table.dataTable tbody tr td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    table.dataTable tbody tr td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    /* --- Avatar --- */
    .avatar-wrapper { width: 45px; height: 45px; min-width: 45px; position: relative; }
    .avatar-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

    /* --- MOBILE & RESPONSIVE TWEAKS --- */
    @media (max-width: 768px) {
        /* 1. Hilangkan Header Bawaan Template agar tidak Double Title */
        .content-header { display: none !important; }
        
        /* 2. Header Page Custom */
        .header-area-mobile {
            flex-direction: column;
            align-items: flex-start !important;
            margin-top: 20px;
            gap: 10px;
        }
        .header-area-mobile .btn { width: 100%; text-align: center; }

        /* 3. Navigasi Tabs Scrollable */
        .nav-pills-modern {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        /* 4. DATA TABLES CONTROLS: 1 BARIS (Show Entries + Search) */
        .dt-controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        /* Show entries (Dropdown) - Kecil di kiri */
        .dataTables_length {
            float: none !important;
            text-align: left !important;
            width: auto !important;
        }
        .dataTables_length select {
            width: auto !important;
            padding: 8px 10px !important;
            border-radius: 50px !important;
        }
        .dataTables_length label { font-size: 0; } /* Hilangkan teks 'Show entries' */
        .dataTables_length label select { font-size: 14px; } /* Tampilkan dropdownnya saja */

        /* Search (Input) - Besar di kanan */
        .dataTables_filter {
            float: none !important;
            text-align: right !important;
            flex-grow: 1; /* Ambil sisa ruang */
        }
        .dataTables_filter label { font-size: 0; width: 100%; } /* Hilangkan teks 'Search:' */
        .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            font-size: 14px !important;
            border-radius: 50px !important;
            padding: 8px 15px !important;
        }
        
        /* Pagination Center */
        .dataTables_paginate {
            display: flex;
            justify-content: center;
            margin-top: 15px !important;
        }
    }
</style>

<section class="content" style="padding-bottom: 50px;">
  <div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4 px-1 header-area-mobile">
        <div>
            <h3 style="font-weight: 800; color: #343a40; margin-bottom: 0;">Data Pegawai</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola data pegawai aktif & purna tugas</p>
        </div>
        <a href="<?= $link_back; ?>" class="btn btn-light shadow-sm rounded-pill px-4" style="font-weight: 600; color: #666; border: 1px solid #e9ecef;">
            <i class="fa fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="card card-modern">
      <div class="card-body p-0">
        
        <div class="card-header-modern">
            <ul class="nav nav-pills nav-pills-modern" id="pegawaiTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="aktif-tab" data-bs-toggle="tab" href="#aktif" role="tab">
                        <i class="fa fa-users mr-1"></i> Aktif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="purna-tab" data-bs-toggle="tab" href="#purna" role="tab">
                        <i class="fa fa-user-clock mr-1"></i> Purna
                    </a>
                </li>
            </ul>

            <?php if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'admin'): ?>
            <div class="mt-2 mt-md-0 d-flex gap-2" style="gap:5px; width:100%; md:width:auto;">
                <a href="home-admin.php?page=form-master-data-pegawai" class="btn btn-primary rounded-pill shadow-sm btn-sm px-3 flex-fill text-center">
                    <i class="fa fa-plus-circle"></i> Tambah
                </a>
                <a href="home-admin.php?page=form-upload-data-pegawai" class="btn btn-outline-success rounded-pill btn-sm px-3 flex-fill text-center">
                    <i class="fa fa-file-excel"></i> Import
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="tab-content p-3 p-md-4" id="pegawaiTabContent">

          <div class="tab-pane fade show active" id="aktif" role="tabpanel">
            
            <div class="row mb-2">
                <div class="col-md-4 col-12">
                    <select id="filter_unit_kerja" class="form-control input-modern mb-3">
                        <option value="">-- Semua Unit Kerja --</option>
                        <?php
                            if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala') {
                                $kode_kantor = esc($conn, isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '');
                                $qUnit = mysqli_query($conn, "SELECT DISTINCT k.nama_kantor, j.unit_kerja FROM tb_jabatan j JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail WHERE j.status_jab = 'Aktif' AND j.unit_kerja = '{$kode_kantor}'");
                            } else {
                                $qUnit = mysqli_query($conn, "SELECT DISTINCT k.nama_kantor, j.unit_kerja FROM tb_jabatan j JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail WHERE j.status_jab = 'Aktif' ORDER BY k.nama_kantor ASC");
                            }
                            if ($qUnit) {
                                while ($u = mysqli_fetch_assoc($qUnit)) {
                                    echo "<option value='".htmlspecialchars($u['unit_kerja'])."'>".htmlspecialchars($u['nama_kantor'])."</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="pegawai" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>Pegawai</th> 
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

          <div class="tab-pane fade" id="purna" role="tabpanel">
            <div class="table-responsive">
                <table id="pegawaiPurna" class="table align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>TTL</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Pensiun</th>
                        </tr>
                    </thead>
                </table>
            </div>
          </div>

        </div> </div> </div> </div>
</section>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {

  // === HELPER FUNCTIONS ===
  function _getCandidateBases() {
    var origin = window.location.origin;
    return [
      origin + '/dummy/pages/assets/foto/',
      origin + '/dummy/assets/foto/',
      origin + '/pages/assets/foto/',
      origin + '/assets/foto/',
      origin + '/'
    ];
  }

  function getDefaultAvatar(name) {
    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name||'') + '&background=random&color=fff&size=128';
  }

  function tryImg(el) {
    var fileName = el.getAttribute('data-filename') || '';
    var name = el.getAttribute('data-nama') || '';
    var bases = _getCandidateBases();
    var attempt = parseInt(el.getAttribute('data-attempt') || '0', 10);

    if (!fileName) { el.onerror = null; el.src = getDefaultAvatar(name); return; }
    attempt++; el.setAttribute('data-attempt', attempt);

    if (attempt <= bases.length) {
      var candidate = bases[attempt - 1] + encodeURIComponent(fileName);
      el.onerror = function(){ tryImg(el); };
      el.src = candidate;
      return;
    }
    el.onerror = null; el.src = getDefaultAvatar(name);
  }
  window.tryImg = tryImg;
  window.getDefaultAvatar = getDefaultAvatar;

  // === DATATABLE INIT ===
  var tableAktif = $('#pegawai').DataTable({
    processing: true, serverSide: true, deferRender: true, autoWidth: false,
    ajax: {
      url: 'pages/pegawai/ajax-data-pegawai.php',
      type: 'GET',
      data: function(d){ d.unit_kerja = $('#filter_unit_kerja').val(); }
    },
    // STRUKTUR DOM AGAR 1 BARIS DI MOBILE
    // Kita bungkus Length (l) dan Filter (f) dalam div 'dt-controls-row'
    dom: '<"dt-controls-row"lf>rtip',
    columns: [
      {
        data: 'foto', orderable: false,
        render: function(data, type, row){
            var fileName = '';
            if (data) {
                var tmp = (/<img/i.test(data)) ? (data.match(/src=["'](.*?)["']/i) || [])[1] : data.trim();
                if(tmp) fileName = tmp.split('/').pop().split('?')[0];
            }
            
            var defaultImg = getDefaultAvatar(row.nama);
            var initialSrc = fileName ? (_getCandidateBases()[0] + encodeURIComponent(fileName)) : defaultImg;

            return `
              <div class="d-flex align-items-center">
                  <div class="avatar-wrapper mr-3">
                      <img src="${initialSrc}" class="avatar-img" 
                           data-filename="${fileName}" data-nama="${row.nama}" 
                           onerror="tryImg(this);">
                  </div>
                  <div>
                      <span class="d-block font-weight-bold text-dark">${row.nama || '-'}</span>
                      <span class="small text-muted">${row.id_peg || '-'}</span>
                  </div>
              </div>`;
        }
      },
      { data: 'ttl', render: function(d){ return `<span style="font-size:0.9rem;">${d||'-'}</span>`; } },
      {
        data: 'unit_kerja',
        render: function(d, t, r) {
            return `<div style="line-height:1.2;"><div class="text-primary font-weight-bold">${d||'-'}</div><div class="text-muted small">${r.jabatan||'-'}</div></div>`;
        }
      },
      { data: 'tgl_masuk', className: 'text-nowrap', render: function(d) { return `<span class="badge badge-modern badge-soft-blue">${d||'-'}</span>`; } },
      { data: 'no_telp', render: function(d){ return `<span style="color:#555;">${d||'-'}</span>`; } },
      { data: 'action', orderable: false, className: "text-center" }
    ],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    language: {
        search: "", 
        searchPlaceholder: "Cari data...", // Placeholder muncul di dalam input
        lengthMenu: "_MENU_" // Hapus tulisan 'Show entries', sisakan dropdown saja
    }
  });

  // Styling Input & Select agar modern
  $('.dataTables_filter input').addClass('form-control input-modern');
  $('.dataTables_length select').addClass('form-control input-modern');

  $('#filter_unit_kerja').on('change', function(){ tableAktif.ajax.reload(); });

  // === TABEL PURNA ===
  $('#purna-tab').on('click', function(){
    if ($.fn.DataTable.isDataTable('#pegawaiPurna')) return;
    $('#pegawaiPurna').DataTable({
      processing: true, serverSide: true, autoWidth: false,
      ajax: { url: 'pages/pegawai/ajax-pegawai-purna.php', type: 'GET' },
      dom: '<"dt-controls-row"lf>rtip',
      columns: [
        { data: 'id_peg' }, { data: 'nama' }, { data: 'ttl' }, { data: 'jabatan' }, { data: 'status_kepeg' }, { data: 'tgl_pensiun' }
      ],
      language: { search: "", searchPlaceholder: "Cari...", lengthMenu: "_MENU_" }
    });
    setTimeout(function(){
         $('#pegawaiPurna_wrapper .dataTables_filter input').addClass('form-control input-modern');
         $('#pegawaiPurna_wrapper .dataTables_length select').addClass('form-control input-modern');
    }, 100);
  });

});
</script>