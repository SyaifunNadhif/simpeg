<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/form-master-data-suami-istri.php
 * MODULE  : SIMPEG — Data Suami/Istri (tb_suamiistri)
 * VERSION : v1.6 (v1.5 + Smart Redirect Logic)
 * DATE    : 2025-12-10
 *
 * CHANGELOG
 * - v1.6: Menambahkan logic redirect pintar. Jika akses dari Detail Pegawai,
 * tombol kembali/simpan akan mengarah ke Detail Pegawai lagi.
 * Jika tidak, kembali ke List Pasangan ter-filter.
 *********************************************************/

if (session_id()==='') session_start();
@include_once __DIR__ . '/../../dist/koneksi.php';
@include_once __DIR__ . '/../../dist/functions.php';
if (!isset($conn)) { @include_once __DIR__ . '/../../config/koneksi.php'; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function postv($k,$d=''){ return isset($_POST[$k]) ? trim($_POST[$k]) : $d; }
function clean($c,$s){ return mysqli_real_escape_string($c, trim($s)); }
function fmt_date($s){ return ($s && $s!='0000-00-00') ? date('d-m-Y', strtotime($s)) : '-'; }

$today = date('Y-m-d');
$mode  = isset($_GET['mode']) ? $_GET['mode'] : 'tambah';
$id_si = isset($_GET['id_si']) ? trim($_GET['id_si']) : '';
$uid   = isset($_GET['uid'])   ? preg_replace('~[^A-Za-z0-9_\-]~','', $_GET['uid']) : '';

// Ambil Data Suami Istri (Jika Edit) untuk memastikan UID terisi
$rowEdit = null;
if ($mode==='edit' && $id_si!=='') {
  $qe = mysqli_query($conn, "SELECT * FROM tb_suamiistri WHERE id_si='".clean($conn,$id_si)."' LIMIT 1");
  if ($qe && mysqli_num_rows($qe)>0) {
    $rowEdit = mysqli_fetch_assoc($qe);
    // Update UID dari database jika mode edit
    $uid = $rowEdit['id_peg'];
  }
}

// Ambil Data Pegawai
$pegawai = null;
if ($uid!=='') {
  $q = mysqli_query($conn, "SELECT id_peg,nama,jk,tempat_lhr,tgl_lhr FROM tb_pegawai WHERE id_peg='".clean($conn,$uid)."' LIMIT 1");
  if ($q && mysqli_num_rows($q)>0) $pegawai = mysqli_fetch_assoc($q);
}

// ==========================================================
// [LOGIC REDIRECT PINTAR] - Disisipkan disini brother
// ==========================================================

// 1. Default: Ke List Pasangan Ter-filter
$url_redirect = 'home-admin.php?page=form-view-data-suami-istri'; 
if($uid !== '') {
    $url_redirect .= '&uid=' . urlencode($uid);
}

// 2. Cek apakah form disubmit? (Ambil dari input hidden agar konsisten)
if (isset($_POST['url_asal']) && !empty($_POST['url_asal'])) {
    $url_redirect = $_POST['url_asal'];
} 
// 3. Cek Referer (Deteksi Datang dari Detail Pegawai)
elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    if (strpos($ref, 'view-detail-data-pegawai') !== false) {
        $url_redirect = 'home-admin.php?page=view-detail-data-pegawai&id_peg=' . urlencode($uid);
    }
}
// ==========================================================


$status=''; $errMsg='';

// generator id_si: SI0001, SI0002, ...
function generate_id_si($conn){
  $res = mysqli_query($conn,"SELECT MAX(CAST(SUBSTRING(id_si,3) AS UNSIGNED)) AS maxid FROM tb_suamiistri");
  $num = 0;
  if($res && ($r=mysqli_fetch_assoc($res))) $num = (int)$r['maxid'];
  return 'SI'.str_pad($num+1, 4, '0', STR_PAD_LEFT);
}

