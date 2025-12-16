<?php
include "dist/koneksi.php";
include "dist/library.php";

$tahun = date('Y');

// Query Logic (Tetap)
$query = mysqli_query($conn, "
  SELECT 
    id_peg, nama, jk, foto,
    (SELECT jabatan FROM tb_jabatan WHERE id_peg=a.id_peg AND status_jab='Aktif' LIMIT 1) AS jabatan,
    tempat_lhr, tgl_pensiun,
    DATEDIFF(tgl_pensiun, CURDATE()) AS selisih_hari
  FROM tb_pegawai a
  WHERE 
    id_peg NOT IN ('101-001','101-002','101-003','101-004','101-005','101-007','101-008')
    AND YEAR(tgl_pensiun) = YEAR(NOW())
  ORDER BY
    CASE
      WHEN DATEDIFF(tgl_pensiun, CURDATE()) BETWEEN 0 AND 30 THEN 1
      ELSE 2
    END,
    tgl_pensiun ASC
");
?>

<style>
/* ================= CSS MODERN FINAL ================= */
.font-primary { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }

/* 1. CARD STYLE */
.card-modern {
  border: none;
  border-radius: 20px; /* Radius lebih bulat biar modern */
  background: #fff;
  box-shadow: 0 10px 40px rgba(0,0,0,0.04);
  margin-bottom: 2rem;
  overflow: hidden;
}

/* 2. HEADER STYLE (Search Kanan Mentok) */
.card-modern .card-header {
  background: #fff;
  border-bottom: 1px solid #f0f2f5;
  padding: 30px 40px; /* Padding Header Lega */
  display: flex;
  align-items: center;
  flex-wrap: wrap; 
  gap: 20px;
}

.title-group {
  margin-right: auto;
}
.title-group h3 {
  font-size: 1.35rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 5px;
  letter-spacing: -0.5px;
}
.title-group p {
  font-size: 0.9rem;
  color: #95a5a6;
  margin-bottom: 0;
}

/* SEARCH BOX */
.header-search {
  position: relative;
  width: 300px; /* Sedikit dilebarkan */
  margin-left: auto;
}
.header-search input {
  width: 100%;
  border-radius: 50px;
  border: 1px solid #edf2f7;
  background: #f8f9fa;
  padding: 12px 25px 12px 50px;
  font-size: 0.95rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  height: 48px;
}
.header-search input:focus {
  background: #fff;
  border-color: #17a2b8;
  box-shadow: 0 5px 20px rgba(23, 162, 184, 0.1);
  outline: none;
}
.header-search i {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: #a0aec0;
  font-size: 1.1rem;
}

/* 3. TABLE STYLE */
.table-modern thead th {
  background-color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #8392a5;
  border-bottom: 2px solid #f0f2f5;
  padding: 25px 40px; /* Padding Table Head Lega */
  letter-spacing: 0.5px;
}
.table-modern tbody td {
  padding: 25px 40px; /* Padding Isi Table Lega */
  vertical-align: middle;
  border-bottom: 1px solid #f8f9fa;
  font-size: 1rem;
  color: #343a40;
}
.table-modern tbody tr:last-child td { border-bottom: none; }
.table-modern tbody tr:hover { background-color: #fafbfc; }

/* ASSETS */
.avatar { width: 50px; height: 50px; border-radius: 14px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.avatar-placeholder { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #ff6b6b, #ee5253); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; }
.badge-soft { padding: 8px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.3px; }
.badge-soft-danger { background: #fff0f0; color: #ff4757; }
.badge-soft-success { background: #e8f8eb; color: #2ecc71; }

/* 4. PAGINATION FIX (EXTRA PADDING & JARAK) */
.dataTables_wrapper .row:last-child {
  /* UPDATE PENTING DISINI: Padding diperbesar */
  padding: 30px 40px 45px 40px !important; /* Atas 30, Kanan 40, Bawah 45, Kiri 40 */
  margin: 0;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  border-top: 1px solid #f8f9fa; /* Garis tipis pemisah dengan tabel */
}

/* Reset CSS */
.dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; background: transparent !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button:hover { border: none !important; background: transparent !important; }

/* Tombol Style */
.page-item .page-link { 
  border: none; 
  width: 45px; height: 45px; /* Tombol lebih besar */
  margin-left: 10px; /* Jarak antar tombol lebih renggang */
  border-radius: 12px !important; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  font-weight: 600; 
  font-size: 0.95rem; 
  color: #636e72 !important; 
  background: #f1f2f6 !important; 
  transition: all 0.25s ease;
}

.page-item:not(.active) .page-link:hover { 
  background-color: #dbeeff !important; 
  color: #17a2b8 !important; 
  transform: translateY(-3px); /* Efek naik dikit pas hover */
}

.page-item.active .page-link { 
  background: #17a2b8 !important; 
  color: #fff !important; 
  box-shadow: 0 8px 20px rgba(23,162,184,0.25) !important; 
}
</style>

<div class="card card-modern">
  <div class="card-header">
    <div class="title-group">
      <h3>Daftar Pensiun <?= $tahun ?></h3>
      <p>Monitoring pegawai purna tugas tahun ini</p>
    </div>

    <div class="header-search">
      <i class="fas fa-search"></i>
      <input type="text" id="customSearch" placeholder="Cari Pegawai...">
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="tabelPensiun" class="table table-modern w-100">
        <thead>
          <tr>
            <th width="35%">Pegawai</th>
            <th>Jabatan</th>
            <th>Status</th>
            <th>Tgl Pensiun</th>
            <th class="text-right">Countdown</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($query)){
          $hari=(int)$r['selisih_hari'];
          if($hari>0){
            $badge='badge-soft-danger'; $status='Segera Pensiun'; $count=$hari.' Hari Lagi'; $countCls='text-danger font-weight-bold';
          }else{
            $badge='badge-soft-success'; $status='Sudah Lewat'; $count='Selesai'; $countCls='text-muted';
          }
          $foto='pages/assets/foto/'.$r['foto'];
          $fotoAda=!empty($r['foto']) && file_exists($foto);
        ?>
          <tr>
            <td>
              <div class="d-flex align-items-center">
                <div class="mr-3">
                  <?php if($fotoAda){ ?><img src="<?= $foto ?>" class="avatar"><?php }else{ ?><div class="avatar-placeholder"><?= strtoupper(substr($r['nama'],0,1)) ?></div><?php } ?>
                </div>
                <div>
                  <div style="font-weight:700;color:#2d3436;font-size:1rem;margin-bottom:3px;"><?= $r['nama'] ?></div>
                  <small style="color:#b2bec3;font-size:0.8rem;font-weight:500;">ID: <?= $r['id_peg'] ?></small>
                </div>
              </div>
            </td>
            <td><span style="font-weight:500;color:#636e72;"><?= $r['jabatan'] ?></span></td>
            <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
            <td>
              <div style="line-height:1.4">
                <strong style="color:#2d3436;font-size:0.95rem;"><?= Indonesia2Tgl($r['tgl_pensiun']) ?></strong><br>
                <small class="text-muted"><?= $r['tempat_lhr'] ?></small>
              </div>
            </td>
            <td class="text-right <?= $countCls ?>"><?= $count ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function(){
  if ($.fn.DataTable.isDataTable('#tabelPensiun')) { $('#tabelPensiun').DataTable().destroy(); }

  var table = $('#tabelPensiun').DataTable({
    dom: 'tp',
    paging: true,
    pageLength: 5,
    ordering: true,
    autoWidth: false,
    info: false,
    order: [],
    columnDefs: [{ orderable: false, targets: [0, 2, 4] }],
    language: {
      zeroRecords: 'Tidak ada data ditemukan',
      paginate: { next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
    },
    drawCallback: function() {
      // Pastikan alignment ke kanan
      $('.dataTables_paginate > .pagination').addClass('justify-content-end');
    }
  });

  $('#customSearch').on('keyup', function(){
    table.search(this.value).draw();
  });
});
</script>