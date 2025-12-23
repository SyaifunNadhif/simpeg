<?php
if (session_id() === '') session_start();

// Koneksi
@include_once __DIR__ . '/../../dist/koneksi.php';
if (!isset($conn)) {
    @include_once __DIR__ . '/../../config/koneksi.php';
    if (isset($koneksi) && $koneksi) $conn = $koneksi;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Filter options
$optThn = array();
$qT = mysqli_query($conn, "SELECT DISTINCT tahun FROM tb_diklat ORDER BY tahun DESC");
if($qT){ while($r=mysqli_fetch_assoc($qT)) $optThn[] = $r['tahun']; }

$optJns = array();
$qJ = mysqli_query($conn, "SELECT DISTINCT diklat FROM tb_diklat ORDER BY diklat ASC");
if($qJ){ while($r=mysqli_fetch_assoc($qJ)) $optJns[] = $r['diklat']; }

$optKtr = array();
$qK = mysqli_query($conn, "SELECT kode_kantor_detail, nama_kantor FROM tb_kantor ORDER BY nama_kantor ASC");
if($qK){ while($r=mysqli_fetch_assoc($qK)) $optKtr[] = $r; }
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Daftar Pelatihan & Diklat</h1>
        <small class="text-muted">Menampilkan seluruh data riwayat pelatihan pegawai.</small>
      </div>
      <div class="col-sm-6 text-right">
        <a class="btn btn-default" href="home-admin.php"><i class="fas fa-home"></i></a>
        <a class="btn btn-success" href="home-admin.php?page=form-import-data-diklat"><i class="fas fa-file-import"></i> Import</a>
        <a class="btn btn-primary" href="home-admin.php?page=ref-diklat/form-master"><i class="fas fa-plus"></i> Tambah Data</a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="card card-outline card-primary">
      <div class="card-body">
        <div class="row">
          <div class="col-md-2">
            <label>Tahun</label>
            <select id="f_tahun" class="form-control select2">
              <option value="">- Semua -</option>
              <?php foreach($optThn as $t){ echo '<option value="'.h($t).'">'.h($t).'</option>'; } ?>
            </select>
          </div>

          <div class="col-md-4">
            <label>Jenis Diklat</label>
            <select id="f_diklat" class="form-control select2">
              <option value="">- Semua Jenis -</option>
              <?php foreach($optJns as $j){ echo '<option value="'.h($j).'">'.h($j).'</option>'; } ?>
            </select>
          </div>

          <div class="col-md-4">
            <label>Unit Kerja</label>
            <select id="f_kantor" class="form-control select2">
              <option value="">- Semua Kantor -</option>
              <?php foreach($optKtr as $k){ echo '<option value="'.h($k['kode_kantor_detail']).'">'.h($k['nama_kantor']).'</option>'; } ?>
            </select>
          </div>

          <div class="col-md-2 d-flex align-items-end">
            <button id="btnFilter" class="btn btn-primary btn-block">Tampil</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body table-responsive">
        <!-- Pakai ID yang kamu sering pakai di project: tabelDiklatAjax -->
        <table id="tabelDiklatAjax" class="table table-hover table-striped" style="width:100%">
          <thead>
            <tr>
              <th width="5%">NO</th>
              <th width="25%">NAMA PEGAWAI</th>
              <th width="20%">JENIS DIKLAT</th>
              <th width="20%">PENYELENGGARA</th>
              <th width="15%">UNIT KERJA</th>
              <th width="10%">AKSI</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>

  </div>
</section>

<!-- MODAL HAPUS (Bootstrap 4 markup aman) -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">Konfirmasi Hapus</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus data ini?</p>

        <div id="dataSummary" class="alert alert-light border">
          <i class="fas fa-spinner fa-spin"></i> Mengambil info...
        </div>

        <div class="form-group">
          <label>Alasan Penghapusan <span class="text-danger">*</span></label>
          <textarea id="deleteReason" class="form-control" rows="3" placeholder="Wajib diisi (misal: Duplikat, Salah Input)"></textarea>
        </div>

        <input type="hidden" id="deleteId">
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">Hapus ke Recycle Bin</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  // Select2 (kalau ada)
  try { $('.select2').select2({ theme: 'bootstrap4', width: '100%' }); } catch(e){}

  // DataTables
  var tbl = $('#tabelDiklatAjax').DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    ajax: {
      url: 'pages/ref-diklat/ajax-data-diklat.php',
      type: 'GET',
      data: function(d){
        // FIX: backend kamu baca param ini: tahun/diklat/kantor :contentReference[oaicite:4]{index=4}
        d.tahun  = $('#f_tahun').val() || '';
        d.diklat = $('#f_diklat').val() || '';
        d.kantor = $('#f_kantor').val() || '';
      }
    },
    columns: [
      { data: 'no', className: 'text-center', orderable: false },
      { data: 'nama_peg' },
      { data: 'diklat' },
      { data: 'penyelenggara' },
      { data: 'unit_kerja' },
      { data: 'aksi', className: 'text-center', orderable: false }
    ]
  });

  $('#btnFilter').on('click', function(){
    tbl.ajax.reload();
  });

  // Helper modal show/hide (BS4/BS5)
  function showModal(){
    // BS5
    if (window.bootstrap && bootstrap.Modal) {
      var el = document.getElementById('modalHapus');
      var inst = bootstrap.Modal.getInstance(el);
      if (!inst) inst = new bootstrap.Modal(el);
      inst.show();
      return true;
    }
    // BS4
    if (window.jQuery && $('#modalHapus').modal) {
      $('#modalHapus').modal('show');
      return true;
    }
    return false;
  }
  function hideModal(){
    // BS5
    if (window.bootstrap && bootstrap.Modal) {
      var el = document.getElementById('modalHapus');
      var inst = bootstrap.Modal.getInstance(el);
      if (inst) inst.hide();
      return;
    }
    // BS4
    if (window.jQuery && $('#modalHapus').modal) {
      $('#modalHapus').modal('hide');
      return;
    }
  }

  // =========================
  // FIX UTAMA: click delete pakai delegation GLOBAL
  // (tidak peduli tabel id apa / DOM DataTables berubah)
  // =========================
  $(document).off('click', '.btn-delete').on('click', '.btn-delete', function(e){
    e.preventDefault();

    var id = $(this).data('id');
    $('#deleteId').val(id);
    $('#deleteReason').val('');
    $('#dataSummary').html('<i class="fas fa-spinner fa-spin"></i> Mengambil info...');

    // Tampilkan modal (kalau modal tidak bisa, fallback Swal)
    var ok = showModal();
    if (!ok && window.Swal) {
      Swal.fire('Error', 'Bootstrap modal tidak aktif di halaman ini. (JS bootstrap belum ke-load)', 'error');
      return;
    }

    // Get info untuk isi modal
    $.ajax({
      url: 'pages/ref-diklat/process_soft_delete.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'get_info', id: id },
      success: function(res){
        if (res.status === 'success') {
          $('#dataSummary').html(
            '<b>Diklat:</b> ' + res.data.diklat + '<br>' +
            '<b>Pegawai:</b> ' + res.data.nama_peg + '<br>' +
            '<b>Tahun:</b> ' + res.data.tahun
          );
        } else {
          $('#dataSummary').html('<span class="text-danger">'+(res.message || 'Gagal ambil data')+'</span>');
        }
      },
      error: function(){
        $('#dataSummary').html('<span class="text-danger">Error koneksi server.</span>');
      }
    });
  });

  // Konfirmasi delete
  $('#btnConfirmDelete').off('click').on('click', function(){
    var id = $('#deleteId').val();
    var reason = $.trim($('#deleteReason').val());

    if (!reason) {
      if (window.Swal) Swal.fire('Error', 'Alasan wajib diisi!', 'warning');
      else alert('Alasan wajib diisi!');
      return;
    }

    var $btn = $(this);
    $btn.prop('disabled', true).text('Memproses...');

    $.ajax({
      url: 'pages/ref-diklat/process_soft_delete.php',
      type: 'POST',
      dataType: 'json',
      data: { action: 'delete', id: id, reason: reason },
      success: function(res){
        $btn.prop('disabled', false).text('Hapus ke Recycle Bin');
        hideModal();

        if (res.status === 'success') {
          if (window.Swal) Swal.fire('Berhasil', 'Data dipindahkan ke Recycle Bin.', 'success');
          else alert('Berhasil: data dipindahkan ke Recycle Bin.');
          tbl.ajax.reload(null, false);
        } else {
          if (window.Swal) Swal.fire('Gagal', res.message || 'Gagal hapus', 'error');
          else alert('Gagal: ' + (res.message || 'Gagal hapus'));
        }
      },
      error: function(){
        $btn.prop('disabled', false).text('Hapus ke Recycle Bin');
        if (window.Swal) Swal.fire('Error', 'Terjadi kesalahan server', 'error');
        else alert('Terjadi kesalahan server');
      }
    });
  });

});
</script>
