<?php
// 1. Setup & Koneksi Database
require '../../vendor/autoload.php';
include "../../dist/koneksi.php"; // Pastikan path koneksi benar

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();

// ==========================================
// SHEET 1: TEMPLATE INPUT (UTAMA)
// ==========================================
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Form Input Biaya');

// Header Kolom (Sesuai tb_biaya_pendidikan)
$headers = [
    'Kode Kategori (Lihat Sheet 2)',    // A -> kode_pengembangan
    'Kode Pihak (Lihat Sheet 3)',       // B -> kode_pihak
    'Judul Pengembangan SDM',           // C -> pengembangan_sdm
    'Waktu Pelaksanaan',                // D -> waktu_pelaksanaan
    'Nama Pihak (Teks Manual)',         // E -> pihak_pelaksana
    'Jumlah SDM (Angka)',               // F -> jumlah_sdm
    'Total Biaya (Angka)',              // G -> total_biaya
    'Tgl Mulai (YYYY-MM-DD)'            // H -> tgl_pengembangan_sdm
];

// Loop Header
$col = 'A';
foreach ($headers as $header) {
    $sheet1->setCellValue($col . '1', $header);
    $sheet1->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Styling Header Sheet 1
$styleHeader = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']], // Warna Biru
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet1->getStyle('A1:H1')->applyFromArray($styleHeader);

// Contoh Data Dummy (Baris 2)
$sheet1->setCellValue('A2', '101'); 
$sheet1->setCellValue('B2', '01');
$sheet1->setCellValue('C2', 'Pelatihan Leadership Manager');
$sheet1->setCellValue('D2', '3 Hari');
$sheet1->setCellValue('E2', 'Internal HRD');
$sheet1->setCellValue('F2', '10');
$sheet1->setCellValue('G2', '5000000');
$sheet1->setCellValue('H2', '2023-10-25');


// ==========================================
// SHEET 2: REFERENSI KATEGORI (Dari DB)
// ==========================================
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Ref Kategori (Kode)');

// Header Sheet 2
$sheet2->setCellValue('A1', 'KODE');
$sheet2->setCellValue('B1', 'NAMA KATEGORI PENGEMBANGAN');
$sheet2->getStyle('A1:B1')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC107']] // Warna Kuning
]);

// Ambil Data dari Database tb_ref_pengembangan
$queryRef1 = mysqli_query($conn, "SELECT kode_sandi, kategori FROM tb_ref_pengembangan ORDER BY kode_sandi ASC");
$rowNum = 2;
while($row = mysqli_fetch_assoc($queryRef1)){
    $sheet2->setCellValue('A' . $rowNum, $row['kode_sandi']);
    $sheet2->setCellValue('B' . $rowNum, $row['kategori']);
    $rowNum++;
}
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);


// ==========================================
// SHEET 3: REFERENSI PIHAK PELAKSANA (Dari DB)
// ==========================================
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Ref Pihak (Kode)');

// Header Sheet 3
$sheet3->setCellValue('A1', 'KODE');
$sheet3->setCellValue('B1', 'JENIS PIHAK PELAKSANA');
$sheet3->getStyle('A1:B1')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']] // Warna Hijau
]);

// Ambil Data dari Database tb_ref_pelaksana
$queryRef2 = mysqli_query($conn, "SELECT kode_pihak, nama_pihak FROM tb_ref_pelaksana ORDER BY kode_pihak ASC");
$rowNum = 2;
while($row = mysqli_fetch_assoc($queryRef2)){
    // Pakai setCellValueExplicit agar kode '01' tidak berubah jadi '1'
    $sheet3->setCellValueExplicit('A' . $rowNum, $row['kode_pihak'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet3->setCellValue('B' . $rowNum, $row['nama_pihak']);
    $rowNum++;
}
$sheet3->getColumnDimension('A')->setAutoSize(true);
$sheet3->getColumnDimension('B')->setAutoSize(true);


// ==========================================
// SHEET 4: PETUNJUK PENGISIAN
// ==========================================
$sheet4 = $spreadsheet->createSheet();
$sheet4->setTitle('Petunjuk Pengisian');

$petunjuk = [
    ['PETUNJUK PENGISIAN TEMPLATE IMPORT BIAYA PENDIDIKAN'],
    [''],
    ['1. Kolom "Kode Kategori" wajib diisi sesuai kode yang ada di Sheet "Ref Kategori".'],
    ['2. Kolom "Kode Pihak" wajib diisi sesuai kode yang ada di Sheet "Ref Pihak".'],
    ['3. Format Tanggal wajib menggunakan format YYYY-MM-DD (Contoh: 2023-12-31).'],
    ['4. Kolom "Total Biaya" cukup tulis angkanya saja tanpa titik/rupiah (Contoh: 5000000).'],
    ['5. Jangan mengubah urutan kolom pada Sheet 1 (Template Input).'],
    ['6. Simpan file tetap dalam format .xlsx saat selesai mengedit.']
];

$sheet4->fromArray($petunjuk, NULL, 'A1');
$sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet4->getColumnDimension('A')->setAutoSize(true);

// Set agar saat dibuka langsung ke Sheet 1
$spreadsheet->setActiveSheetIndex(0);

// ==========================================
// OUTPUT DOWNLOAD
// ==========================================
$filename = "Template_Import_Biaya_Diklat_" . date('Ymd') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>