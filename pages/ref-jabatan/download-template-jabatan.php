<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- HEADER KOLOM ---
$headers = [
    'ID Pegawai (Wajib)',       // A
    'Kode Jabatan (Opsional)',  // B (Boleh kosong jika C diisi)
    'Nama Jabatan (Wajib)',  // C (Wajib jika B kosong)
    'Kode Unit Kerja',          // D
    'No SK',                    // E
    'Tgl SK (YYYY-MM-DD)',      // F
    'Status (Aktif/Non)'        // G
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Styling Header
$styleArray = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:G1')->applyFromArray($styleArray);

// CONTOH PENGISIAN

// Baris 2: Isi Kode Saja (Nama Kosong -> Sistem cari namanya)
$sheet->setCellValue('A2', '101-001');
$sheet->setCellValue('B2', '10'); 
$sheet->setCellValue('C2', ''); 
$sheet->setCellValue('D2', '018001');
$sheet->setCellValue('E2', 'SK/001/2025');
$sheet->setCellValue('F2', date('Y-m-d'));
$sheet->setCellValue('G2', 'Aktif');

// Baris 3: Isi Nama Saja (Kode Kosong -> Sistem cari kodenya)
$sheet->setCellValue('A3', '102-005');
$sheet->setCellValue('B3', ''); 
$sheet->setCellValue('C3', 'Kepala Cabang'); // Pastikan ejaan sama persis dengan master
$sheet->setCellValue('D3', '018001');
$sheet->setCellValue('E3', 'SK/002/2025');
$sheet->setCellValue('F3', date('Y-m-d'));
$sheet->setCellValue('G3', 'Aktif');

// Output File
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Import_Jabatan_Smart.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>