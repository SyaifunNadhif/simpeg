<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/form-master-data-ortu.php
 * MODULE  : SIMPEG — Data Orang Tua Pegawai (tb_ortu)
 * VERSION : v1.9 (PHP 5.6)
 * DATE    : 2025-09-06
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * TUJUAN  :
 *   Sederhana & konsisten dgn form Jabatan.
 *   • RELASI jelas: konteks pegawai via ?uid=<id_peg>
 *   • EDIT: header pegawai SELALU muncul (ambil id_peg dari baris ortu bila perlu)
 *   • ADD: jika belum ada uid → tampilkan picker pegawai (cari & gunakan)
 *   • Simpan/ubah memakai FK resmi: tb_ortu.id_peg
 *   • Validasi dasar & sanitasi input
 *
 * CHANGELOG
 * - v1.9: Re-faktor penuh mengikuti pola form jabatan (menggunakan ?uid sebagai id_peg).
 *********************************************************/
if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function getv($k,$d=''){ return isset($_GET[$k]) ? trim($_GET[$k]) : $d; }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
function fmt_date($s){ return ($s && $s!='0000-00-00') ? date('d-m-Y', strtotime($s)) : '-'; }

$today   = date('Y-m-d');
$mode    = strtolower(getv('mode','add'))==='edit' ? 'edit' : 'add';
$id_ortu = isset($_GET['id']) ? (int)$_GET['id'] : ( isset($_GET['id_ortu'])?(int)$_GET['id_ortu']:0 );
$uid     = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9\-_/]~','', $_GET['uid']) : '';
$q       = getv('q',''); // pencarian pegawai (add mode)

$pegawai = null; $rowEdit = null;

/* EDIT: ambil data ortu; jika uid kosong → isi dari baris */
if ($mode==='edit' && $id_ortu>0){
  $qOrtu = mysqli_query($conn, "SELECT id_peg, nik, nama, tmp_lhr, tgl_lhr, pendidikan, id_pekerjaan, pekerjaan, status_hub FROM tb_ortu WHERE id_ortu=".(int)$id_ortu." LIMIT 1");
  if ($qOrtu && mysqli_num_rows($qOrtu)>0){
    $rowEdit = mysqli_fetch_assoc($qOrtu);
    if ($uid==='') $uid = $rowEdit['id_peg'];
  }
}

