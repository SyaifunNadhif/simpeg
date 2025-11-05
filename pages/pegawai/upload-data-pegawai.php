<?php
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_excel'])) {
    $file = $_FILES['file_excel'];

    // Validasi tipe dan ukuran file
    $allowedMimeTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    if (!in_array($file['type'], $allowedMimeTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'File harus berformat .xlsx']);
        exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal 2MB']);
        exit;
    }

    // Baca isi file Excel
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (count($rows) <= 1) {
        echo json_encode(['status' => 'warning', 'message' => 'File tidak memiliki data baris']);
        exit;
    }

    ob_start(); // Tampung HTML
    echo '<table class="table table-bordered table-sm"><thead><tr>';
    foreach ($rows[0] as $col) echo '<th>' . htmlspecialchars($col) . '</th>';
    echo '</tr></thead><tbody>';
    for ($i = 1; $i < count($rows); $i++) {
        echo '<tr>';
        foreach ($rows[$i] as $cell) echo '<td>' . htmlspecialchars($cell) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<button class="btn btn-success mt-3" id="btnSimpanKolektif"><i class="fa fa-save"></i> Simpan ke Database</button>';
    $html = ob_get_clean();

    echo json_encode(['status' => 'success', 'html' => $html]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang diunggah']);
}
?>
