<?php
/*********************************************************
 * FILE    : pages/ref-jabatan/form-import-jabatan.php
 * MODULE  : SIMPEG — Import Jabatan Pegawai (Kolektif)
 * VERSION : v1.0 (PHP 5.6 compatible)
 * DATE    : 2025-09-07
 * AUTHOR  : EWS/SIMPEG BKK Jateng — by ChatGPT
 *
 * PURPOSE :
 *   - Upload & impor jabatan pegawai secara kolektif dari Excel/CSV.
 *   - Mendukung .xlsx (PHPExcel) dan .csv.
 *   - Aturan SIMPAN:
 *       • tmt_jabatan = tgl_sk
 *       • unit_kerja menyimpan kode_kantor_detail
 *       • status_jab='Aktif' → otomatis menutup jabatan aktif lama (Non, sampai_tgl = tgl_sk_baru - 1)
 *
 * FORMAT TEMPLATE (header kolom persis):
 *   id_peg | kode_jabatan | jabatan | unit_kerja | status_jab | no_sk | tgl_sk
 *   - id_peg        : kode pegawai (PK skema lama)
 *   - kode_jabatan  : wajib (sesuai tb_ref_jabatan)
 *   - jabatan       : opsional (jika kosong akan diisi dari tb_ref_jabatan berdasarkan kode_jabatan)
 *   - unit_kerja    : wajib; isi kode_kantor_detail (contoh KC01.01)
 *   - status_jab    : Aktif/Non (default Aktif jika kosong)
 *   - no_sk         : teks
 *   - tgl_sk        : yyyy-mm-dd atau dd/mm/yyyy
 *
 * DEPENDENCY:
 *   - PHPExcel (plugins/phpexcel/Classes/PHPExcel.php)
 *********************************************************/
if (session_id()==='') session_start();
require_once __DIR__ . '/../../dist/koneksi.php';

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$maxSize = 2 * 1024 * 1024; // 2MB
$today   = date('Y-m-d');

$flash = isset($_SESSION['flash_msg']) ? $_SESSION['flash_msg'] : '';
unset($_SESSION['flash_msg']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Impor Jabatan Pegawai (Kolektif)</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .card{border-radius:14px;border:1px solid rgba(0,0,0,.05);box-shadow:0 6px 24px rgba(0,0,0,.06)}
    .card-header{background:linear-gradient(90deg,#2563eb,#0ea5e9);color:#fff;border-radius:14px 14px 0 0}
  </style>
</head>
<body>
<div class="container my-4">
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Impor Jabatan Pegawai (Kolektif)</h5>
      <small>Unggah file Excel/CSV sesuai template</small>
    </div>
    <div class="card-body">
      <?php if ($flash!=''): ?>
        <script>Swal.fire({icon:'info', title:'Info', html: <?php echo json_encode($flash); ?>});</script>
      <?php endif; ?>

      <div class="mb-3">
        <b>Unduh Template:</b>
        <a class="btn btn-sm btn-outline-primary" href="pages/ref-jabatan/template-jabatan.xlsx">template-jabatan.xlsx</a>
        <a class="btn btn-sm btn-outline-secondary" href="pages/ref-jabatan/template-jabatan.csv">template-jabatan.csv</a>
      </div>

      <form class="row g-3" method="post" action="home-admin.php?page=simpan-import-jabatan" enctype="multipart/form-data">
        <div class="col-md-6">
          <label class="form-label">File Excel/CSV (max 2MB)</label>
          <input type="file" name="file_import" accept=".xlsx,.csv" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Mode</label>
          <select name="mode" class="form-select">
            <option value="insert">Insert Only</option>
            <option value="upsert">Update jika duplikat</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Simulasi</label>
          <select name="dryrun" class="form-select">
            <option value="1">Ya, cek dulu</option>
            <option value="0">Tidak, langsung proses</option>
          </select>
        </div>
        <div class="col-12 d-flex justify-content-between mt-2">
          <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Kembali</button>
          <button type="submit" class="btn btn-primary">Proses Impor</button>
        </div>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (int)$maxSize; ?>">
      </form>

      <hr>
      <div>
        <b>Catatan:</b>
        <ul>
          <li>Kolom wajib: <code>id_peg, kode_jabatan, unit_kerja, no_sk, tgl_sk</code></li>
          <li><code>status_jab</code> default <code>Aktif</code> bila dikosongkan.</li>
          <li>Tanggal boleh <code>yyyy-mm-dd</code> atau <code>dd/mm/yyyy</code>.</li>
          <li>Bila <code>status_jab = 'Aktif'</code>, sistem otomatis menutup jabatan aktif lama (sampai_tgl = tgl_sk - 1).</li>
        </ul>
      </div>
    </div>
  </div>
</div>
</body>
</html>