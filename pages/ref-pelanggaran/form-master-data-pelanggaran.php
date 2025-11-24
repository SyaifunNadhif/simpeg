<?php
/*********************************************************
 * FILE    : pages/pelanggaran/form-master-data-hukuman.php
 * MODULE  : Form Input Pelanggaran (Fix Pejabat Dropdown)
 * VERSION : v2.5
 *********************************************************/

include "dist/koneksi.php";
include "dist/library.php";

// --- LOGIKA FILTER CABANG (Hanya untuk Pegawai Pelanggar) ---
$hak_akses      = isset($_SESSION['hak_akses']) ? strtolower($_SESSION['hak_akses']) : '';
$kode_kantor    = isset($_SESSION['kode_kantor']) ? $_SESSION['kode_kantor'] : '';

// Default Filter: True
$where_clause_pegawai = "WHERE 1=1"; 

// Jika BUKAN Admin (misal: Kepala), filter pegawai sesuai cabangnya
if ($hak_akses !== 'admin') {
    $where_clause_pegawai .= " AND id_peg IN (
        SELECT id_peg FROM tb_jabatan 
        WHERE unit_kerja = '$kode_kantor' AND status_jab = 'Aktif'
    )";
}
?>

<style>
    .card-modern {
        border: none; border-radius: 15px; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
        background: #fff;
    }
    .form-header-modern {
        background: #f8f9fa; border-bottom: 1px solid #f1f1f1;
        padding: 20px; border-radius: 15px 15px 0 0;
    }
    .input-modern {
        border-radius: 10px; border: 1px solid #e2e8f0;
        padding: 10px 15px; height: 45px; font-size: 0.95rem;
        width: 100%;
    }
    .input-modern:focus { border-color: #dc3545; box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1); outline: none; }
    .input-modern[readonly] { background-color: #e9ecef; cursor: not-allowed; }

    .form-label-modern {
        font-size: 0.8rem; font-weight: 700; color: #6c757d;
        text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
    }

    .btn-modern {
        border-radius: 50px; padding: 10px 30px; font-weight: 600; transition: 0.3s;
    }
    .btn-modern:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
</style>

<section class="content-header pt-4 pb-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 font-weight-bold text-dark" style="font-size: 1.8rem;">Input Pelanggaran</h1>
                <p class="text-muted mb-0">Form pencatatan sanksi dan hukuman disiplin</p>
            </div>
            <div>
                <ol class="breadcrumb bg-transparent p-0 m-0 small">
                    <li class="breadcrumb-item"><a href="home-admin.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="home-admin.php?page=form-view-data-hukuman">Pelanggaran</a></li>
                    <li class="breadcrumb-item active">Input</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                <div class="card card-modern">
                    <div class="form-header-modern">
                        <h5 class="m-0 font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i> Form Data Pelanggaran</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        <form role="form" id="formPelanggaran" action="home-admin.php?page=master-data-hukuman" method="POST" enctype="multipart/form-data">
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label class="form-label-modern">Pilih Pegawai (Pelanggar) <span class="text-danger">*</span></label>
                                        <select name="id_peg" id="id_peg" class="form-control select2bs4 input-modern" required style="width: 100%;">
                                            <option value="">-- Cari Nama / NIP --</option>
                                            <?php
                                            // Query Khusus Pegawai (Sesuai Cabang Login)
                                            $data = mysqli_query($conn, "SELECT id_peg, nama FROM tb_pegawai $where_clause_pegawai ORDER BY nama ASC");
                                            while ($row = mysqli_fetch_array($data)) {
                                                echo '<option value="'.$row['id_peg'].'">'.$row['nama'].' ('.$row['id_peg'].')</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jenis Sanksi <span class="text-danger">*</span></label>
                                        <select name="hukuman" class="form-control input-modern select2bs4" required style="width: 100%;">
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="Surat Peringatan I">Surat Peringatan I</option>
                                            <option value="Surat Peringatan II">Surat Peringatan II</option>
                                            <option value="Surat Peringatan III">Surat Peringatan III</option>
                                            <option value="Skorsing">Skorsing</option>
                                            <option value="PTDH">PTDH (Pemberhentian)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label-modern">Keterangan Pelanggaran</label>
                                <textarea name="keterangan" class="form-control input-modern" rows="3" style="height: auto;" placeholder="Deskripsikan pelanggaran..."></textarea>
                            </div>

                            <h6 class="text-muted font-weight-bold border-bottom pb-2 mb-3 mt-4 text-uppercase" style="font-size: 0.85rem;">Detail SK Hukuman</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Pejabat Pengesah <span class="text-danger">*</span></label>
                                        <select name="pejabat_sk" id="pejabat_sk" class="form-control select2bs4 input-modern" style="width: 100%;">
                                            <option value="" data-jabatan="">-- Pilih Pejabat --</option>
                                            <?php
                                            // Query Pejabat (GLOBAL - Agar cabang bisa pilih pejabat pusat)
                                            $qPejabat = mysqli_query($conn, "
                                                SELECT p.nama, j.jabatan 
                                                FROM tb_pegawai p 
                                                LEFT JOIN tb_jabatan j ON p.id_peg = j.id_peg 
                                                WHERE j.status_jab = 'Aktif' 
                                                ORDER BY p.nama ASC
                                            ");
                                            
                                            // Simpan ke array agar bisa dipakai di dropdown bawah (pemulih)
                                            $list_pejabat = [];
                                            while ($pj = mysqli_fetch_array($qPejabat)) {
                                                $list_pejabat[] = $pj;
                                                echo '<option value="'.$pj['nama'].'" data-jabatan="'.$pj['jabatan'].'">'.$pj['nama'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jabatan Pengesah</label>
                                        <input type="text" name="jabatan_sk" id="jabatan_sk" class="form-control input-modern" placeholder="Otomatis terisi..." readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Nomor SK <span class="text-danger">*</span></label>
                                        <input type="text" name="no_sk" class="form-control input-modern" style="text-transform:uppercase" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tanggal SK <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl_sk" class="form-control input-modern" required>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted font-weight-bold border-bottom pb-2 mb-3 mt-4 text-uppercase" style="font-size: 0.85rem;">Detail Pemulihan (Opsional)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Pejabat Pemulih</label>
                                        <select name="pejabat_pulih" id="pejabat_pulih" class="form-control select2bs4 input-modern" style="width: 100%;">
                                            <option value="" data-jabatan="">-- Pilih Pejabat (Opsional) --</option>
                                            <?php
                                            // Loop ulang array yang sudah diambil diatas
                                            foreach ($list_pejabat as $pj2) {
                                                echo '<option value="'.$pj2['nama'].'" data-jabatan="'.$pj2['jabatan'].'">'.$pj2['nama'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Jabatan Pemulih</label>
                                        <input type="text" name="jabatan_pulih" id="jabatan_pulih" class="form-control input-modern" placeholder="Otomatis terisi..." readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">No. SK Pemulihan</label>
                                        <input type="text" name="no_pulih" class="form-control input-modern">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="form-label-modern">Tgl Pemulihan</label>
                                        <input type="date" name="tgl_pulih" class="form-control input-modern">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-right mt-5 border-top pt-4">
                                <a href="home-admin.php?page=form-view-data-hukuman" class="btn btn-light btn-modern mr-2 text-muted border">Batal</a>
                                <button type="submit" name="save" value="save" class="btn btn-danger btn-modern shadow-sm">
                                    <i class="fa fa-save mr-2"></i> Simpan Data
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>  
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(function () {
    // Initialize Select2
    $('.select2bs4').select2({
      theme: 'bootstrap4',
      placeholder: "Pilih opsi...",
      allowClear: true
    });

    // 1. LOGIKA AUTO FILL - PEJABAT PENGESAH
    $('#pejabat_sk').on('change', function() {
        var jabatan = $(this).find(':selected').data('jabatan');
        $('#jabatan_sk').val(jabatan);
    });

    // 2. LOGIKA AUTO FILL - PEJABAT PEMULIH
    $('#pejabat_pulih').on('change', function() {
        var jabatan = $(this).find(':selected').data('jabatan');
        $('#jabatan_pulih').val(jabatan);
    });
  });
</script>