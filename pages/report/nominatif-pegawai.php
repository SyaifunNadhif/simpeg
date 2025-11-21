<?php
/*********************************************************
 * FILE   : laporan-nominatif-pegawai.php
 * MODULE : Laporan Pegawai (Nominatif)
 * VERSION: v2.7 (Mobile Responsive Fix: Compact Buttons)
 * DATE   : 20 November 2025
 * AUTHOR : SIMPEG BPR BKK Jateng
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// --- LOGIKA FILTER ---
$status_kepeg = isset($_GET['status_kepeg']) ? mysqli_real_escape_string($conn, $_GET['status_kepeg']) : '';
$unit_kerja   = isset($_GET['unit_kerja']) ? mysqli_real_escape_string($conn, $_GET['unit_kerja']) : '';
$jabatan      = isset($_GET['jabatan']) ? mysqli_real_escape_string($conn, $_GET['jabatan']) : '';

$hak_akses = isset($_SESSION['hak_akses']) ? $_SESSION['hak_akses'] : '';
$kode_kantor_user = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';
$is_kepala = ($hak_akses == 'kepala');

if ($is_kepala) {
  $unit_kerja = $kode_kantor_user;
  $unit_filter_locked = true;
} else {
  $unit_filter_locked = false;
}

$where = "WHERE p.status_aktif = 1";
if ($status_kepeg != '') $where .= " AND p.status_kepeg = '$status_kepeg'";
if ($unit_kerja != '')   $where .= " AND j.unit_kerja = '$unit_kerja'";
if ($jabatan != '')      $where .= " AND j.jabatan = '$jabatan'";

// Query Data
$query = "SELECT
            p.id_peg, p.nama, p.nip,
            j.jabatan, j.tmt_jabatan,
            (SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail = j.unit_kerja) AS unit_kerja,
            p.status_kepeg,
            s.nama_sekolah, s.tgl_ijazah, s.jenjang
          FROM tb_pegawai p
          LEFT JOIN (
            SELECT j1.* FROM tb_jabatan j1
            INNER JOIN (
              SELECT id_peg, MAX(tmt_jabatan) AS tmt_max FROM tb_jabatan GROUP BY id_peg
            ) j2 ON j1.id_peg = j2.id_peg AND j1.tmt_jabatan = j2.tmt_max
          ) j ON p.id_peg = j.id_peg
          LEFT JOIN tb_pendidikan s ON p.id_peg = s.id_peg AND s.status = 'Akhir'
          $where
          ORDER BY p.nama ASC";

$result = mysqli_query($conn, $query);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

<style>
  .content-wrapper { background-color: #f4f6f9; overflow-x: hidden; }
  
  /* Card Styles */
  .card-modern {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    background: #fff;
    margin-bottom: 20px;
  }
  
  /* Table Header Style */
  table.dataTable thead th {
    background-color: #fff;
    border-bottom: 2px solid #e2e8f0 !important;
    color: #64748b;
    font-size: 0.85rem;
    text-transform: uppercase;
    padding: 15px !important;
    vertical-align: middle !important;
    white-space: nowrap;
  }

  /* Input & Select */
  .input-modern {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    height: 45px;
    font-size: 0.95rem;
    width: 100%;
    padding: 0 15px;
    background-color: #f8f9fa;
  }
  
  .select2-container--bootstrap4 .select2-selection--single {
    height: 45px !important;
    border-radius: 10px !important;
    background-color: #f8f9fa !important;
    border: 1px solid #e2e8f0 !important;
    padding-top: 8px;
  }

  /* Label Style */
  .label-filter {
    font-size: 0.75rem;
    font-weight: 700;
    color: #94a3b8;
    margin-bottom: 5px;
    text-transform: uppercase;
    display: block;
  }

  /* Buttons Style */
  .btn-modern {
    border-radius: 10px; /* Sedikit kotak agar muat di mobile */
    height: 45px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0 15px;
  }
  .btn-primary-modern { background: #3b82f6; color: white; }
  .btn-success-modern { background: #10b981; color: white; }
  .btn-secondary-modern { background: #f1f5f9; color: #64748b; }
  
  .dt-buttons { display: none !important; }

  /* Table Overflow Fix */
  .table-responsive { overflow-x: auto; }
  #nominatifTable { min-width: 1200px; }

  /* --- MOBILE SPECIFIC FIXES --- */
  @media (max-width: 768px) {
    /* Header Title lebih rapat */
    .content-header h1 { font-size: 1.5rem; }
    .content-header p { font-size: 0.85rem; }

    /* Form Group lebih rapat */
    .form-group-mobile { margin-bottom: 10px; }
    
    /* Container Tombol: Paksa Sejajar (Row) */
    .btn-action-wrapper {
        display: flex;
        flex-direction: row; /* Wajib Row */
        gap: 8px;
        width: 100%;
    }
    
    /* Tombol di Mobile: Flexible width */
    .btn-modern {
        flex: 1; /* Membagi rata lebar */
        width: auto; /* Reset width 100% */
        padding: 0 10px; /* Padding lebih kecil */
        font-size: 0.9rem;
    }
    
    /* Tombol Reset lebih kecil (kotak) */
    .btn-reset-mobile {
        flex: 0 0 45px !important; /* Lebar fix 45px */
    }

    /* Hide text di mobile untuk menghemat tempat (Optional, kalau mau muncul hapus d-none d-sm-inline) */
    .btn-text { display: inline; } 
  }
</style>

<section class="content-header pt-3 pb-2">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center header-title">
      <div>
        <h1 style="font-weight: 800; color: #1e293b; margin-bottom: 0;">Laporan Nominatif</h1>
        <p class="text-muted mb-0">Data detail pegawai aktif per unit kerja</p>
      </div>
      <div class="d-none d-md-block text-muted small"><i class="fa fa-home"></i> Home / Laporan</div>
    </div>
  </div>
</section>

<section class="content mt-2">
  <div class="container-fluid">
    <div class="card card-modern">
      
      <div class="card-body border-bottom pb-3 pt-3">
        <form method="GET" action="">
          <input type="hidden" name="page" value="nominatif">
          
          <div class="row align-items-end">
            
            <div class="col-lg-3 col-md-6 col-12 form-group-mobile">
              <span class="label-filter">Status Pegawai</span>
              <select name="status_kepeg" class="form-control input-modern">
                <option value="">-- Semua --</option>
                <option value="Tetap" <?= $status_kepeg == 'Tetap' ? 'selected' : '' ?>>Tetap</option>
                <option value="Calon Pegawai" <?= ($status_kepeg == 'Calon Pegawai' || $status_kepeg == 'Capeg') ? 'selected' : '' ?>>Calon Pegawai</option>
                <option value="Kontrak" <?= $status_kepeg == 'Kontrak' ? 'selected' : '' ?>>Kontrak</option>
                <option value="Outsource" <?= $status_kepeg == 'Outsource' ? 'selected' : '' ?>>Outsource</option>
              </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12 form-group-mobile">
              <span class="label-filter">Unit Kerja</span>
              <select name="unit_kerja" class="form-control input-modern" <?= $unit_filter_locked ? 'disabled' : '' ?> >
                <option value="">-- Semua --</option>
                <?php
                $qKantor = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY nama_kantor");
                while ($k = mysqli_fetch_array($qKantor)) {
                  $sel = ($unit_kerja == $k['kode_kantor_detail']) ? 'selected' : '';
                  echo "<option value='".$k['kode_kantor_detail']."' $sel>".$k['nama_kantor']."</option>";
                }
                ?>
              </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12 form-group-mobile">
              <span class="label-filter">Jabatan</span>
              <select name="jabatan" class="form-control select2-jabatan">
                <option value="">-- Cari Jabatan --</option>
                <?php
                $qJab = mysqli_query($conn, "SELECT DISTINCT jabatan FROM tb_jabatan ORDER BY jabatan");
                while ($j = mysqli_fetch_array($qJab)) {
                  $sel = ($jabatan == $j['jabatan']) ? 'selected' : '';
                  echo "<option value='".$j['jabatan']."' $sel>".$j['jabatan']."</option>";
                }
                ?>
              </select>
            </div>

            <div class="col-lg-3 col-md-6 col-12 form-group-mobile">
              <div class="btn-action-wrapper">
                
                <button type="submit" class="btn btn-modern btn-primary-modern">
                    <i class="fa fa-filter"></i> <span class="d-none d-sm-inline ml-1">Filter</span>
                </button>
                
                <a href="?page=nominatif" class="btn btn-modern btn-secondary-modern btn-reset-mobile" title="Reset">
                    <i class="fa fa-sync-alt text-dark"></i>
                </a>
                
                <button type="button" id="directExportExcel" class="btn btn-modern btn-success-modern">
                    <i class="fa fa-file-excel"></i> <span class="d-none d-sm-inline ml-1">Excel</span>
                </button>

              </div>
            </div>

          </div>
        </form>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="nominatifTable" class="table table-hover w-100 mb-0">
            <thead>
              <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Pegawai</th>
                <th>NIP / NIK</th>
                <th>Jabatan</th>
                <th>Unit Kerja</th>
                <th>Status</th>
                <th>TMT Jabatan</th>
                <th>Pendidikan</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 0;
              while ($row = mysqli_fetch_array($result)) {
                $no++;
                $status_lower = strtolower($row['status_kepeg']);
                $badge_class = 'badge-light';
                if($status_lower == 'tetap') $badge_class = 'badge-primary';
                elseif ($status_lower == 'calon pegawai' || $status_lower == 'capeg') $badge_class = 'badge-info';
                elseif ($status_lower == 'kontrak') $badge_class = 'badge-warning';
                
                echo "<tr>
                  <td class='text-center font-weight-bold'>$no</td>
                  <td><div style='font-weight:700; color:#334155;'>".htmlspecialchars($row['nama'])."</div></td>
                  <td><div class='text-muted' style='font-family:monospace; font-size:0.9rem;'>".htmlspecialchars($row['nip'])."</div></td>
                  <td>".htmlspecialchars($row['jabatan'])."</td>
                  <td><span class='text-primary font-weight-bold'>".htmlspecialchars($row['unit_kerja'])."</span></td>
                  <td><span class='badge $badge_class px-3 py-2 rounded-pill'>".$row['status_kepeg']."</span></td>
                  <td>".$row['tmt_jabatan']."</td>
                  <td><span class='font-weight-bold'>".$row['jenjang']."</span> - ".htmlspecialchars($row['nama_sekolah'])."</td>
                </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function () {
    $('.select2-jabatan').select2({ theme: 'bootstrap4', width: '100%', placeholder: "-- Cari --", allowClear: true });

    var table = $('#nominatifTable').DataTable({
      "paging": true, "pageLength": 10, "searching": true, "ordering": true, "info": true,
      "autoWidth": false, "responsive": false, "scrollX": true,
      "dom": 'Bfrtip',
      "buttons": [
        {
          extend: 'excelHtml5', className: 'd-none buttons-excel', title: 'Laporan Nominatif Pegawai',
          exportOptions: { columns: ':visible', format: { body: function (data) { return data.replace(/<\/?[^>]+(>|$)/g, ""); } } }
        }
      ],
      "language": { "search": "", "searchPlaceholder": "Cari data...", "zeroRecords": "Data tidak ditemukan" }
    });

    $('.dataTables_filter input').addClass('form-control input-modern').css({'width':'100%', 'min-width':'200px'});
    $('.dataTables_length').hide();

    $('#directExportExcel').on('click', function() { table.button('.buttons-excel').trigger(); });
  });
</script>