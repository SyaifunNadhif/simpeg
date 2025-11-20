<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Data<small> Mutasi Pegawai</small></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
          <li class="breadcrumb-item active">Data Mutasi Pegawai</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<?php
    include "dist/koneksi.php";
    include "dist/library.php";

    // --- LOGIKA LINK KEMBALI (FIXED) ---
    $hak_akses_user = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
    
    if ($hak_akses_user === 'admin') {
        $link_back = "home-admin.php?page=dashboard-cabang";
    } else {
        $link_back = "home-admin.php";
    }

    // --- UPDATE KE MYSQLI ---
    // Menggunakan mysqli_query dan variabel $conn dari dist/koneksi.php
    $sql = "SELECT tb_mutasi.*, 
            (SELECT nama FROM tb_pegawai WHERE id_peg=tb_mutasi.id_peg) as nama_peg
            FROM tb_mutasi 
            -- WHERE YEAR(tgl_mutasi) = YEAR(NOW())
            ORDER BY tgl_mutasi DESC";
            
    $tampilMutasi = mysqli_query($conn, $sql);

    // Cek error query jika ada
    if (!$tampilMutasi) {
        die("Error query: " . mysqli_error($conn));
    }
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">               
                    <div class="card-body">                          
                        <div class="d-flex justify-content-between mb-3">
                            <a href="home-admin.php?page=form-master-data-mutasi" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Tambah Data Mutasi
                            </a>
                            <a href="<?= $link_back ?>" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                        
                        <table id="mutasi" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Pegawai</th>
                                    <th>Jabatan</th>
                                    <th>Jenis Mutasi</th>
                                    <th>Tanggal SK Mutasi</th>
                                    <th>No SK</th>
                                    <th>TMT</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                // --- UPDATE KE MYSQLI FETCH ---
                                while($Mutasi = mysqli_fetch_array($tampilMutasi)){
                            ?>  
                                <tr>
                                    <td><?php echo $Mutasi['nama_peg'];?></td>
                                    <td><?php echo $Mutasi['jabatan'];?></td>
                                    <td><?php echo $Mutasi['jns_mutasi'];?></td>
                                    <td><?php echo Indonesia2Tgl($Mutasi['tgl_mutasi']);?></td>
                                    <td><?php echo $Mutasi['no_mutasi'];?></td>
                                    <td><?php echo Indonesia2Tgl($Mutasi['tmt']);?></td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-info" href="home-admin.php?page=view-detail-data-pegawai&id_peg=<?=$Mutasi['id_peg'];?>" title="Detail"><i class="fa fa-folder-open"></i></a>
                                        <a class="btn btn-sm btn-warning" href="home-admin.php?page=form-edit-data-mutasi&id_mutasi=<?=$Mutasi['id_mutasi'];?>" title="Edit"><i class="fa fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<script>
  $(function () {
    $("#mutasi").DataTable({
      "responsive": true, 
      "lengthChange": false, 
      "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
      // Fix date sorting logic if needed, column index 5 is TMT
      "order": [[ 5, "desc" ]] 
    }).buttons().container().appendTo('#mutasi_wrapper .col-md-6:eq(0)');
  });
</script>