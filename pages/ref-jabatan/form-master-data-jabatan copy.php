<?php
/*********************************************************
 * FILE    : pages/ref-jabatan/form-master-data-jabatan.php
 * MODULE  : SIMPEG — Entry Jabatan Pegawai
 * VERSION : v1.3a (PHP 5.6 compatible)
 * DATE    : 2025-09-07
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * PURPOSE :
 *   - Entry jabatan baru / mutasi jabatan pegawai.
 *   - MODE INI MENGACU PADA id_peg (bukan pegawai_uid) agar kompatibel dg skema lama.
 *   - Jika ?uid=<id_peg> ada → tampilkan header identitas & hidden id_peg.
 *   - Jika tidak ada → tampilkan picker pegawai (select2) berbasis id_peg.
 *   - Aturan simpan:
 *       • tmt_jabatan = tgl_sk; sampai_tgl = NULL (isi saat ada jabatan baru)
 *       • Jika status_jab = 'Aktif' → jabatan lama otomatis Non, sampai_tgl = (tgl_sk_baru - 1)
 *       • unit_kerja menyimpan kode_kantor_detail
 *
 * CHANGELOG
 * - v1.3a (2025-09-07): Perbaiki picker pegawai: query & kolom pakai id_peg/nama; redirect kirim id_peg; header gunakan kolom skema lama.
 * - v1.3  (2025-09-07): Tambah konteks pegawai + picker (versi uid).
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; $conn = isset($koneksi)?$koneksi:null; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function getv($k,$d=''){ return isset($_GET[$k]) ? trim($_GET[$k]) : $d; }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
function fmt_date($s){ return ($s && $s!='0000-00-00') ? date('d-m-Y', strtotime($s)) : '-'; }

$today      = date('Y-m-d');
$user_login = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'system';

// --- Ambil data pegawai (konteks) —> gunakan id_peg
$uid = isset($_GET['uid']) ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';
$pegawai = null;
if ($uid !== '') {
  $q = mysqli_query($conn, "SELECT id_peg, id_peg_old, nama, jk, tempat_lhr, tgl_lhr 
                             FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
  if ($q && mysqli_num_rows($q)>0) { $pegawai = mysqli_fetch_assoc($q); }
}

// --- Referensi jabatan & unit
$ref_jabatan = array();
$rsJ = mysqli_query($conn, "SELECT kode_jabatan, jabatan FROM tb_ref_jabatan ORDER BY kode_jabatan");
if ($rsJ) { while($r=mysqli_fetch_assoc($rsJ)){ $ref_jabatan[]=$r; } }

$ref_unit = array();
$rsU = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY kode_kantor_detail");
if ($rsU) { while($u=mysqli_fetch_assoc($rsU)){ $ref_unit[]=$u; } }

// --- Jabatan aktif sekarang
$jabAktif = null;
if ($uid !== '') {
  $qJ = mysqli_query($conn, "SELECT j.id_jab, j.id_peg, j.kode_jabatan, j.jabatan,
    j.unit_kerja, k.nama_kantor, j.tmt_jabatan, j.sampai_tgl,
    j.no_sk, j.tgl_sk, j.status_jab
    FROM tb_jabatan j
    LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
    WHERE j.id_peg='".clean($conn,$uid)."' AND j.status_jab='Aktif'
    ORDER BY COALESCE(j.tmt_jabatan,'1000-01-01') DESC, j.id_jab DESC LIMIT 1");
  if ($qJ && mysqli_num_rows($qJ)>0) { $jabAktif = mysqli_fetch_assoc($qJ); }
}

// --- Proses simpan (inline)
$status = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id_peg       = clean($conn, postv('id_peg'));
  $kode_jabatan = clean($conn, postv('kode_jabatan'));
  $jabatan      = clean($conn, postv('jabatan'));
  $unit_kerja   = clean($conn, postv('unit_kerja')); // kode_kantor_detail
  $status_jab   = clean($conn, postv('status_jab','Aktif'));
  $no_sk        = clean($conn, postv('no_sk'));
  $tgl_sk       = clean($conn, postv('tgl_sk'));

  $tmt_jabatan = $tgl_sk; // aturan: TMT = tgl_sk

  if ($id_peg==='') {
    $status = 'gagal';
  } else {
    mysqli_begin_transaction($conn);
    $ok = true;
    if ($status_jab==='Aktif') {
      $sqlClose = "UPDATE tb_jabatan SET status_jab='Non', sampai_tgl=DATE_SUB('{$tgl_sk}',INTERVAL 1 DAY), updated_at=NOW(), updated_by='{$user_login}' WHERE id_peg='{$id_peg}' AND status_jab='Aktif'";
      $ok = mysqli_query($conn, $sqlClose);
    }
    if ($ok){
      $sql = "INSERT INTO tb_jabatan (id_peg,id_peg_old,kode_jabatan,jabatan,unit_kerja,
                tmt_jabatan,sampai_tgl,status_jab,no_sk,tgl_sk,date_reg,created_by)
              VALUES (
                '{$id_peg}',NULL,'{$kode_jabatan}','{$jabatan}','{$unit_kerja}',
                '{$tmt_jabatan}',NULL,'{$status_jab}','{$no_sk}','{$tgl_sk}',
                '{$today}','{$user_login}'
              )";
      $ok = mysqli_query($conn,$sql);
    }
    if ($ok) { mysqli_commit($conn); $status='sukses'; } else { mysqli_rollback($conn); $status='gagal'; }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Entry Jabatan Pegawai</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .form-section{max-width:980px;margin:20px auto}
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
    .readonly-info{background:#f8fafc;border-radius:10px;padding:10px}
    .jab-panel{border:1px solid #e5e7eb;border-radius:12px;padding:14px;background:#fff}
    .jab-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
    .jab-head h6{margin:0;font-weight:700}
    .badge-aktif{display:inline-block;background:#10b981;color:#fff;padding:2px 8px;border-radius:999px;font-size:12px}
    .badge-non{display:inline-block;background:#9ca3af;color:#fff;padding:2px 8px;border-radius:999px;font-size:12px}
  </style>
</head>
<body>
<div class="form-section">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Entry Jabatan Pegawai</h5>
        <small>Lengkapi data jabatan & unit kerja pegawai</small>
      </div>
    </div>
    <div class="card-body">

      <?php if ($status==='sukses'): ?>
        <script>
          Swal.fire({icon:'success',title:'Tersimpan',text:'Data jabatan berhasil disimpan.'}).then(function(){
            window.location = 'home-admin.php?page=form-view-data-jabatan&uid=<?php echo e($uid); ?>';
          });
        </script>
      <?php elseif ($status==='gagal'): ?>
        <script>Swal.fire({icon:'error',title:'Gagal',text:'Data jabatan tidak dapat disimpan. Pastikan Pegawai dipilih.'});</script>
      <?php endif; ?>

      <!-- KONTEKS PEGAWAI: header atau picker -->
      <div class="card mb-3">
        <div class="card-body">
          <?php if ($pegawai): ?>
            <div class="d-flex align-items-center">
              <div class="me-3">
                <img src="pages/assets/foto/<?php echo ($pegawai['jk']=='Perempuan'?'no-foto-female.png':'no-foto-male.png'); ?>" class="rounded-circle" width="48" alt="foto">
              </div>
              <div>
                <div class="fw-bold">Pegawai: <?php echo e($pegawai['nama']); ?></div>
                <div class="text-muted">ID Peg: <?php echo e($pegawai['id_peg']); ?><?php echo ($pegawai['id_peg_old']? ' • Old: '.e($pegawai['id_peg_old']) : ''); ?></div>
              </div>
            </div>
          <?php else: ?>
            <div class="row g-2 align-items-end">
              <div class="col-md-8">
                <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                <select id="uid_picker" class="form-select" style="width:100%">
                  <option value="">- Pilih Pegawai -</option>
                  <?php
                    // PENTING: pakai kolom skema lama (id_peg, nama) agar tidak kosong
                  $qp = mysqli_query($conn, "SELECT p.id_peg, p.nama
                    FROM tb_pegawai p
                    WHERE p.status_aktif = '1'
                    AND NOT EXISTS (
                      SELECT 1
                      FROM tb_jabatan j
                      WHERE j.id_peg = p.id_peg
                      AND j.status_jab = 'Aktif'
                      )
                    ORDER BY p.nama ASC");
                    if ($qp){ while($p=mysqli_fetch_assoc($qp)){
                      echo '<option value="'.e($p['id_peg']).'">'.e($p['id_peg'].' — '.$p['nama'])."</option>";
                    } }
                  ?>
                </select>
                <div class="form-text">Silakan pilih pegawai terlebih dahulu.</div>
              </div>
            </div>
            <script>
              $(function(){
                $('#uid_picker').on('change', function(){
                  var v = $(this).val(); if(v){ window.location = 'home-admin.php?page=form-master-data-jabatan&uid='+encodeURIComponent(v); }
                });
              });
            </script>
          <?php endif; ?>
        </div>
      </div>

      <!-- FORM ENTRY JABATAN -->
      <div class="jab-panel mb-4">
        <div class="jab-head"><h6>Tambah Data/Mutasi Jabatan</h6></div>
        <form method="post" action="" autocomplete="off">
          <input type="hidden" name="id_peg" value="<?php echo e($uid); ?>"/>

          <div class="row">
            <div class="col-md-6">
              <label>Kode & Nama Jabatan</label>
              <select name="kode_jabatan" id="kode_jabatan" class="form-control" required>
                <option value="">- pilih jabatan -</option>
                <?php foreach($ref_jabatan as $j): ?>
                  <option value="<?php echo e($j['kode_jabatan']); ?>"><?php echo e($j['kode_jabatan'].' — '.$j['jabatan']); ?></option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="jabatan" id="jabatanHidden">
            </div>
            <div class="col-md-6">
              <label>Unit Kerja</label>
              <select name="unit_kerja" id="unit_kerja" class="form-control" required>
                <option value="">- pilih unit kerja -</option>
                <?php foreach($ref_unit as $u): ?>
                  <option value="<?php echo e($u['kode_kantor_detail']); ?>"><?php echo e($u['kode_kantor_detail'].' — '.$u['nama_kantor']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3">
              <label>Status Jabatan</label>
              <select name="status_jab" class="form-control">
                <option value="Aktif">Aktif</option>
                <option value="Non">Non</option>
              </select>
            </div>
            <div class="col-md-5">
              <label>No. SK</label>
              <input type="text" name="no_sk" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Tanggal SK</label>
              <input type="date" name="tgl_sk" class="form-control" value="<?php echo e($today); ?>">
            </div>
          </div>

          <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Kembali</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>

      <?php if ($uid!=='' && !$pegawai): ?>
        <div class="alert alert-warning mt-3">Data pegawai dengan ID <b><?php echo e($uid); ?></b> tidak ditemukan.</div>
      <?php endif; ?>

      <!-- Panel Jabatan Aktif (Read-only) -->
      <?php if ($pegawai): ?>
      <div class="readonly-info mt-4">
        <div class="jab-head"><h6>Jabatan Saat Ini</h6></div>
        <?php if ($jabAktif): ?>
          <div class="row">
            <div class="col-md-4"><small class="text-muted">Kode / Nama Jabatan</small><br><strong><?php echo e($jabAktif['kode_jabatan']); ?> — <?php echo e($jabAktif['jabatan']); ?></strong> <span class="badge-aktif">Aktif</span></div>
            <div class="col-md-4"><small class="text-muted">Unit Kerja</small><br><?php echo e($jabAktif['unit_kerja']); ?> — <?php echo e($jabAktif['nama_kantor']); ?></div>
            <div class="col-md-4"><small class="text-muted">TMT s.d</small><br><?php echo fmt_date($jabAktif['tmt_jabatan']); ?> — <?php echo ($jabAktif['status_jab']==='Aktif'?'Sekarang':fmt_date($jabAktif['sampai_tgl'])); ?></div>
          </div>
          <div class="row">
            <div class="col-md-4"><small class="text-muted">No. SK</small><br><?php echo e($jabAktif['no_sk']); ?></div>
            <div class="col-md-4"><small class="text-muted">Tanggal SK</small><br><?php echo fmt_date($jabAktif['tgl_sk']); ?></div>
          </div>
        <?php else: ?>
          <div class="d-flex justify-content-between align-items-center">
            <div>Pegawai ini belum memiliki jabatan aktif yang tercatat.</div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(function(){
    function initSelect2(sel, placeholder){
      try{ $(sel).select2({ theme: 'bootstrap-5', width: '100%', placeholder: placeholder }); }catch(e){}
    }
    initSelect2('#kode_jabatan','- Pilih jabatan -');
    initSelect2('#unit_kerja','- Pilih Unit Kerja -');
    initSelect2('#uid_picker','- Pilih Pegawai -');

    $('#kode_jabatan').on('change', function(){
      var txt = $(this).find('option:selected').text().split('—');
      if(txt.length>1){ $('#jabatanHidden').val($.trim(txt[1])); }
    });
  });
</script>
</body>
</html>
