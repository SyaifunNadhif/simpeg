<?php
/*********************************************************
 * FILE     : pages/pegawai/form-view-data-pegawai.php
 * MODULE   : SIMPEG — Data Pegawai (Modern UI Fix)
 * VERSION  : v3.0 (Final Fix Path Foto)
 * DATE     : 2025-11-19
 *********************************************************/

// Header & breadcrumbs
$page_title    = "Data";
$page_subtitle = "Pegawai";
$breadcrumbs   = [ ["label" => "Dashboard", "url" => "home-admin.php"], ["label" => "Data Pegawai"] ];
include "komponen/header.php";

// Koneksi & Helper
function esc($c,$s){ return mysqli_real_escape_string($c, $s); }

// --- LOGIKA LINK KEMBALI ---
$hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$link_back = ($hak_akses_user === 'kepala') ? "home-admin.php?page=dashboard-cabang" : "home-admin.php";
?>

<style>
    .content-wrapper { background-color: #f4f6f9; }
    
    /* Card Style */
    .card-modern {
        border: none; border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff;
    }
    .card-header-modern { padding: 20px 25px; background: transparent; border-bottom: 1px solid #f1f1f1; }

    /* Tabs Style */
    .nav-pills-modern .nav-link {
        border-radius: 50px; padding: 8px 25px; font-weight: 600;
        color: #95a5a6; background: transparent; margin-right: 5px;
        transition: all 0.3s;
    }
    .nav-pills-modern .nav-link.active {
        background-color: #007bff; color: #fff;
        box-shadow: 0 4px 10px rgba(0,123,255,0.3);
    }

    /* Filter Style */
    .input-group-round {
        background-color: #f8f9fa; border-radius: 50px;
        padding: 5px 20px; border: 1px solid #e9ecef;
    }
    .input-group-round select { border: none; background: transparent; height: auto; color: #555; font-weight: 500; }
    .input-group-round select:focus { outline: none; box-shadow: none; }

    /* Table Style */
    table.dataTable { border-collapse: separate !important; border-spacing: 0 10px !important; margin-top: 15px !important; }
    table.dataTable thead th {
        border-bottom: none !important; font-size: 0.75rem; text-transform: uppercase;
        letter-spacing: 1px; color: #aaa; padding: 15px;
    }
    table.dataTable tbody tr {
        background-color: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: transform 0.2s; border-radius: 10px;
    }
    table.dataTable tbody tr:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    table.dataTable tbody td { border: none !important; padding: 15px; vertical-align: middle; }
    
    /* Rounded Corners for Row */
    table.dataTable tbody tr td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    table.dataTable tbody tr td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

    /* Avatar & Text */
    .avatar-wrapper { width: 45px; height: 45px; min-width: 45px; position: relative; }
    .avatar-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid #f4f4f4; }
    .text-name { font-weight: 700; color: #34495e; display: block; font-size: 0.95rem; }
    .text-sub { font-size: 0.8rem; color: #95a5a6; }
    .text-unit { font-weight: 600; color: #007bff; font-size: 0.85rem; }
    .badge-date { background: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
</style>

<section class="content" style="margin-top: 30px; padding-bottom: 50px;">
  <div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-dark mb-0">Direktori Pegawai</h3>
            <p class="text-muted mb-0 small">Kelola data pegawai aktif & purna tugas</p>
        </div>
        <a href="<?= $link_back; ?>" class="btn btn-white shadow-sm rounded-pill px-4 text-secondary">
            <i class="fa fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="card card-modern">
      
      <div class="card-header-modern d-flex flex-column flex-md-row justify-content-between align-items-center">
        <ul class="nav nav-pills nav-pills-modern mb-3 mb-md-0" id="pegawaiTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="aktif-tab" data-bs-toggle="tab" href="#aktif" role="tab">
                    <i class="fa fa-users mr-1"></i> Pegawai Aktif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="purna-tab" data-bs-toggle="tab" href="#purna" role="tab">
                    <i class="fa fa-user-clock mr-1"></i> Pegawai Purna
                </a>
            </li>
        </ul>

        <?php if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'admin'): ?>
        <div>
            <a href="home-admin.php?page=form-master-data-pegawai" class="btn btn-primary btn-sm rounded-pill px-3 mr-1 shadow-sm">
                <i class="fa fa-plus mr-1"></i> Tambah
            </a>
            <a href="home-admin.php?page=form-upload-data-pegawai" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="fa fa-file-excel mr-1"></i> Import
            </a>
        </div>
        <?php endif; ?>
      </div>

      <div class="card-body px-4 pb-5">
        <div class="tab-content">
          
          <div class="tab-pane fade show active" id="aktif" role="tabpanel">
            
            <div class="row mb-4 mt-2">
              <div class="col-md-4">
                <div class="input-group-round d-flex align-items-center">
                    <i class="fa fa-filter text-muted mr-2"></i>
                    <select id="filter_unit_kerja" class="form-control w-100">
                      <option value="">-- Semua Unit Kerja --</option>
                      <?php
                        // Logic Filter
                        if (isset($_SESSION['hak_akses']) && strtolower($_SESSION['hak_akses']) === 'kepala') {
                          $kode_kantor = esc($conn, isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '');
                          $sqlUnit = "SELECT DISTINCT k.nama_kantor, j.unit_kerja FROM tb_jabatan j JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail WHERE j.status_jab = 'Aktif' AND j.unit_kerja = '{$kode_kantor}'";
                        } else {
                          $sqlUnit = "SELECT DISTINCT k.nama_kantor, j.unit_kerja FROM tb_jabatan j JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail WHERE j.status_jab = 'Aktif' ORDER BY k.nama_kantor ASC";
                        }
                        $qUnit = mysqli_query($conn, $sqlUnit);
                        if ($qUnit) {
                          while ($rUnit = mysqli_fetch_assoc($qUnit)) {
                            echo "<option value='".$rUnit['unit_kerja']."'>".$rUnit['nama_kantor']."</option>";
                          }
                        }
                      ?>
                    </select>
                </div>
              </div>
            </div>

            <div class="table-responsive">
                <table id="pegawai" class="table align-middle" style="width:100%">
                  <thead>
                    <tr>
                      <th>Pegawai</th> <th>TTL</th>
                      <th>Unit & Jabatan</th>
                      <th>Mulai Kerja</th>
                      <th>Kontak</th>
                      <th class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
            </div>
          </div>

          <div class="tab-pane fade" id="purna" role="tabpanel">
            <div class="table-responsive mt-3">
                <table id="pegawaiPurna" class="table align-middle" style="width:100%">
                  <thead>
                    <tr>
                      <th>ID Pegawai</th>
                      <th>Nama</th>
                      <th>TTL</th>
                      <th>Jabatan</th>
                      <th>Jenis Mutasi</th>
                      <th>Tgl. Mutasi</th>
                    </tr>
                  </thead>
                </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
  
  // ======= TABEL PEGAWAI AKTIF =======
  var tableAktif = $('#pegawai').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: {
      url: 'pages/pegawai/ajax-data-pegawai.php',
      type: 'GET',
      data: function(d){
        d.unit_kerja = $('#filter_unit_kerja').val();
      }
    },
    columns: [
      // KOLOM 1: Foto + Nama + ID (Digabung)
      { 
        data: 'foto', orderable: false,
        render: function(data, type, row){
            // LOGIKA PATH FOTO (FIXED)
            var fileName = '';
            
            // Jika data dari server berupa tag <img ...>, kita ambil nama filenya saja (bersihkan tagnya)
            // atau jika data bersih, langsung pakai.
            if (data && typeof data === 'string') {
                // Regex untuk membuang tag HTML kalau ada
                var cleanData = data.replace(/<[^>]*>?/gm, '').trim();
                fileName = cleanData;
            }

            // PATH YANG BENAR SESUAI SCREENSHOT ANDA
            var path = fileName ? ('pages/assets/foto/' + fileName) : 'assets/img/user.png';
            var cb   = fileName ? ('?cb=' + new Date().getTime()) : ''; // Cache buster

            return `
              <div class="d-flex align-items-center">
                  <div class="avatar-wrapper mr-3">
                      <img class="avatar-img" src="${path}${cb}" 
                           alt="Foto" onerror="this.onerror=null; this.src='assets/img/user.png';">
                  </div>
                  <div>
                      <span class="text-name">${row.nama || 'Tanpa Nama'}</span>
                      <span class="text-sub"><i class="fa fa-id-badge mr-1"></i> ${row.id_peg || '-'}</span>
                  </div>
              </div>
            `;
        }
      },
      // KOLOM 2: TTL
      { data: 'ttl', render: function(d){ return `<span class="text-sub text-dark">${d || '-'}</span>`; } },
      
      // KOLOM 3: Unit & Jabatan
      { 
        data: 'unit_kerja',
        render: function(data, type, row) {
            return `
                <div>
                    <div class="text-unit mb-1">${data || '-'}</div>
                    <div class="text-sub text-muted" style="line-height:1.2;">${row.jabatan || '-'}</div>
                </div>
            `;
        }
      },
      
      // KOLOM 4: Mulai Kerja
      { 
          data: 'tgl_masuk', className: 'text-nowrap',
          render: function(data) { return `<span class="badge-date">${data || '-'}</span>`; }
      },
      
      // KOLOM 5: Kontak
      { data: 'no_telp', render: function(d){ return `<span class="text-sub">${d || '-'}</span>`; } },
      
      // KOLOM 6: Aksi
      { data: 'action', orderable: false, className: "text-center" }
    ],
    pageLength: 10,
    lengthMenu: [[10,25,50,-1],[10,25,50,'Semua']],
    language: {
        search: "", searchPlaceholder: "Cari Pegawai...",
        paginate: { previous: "<", next: ">" },
        processing: "Memuat Data..."
    }
  });

  // Style Kolom Pencarian agar Modern
  $('.dataTables_filter input').addClass('form-control form-control-sm input-group-round')
     .css({'width':'250px','padding':'18px 20px', 'margin-left':'10px'});

  // Reload Tabel saat filter ganti
  $('#filter_unit_kerja').on('change', function(){ tableAktif.ajax.reload(); });

  // ======= TABEL PURNA =======
  var purnaInitialized = false;
  $('#purna-tab').on('click', function(){
    if (purnaInitialized) return;
    $('#pegawaiPurna').DataTable({
      processing: true, serverSide: true, deferRender: true,
      ajax: { url: 'pages/pegawai/ajax-pegawai-purna.php', type: 'GET' },
      columns: [
        { data: 'id_peg' },
        { data: 'nama', render: function(d){ return `<b>${d}</b>`; } },
        { data: 'ttl' },
        { data: 'jabatan' },
        { data: 'status_kepeg', render: function(d){ return `<span class="badge badge-danger">${d}</span>`; } },
        { data: 'tgl_pensiun' }
      ],
      language: { search: "", searchPlaceholder: "Cari Purna..." }
    });
    // Style search purna juga
    setTimeout(function(){
        $('#pegawaiPurna_filter input').addClass('form-control form-control-sm input-group-round')
          .css({'width':'250px','padding':'18px 20px'});
    }, 200);
    purnaInitialized = true;
  });
});
</script>