<?php
$page_title = "Data";
$page_subtitle = "Diklat Pegawai";
$breadcrumbs = [
  ["label" => "Dashboard", "url" => "home-admin.php"],
  ["label" => "Data Diklat Pegawai"]
];
include "komponen/header.php";
// master-data-diklat.php
include 'dist/koneksi.php';
include 'dist/library.php'; // jika ada fungsi format tanggal, dsb

$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$kantor = isset($_GET['kantor']) ? $_GET['kantor'] : '';

// Query filter tahun & kantor
$where = "1=1";
if ($tahun != '') {
	$where .= " AND d.tahun = '$tahun'";
}
if ($kantor != '') {
	$where .= " AND j.unit_kerja = '$kantor'";
}

$query = "
SELECT d.*, p.nama, j.jabatan, j.unit_kerja, k.nama_kantor
FROM tb_diklat d
JOIN tb_pegawai p ON d.id_peg = p.id_peg
LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg
LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
WHERE $where
ORDER BY d.tahun DESC, d.date_reg DESC
";
$result = mysqli_query($conn, $query);

// Ambil data filter dropdown
$qTahun = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat ORDER BY tahun DESC");
$qKantor = mysqli_query($conn, "SELECT * FROM tb_kantor WHERE level = 'KC' ORDER BY nama_kantor ASC");
?>


<style>
#tabelDiklat th, #tabelDiklat td {
  font-size: 0.85rem; /* kecil tapi tetap terbaca */
  vertical-align: middle;
}
.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
  font-size: 0.85rem;
  padding: 3px 6px;
}
</style>

<div class="card">
	<div class="card-header bg-primary text-white">
		<h4 class="card-title">Data Riwayat Diklat Pegawai</h4>
	</div>
	<div class="card-body">

		<!-- FILTER -->
		<form method="GET" action="home-admin.php" class="form-inline mb-3">
			<input type="hidden" name="page" value="master-data-diklat">
			<label class="mr-2">Tahun:</label>
			<select name="tahun" class="form-control mr-3">
				<option value="">Semua Tahun</option>
				<?php while ($row = mysqli_fetch_assoc($qTahun)) { ?>
					<option value="<?= $row['tahun'] ?>" <?= ($tahun == $row['tahun']) ? 'selected' : '' ?>>
						<?= $row['tahun'] ?>
					</option>
				<?php } ?>
			</select>

			<label class="mr-2">Kantor:</label>
			<select name="kantor" class="form-control mr-3">
				<option value="">Semua Kantor</option>
				<?php while ($row = mysqli_fetch_assoc($qKantor)) { ?>
					<option value="<?= $row['kode_kantor_detail'] ?>" <?= ($kantor == $row['kode_kantor_detail']) ? 'selected' : '' ?>>
						<?= $row['nama_kantor'] ?>
					</option>
				<?php } ?>
			</select>

			<button type="submit" class="btn btn-sm btn-secondary">Filter</button>
			<a href="master-data-diklat.php" class="btn btn-sm btn-outline-secondary ml-2">Reset</a>
		</form>

		<!-- TOMBOL -->
		<div class="mb-3">
			<a href="home-admin.php?page=form-diklat" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Data</a>
			<a href="home-admin.php?page=import-diklat" class="btn btn-outline-primary btn-sm">
				<i class="fa fa-upload"></i> Upload Excel
			</a>
			<a href="export-diklat.php?tahun=<?= $tahun ?>&kantor=<?= $kantor ?>" class="btn btn-outline-info btn-sm">
				<i class="fa fa-file-excel"></i> Export Excel
			</a>
		</div>

		<!-- DATATABLE -->
		<div class="table-responsive">
			<table class="table table-hover table-bordered" id="tabelDiklat">
				<thead class="thead-light">
					<tr>
						<th>No</th>
						<th>Nama Pegawai</th>
						<th>Diklat</th>
						<th>Tahun</th>
						<th>Penyelenggara</th>
						<th>Tempat</th>
						<th>Kantor</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php $no = 1;
					while ($row = mysqli_fetch_assoc($result)) { ?>
						<tr>
							<td><?= $no++ ?></td>
							<td><?= $row['nama'] ?></td>
							<td><?= $row['diklat'] ?></td>
							<td><?= $row['tahun'] ?></td>
							<td><?= $row['penyelenggara'] ?></td>
							<td><?= $row['tempat'] ?></td>
							<td><?= $row['nama_kantor'] ?></td>
							<td>
								<a href="home-admin.php?page=form-diklat&id=<?= $row['id_diklat'] ?>" class="btn btn-sm btn-outline-warning"><i class="fa fa-edit"></i></a>
								<a href="proses-diklat.php?act=hapus&id=<?= $row['id_diklat'] ?>" class="btn btn-sm btn-outline-danger btn-hapus"><i class="fa fa-trash"></i></a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function () {
  $('#tabelDiklat').DataTable({
    "pageLength": 10,
    "lengthChange": true,
    "ordering": true,
    "searching": true,
    "responsive": true,
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
    }
  });

  $('.btn-hapus').on('click', function (e) {
    e.preventDefault();
    let link = $(this).attr('href');
    Swal.fire({
      title: 'Yakin ingin menghapus?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = link;
      }
    });
  });
});
</script>
