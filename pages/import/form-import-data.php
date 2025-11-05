<section class="content-header">
    <h1>Import<small>Data Excel</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Import Data Excel</li>
    </ol>
</section>


<hr />
<table width="300" border="0">
  <tr>
    <td width="142">
   <a href="hapus.php" onclick="javascript: return confirm('Anda yakin ingin menghapus data ?')"> 
   <input type="button" name="button" class="btn btn-danger" value="Kosongkan Data" />
   </a>
   </td>
    <td width="142">	
	<a href="import.php">
      <input type="button" name="button" class="btn btn-danger" value="Import Data" />
    </a>
	</td>
  </tr>
</table>
<hr />


<table width="100%" border="1" align="center" rules="all">
  <tr bgcolor="#CCCCCC">
    <td width="31"><div align="center"><strong>No</strong></div></td>
    <td width="143"><strong>Kode </strong></td>
    <td width="284"><strong>Nama</strong></td>
    <td width="140"><strong>Harga</strong></td>
    <td width="155"><strong>Berat</strong></td>
    <td width="175"><strong>diskon</strong></td>
    <td width="184"><strong>Kondisi</strong></td>
    <td width="171"><strong>Keterangan</strong></td>
   </tr>
  <?php
  		include "dist/koneksi.php";
		$no=1;
		$data=mysqli_query($koneksi,"select * from mproduk");
		while($arraytampil=mysqli_fetch_array($data)){
  ?>
  <tbody id="myTable">
    <tr><td height="42"><div align="center"><?php echo $no++; ?></div></td>
    <td><?php echo "&nbsp; $arraytampil[kode] "; ?></td>
    <td><?php echo "&nbsp; $arraytampil[nama] "; ?></td>
    <td>
	<?php 
	$harga=number_format($arraytampil['harga'],0, ".", ".");
	echo "Rp. $harga "; ?>	</td>
    <td><?php echo "&nbsp; $arraytampil[berat] "; ?></td>
    <td><?php echo "&nbsp; $arraytampil[disc] "; ?> %</td>
    <td><?php echo "&nbsp; $arraytampil[kondisi] "; ?></td>
    <td><?php echo "&nbsp; $arraytampil[ket] "; ?></td>
    </tr>
   </tbody>
  
   <?php 

 } ?>
</table>