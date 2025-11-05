<?php
// File: formasi.php
// Versi: 2.0 - Formasi Berdasarkan tb_ref_jabatan + lingkup + kode_cabang

include "dist/koneksi.php";

$kode_cabang = isset($_GET['kode_cabang']) ? mysqli_real_escape_string($conn, $_GET['kode_cabang']) : '';
$filter_cabang = ($kode_cabang != '') ? "AND j.unit_kerja = '$kode_cabang'" : '';
$filter_lingkup = ($kode_cabang != '') ? "WHERE r.lingkup = 'KC'" : "";

$query = "SELECT 
            r.jabatan,
            r.kuota,
            COUNT(p.id_peg) AS terisi,
            (r.kuota - COUNT(p.id_peg)) AS kosong
          FROM tb_ref_jabatan r
          LEFT JOIN tb_jabatan j ON j.jabatan = r.jabatan $filter_cabang
          LEFT JOIN tb_pegawai p ON p.id_peg = j.id_peg AND p.status_aktif = 1
          $filter_lingkup
          GROUP BY r.jabatan, r.kuota
          ORDER BY r.jabatan";

$result = mysqli_query($conn, $query);
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Laporan Formasi Pegawai</h1>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card">
    <div class="card-header">
      <form method="GET" action="">
        <div class="form-row align-items-end">
          <div class="col-md-4">
            <label>Pilih Kantor Cabang</label>
            <select name="kode_cabang" class="form-control">
              <option value="">-- Semua Kantor --</option>
              <?php
              $qc = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor WHERE level = 'KC' ORDER BY nama_kantor");
              while ($c = mysqli_fetch_array($qc)) {
                $sel = ($kode_cabang == $c['kode_kantor_detail']) ? 'selected' : '';
                echo "<option value='".$c['kode_kantor_detail']."' $sel>".$c['nama_kantor']."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-4">
            <label>&nbsp;</label>
            <div class="btn-group d-flex">
              <button type="submit" class="btn btn-primary w-33">Terapkan</button>
              <a href="pages/report/print-formasi-pegawai.php?<?= http_build_query($_GET); ?>" target="_blank" class="btn btn-success w-33">Cetak</a>
              <a href="pages/report/export-formasi-pegawai.php?<?= http_build_query($_GET); ?>" class="btn btn-warning w-33">Excel</a>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-light">
          <tr>
            <th>No</th>
            <th>Jabatan</th>
            <th>Kuota</th>
            <th>Terisi</th>
            <th>Kosong</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          while ($row = mysqli_fetch_array($result)) {
            $ket = ($row['kosong'] == 0) ? '<span class="text-success">Terpenuhi</span>' : '<span class="text-danger">Perlu Penempatan</span>';
            echo "<tr>
              <td align='center'>".$no++."</td>
              <td>".$row['jabatan']."</td>
              <td align='center'>".$row['kuota']."</td>
              <td align='center'>".$row['terisi']."</td>
              <td align='center'>".$row['kosong']."</td>
              <td>".$ket."</td>
            </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