// submit
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $id_si        = postv('id_si');
  $id_peg       = clean($conn, postv('id_peg'));
  $nik          = postv('nik');
  $nama         = postv('nama');
  $tmp_lhr      = postv('tmp_lhr');
  $tgl_lhr      = postv('tgl_lhr');
  $pendidikan   = postv('pendidikan');
  $id_pekerjaan = postv('id_pekerjaan');
  $pekerjaan    = postv('pekerjaan');
  $status_hub   = postv('status_hub');
  $hp           = postv('hp');
  $bpjs         = postv('bpjs_pasangan');

  if ($id_peg==='')           $errMsg='ID Pegawai belum diisi.';
  elseif ($nama==='')         $errMsg='Nama pasangan wajib diisi.';
  elseif ($tmp_lhr==='')      $errMsg='Tempat lahir wajib diisi.';
  elseif ($tgl_lhr==='')      $errMsg='Tanggal lahir wajib diisi.';
  elseif ($id_pekerjaan==='') $errMsg='Kategori pekerjaan wajib dipilih.';
  elseif ($status_hub==='')   $errMsg='Status hubungan wajib dipilih.';

  if ($errMsg==='') {
    if ($mode==='edit' && $id_si!=='') {
      $sql = "UPDATE tb_suamiistri SET ".
             "id_peg='{$id_peg}',".
             "nik=".($nik!==''?"'".clean($conn,$nik)."'":"NULL").",".
             "nama='".clean($conn,$nama)."',".
             "tmp_lhr='".clean($conn,$tmp_lhr)."',".
             "tgl_lhr='".clean($conn,$tgl_lhr)."',".
             "pendidikan=".($pendidikan!==''?"'".clean($conn,$pendidikan)."'":"NULL").",".
             "id_pekerjaan='".clean($conn,$id_pekerjaan)."',".
             "pekerjaan=".($pekerjaan!==''?"'".clean($conn,$pekerjaan)."'":"NULL").",".
             "status_hub='".clean($conn,$status_hub)."',".
             "hp=".($hp!==''?"'".clean($conn,$hp)."'":"NULL").",".
             "bpjs_pasangan=".($bpjs!==''?"'".clean($conn,$bpjs)."'":"NULL")." ".
             "WHERE id_si='".clean($conn,$id_si)."' LIMIT 1";
      $ok = mysqli_query($conn,$sql);
      $status = $ok?'sukses':'gagal';
    } else {
      $new_id = generate_id_si($conn);
      $sql = "INSERT INTO tb_suamiistri(id_si,id_peg,nik,nama,tmp_lhr,tgl_lhr,pendidikan,id_pekerjaan,pekerjaan,status_hub,hp,bpjs_pasangan,date_reg)
              VALUES('{$new_id}','{$id_peg}',".
              ($nik!==''?"'".clean($conn,$nik)."'":"NULL").",".
              "'".clean($conn,$nama)."','".clean($conn,$tmp_lhr)."','".clean($conn,$tgl_lhr)."',".
              ($pendidikan!==''?"'".clean($conn,$pendidikan)."'":"NULL").",".
              "'".clean($conn,$id_pekerjaan)."',".
              ($pekerjaan!==''?"'".clean($conn,$pekerjaan)."'":"NULL").",".
              "'".clean($conn,$status_hub)."',".
              ($hp!==''?"'".clean($conn,$hp)."'":"NULL").",".
              ($bpjs!==''?"'".clean($conn,$bpjs)."'":"NULL").",".
              "'{$today}')";
      $ok = mysqli_query($conn,$sql);
      $status = $ok?'sukses':'gagal';
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
<title><?php echo ($mode==='edit'?'Ubah':'Tambah'); ?> Data Suami/Istri</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  .form-section{max-width:880px;margin:20px auto}
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
        <h5 class="mb-0"><?php echo ($mode==='edit'?'Ubah':'Tambah'); ?> Data Suami/Istri</h5>
        <small>Lengkapi data pasangan sesuai dokumen kependudukan.</small>
      </div>
    </div>
    <div class="card-body">

<?php if($status==='sukses'): ?>
<script>
Swal.fire({icon:'success',title:'Tersimpan',text:'Data pasangan berhasil disimpan.', timer: 1500, showConfirmButton: false}).then(function(){
  // Menggunakan URL Redirect Pintar
  window.location='<?php echo $url_redirect; ?>';
});
</script>
<?php elseif($status==='gagal' && $errMsg!=''): ?>
<script>Swal.fire({icon:'error',title:'Gagal',text:<?php echo json_encode($errMsg); ?>});</script>
<?php elseif($status==='gagal'): ?>
<script>Swal.fire({icon:'error',title:'Gagal',text:'Terjadi kesalahan penyimpanan.'});</script>
<?php endif; ?>

<form method="post" action="" autocomplete="off">
  <input type="hidden" name="url_asal" value="<?php echo e($url_redirect); ?>">

<?php if($mode==='edit'): ?>
  <input type="hidden" name="id_si" value="<?php echo e($rowEdit?$rowEdit['id_si']:''); ?>">
<?php endif; ?>

<?php
  // id pegawai yang harus terpilih pada select (edit -> id_peg dari rowEdit; tambah -> uid bila ada)
  $currentPeg = ($mode==='edit' && $rowEdit) ? $rowEdit['id_peg'] : $uid;
?>
<div class="form-group">
  <label>Pilih Pegawai <span class="text-danger">*</span></label>

  <select name="id_peg" id="id_peg" class="form-control select2bs4" <?php echo ($mode==='edit' ? 'disabled' : 'required'); ?> style="width:100%">
    <option value="">— pilih pegawai —</option>
    <?php
    $rp = mysqli_query($conn,"SELECT id_peg,nama FROM tb_pegawai WHERE status_aktif=1 ORDER BY id_peg");
    if($rp){ while($pg=mysqli_fetch_assoc($rp)){
      $sel = ($currentPeg === $pg['id_peg']) ? 'selected' : '';
      echo '<option value="'.e($pg['id_peg']).'" '.$sel.'>'.e($pg['id_peg'].' — '.$pg['nama'])."</option>";
    }}
    ?>
  </select>

  <?php if($mode==='edit' && $currentPeg!=''): ?>
    <input type="hidden" name="id_peg" value="<?php echo e($currentPeg); ?>">
  <?php endif; ?>
</div>

<div class="row">
  <div class="col-md-6">
    <label>Nama Pasangan <span class="text-danger">*</span></label>
    <input type="text" name="nama" class="form-control" required value="<?php echo e($rowEdit?$rowEdit['nama']:''); ?>">
  </div>
  <div class="col-md-6">
    <label>NIK</label>
    <input type="text" name="nik" maxlength="16" class="form-control" value="<?php echo e($rowEdit?$rowEdit['nik']:''); ?>">
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-6">
    <label>Tempat Lahir <span class="text-danger">*</span></label>
    <input type="text" name="tmp_lhr" class="form-control" required value="<?php echo e($rowEdit?$rowEdit['tmp_lhr']:''); ?>">
  </div>
  <div class="col-md-6">
    <label>Tanggal Lahir <span class="text-danger">*</span></label>
    <input type="date" name="tgl_lhr" class="form-control" required value="<?php echo e($rowEdit?$rowEdit['tgl_lhr']:''); ?>">
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-4">
    <label>Pendidikan</label>
    <?php $pendList=array('SD','SMP','SMA','D1','D2','D3','D4','S1','S2','S3'); $pdSel=$rowEdit?$rowEdit['pendidikan']:''; ?>
    <select name="pendidikan" class="form-control select2bs4" style="width:100%">
      <option value="">- pilih -</option>
      <?php foreach($pendList as $pd): ?>
        <option value="<?php echo e($pd); ?>" <?php echo ($pdSel===$pd?'selected':''); ?>><?php echo e($pd); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <label>Kategori Pekerjaan <span class="text-danger">*</span></label>
    <select name="id_pekerjaan" id="id_pekerjaan" class="form-control select2bs4" required style="width:100%">
      <option value="">— pilih —</option>
      <?php
      $qpk=mysqli_query($conn,"SELECT id_pekerjaan,desc_pekerjaan FROM tb_master_pekerjaan ORDER BY id_pekerjaan");
      $sel=$rowEdit?$rowEdit['id_pekerjaan']:'';
      if($qpk){ while($pk=mysqli_fetch_assoc($qpk)){
        echo '<option value="'.e($pk['id_pekerjaan']).'" '.($sel==$pk['id_pekerjaan']?'selected':'').'>'.e($pk['desc_pekerjaan'])."</option>";
      }}
      ?>
    </select>
  </div>
  <div class="col-md-4">
    <label>Nama Pekerjaan</label>
    <input type="text" name="pekerjaan" id="pekerjaan_text" class="form-control" value="<?php echo e($rowEdit?$rowEdit['pekerjaan']:''); ?>" placeholder="cth: Wiraswasta" readonly>
  </div>
</div>

<div class="row mt-2">
  <div class="col-md-4">
    <label>Status Hubungan <span class="text-danger">*</span></label>
    <?php $hubSel=$rowEdit?$rowEdit['status_hub']:''; ?>
    <select name="status_hub" class="form-control select2bs4" required style="width:100%">
      <option value="">— pilih —</option>
      <option value="Suami" <?php echo ($hubSel==='Suami'?'selected':''); ?>>Suami</option>
      <option value="Istri" <?php echo ($hubSel==='Istri'?'selected':''); ?>>Istri</option>
    </select>
  </div>
  <div class="col-md-4">
    <label>No. HP</label>
    <input type="text" name="hp" maxlength="13" class="form-control" value="<?php echo e($rowEdit?$rowEdit['hp']:''); ?>">
  </div>
  <div class="col-md-4">
    <label>No. BPJS Pasangan</label>
    <input type="text" name="bpjs_pasangan" maxlength="20" class="form-control" value="<?php echo e($rowEdit?$rowEdit['bpjs_pasangan']:''); ?>">
  </div>
</div>

<div class="mt-4 d-flex justify-content-between">
  <a href="<?php echo $url_redirect; ?>" class="btn btn-outline-secondary">Kembali</a>
  <button type="submit" class="btn btn-primary">Simpan</button>
</div>
</form>

</div></div></div>

<script>
$(document).ready(function(){

  // cari key yang kemungkinan berisi id_si dalam objek (rekursif, aman)
  function findIdSiRecursive(obj, depth) {
    depth = (typeof depth === 'number') ? depth : 3; // max depth
    if (obj === null || typeof obj === 'undefined') return null;

    // jika primitif (string/number) tidak cocok
    if (typeof obj === 'string' || typeof obj === 'number') return null;

    // if it's array, iterate elements
    if (Array.isArray(obj)) {
      for (var i=0;i<obj.length;i++){
        var v = findIdSiRecursive(obj[i], depth-1);
        if (v) return v;
      }
      return null;
    }

    // object: check direct keys first (case-insensitive)
    var keys = Object.keys(obj || {});
    for (var k=0;k<keys.length;k++){
      var key = keys[k];
      var low = key.toLowerCase();

      // direct matches likely to be id fields
      if (low === 'id_si' || low === 'id' || low === 'id_si_str' || low === 'idsi' || low === 'idpasangan' || low.indexOf('id_si') !== -1) {
        var val = obj[key];
        if (val !== null && typeof val !== 'undefined' && String(val).trim() !== '') return String(val);
      }

      // sometimes id is nested under data or DT_RowData etc.
      if (low === 'data' || low === 'dt_rowdata' || low === 'dt_row' || low === 'row' || low === 'attributes') {
        if (depth > 0) {
          var v2 = findIdSiRecursive(obj[key], depth-1);
          if (v2) return v2;
        }
      }
    }

    // fallback: check any key containing both 'id' and 'si'
    for (var k2=0;k2<keys.length;k2++){
      var kk = keys[k2].toLowerCase();
      if (kk.indexOf('id') !== -1 && kk.indexOf('si') !== -1) {
        var valk = obj[keys[k2]];
        if (valk !== null && typeof valk !== 'undefined' && String(valk).trim() !== '') return String(valk);
      }
    }

    // deep dive into values (if depth left)
    if (depth > 0) {
      for (var k3=0;k3<keys.length;k3++){
        var nested = obj[keys[k3]];
        if (typeof nested === 'object' && nested !== null) {
          var v3 = findIdSiRecursive(nested, depth-1);
          if (v3) return v3;
        }
      }
    }

    return null;
  }

  var table = $('#tblPasangan').DataTable({
    processing: true, serverSide: true, searching: true, responsive: true,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
    ajax: { 
        url: 'pages/ref-keluarga/ajax-data-pasangan.php', 
        type: 'GET', 
        data: { uid: <?php echo json_encode($uid); ?> },
        dataSrc: function(json){
            // debug: tampilkan sample 1 row agar kita lihat struktur
            try {
              if (json && json.data && json.data.length) {
                console.info('AJAX sample row (cek struktur):', json.data[0]);
              } else {
                console.info('AJAX response (no data or different structure):', json);
              }
            } catch(e){ console.warn('debug log error', e); }
            return json.data || [];
        }
    },
    columns: [
      { data: 'no', orderable:false, className: 'text-center fw-bold text-secondary' },
      { data: 'nama_peg', className: 'fw-bold text-dark', render: function(data, type, row) { if(row.id_peg && data) return `<div>${data}</div><small class='text-muted'>${row.id_peg}</small>`; return data || '-'; } },
      { data: 'nama', className: 'text-primary fw-medium', defaultContent: '-' },
      { data: 'nik', defaultContent: '-' },
      { data: 'pendidikan', defaultContent: '-' },
      { data: 'pekerjaan_desc', defaultContent: '-' },
      { data: 'status_hub', render: function(data) {
            var txt = data?data:'-';
            var cls = 'bg-light text-dark border';
            if(data && data.toLowerCase() === 'suami') cls = 'bg-info bg-opacity-10 text-info border-info border-opacity-25';
            else if(data && data.toLowerCase() === 'istri') cls = 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25';
            return '<span class="badge '+cls+' px-2 py-1 rounded-pill">'+txt+'</span>';
        }, defaultContent: '-' 
      },
      { 
        data: null, orderable: false, className: 'text-center',
        render: function(data, type, row) {
            // coba cari id_si rekursif di object row
            var idSi = findIdSiRecursive(row, 4);
            // jika belum, coba juga row.data, row.DT_RowData, row[0]
            if(!idSi) {
                idSi = findIdSiRecursive(row.data || row.DT_RowData || row.row || row[0] || null, 3);
            }

            if (!idSi) {
                console.warn('Tidak menemukan id_si pada row (render):', row);
                return '<span class="text-muted">-</span>';
            }

            // pastikan string (agar leading zero tidak hilang)
            idSi = String(idSi);
            // dapatkan uid pegawai jika ada
            var uidPeg = row.id_peg || row.idPeg || (row.data && row.data.id_peg) || '';

            var href = 'home-admin.php?page=form-master-data-suami-istri&mode=edit&id_si=' + encodeURIComponent(idSi) + '&uid=' + encodeURIComponent(uidPeg || '');
            return `
                <a href="${href}" 
                   class="btn btn-primary btn-sm rounded-circle d-inline-flex justify-content-center align-items-center" 
                   style="width: 34px; height: 34px;"
                   title="Edit Data">
                    <i class="fas fa-pencil-alt" style="font-size:13px;"></i>
                </a>`;
        }
      }
    ],
    language: { search: "", searchPlaceholder: "Cari data...", lengthMenu: "_MENU_", info: "Menampilkan _START_ - _END_ dari _TOTAL_ data", paginate: { first: "«", last: "»", next: "›", previous: "‹" }, processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat...' },
    dom: "<'row px-3 pt-3 align-items-center'<'col-6 col-md-6'l><'col-6 col-md-6'f>>" + "<'row px-3'<'col-sm-12'tr>>" + "<'row px-3 pb-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
  });

});
</script>


</body>
</html>