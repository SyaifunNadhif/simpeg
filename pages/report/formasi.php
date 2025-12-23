<?php
// File: formasi.php
// Versi: 2.2 - Source: tb_master_jabatan (Modern UI)

include "dist/koneksi.php";

// --- LOGIC PHP ---
$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';

// Filter Cabang (Untuk Join ke data real pegawai)
$filter_cabang = ($kode_cabang != '') ? "AND j.unit_kerja = '$kode_cabang'" : '';

// Filter Lingkup Master Jabatan (Jika di master ada kolom 'lingkup', jika tidak ada silakan hapus baris ini)
// Asumsi: Struktur master mirip ref, ada pembeda mana jabatan Cabang (KC) mana Pusat.
$filter_lingkup = ($kode_cabang != '') ? "WHERE m.lingkup = 'KC'" : ""; 

// --- UPDATE QUERY: Menggunakan tb_master_jabatan ---
$query = "SELECT 
            m.nama_jabatan, 
            m.kuota,
            COUNT(p.id_peg) AS terisi,
            (m.kuota - COUNT(p.id_peg)) AS kosong
          FROM tb_master_jabatan m
          -- Join berdasarkan nama_jabatan
          LEFT JOIN tb_jabatan j ON j.jabatan = m.nama_jabatan $filter_cabang
          -- Join ke Pegawai Aktif saja
          LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = 1
          
          $filter_lingkup
          
          GROUP BY m.nama_jabatan, m.kuota
          ORDER BY m.nama_jabatan";

$result = mysqli_query($conn, $query);

// Cek error query jika nama kolom beda
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    .modern-wrapper {
        font-family: 'Poppins', sans-serif;
        color: #444;
        font-size: 0.9rem;
    }

    /* Card Styling */
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        background: #fff;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .card-modern-header {
        padding: 20px 25px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Table Styling */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern thead th {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: white;
        padding: 15px;
        font-weight: 500;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .table-modern tbody tr {
        transition: background 0.2s;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8faff;
    }

    .table-modern td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }

    /* Badges & Elements */
    .badge-modern {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .bg-soft-success { background: #e8f5e9; color: #2e7d32; }
    .bg-soft-danger { background: #ffebee; color: #c62828; }
    .bg-soft-warning { background: #fff8e1; color: #f57f17; } 

    .progress-thin {
        height: 6px;
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 5px;
        width: 100px;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
    }

    /* Form Elements */
    .form-control-modern {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    .form-control-modern:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
    }

    .btn-modern {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        font-size: 0.9rem;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .btn-modern:hover { transform: translateY(-2px); }
    
    .btn-primary-m { background: #2a5298; color: white; }
    .btn-success-m { background: #00c853; color: white; }
    .btn-warning-m { background: #ffb300; color: #fff; }
</style>

<div class="modern-wrapper">
    <section class="content-header" style="padding: 15px 0;">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 style="font-weight: 700; color: #2c3e50; font-size: 1.5rem;">
                <i class="fas fa-chart-pie mr-2 text-primary"></i> Laporan Formasi Pegawai
            </h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card-modern">
        <div class="card-modern-header">
            <h5 class="m-0 font-weight-bold text-secondary">Filter Data</h5>
        </div>
        <div class="card-body">
          <form method="GET" action="">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label style="font-weight: 500;">Pilih Kantor Cabang</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-building text-muted"></i></span>
                    </div>
                    <select name="kode_cabang" class="form-control form-control-modern border-left-0 pl-0">
                      <option value="">-- Semua Kantor / Global --</option>
                      <?php
                      $qc = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level = 'KC' ORDER BY nama_kantor");
                      while ($c = mysqli_fetch_array($qc)) {
                        $sel = ($kode_cabang == $c['kode_kantor_detail']) ? 'selected' : '';
                        echo "<option value='".$c['kode_kantor_detail']."' $sel>".$c['nama_kantor']."</option>";
                      }
                      ?>
                    </select>
                </div>
              </div>
              <div class="col-md-8">
                 <div class="d-flex gap-2" style="gap: 10px;">
                     <button type="submit" class="btn btn-modern btn-primary-m">
                         <i class="fas fa-filter"></i> Terapkan
                     </button>
                     <a href="pages/report/print-formasi-pegawai.php?<?= http_build_query($_GET); ?>" target="_blank" class="btn btn-modern btn-success-m">
                         <i class="fas fa-print"></i> Cetak
                     </a>
                     <a href="pages/report/export-formasi-pegawai.php?<?= http_build_query($_GET); ?>" class="btn btn-modern btn-warning-m">
                         <i class="fas fa-file-excel"></i> Excel
                     </a>
                 </div>
              </div>
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
                <th width="35%">Jabatan</th>
                <th width="10%" class="text-center">Kuota</th>
                <th width="15%" class="text-center">Terisi</th>
                <th width="10%" class="text-center">Sisa</th>
                <th width="25%">Status Formasi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              if (mysqli_num_rows($result) > 0) {
                  while ($row = mysqli_fetch_array($result)) {
                    // FIELD DISESUAIKAN DENGAN TABLE MASTER
                    $nama_jabatan = $row['nama_jabatan']; 
                    $kuota = $row['kuota'];
                    $terisi = $row['terisi'];
                    $kosong = $row['kosong'];
                    
                    // Hitung Persentase untuk visual bar
                    $persen = ($kuota > 0) ? round(($terisi / $kuota) * 100) : 0;
                    if($persen > 100) $persen = 100; 

                    // Logic Status Modern
                    if ($kosong > 0) {
                        $badgeClass = "bg-soft-danger";
                        $icon = "fa-exclamation-circle";
                        $text = "Kurang $kosong";
                        $barColor = "bg-danger";
                    } elseif ($kosong < 0) {
                        $over = abs($kosong);
                        $badgeClass = "bg-soft-warning";
                        $icon = "fa-users";
                        $text = "Over $over";
                        $barColor = "bg-warning";
                    } else {
                        $badgeClass = "bg-soft-success";
                        $icon = "fa-check-circle";
                        $text = "Terpenuhi";
                        $barColor = "bg-success";
                    }
                    ?>
                    <tr>
                      <td align="center" style="font-weight: 600; color:#888;"><?= $no++ ?>.</td>
                      
                      <td style="font-weight: 500; color: #2c3e50;">
                          <?= $nama_jabatan ?>
                      </td>

                      <td align="center">
                          <span style="background:#eee; padding:5px 10px; border-radius:5px; font-weight:bold;">
                              <?= $kuota ?>
                          </span>
                      </td>

                      <td align="center">
                          <div style="font-weight:bold; color:#444;"><?= $terisi ?></div>
                          <div class="progress-thin mx-auto" title="Terisi <?= $persen ?>%">
                              <div class="progress-bar-custom <?= $barColor ?>" style="width: <?= $persen ?>%"></div>
                          </div>
                      </td>

                      <td align="center" style="font-size:1.1rem; font-weight:bold; color: #555;">
                          <?= ($kosong < 0) ? '+' . abs($kosong) : $kosong ?>
                      </td>

                      <td>
                          <div class="badge-modern <?= $badgeClass ?>">
                              <i class="fas <?= $icon ?>"></i> <?= $text ?>
                          </div>
                      </td>
                    </tr>
                    <?php
                  }
              } else {
                  echo "<tr><td colspan='6' class='text-center py-4 text-muted font-italic'>Data formasi belum tersedia.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
</div>