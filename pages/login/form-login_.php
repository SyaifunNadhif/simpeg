<?php
include "dist/koneksi.php";
	$App=mysql_query("SELECT * FROM tb_config WHERE id_app='1'");
	$set=mysql_fetch_array($App);
	$alias	= $set['nama_app'];
	list($als,$app) = explode (" ",$alias);
?>
<div class="login-box">
	<div class="login-logo">
		<img src="dist/img/profile/bkk.png" class="user-image" alt="User Image"><br>
		<a href="index.php"><b><?=$als?></b> SIMPEG</a>
	</div>
	<div class="box box-primary">
		<div class="login-box-body">
			<p class="login-box-msg">Login Untuk Masuk ke Aplikasi</p>
			<form action="index.php?page=act-login&op=in" method="POST">
				<div class="form-group has-feedback">
					<input type="text" name="id_user" class="form-control" placeholder="User Name"><span class="glyphicon glyphicon-user form-control-feedback"></span>
				</div>
				<div class="form-group has-feedback">
					<input type="password" name="password" class="form-control" placeholder="Password"><span class="glyphicon glyphicon-lock form-control-feedback"></span>
				</div>
				<div class="row">
					<div class="col-xs-8"></div>
					<div class="col-xs-4">
					  <button type="submit" class="btn btn-danger btn-block">Login</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>