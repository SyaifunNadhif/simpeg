<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><strong>Home</strong></li>
          <li class="breadcrumb-item active">SK Pengangkatan Pegawai</li>
        </ol>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><button onclick=location.href="javascript:history.back()" class="btn btn-block btn-outline-danger btn-sm"><i class="fa fa-step-backward"></i> Tutup</button></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-primary">       
        <div class="box-body">              
          <?php
            $id_angkat=$_GET['id_angkat'];
            $queryAngkat=mysql_query("SELECT * from tb_angkat WHERE id_angkat=$id_angkat");
            while ($hasil=mysql_fetch_array($queryAngkat)) {
          ?>
            <object data="pages/asset/sk_mutasi/<?php echo $hasil['sk_mutasi']?>" width="100%" height="1000px" style="border:1px solid; box-shadow: 2px 2px 8px #000000;"></object>

          <?php
            }
          ?>        
        </div>
      </div>
    </div>
  </div>
</section>