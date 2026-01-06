<?php
// pages/pegawai/form-ganti-foto.php

// Matikan error display agar tidak merusak output JSON/Redirect
ini_set('display_errors', 0);
error_reporting(0);

include __DIR__ . '/../../dist/koneksi.php';

// Validasi ID Pegawai
if (!isset($_GET['id_peg'])) die("Error. No Kode Selected!");
$id_peg = mysqli_real_escape_string($conn, $_GET['id_peg']);

// --- HANDLE POST REQUEST (PROSES SIMPAN) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil URL Redirect
    $redirect_url = isset($_POST['redirect_back']) && !empty($_POST['redirect_back']) 
                    ? $_POST['redirect_back'] 
                    : 'home-admin.php?page=profil-pegawai';

    // Validasi Data Gambar
    if (!isset($_POST['cropped_image']) || empty($_POST['cropped_image'])) {
        echo_swal('Gagal!', 'Data gambar tidak ditemukan.', 'error');
        exit;
    }

    $dataURL = $_POST['cropped_image'];

    // 1. Validasi Ukuran (Max 3MB)
    // Hitung ukuran base64 string secara kasar
    $sizeInBytes = (strlen($dataURL) * 3 / 4) - substr_count(substr($dataURL, -2), '=');
    $maxSizeMB = 3;
    $maxSizeBytes = $maxSizeMB * 1024 * 1024;

    if ($sizeInBytes > $maxSizeBytes) {
        echo_swal('File Terlalu Besar', 'Maksimal 3MB.', 'error');
        exit;
    }

    // 2. Proses Decode Base64
    $parts = explode(',', $dataURL);
    if (count($parts) !== 2) {
        echo_swal('Error!', 'Format data gambar salah.', 'error');
        exit;
    }

    $data = base64_decode($parts[1]);
    if ($data === false) {
        echo_swal('Error!', 'Gagal decode gambar.', 'error');
        exit;
    }

    // 3. Persiapan File
    $nama_file_baru = 'foto_' . $id_peg . '_' . time() . '.jpg';
    $folder_tujuan  = __DIR__ . '/../../pages/assets/foto/'; // Path absolut folder
    $path_tujuan    = $folder_tujuan . $nama_file_baru;

    // Cek apakah folder ada dan bisa ditulisi
    if (!is_dir($folder_tujuan)) {
        if (!mkdir($folder_tujuan, 0755, true)) {
            echo_swal('Error Server', 'Gagal membuat folder upload.', 'error');
            exit;
        }
    }

    if (!is_writable($folder_tujuan)) {
        echo_swal('Error Permission', 'Folder upload tidak bisa ditulisi (Permission Denied). Hubungi Admin.', 'error');
        exit;
    }
    
    // 4. Simpan File Baru
    if (file_put_contents($path_tujuan, $data)) {
        
        // Hapus foto lama jika ada
        $qLama = mysqli_query($conn, "SELECT foto FROM tb_pegawai WHERE id_peg='$id_peg'");
        if ($qLama && mysqli_num_rows($qLama) > 0) {
            $rLama = mysqli_fetch_assoc($qLama);
            $fileLama = $folder_tujuan . $rLama['foto'];
            if (!empty($rLama['foto']) && file_exists($fileLama) && is_file($fileLama)) {
                @unlink($fileLama); // Pakai @ biar gak error warning
            }
        }

        // Update Database
        $update = mysqli_query($conn, "UPDATE tb_pegawai SET foto='$nama_file_baru' WHERE id_peg='$id_peg'");
        
        if ($update) {
            echo_swal('Berhasil!', 'Foto pegawai telah diperbarui.', 'success', $redirect_url);
            exit;
        } else {
            // Jika DB gagal update, hapus file yg baru diupload biar ga nyampah
            @unlink($path_tujuan); 
            echo_swal('Gagal DB!', 'Gagal update database: ' . mysqli_error($conn), 'error');
            exit;
        }

    } else {
        echo_swal('Gagal Simpan!', 'Gagal menulis file ke server.', 'error');
        exit;
    }
}

// Helper Function untuk Output SweetAlert
function echo_swal($title, $text, $icon, $redirect = null) {
    echo '<!DOCTYPE html><html><head><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script></head><body style="background:#f4f6f9">';
    echo "<script>
        Swal.fire({
            title: '$title',
            text: '$text',
            icon: '$icon',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            " . ($redirect ? "window.location.href = '$redirect';" : "history.back();") . "
        });
    </script>";
    echo '</body></html>';
}

// --- TAMPILAN FORM (METHOD GET) ---

$redirect_back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'home-admin.php?page=profil-pegawai';