/* HEADER: jika uid ada → load pegawai */
if ($uid!==''){
  $p = mysqli_query($conn, "SELECT id_peg, nama, jk, tempat_lhr, tgl_lhr FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
  if ($p && mysqli_num_rows($p)>0){ $pegawai = mysqli_fetch_assoc($p); }
}

$status=''; $errMsg='';
$hubList = array('Ayah','Ibu','Ayah Sambung','Ibu Sambung','Mertua L','Mertua P','Wali');

/* SIMPAN */
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $id_peg_post  = postv('id_peg',$uid);
  $nik          = postv('nik');
  $nama         = postv('nama');
  $tmp_lhr      = postv('tmp_lhr');
  $tgl_lhr      = postv('tgl_lhr');
  $pendidikan   = postv('pendidikan');
  $id_pekerjaan = postv('id_pekerjaan');
  $pekerjaan    = postv('pekerjaan');
  $status_hub   = postv('status_hub');

  // Validasi dasar
  if ($id_peg_post==='') { $errMsg='Pilih pegawai dulu.'; }
  elseif ($nama==='') { $errMsg='Nama Orang Tua wajib.'; }
  elseif ($status_hub==='' || !in_array($status_hub,$hubList)) { $errMsg='Status Hubungan tidak valid.'; }
  elseif ($nik!=='' && !preg_match('~^\d{16}$~',$nik)) { $errMsg='NIK harus 16 digit.'; }
  elseif ($id_pekerjaan!=='' && !preg_match('~^\d{1,3}$~',$id_pekerjaan)) { $errMsg='ID Pekerjaan maks 3 digit.'; }
  elseif ($tgl_lhr!=='' && !preg_match('~^\d{4}-\d{2}-\d{2}$~',$tgl_lhr)) { $errMsg='Tanggal lahir harus YYYY-MM-DD.'; }

  // pastikan pegawai ada
  if ($errMsg===''){
    $cek = mysqli_query($conn, "SELECT 1 FROM tb_pegawai WHERE id_peg='".clean($conn,$id_peg_post)."' LIMIT 1");
    if (!$cek || mysqli_num_rows($cek)==0) $errMsg='Pegawai tidak valid.';
  }

  if ($errMsg===''){
    if ($mode==='edit' && $id_ortu>0){
      $sql = "UPDATE tb_ortu SET ".
             "id_peg='".clean($conn,$id_peg_post)."',".
             "nik=".($nik!==''? "'".clean($conn,$nik)."'":"NULL").",".
             "nama='".clean($conn,$nama)."',".
             "tmp_lhr=".($tmp_lhr!==''? "'".clean($conn,$tmp_lhr)."'":"NULL").",".
             "tgl_lhr=".($tgl_lhr!==''? "'".clean($conn,$tgl_lhr)."'":"NULL").",".
             "pendidikan=".($pendidikan!==''? "'".clean($conn,$pendidikan)."'":"NULL").",".
             "id_pekerjaan=".($id_pekerjaan!==''? "'".clean($conn,$id_pekerjaan)."'":"NULL").",".
             "pekerjaan=".($pekerjaan!==''? "'".clean($conn,$pekerjaan)."'":"NULL").",".
             "status_hub='".clean($conn,$status_hub)."' ".
             "WHERE id_ortu=".(int)$id_ortu." LIMIT 1";
      $ok = mysqli_query($conn,$sql);
      $status = $ok? 'sukses':'gagal';
    } else {
      $sql = "INSERT INTO tb_ortu(id_peg,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,date_reg) VALUES (".
             "'".clean($conn,$id_peg_post)."',".
             ($nik!==''? "'".clean($conn,$nik)."'":"NULL").",".
             "'".clean($conn,$nama)."',".
             ($tmp_lhr!==''? "'".clean($conn,$tmp_lhr)."'":"NULL").",".
             ($tgl_lhr!==''? "'".clean($conn,$tgl_lhr)."'":"NULL").",".
             ($pendidikan!==''? "'".clean($conn,$pendidikan)."'":"NULL").",".
             ($id_pekerjaan!==''? "'".clean($conn,$id_pekerjaan)."'":"NULL").",".
             ($pekerjaan!==''? "'".clean($conn,$pekerjaan)."'":"NULL").",".
             ($status_hub!==''? "'".clean($conn,$status_hub)."'":"NULL").",".
             "'".$today."')";
      $ok = mysqli_query($conn,$sql);
      $status = $ok? 'sukses':'gagal';
    }
  } else { $status='gagal'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo ($mode==='edit'?'Ubah':'Tambah'); ?> Data Orang Tua</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .form-section{max-width:980px;margin:20px auto}
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .readonly-info{background:#f8fafc;border-radius:10px;padding:10px}
  </style>
</head>
<body>
<div class="form-section">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0"><?php echo ($mode==='edit'?'Ubah':'Tambah'); ?> Data Orang Tua</h5>
        <small>Lengkapi data sesuai dokumen kependudukan.</small>
      </div>
    </div>
    <div class="card-body">

      <?php if ($status==='sukses'): ?>
        <script>
          Swal.fire({icon:'success',title:'Tersimpan',text:'Data orang tua berhasil disimpan.'}).then(function(){
            var qs = '<?php echo $uid ? ('&uid='.urlencode($uid)) : ''; ?>';
            window.location = 'home-admin.php?page=form-view-data-ortu'+qs;
          });
        </script>
      <?php elseif ($status==='gagal' && $errMsg!==''): ?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($errMsg) ?>});</script>
      <?php elseif ($status==='gagal'): ?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:'Data tidak dapat disimpan.'});</script>
      <?php endif; ?>

      <?php if ($pegawai): ?>
      <div class="readonly-info mb-3">
        <div class="row">
          <div class="col-md-6"><strong>ID Pegawai</strong><br><?php echo e($pegawai['id_peg']); ?></div>
          <div class="col-md-6"><strong>Nama</strong><br><?php echo e($pegawai['nama']); ?></div>
        </div>
        <div class="row mt-2">
          <div class="col-md-6"><strong>Tempat, Tgl Lahir</strong><br><?php echo e($pegawai['tempat_lhr']); ?>, <?php echo fmt_date($pegawai['tgl_lhr']); ?></div>
          <div class="col-md-6"><strong>Jenis Kelamin</strong><br><?php echo e($pegawai['jk']); ?></div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($mode==='add' && !$pegawai): ?>
      <!-- PICKER PEGAWAI -->
      <div class="readonly-info mb-3">
        <b>Pilih Pegawai</b>
        <form method="get" class="form-inline" style="margin-top:8px">
          <input type="hidden" name="page" value="form-master-data-ortu">
          <input type="hidden" name="mode" value="add">
          <div class="form-group">
            <input type="text" name="q" class="form-control" placeholder="Cari ID/Nama (min 3 huruf)" value="<?php echo e($q); ?>" style="min-width:260px">
            <button class="btn btn-primary btn-sm" type="submit">Cari</button>
          </div>
        </form>
        <?php
        if (strlen($q)>=3){
          $qLike = clean($conn,$q);
          $rs = mysqli_query($conn,"SELECT id_peg, nama FROM tb_pegawai WHERE nama LIKE '%{$qLike}%' OR id_peg LIKE '%{$qLike}%' ORDER BY nama ASC LIMIT 25");
          if ($rs && mysqli_num_rows($rs)>0){
            echo '<div class="mt-2">';
            while($r=mysqli_fetch_assoc($rs)){
              $lnk = 'home-admin.php?page=form-master-data-ortu&mode=add&uid='.urlencode($r['id_peg']);
              echo '<div class="d-flex justify-content-between align-items-center" style="border:1px solid #e5e7eb;border-radius:8px;padding:8px;margin-bottom:6px">'
                 . '<div><b>'.e($r['id_peg']).'</b> — '.e($r['nama']).'</div>'
                 . '<div><a class="btn btn-success btn-sm" href="'.$lnk.'">Gunakan</a></div>'
                 . '</div>';
            }
            echo '</div>';
          } else {
            echo '<div class="text-muted mt-2">Tidak ada data.</div>';
          }
        }
        ?>
      </div>
      <?php endif; ?>

      <form method="post" action="" autocomplete="off">
        <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>">

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>NIK</label>
              <input type="text" name="nik" class="form-control" maxlength="16" value="<?php echo e($rowEdit? e($rowEdit['nik']) : ''); ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Nama Orang Tua<span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control" required value="<?php echo e($rowEdit? e($rowEdit['nama']) : ''); ?>">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Tempat Lahir</label>
              <input type="text" name="tmp_lhr" class="form-control" value="<?php echo e($rowEdit? e($rowEdit['tmp_lhr']) : ''); ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Tanggal Lahir</label>
              <input type="date" name="tgl_lhr" class="form-control" value="<?php echo e($rowEdit? e($rowEdit['tgl_lhr']) : ''); ?>">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Pendidikan</label>
              <?php $pendList = array('SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3'); $pdSel=$rowEdit? $rowEdit['pendidikan'] : ''; ?>
              <select name="pendidikan" class="form-control">
                <option value="">- pilih -</option>
                <?php foreach($pendList as $pd): ?>
                  <option value="<?php echo $pd; ?>" <?php echo ($pdSel===$pd?'selected':''); ?>><?php echo $pd; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>ID Pekerjaan (kode)</label>
              <input type="text" name="id_pekerjaan" maxlength="3" class="form-control" value="<?php echo e($rowEdit? e($rowEdit['id_pekerjaan']) : ''); ?>" placeholder="cth: 001">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Nama Pekerjaan</label>
              <input type="text" name="pekerjaan" class="form-control" value="<?php echo e($rowEdit? e($rowEdit['pekerjaan']) : ''); ?>" placeholder="cth: Wiraswasta">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Status Hubungan<span class="text-danger">*</span></label>
              <?php $hubSel=$rowEdit? $rowEdit['status_hub'] : ''; ?>
              <select name="status_hub" class="form-control" required>
                <option value="">- pilih -</option>
                <?php foreach($hubList as $h): ?>
                  <option value="<?php echo e($h); ?>" <?php echo ($hubSel===$h?'selected':''); ?>><?php echo e($h); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
          <a href="home-admin.php?page=form-view-data-ortu" class="btn btn-outline-secondary">Kembali</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>

    </div>
  </div>
</div>
</body>
</html>
