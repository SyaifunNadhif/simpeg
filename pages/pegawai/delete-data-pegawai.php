<section class="content-header">
    <h1>Delete<small>Data Pegawai</small></h1>
    <ol class="breadcrumb">
        <li><a href="home-admin.php"><i class="fa fa-dashboard"></i>Dashboard</a></li>
        <li class="active">Delete Data Pegawai</li>
    </ol>
</section>

<div class="register-box">
<?php
    include "dist/koneksi.php"; // Pastikan di file ini variabel koneksinya bernama $conn atau $koneksi

    // Cek apakah ada parameter id_peg
    if (isset($_GET['id_peg'])) {
        // Amankan input dari SQL Injection
        $id_peg = mysqli_real_escape_string($conn, $_GET['id_peg']);
    } else {
        die("Error. No ID Selected!");
    }

    if (!empty($id_peg) && $id_peg != "") {
        
        // 1. PINDAHKAN DATA KE RECYCLE BIN DULU
        // Catatan: Kolom di SELECT tidak boleh pakai tanda kutip ('nama'), harus (nama)
        $insert = "INSERT INTO tb_recycle_bin_pegawai 
                   (id_peg, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah, status_nikah, status_kepeg, alamat, telp, email, foto, tgl_pensiun, date_reg, bpjstk, bpjskes, status_aktif) 
                   SELECT id_peg, nip, nama, tempat_lhr, tgl_lhr, agama, jk, gol_darah, status_nikah, status_kepeg, alamat, telp, email, foto, tgl_pensiun, date_reg, bpjstk, bpjskes, status_aktif 
                   FROM tb_pegawai WHERE id_peg='$id_peg'";
        
        $move_data = mysqli_query($conn, $insert);

        // Jika backup berhasil (atau abaikan jika tidak ingin strict), lanjut delete
        if($move_data) {
            
            // 2. HAPUS DATA ANAK & TURUNANNYA
            mysqli_query($conn, "DELETE FROM tb_anak WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_bahasa WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_cuti WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_diklat WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_dp3 WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_hukuman WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_jabatan WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_ortu WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_pangkat WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_penghargaan WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_penugasan WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_sekolah WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_seminar WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_suamiistri WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_lat_jabatan WHERE id_peg='$id_peg'");
            mysqli_query($conn, "DELETE FROM tb_mutasi WHERE id_peg='$id_peg'");

            // 3. HAPUS DATA UTAMA PEGAWAI
            $delete = "DELETE FROM tb_pegawai WHERE id_peg='$id_peg'";
            $sqldel = mysqli_query($conn, $delete);

            if ($sqldel) {
                echo "<div class='register-logo'><b>Delete</b> Successful!</div>
                    <div class='box box-primary'>
                        <div class='register-box-body'>
                            <p>Data Pegawai ID <b>$id_peg</b> Berhasil di Hapus dan dipindahkan ke Recycle Bin.</p>
                            <div class='row'>
                                <div class='col-xs-8'></div>
                                <div class='col-xs-4'>
                                    <button type='button' onclick=\"location.href='home-admin.php?page=form-view-data-pegawai'\" class='btn btn-danger btn-block'>Next >></button>
                                </div>
                            </div>
                        </div>
                    </div>";
            } else {
                echo "<div class='register-logo'><b>Oops!</b> Gagal Menghapus Data Pegawai.</div>";
            }
        } else {
             echo "<div class='register-logo'><b>Oops!</b> Gagal Backup ke Recycle Bin. Proses Hapus Dibatalkan. <br><small>".mysqli_error($conn)."</small></div>";
        }
    }
?>
</div>