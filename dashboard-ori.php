<div class="alert alert-info alert-dismissible">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <h5><i class="icon fas fa-info"></i> Informasi</h5>
  Selamat Datang  &nbsp;<b><?php echo $_SESSION['nama_user'] ?></b>! &nbsp;&nbsp;
Di Sistem Informasi Kepagawaian BPR BKK Jateng
</div>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- ChartJS -->
  <script src="plugins/chart.js/Chart.min.js"></script>
  <script src="plugins/chart.js/canvasjs.min.js"></script>
  <title>Simpeg BPR BKK Jateng | Dashboard</title>
</head>

  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Dashboard</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item "><a href="#">Dashboard</a></li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <?php
  include "dist/koneksi.php";
  include "dist/library.php";
  $pegawai = mysqli_query($conn, "SELECT * FROM tb_pegawai WHERE status_aktif=1");
  $jmlpegawai = mysqli_num_rows($pegawai);

  $purna = mysqli_query($conn, "SELECT a.*, b.tgl_mutasi FROM tb_pegawai a, tb_mutasi b WHERE a.id_peg=b.id_peg AND status_aktif=3 AND YEAR(tgl_mutasi) = YEAR(CURRENT_DATE())");
  $jmlpurna = mysqli_num_rows($purna);

  $punishment = mysqli_query($conn, "SELECT id_peg FROM tb_hukuman WHERE YEAR(tgl_sk)=YEAR(CURRENT_DATE())");
  $jmlpunishment = mysqli_num_rows($punishment);

  $diklat = mysqli_query($conn, "SELECT diklat FROM tb_diklat WHERE tahun=YEAR(CURRENT_DATE()) GROUP BY diklat");
  $jmldiklat = mysqli_num_rows($diklat);

  $pie = "SELECT jk, COUNT(id_peg) AS qty FROM tb_pegawai GROUP BY jk";
  $hasilPie = mysqli_query($conn, $pie);

  $dtPegawai = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE status_aktif=1 AND (YEAR(CURDATE()) - YEAR(tmt_kerja)) <= 10");
  $jml1 = mysqli_num_rows($dtPegawai);

  $dtPegawai2 = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE status_aktif=1 AND (YEAR(CURDATE()) - YEAR(tmt_kerja)) > 10 AND (YEAR(CURDATE()) - YEAR(tmt_kerja)) <= 20");
  $jml2 = mysqli_num_rows($dtPegawai2);

  $dtPegawai3 = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE status_aktif=1 AND (YEAR(CURDATE()) - YEAR(tmt_kerja)) > 20 AND (YEAR(CURDATE()) - YEAR(tmt_kerja)) <= 30");
  $jml3 = mysqli_num_rows($dtPegawai3);

  $dtPegawai4 = mysqli_query($conn, "SELECT id_peg FROM tb_pegawai WHERE status_aktif=1 AND (YEAR(CURDATE()) - YEAR(tmt_kerja)) > 30");
  $jml4 = mysqli_num_rows($dtPegawai4);  
  ?>
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?=number_format($jmlpegawai, 0, ",", ".")?></h3>

              <p>Jml. Total Pegawai</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-pegawai" class="small-box-footer">Info Detail <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?=number_format($jmlpurna, 0, ",", ".")?></h3>

              <p>Jml Pegawai Purna / Non Aktif Th. <?php echo date('Y'); ?></p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-mutasi" class="small-box-footer">Info Detail <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?=number_format($jmlpunishment, 0, ",", ".")?></h3>

              <p>Jml Pelanggaran Pegawai Th. <?php echo date('Y'); ?></p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-hukuman" class="small-box-footer">Info Detail <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?=number_format($jmldiklat, 0, ",", ".")?></h3>

              <p>Jml Pelaksanaan Diklat Th. <?php echo date('Y'); ?></p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
            <a href="home-admin.php?page=form-view-data-diklat" class="small-box-footer">Info Detail <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->   
        <div class="card card-default card-outline">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tag"></i>
            Statistik Jumlah Pegawai Per Masa Kerja</h3>
          </div> <!-- /.card-body -->
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="far fa-user"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text"><h4><?=number_format($jml1, 0, ",", ".")?> Pegawai</h4></span>
                    <span class="info-box-text">Masa Kerja s.d. 10 Tahun</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                  <span class="info-box-icon bg-success"><i class="far fa-user"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text"><h4><?=number_format($jml2, 0, ",", ".")?> Pegawai</h4></span>
                    <span class="info-box-text">Masa Kerja diatas 10 s.d. 20 Tahun</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                  <span class="info-box-icon bg-warning"><i class="far fa-user"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text"><h4><?=number_format($jml3, 0, ",", ".")?> Pegawai</h4></span>
                    <span class="info-box-text">Masa Kerja diatas 20 s.d. 30 Tahun</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box">
                  <span class="info-box-icon bg-danger"><i class="far fa-user"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text"><h4><?=number_format($jml4, 0, ",", ".")?> Pegawai</h4></span>
                    <span class="info-box-text">Masa Kerja Lebih Dari 30 Tahun</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
            </div> 
            <!-- /.row 2 --> 
          </div><!-- /.card-body --> 
        </div> 
      <!-- Main row -->
      <div class="row">
        <!-- Konten Utama -->
        <div class="col-md-6">
          <!-- CHART JKEL -->
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Berdasarkan Jenis Kelamin</h3>
            </div>
            <div class="card-body">
              <canvas id="pieChart" style="min-width: 250px; height: 250px; margin: 0 auto"></canvas>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
          <?php
            include "dist/koneksi.php";

            $jenis_kelamin= "";
            $jmljk=null;
            $sql="SELECT jk,COUNT(*) as 'total' from tb_pegawai WHERE status_aktif=1 GROUP by jk";
            $hasil=mysql_query($sql) or die(mysql_error());

            while ($data = mysql_fetch_array($hasil)) {
              $jk=$data['jk'];
              $jenis_kelamin .= "'$jk'". ", ";

              $jum=$data['total'];
              $jmljk .= "$jum". ", ";
            }
          ?>
          <script>
            var ctx = document.getElementById('pieChart').getContext('2d');
            var chart = new Chart(ctx, {
            // The type of chart we want to create
              type: 'pie',
            // The data for our dataset
              data: {
                labels: [<?php echo $jenis_kelamin; ?>],
                datasets: [{
                  label:'Berdasarkan Jenis Kelamin',
                  backgroundColor: 
                  [
                    'rgba(60,141,188,0.9)', 
                    'rgba(210, 214, 222, 1)'
                  ],
                  borderColor: 
                  [
                    'rgba(210, 214, 222, 0.5)'
                  ],
                  data: [<?php echo $jmljk; ?>]
                }]
              },
            
            });
          </script>
          <!-- /.card -->         
          <!-- CHART STATPEG -->
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Berdasarkan Status Kepegawaian</h3>
            </div>
            <div class="card-body">
              <canvas id="StatusPeg" style="min-width: 250px; height: 250px; margin: 0 auto"></canvas>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
          <?php
            include "dist/koneksi.php";

            $status_peg= "";
            $jmlstat=null;
            $sql="SELECT status_kepeg,COUNT(*) as 'total' from tb_pegawai WHERE status_aktif=1 GROUP by status_kepeg ORDER BY FIELD(status_kepeg, 'Kontrak', 'Calon Pegawai','Tetap')";
            $hasil=mysql_query($sql) or die(mysql_error());

            while ($data = mysql_fetch_array($hasil)) {
              $jk=$data['status_kepeg'];
              $status_peg .= "'$jk'". ", ";

              $jum=$data['total'];
              $jmlstat .= "$jum". ", ";
            }
          ?>
          <script>
            var ctx = document.getElementById('StatusPeg').getContext('2d');
            var chart = new Chart(ctx, {
            // The type of chart we want to create
              type: 'bar',
            // The data for our dataset
              data: {
                labels: [<?php echo $status_peg; ?>],
                datasets: [{
                  label:' ',
                  backgroundColor: 
                  [
                    'Tomato','Orange','Teal'
                  ],
                  borderColor: ['rgb(255, 99, 132)'],
                  data: [<?php echo $jmlstat; ?>]
                }]
              },
            });
          </script>
          <!-- /.card -->         
        </div>
        <!-- /.col (LEFT) -->
        <div class="col-md-6">
          <!-- CHART PENDIDIKAN -->
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Berdasarkan Jenjang Pendidikan</h3>
            </div>
            <div class="card-body">
              <canvas id="Pendidikan" style="min-width: 250px; height: 250px; margin: 0 auto"></canvas>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
          <?php
            include "dist/koneksi.php";

            $pendidikan= "";
            $jmlpend=null;
            $sql="SELECT jenjang,COUNT(*) as 'total' FROM tb_sekolah GROUP BY jenjang ORDER BY FIELD(jenjang, 'SD', 'SMP','SMA','D1','D2','D3','D4','S1','S2','S3')";
            $hasil=mysql_query($sql) or die(mysql_error());

            while ($data = mysql_fetch_array($hasil)) {
              $pend=$data['jenjang'];
              $pendidikan .= "'$pend'". ", ";

              $jum=$data['total'];
              $jmlpend .= "$jum". ", ";
            }
          ?>
          <script>
            var ctx = document.getElementById('Pendidikan').getContext('2d');
            var chart = new Chart(ctx, {
            // The type of chart we want to create
              type: 'bar',
            // The data for our dataset
              data: {
                labels: [<?php echo $pendidikan; ?>],
                datasets: [{
                  label:' ',
                  backgroundColor: 
                  [
                    'salmon','red','LightSlateGrey','RoyalBlue','lightblue','aqua','fuchsia','gold'
                  ],
                  borderColor: ['rgb(255, 99, 132)'],
                  data: [<?php echo $jmlpend; ?>]
                }]
              },
            });
          </script>
          <!-- /.card --> 

          <!-- CHART USIA -->


          <!-- /CHART USIA-->     

          <!-- CHART PELANGGARAN -->
          <div class="card card-danger">
            <div class="card-header">
              <h3 class="card-title">Daftar Pelanggaran Pegawai</h3>
            </div>
            <div class="card-body">
              <canvas id="Pelanggaran" style="min-width: 250px; height: 250px; margin: 0 auto"></canvas>
            </div>
            <!-- /.card-body -->
          </div>
          <?php
            include "dist/koneksi.php";
            $namaBln = array("12022" => "Jan-22", "22022" => "Feb-22", "32022" => "Mar-22", "42022" => "Apr-22", "52022" => "Mei-22", "62022" => "Jun-22", 
              "72022" => "Jul-22", "82022" => "Ags-22", "92022" => "Sep-22", "102022" => "Okt-22", "112022" => "Nov-22", "122022" => "Des-22",
              "12023" => "Jan-23", "22023" => "Feb-23", "32023" => "Mar-23", "42023" => "Apr-23", "52023" => "Mei-23", "62023" => "Jun-23", 
              "72023" => "Jul-23", "82023" => "Ags-23", "92023" => "Sep-23", "102023" => "Okt-23", "112023" => "Nov-23", "122023" => "Des-23","12024" => "Jan-24", "22024" => "Feb-24", "32024" => "Mar-24", "42024" => "Apr-24", "52024" => "Mei-24", "62024" => "Jun-24", 
              "72024" => "Jul-24", "82024" => "Ags-24", "92024" => "Sep-24", "102024" => "Okt-24", "112024" => "Nov-24", "122024" => "Des-24");
            $pelanggaran= "";
            $jmlpel=null;
            $sql="SELECT COUNT(id_peg) as id_peg, concat(month(tgl_sk),year(tgl_sk)) as bln from tb_hukuman where year(tgl_sk)=year(current_date()) group by bln order by tgl_sk";
            $hasil=mysql_query($sql) or die(mysql_error());

            while ($data = mysql_fetch_array($hasil)) {
              $pel=$namaBln[$data['bln']];
              $pelanggaran .= "'$pel'". ", ";

              $jum=$data['id_peg'];
              $jmlpel .= "$jum". ", ";
            }
          ?>
          <script>
            var ctx = document.getElementById('Pelanggaran').getContext('2d');
            var chart = new Chart(ctx, {
            // The type of chart we want to create
              type: 'line',
            // The data for our dataset
              data: {
                labels: [<?php echo $pelanggaran; ?>],
                datasets: [{
                  label:' ',
                  backgroundColor: 
                  [
                    'salmon','red','aliceblue','blue','lightblue','aqua','fuchsia','gold'
                  ],
                  borderColor: ['rgb(255, 99, 132)'],
                  data: [<?php echo $jmlpel; ?>]
                }]
              },
            });
          </script>
          <!-- /CHART PELANGGARAN-->                    
        </div>  
        <!-- /.col (RIGHT) -->   
        <!-- .col (MIDDLE) -->
        <div class="col-md-12">
          <!-- CHART JABATAN -->
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Berdasarkan Jabatan</h3>
            </div>
            <div class="card-body">
              <canvas id="Jabatan" style="min-width: 250px; height: 250px; margin: 0 auto"></canvas>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
          <?php
            include "dist/koneksi.php";

            $jabatan= "";
            $jmljab=null;
            $sql="SELECT jabatan,COUNT(*) as 'total' FROM tb_jabatan GROUP BY jabatan ORDER BY jabatan";
            $hasil=mysql_query($sql) or die(mysql_error());

            while ($data = mysql_fetch_array($hasil)) {
              $jab=$data['jabatan'];
              $jabatan .= "'$jab'". ", ";

              $jum=$data['total'];
              $jmljab .= "$jum". ", ";
            }
          ?>
          <script>
            var ctx = document.getElementById('Jabatan').getContext('2d');
            var chart = new Chart(ctx, {
            // The type of chart we want to create
              type: 'bar',
            // The data for our dataset
              data: {
                labels: [<?php echo $jabatan; ?>],
                datasets: [{
                  label:' ',
                  backgroundColor: 'gold',
                  borderColor: ['rgb(255, 99, 132)'],
                  data: [<?php echo $jmljab; ?>]
                }]
              },
            });
          </script>                  
        </div>  
        <!-- /.col (RIGHT) -->     
      </div>
      <!-- /.row (main row) -->
      <?php
      $tampilPeg=mysql_query("SELECT id_peg, nama, jk, (SELECT jabatan FROM tb_jabatan WHERE id_peg=a.id_peg AND status_jab='Aktif') jabatan, ( SELECT nama_kantor FROM tb_kantor WHERE kode_kantor_detail =( SELECT unit_kerja FROM tb_jabatan WHERE id_peg = a.id_peg AND status_jab = 'Aktif' )) unit_kerja,
      	tempat_lhr,
      	tgl_lhr,
      	tgl_pensiun 
      	FROM
      	tb_pegawai a
      	WHERE
      	id_peg NOT IN ('101-001','101-002','101-003','101-004','101-005','101-007','101-008')
      	AND YEAR ( tgl_pensiun ) = YEAR (now())
      	order by tgl_pensiun");
      $jmlPeg = mysql_num_rows($tampilPeg);
      ?>
      <!-- Tabel Pegawai Yang Akan Purna -->
      <div class="row">
        <div class="col-md-12">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Daftar Pegawai Memasuki Usia Pensiun Tahun <?php echo date('Y');?></h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <form role="form" id="formFileDiklat" action="home-admin.php?page=upload-data-diklat" class="form-horizontal" method="POST" enctype="multipart/form-data">
                <div class="col-sm-12">
                  <div class="form-group">
                  	<table id="purna" class="table table-bordered table-striped">
                  		<thead>
                  			<tr>
                  				<th>ID Pegawai</th>
                  				<th>Nama</th>
                  				<th>Jenis Kelamin</th>
                  				<th>Jabatan</th>                  				
                  				<th>Tempat, Tgl Lahir</th>
                  				<th>Tgl Pensiun</th>
                  			</tr>
                  		</thead>
                  		<tbody>
                  			<?php
                  				while($peg=mysql_fetch_array($tampilPeg)){
                  			?>	
                  				<tr>
                  					<td><?php echo $peg['id_peg'];?></td>
                  					<td><?php echo $peg['nama'];?></td>
                  					<td><?php echo $peg['jk'];?></td>
                  					<td><?php echo $peg['jabatan'];?></td>                  					
                  					<td><?php echo $peg['tempat_lhr'];?>, <?php echo Indonesia2Tgl($peg['tgl_lhr']);?></td>
                  					<td><?php echo $peg['tgl_pensiun']; ?></td>
                  			<?php
                  				}
                  			?>
                  		</tbody>
                  	</table>
                  </div>
                </div>  
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- /.row -->
      <!-- /.row (main row) -->
      <?php
      $tampilPeg=mysql_query("SELECT kode_jabatan, jabatan,
                              (
                              SELECT
                                count( tb_jabatan.id_peg ) 
                              FROM
                                tb_jabatan,
                                tb_pegawai 
                              WHERE
                                tb_jabatan.id_peg = tb_pegawai.id_peg 
                                AND jabatan = a.jabatan 
                                AND status_jab = 'Aktif' 
                                AND status_aktif = 1 
                              ) jml,
                              kuota 
                            FROM
                              tb_ref_jabatan a,
                              tb_pegawai b 
                            WHERE
                              `group` = 'PE' 
                            GROUP BY
                              kode_jabatan 
                            ORDER BY
                              kode_jabatan");
      $jmlPeg = mysql_num_rows($tampilPeg);
      ?>

      <!-- Tabel Pegawai Yang Akan Purna -->
      <div class="row">
        <div class="col-md-6">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Data Keterisian Pejabat Eksekutif <?php echo date('Y');?></h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <form role="form" id="formFileDiklat" action="home-admin.php?page=upload-data-diklat" class="form-horizontal" method="POST" enctype="multipart/form-data">
                <div class="col-sm-12">
                  <div class="form-group">
                    <table id="jabatan" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th width="10%">Kode Jabatan</th>
                          <th width="60%">Jabatan</th>
                          <th width="30%">Jml Pegawai</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          while($peg=mysql_fetch_array($tampilPeg)){
                        ?>  
                          <tr>
                            <td><?php echo $peg['kode_jabatan'];?></td>
                            <td><?php echo $peg['jabatan'];?></td>
                            <td align="center"><?php echo $peg['jml'];?>  &nbsp;&nbsp;&nbsp;dari&nbsp;&nbsp;&nbsp; <?php echo $peg['kuota'];?></td>
                        <?php
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>  
              </form>
            </div>
          </div>
        </div>
        <?php
        $tampilPeg=mysql_query("SELECT kode_jabatan, jabatan,
                              (
                              SELECT
                                count( tb_jabatan.id_peg ) 
                              FROM
                                tb_jabatan,
                                tb_pegawai 
                              WHERE
                                tb_jabatan.id_peg = tb_pegawai.id_peg 
                                AND jabatan = a.jabatan 
                                AND status_jab = 'Aktif' 
                                AND status_aktif = 1 
                              ) jml,
                              kuota 
                            FROM
                              tb_ref_jabatan a,
                              tb_pegawai b 
                            WHERE
                              `group` = 'PS' 
                            GROUP BY
                              kode_jabatan 
                            ORDER BY
                              kode_jabatan");
        $jmlPeg = mysql_num_rows($tampilPeg);
        ?>        
        <div class="col-md-6">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Data Keterisian Pejabat Struktural <?php echo date('Y');?></h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <form role="form" id="formFileDiklat" action="home-admin.php?page=upload-data-diklat" class="form-horizontal" method="POST" enctype="multipart/form-data">
                <div class="col-sm-12">
                  <div class="form-group">
                    <table id="jmlps" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th width="10%">Kode Jabatan</th>
                          <th width="60%">Jabatan</th>
                          <th width="30%">Jml Pegawai</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          while($peg=mysql_fetch_array($tampilPeg)){
                        ?>  
                          <tr>
                            <td><?php echo $peg['kode_jabatan'];?></td>
                            <td><?php echo $peg['jabatan'];?></td>
                            <td align="center"><?php echo $peg['jml'];?>  &nbsp;&nbsp;&nbsp;dari&nbsp;&nbsp;&nbsp; <?php echo $peg['kuota'];?></td>
                        <?php
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>  
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- /.row --> 
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
<!-- ./wrapper -->
</html>
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
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
    $("#purna").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
      "order": [5, 'asc'],
      "pageLength": 5
    }).buttons().container().appendTo('#kosong_wrapper .col-md-6:eq(0)');
    $("#jabatan").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
      "order": [0, 'asc'],
      "pageLength": 5
    }).buttons().container().appendTo('#kosong_wrapper .col-md-6:eq(0)');
    $("#jmlps").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
      "order": [0, 'asc'],
      "pageLength": 5
    }).buttons().container().appendTo('#kosong_wrapper .col-md-6:eq(0)');    
  });  
</script>
