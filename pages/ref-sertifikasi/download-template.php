<?php
// =============================================================
// FILE: pages/ref-sertifikasi/download-template.php
// =============================================================

require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// 1. Init Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. Header Kolom
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'Nama Sertifikasi',
    'C' => 'Penyelenggara',
    'D' => 'Tgl Sertifikat (YYYY-MM-DD)',
    'E' => 'Tgl Expired (YYYY-MM-DD)',
    'F' => 'No Sertifikat'
];

foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '1', $text);
    $sheet->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 3. Contoh Data
$sheet->setCellValue('A2', 'P001');
$sheet->setCellValue('B2', 'Ahli K3 Umum');
$sheet->setCellValue('C2', 'Kemnaker RI');
$sheet->setCellValue('D2', '2000-10-11'); // Format Teks
$sheet->setCellValue('E2', '2010-10-11');
$sheet->setCellValue('F2', 'SERT-K3-001');

// Format Kolom Tanggal jadi Text
$sheet->getStyle('D:E')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

// 4. Download
$filename = 'template_sertifikasi.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>