$q = mysqli_query($conn, "SELECT nama, foto FROM tb_pegawai WHERE id_peg = '$id_peg'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<div class='alert alert-warning'>Data pegawai tidak ditemukan.</div>";
    exit;
}
$peg = mysqli_fetch_assoc($q);
$nama = htmlspecialchars($peg['nama'], ENT_QUOTES, 'UTF-8');
$foto_file = trim($peg['foto']);

// Path Foto Display
$foto_display = 'dist/img/avatar5.png'; // Default
if (!empty($foto_file)) {
    $path_fisik = __DIR__ . '/../../pages/assets/foto/' . $foto_file;
    if (file_exists($path_fisik)) {
        // Tambah time() biar cache browser ke-refresh
        $foto_display = 'pages/assets/foto/' . $foto_file . '?v=' . time(); 
    }
}
?>

<style>
    .crop-container { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .editor-area { flex: 1; min-width: 300px; max-width: 500px; display: flex; flex-direction: column; align-items: center; }
    .crop-frame { width: 320px; height: 320px; border-radius: 50%; border: 8px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.15); overflow: hidden; position: relative; background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAACpJREFUeNpiVk6xcgYDCgAjIyMp+k0YGBgY/v///x8Hxk+00Q9G4w8ABBgAVj0E0/2/j/QAAAAASUVORK5CYII='); cursor: grab; }
    .crop-frame:active { cursor: grabbing; }
    .crop-image { position: absolute; top: 0; left: 0; max-width: none; user-select: none; -webkit-user-drag: none; transform-origin: center center; }
    .preview-area { flex: 1; min-width: 250px; max-width: 400px; text-align: center; border-left: 1px solid #eee; padding-left: 30px; display: flex; flex-direction: column; justify-content: center; }
    .preview-circle { width: 160px; height: 160px; border-radius: 50%; overflow: hidden; border: 4px solid #e9ecef; margin: 0 auto 20px; background: #f8f9fa; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .control-group { width: 100%; margin-top: 20px; }
    .range-slider { width: 100%; margin: 15px 0; cursor: pointer; }
    .helper-text { font-size: 13px; color: #888; margin-top: 5px; text-align: center; }
    .btn-block { width: 100%; display: block; }
    @media(max-width: 768px) {
        .preview-area { border-left: none; padding-left: 0; border-top: 1px solid #eee; padding-top: 20px; }
        .crop-container { gap: 15px; }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Ganti Foto Pegawai</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Ganti Foto</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Upload Foto untuk <b><?= $nama ?></b></h3>
            </div>
            <div class="card-body">
                <div class="crop-container">
                    
                    <div class="editor-area">
                        <div class="crop-frame" id="cropBox">
                            <img id="cropImage" class="crop-image" src="<?= htmlspecialchars($foto_display) ?>" alt="Editor">
                        </div>
                        
                        <div class="control-group">
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="fileInput" accept="image/*">
                                <label class="custom-file-label" for="fileInput">Pilih Foto Baru...</label>
                            </div>
                            <label class="text-muted small"><i class="fas fa-search-minus"></i> Zoom <i class="fas fa-search-plus"></i></label>
                            <input type="range" class="custom-range range-slider" id="zoomRange" min="0.5" max="3" step="0.01" value="1">
                            <div class="helper-text"><i class="fas fa-arrows-alt"></i> Geser gambar untuk memposisikan wajah di tengah lingkaran.</div>
                        </div>
                    </div>

                    <div class="preview-area">
                        <h5 class="mb-3 text-muted font-weight-bold">Preview Hasil</h5>
                        <canvas id="previewCanvas" class="preview-circle" width="300" height="300"></canvas>
                        
                        <div class="mt-4" style="width: 100%;">
                            <button id="btnSave" class="btn btn-primary btn-block btn-lg shadow-sm mb-2">
                                <i class="fas fa-save mr-1"></i> Simpan Foto
                            </button>
                            <a href="<?= htmlspecialchars($redirect_back) ?>" class="btn btn-default btn-block">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                        </div>
                        <div class="helper-text mt-2">Ukuran file maksimal: <b>3 MB</b></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<form id="postForm" method="POST" style="display:none">
    <input type="hidden" name="cropped_image" id="cropped_image_input" value="">
    <input type="hidden" name="redirect_back" value="<?= htmlspecialchars($redirect_back) ?>">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// JAVASCRIPT LOGIC (SAMA SEPERTI YANG KAMU PUNYA, CUMA SAYA RAPIKAN DIKIT)
document.addEventListener('DOMContentLoaded', function() {
    const cropBox   = document.getElementById('cropBox');
    const img       = document.getElementById('cropImage');
    const fileInput = document.getElementById('fileInput');
    const zoomRange = document.getElementById('zoomRange');
    const canvas    = document.getElementById('previewCanvas');
    const ctx       = canvas.getContext('2d');
    const btnSave   = document.getElementById('btnSave');
    const postForm  = document.getElementById('postForm');
    const hiddenInput = document.getElementById('cropped_image_input');

    let state = { imgWidth: 0, imgHeight: 0, scale: 1, posX: 0, posY: 0, isDragging: false, startX: 0, startY: 0, boxSize: 320 };

    img.onload = function() {
        state.imgWidth  = img.naturalWidth;
        state.imgHeight = img.naturalHeight;
        fitImageToBox();
        render(); 
    };

    function fitImageToBox() {
        const ratioW = state.boxSize / state.imgWidth;
        const ratioH = state.boxSize / state.imgHeight;
        state.scale = Math.max(ratioW, ratioH); 
        state.posX = (state.boxSize - (state.imgWidth * state.scale)) / 2;
        state.posY = (state.boxSize - (state.imgHeight * state.scale)) / 2;
        zoomRange.value = state.scale;
        zoomRange.min = state.scale * 0.5; 
        zoomRange.max = state.scale * 3;   
    }

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if(file.size > 5 * 1024 * 1024) { 
             Swal.fire('File Terlalu Besar', 'Mohon pilih foto di bawah 5MB.', 'warning');
             this.value = ''; return;
        }
        e.target.nextElementSibling.innerText = file.name;
        const reader = new FileReader();
        reader.onload = function(ev) { img.src = ev.target.result; }
        reader.readAsDataURL(file);
    });

    function render() {
        img.style.width     = (state.imgWidth * state.scale) + 'px';
        img.style.height    = (state.imgHeight * state.scale) + 'px';
        img.style.transform = `translate(${state.posX}px, ${state.posY}px)`;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        const ratioCanvas = canvas.width / state.boxSize; 
        const drawX = state.posX * ratioCanvas;
        const drawY = state.posY * ratioCanvas;
        const drawW = (state.imgWidth * state.scale) * ratioCanvas;
        const drawH = (state.imgHeight * state.scale) * ratioCanvas;
        ctx.drawImage(img, 0, 0, state.imgWidth, state.imgHeight, drawX, drawY, drawW, drawH);
    }

    zoomRange.addEventListener('input', function() {
        const oldScale = state.scale;
        const newScale = parseFloat(this.value);
        const boxCenter = state.boxSize / 2;
        const imgRelCenterX = (boxCenter - state.posX) / oldScale;
        const imgRelCenterY = (boxCenter - state.posY) / oldScale;
        state.scale = newScale;
        state.posX = boxCenter - (imgRelCenterX * newScale);
        state.posY = boxCenter - (imgRelCenterY * newScale);
        render();
    });

    const startDrag = (e) => { e.preventDefault(); state.isDragging = true; state.startX = getX(e); state.startY = getY(e); cropBox.style.cursor = 'grabbing'; };
    const doDrag = (e) => {
        if (!state.isDragging) return;
        e.preventDefault(); 
        const curX = getX(e); const curY = getY(e);
        state.posX += curX - state.startX;
        state.posY += curY - state.startY;
        state.startX = curX; state.startY = curY;
        render();
    };
    const stopDrag = () => { state.isDragging = false; cropBox.style.cursor = 'grab'; };
    const getX = (e) => e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
    const getY = (e) => e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;

    cropBox.addEventListener('mousedown', startDrag); window.addEventListener('mousemove', doDrag); window.addEventListener('mouseup', stopDrag);
    cropBox.addEventListener('touchstart', startDrag, {passive:false}); window.addEventListener('touchmove', doDrag, {passive:false}); window.addEventListener('touchend', stopDrag);

    btnSave.addEventListener('click', function() {
        Swal.fire({title: 'Memproses...', text: 'Sedang menyimpan gambar', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
        setTimeout(() => {
            const outSize = 600; 
            const outCanvas = document.createElement('canvas');
            outCanvas.width = outSize; outCanvas.height = outSize;
            const outCtx = outCanvas.getContext('2d');
            outCtx.fillStyle = "#ffffff";
            outCtx.fillRect(0, 0, outSize, outSize);
            const ratio = outSize / state.boxSize;
            const dX = state.posX * ratio;
            const dY = state.posY * ratio;
            const dW = (state.imgWidth * state.scale) * ratio;
            const dH = (state.imgHeight * state.scale) * ratio;
            outCtx.drawImage(img, 0, 0, state.imgWidth, state.imgHeight, dX, dY, dW, dH);
            const dataURL = outCanvas.toDataURL('image/jpeg', 0.9); 
            
            hiddenInput.value = dataURL;
            postForm.submit();
        }, 300);
    });

    if (img.complete) img.onload();
});
</script>