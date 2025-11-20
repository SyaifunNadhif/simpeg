<script>
$(document).ready(function(){
  var table = $('#tblJabatan').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'pages/ref-jabatan/ajax-jabatan-aktif.php',
      type: 'GET',
      data: function(d){
        d.filter_unit = $('#filterUnit').val() || '';
        d.filter_jab  = $('#filterJabatan').val() || '';
      }
    },
    columns: [
      { data: null, orderable:false, render: function(data, type, row, meta){ return meta.row + meta.settings._iDisplayStart + 1; }}, // No
      { data: 'id_peg' },
      { data: 'nama' },
      { data: 'kode_jabatan' },
      { data: 'jabatan' },
      { data: 'unit_kerja' },
      { data: 'tmt_jabatan' },
      { data: 'sampai_tgl' },
      { data: 'no_sk' },
      { data: 'tgl_sk' },
      { data: 'status', orderable:false },
      { data: 'action', orderable:false }
    ],
    order: [[5,'asc'],[4,'asc']],
    pageLength: 10,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Semua']],
    language: {
      url: 'assets/js/plugin/datatables/i18n/Indonesian.json',
      emptyTable: 'Belum ada data jabatan aktif.'
    }
  });

  $('#filterUnit, #filterJabatan').on('change', function(){
    table.ajax.reload();
  });
});
</script>

<script>
$(function(){
  // aktifkan select2
  $('.select2').select2({ theme: 'bootstrap4', placeholder: 'Pilih', allowClear: true });

  // inisialisasi DataTable serverSide
  var table = $('#tblJabatan2').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'pages/ref-jabatan/ajax-jabatan-aktif.php',
      type: 'GET',
      data: function(d){
        d.filter_unit = $('#filterUnit').val() || '';
        d.filter_jab  = $('#filterJabatan').val() || '';
      }
    },
    columns: [
      { data: null, orderable:false, render: function(data, type, row, meta){ return meta.row + meta.settings._iDisplayStart + 1; }},
      { data: 'id_peg' }, { data: 'nama' }, { data: 'kode_jabatan' },
      { data: 'jabatan' }, { data: 'unit_kerja' },
      { data: 'tmt_jabatan' }, { data: 'sampai_tgl' }, { data: 'no_sk' },
      { data: 'tgl_sk' }, { data: 'status', orderable:false }, { data: 'action', orderable:false }
    ],
    order: [[5,'asc'],[4,'asc']],
    pageLength: 10,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Semua']],
    language: { url: 'assets/js/plugin/datatables/i18n/Indonesian.json' }
  });

  // reload ketika filter berubah (select2 menembakkan 'change')
  $('#filterUnit, #filterJabatan').on('change', function(){ table.ajax.reload(); });
});
</script>