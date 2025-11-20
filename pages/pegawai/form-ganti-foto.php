<?php
// pages/pegawai/form-ganti-foto.php
include __DIR__ . '/../../dist/koneksi.php';

// Validasi ID Pegawai
if (!isset($_GET['id_peg'])) die("Error. No Kode Selected!");
$id_peg = mysqli_real_escape_string($conn, $_GET['id_peg']);

// Ambil Data Pegawai
$q = mysqli_query($conn, "SELECT nama, foto FROM tb_pegawai WHERE id_peg = '$id_peg'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<div class='alert alert-warning'>Data pegawai tidak ditemukan.</div>";
    exit;
}
$peg = mysqli_fetch_assoc($q);
$nama = htmlspecialchars($peg['nama'], ENT_QUOTES, 'UTF-8');
$foto_file = trim($peg['foto']);

// Path Foto
$uploadRel = 'pages/assets/foto/';
$foto_display = (!empty($foto_file) && file_exists(__DIR__ . '/../../' . $uploadRel . $foto_file))
    ? $uploadRel . $foto_file
    : 'dist/img/avatar5.png'; // Default avatar jika kosong
?>

<style>
    .crop-container {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    /* Area Kiri: Editor */
    .editor-area {
        flex: 1;
        min-width: 300px;
        max-width: 500px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .crop-frame {
        width: 320px;
        height: 320px;
        border-radius: 50%;
        border: 8px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        overflow: hidden;
        position: relative;
        background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAACpJREFUeNpiVk6xcgYDCgAjIyMp+k0YGBgY/v///x8Hxk+00Q9G4w8ABBgAVj0E0/2/j/QAAAAASUVORK5CYII='); /* Pattern transparan */
        cursor: grab;
    }

    .crop-frame:active {
        cursor: grabbing;
    }

    .crop-image {
        position: absolute;
        top: 0; 
        left: 0;
        max-width: none; /* Penting agar zoom tidak dibatasi CSS */
        user-select: none;
        -webkit-user-drag: none;
        transform-origin: center center;
    }

    /* Area Kanan: Preview & Aksi */
    .preview-area {
        flex: 1;
        min-width: 250px;
        max-width: 400px;
        text-align: center;
        border-left: 1px solid #eee;
        padding-left: 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .preview-circle {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #e9ecef;
        margin: 0 auto 20px;
        background: #f8f9fa;
    }

    .preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Preview statis hanya ilustrasi */
    }
    
    /* Controls */
    .control-group {
        width: 100%;
        margin-top: 20px;
    }

    .custom-file-label {
        cursor: pointer;
        overflow: hidden;
    }
    
    .range-slider {
        width: 100%;
        margin: 15px 0;
        cursor: pointer;
    }

    .btn-block { width: 100%; }
    
    .helper-text {
        font-size: 13px;
        color: #888;
        margin-top: 5px;
        text-align: center;
    }

    @media(max-width: 768px) {
        .preview-area {
            border-left: none;
            padding-left: 0;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .crop-container { gap: 15px; }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Ganti Foto Pegawai</h1>
            </div>
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

                            <label><i class="fas fa-search-minus"></i> Zoom <i class="fas fa-search-plus"></i></label>
                            <input type="range" class="custom-range range-slider" id="zoomRange" min="0.5" max="3" step="0.01" value="1">
                            <div class="helper-text"><i class="fas fa-arrows-alt"></i> Geser gambar untuk memposisikan wajah di tengah lingkaran.</div>
                        </div>
                    </div>

                    <div class="preview-area">
                        <h5 class="mb-3 text-muted">Preview Hasil</h5>
                        
                        <canvas id="previewCanvas" class="preview-circle" width="300" height="300"></canvas>
                        
                        <div class="mt-4">
                            <button id="btnSave" class="btn btn-primary btn-block btn-lg shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Foto
                            </button>
                            <a href="home-admin.php?page=profil-pegawai&id_peg=<?=urlencode($id_peg)?>" class="btn btn-default btn-block mt-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                        </div>
                        <div class="helper-text mt-2">Foto akan otomatis dipotong bulat sesuai tampilan editor.</div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<form id="postForm" action="home-admin.php?page=ganti-foto&id_peg=<?=urlencode($id_peg)?>" method="POST" style="display:none">
    <input type="hidden" name="cropped_image" id="cropped_image_input" value="">
</form>

<script>
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

    // Variabel State
    let state = {
        imgWidth: 0,
        imgHeight: 0,
        scale: 1,
        posX: 0,
        posY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        boxSize: 320 // Ukuran container crop (px)
    };

    // 1. Inisialisasi Gambar saat Load
    img.onload = function() {
        state.imgWidth  = img.naturalWidth;
        state.imgHeight = img.naturalHeight;
        
        // Reset posisi ke tengah & fit to box
        fitImageToBox();
        render(); // Render awal
    };

    // Fungsi Fit Gambar ke Kotak (Reset)
    function fitImageToBox() {
        const ratioW = state.boxSize / state.imgWidth;
        const ratioH = state.boxSize / state.imgHeight;
        // Pilih skala yang mengisi kotak (cover) atau memuat (contain) - disini pakai cover agar penuh
        state.scale = Math.max(ratioW, ratioH); 
        
        // Pusatkan
        state.posX = (state.boxSize - (state.imgWidth * state.scale)) / 2;
        state.posY = (state.boxSize - (state.imgHeight * state.scale)) / 2;

        // Update slider
        zoomRange.value = state.scale;
        // Update min/max slider agar responsif thd ukuran gambar asli
        zoomRange.min = state.scale * 0.5; // Bisa zoom out dikit
        zoomRange.max = state.scale * 3;   // Bisa zoom in 3x
    }

    // 2. Handle File Input (Ganti Gambar)
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Update label input file (nama file)
        e.target.nextElementSibling.innerText = file.name;

        const reader = new FileReader();
        reader.onload = function(ev) {
            img.src = ev.target.result; // Trigger img.onload
        }
        reader.readAsDataURL(file);
    });

    // 3. Render Gambar ke Editor & Preview Canvas
    function render() {
        // Update CSS Image di Editor
        img.style.width     = (state.imgWidth * state.scale) + 'px';
        img.style.height    = (state.imgHeight * state.scale) + 'px';
        img.style.transform = `translate(${state.posX}px, ${state.posY}px)`;

        // Update Canvas Preview (Realtime)
        // Bersihkan canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Isi background putih (opsional, mencegah transparan hitam)
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Hitung kordinat relatif untuk canvas
        // Canvas size kita set 300x300, Box Editor 320x320. Kita mapping proporsional.
        const ratioCanvas = canvas.width / state.boxSize; 

        const drawX = state.posX * ratioCanvas;
        const drawY = state.posY * ratioCanvas;
        const drawW = (state.imgWidth * state.scale) * ratioCanvas;
        const drawH = (state.imgHeight * state.scale) * ratioCanvas;

        // Gambar ke canvas
        ctx.drawImage(img, 0, 0, state.imgWidth, state.imgHeight, drawX, drawY, drawW, drawH);
    }

    // 4. Handle Zoom (Slider) - Zoom ke Tengah (Center Zoom)
    zoomRange.addEventListener('input', function() {
        const oldScale = state.scale;
        const newScale = parseFloat(this.value);

        // Hitung pusat kotak saat ini relative terhadap gambar
        const boxCenterX = state.boxSize / 2;
        const boxCenterY = state.boxSize / 2;

        // Hitung posisi pusat gambar relatif thd pojok kiri atas gambar (dalam skala lama)
        const imgRelCenterX = (boxCenterX - state.posX) / oldScale;
        const imgRelCenterY = (boxCenterY - state.posY) / oldScale;

        // Update skala
        state.scale = newScale;

        // Hitung posisi baru agar titik pusat gambar tetap di tengah kotak
        state.posX = boxCenterX - (imgRelCenterX * newScale);
        state.posY = boxCenterY - (imgRelCenterY * newScale);

        render();
    });

    // 5. Handle Drag (Mouse & Touch)
    const startDrag = (e) => {
        e.preventDefault(); // Cegah drag bawaan browser
        state.isDragging = true;
        // Ambil posisi pointer (support touch & mouse)
        state.startX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
        state.startY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
        cropBox.style.cursor = 'grabbing';
    };

    const doDrag = (e) => {
        if (!state.isDragging) return;
        e.preventDefault(); // Cegah scrolling saat touch drag
        
        const clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
        const clientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;

        const dx = clientX - state.startX;
        const dy = clientY - state.startY;

        state.posX += dx;
        state.posY += dy;

        // Update start point untuk frame berikutnya
        state.startX = clientX;
        state.startY = clientY;

        render();
    };

    const stopDrag = () => {
        state.isDragging = false;
        cropBox.style.cursor = 'grab';
    };

    // Event Listeners Mouse
    cropBox.addEventListener('mousedown', startDrag);
    window.addEventListener('mousemove', doDrag);
    window.addEventListener('mouseup', stopDrag);

    // Event Listeners Touch (Mobile)
    cropBox.addEventListener('touchstart', startDrag, {passive: false});
    window.addEventListener('touchmove', doDrag, {passive: false});
    window.addEventListener('touchend', stopDrag);


    // 6. Handle Simpan (Generate Final Output)
    btnSave.addEventListener('click', function() {
        // Buat canvas baru untuk output high-res (misal 600x600)
        const outSize = 600; 
        const outCanvas = document.createElement('canvas');
        outCanvas.width = outSize;
        outCanvas.height = outSize;
        const outCtx = outCanvas.getContext('2d');

        // Background putih bersih
        outCtx.fillStyle = "#ffffff";
        outCtx.fillRect(0, 0, outSize, outSize);

        // Kalkulasi proporsi dari Editor(320px) ke Output(600px)
        const ratio = outSize / state.boxSize;

        const dX = state.posX * ratio;
        const dY = state.posY * ratio;
        const dW = (state.imgWidth * state.scale) * ratio;
        const dH = (state.imgHeight * state.scale) * ratio;

        // Gambar hasil akhir
        outCtx.drawImage(img, 0, 0, state.imgWidth, state.imgHeight, dX, dY, dW, dH);

        // Konversi ke Base64 String
        const dataURL = outCanvas.toDataURL('image/jpeg', 0.9); // Kualitas 90%
        
        // Masukkan ke hidden input & submit
        hiddenInput.value = dataURL;
        postForm.submit();
    });

    // Trigger load awal jika gambar sudah ada di cache browser
    if (img.complete) img.onload();
});
</script>