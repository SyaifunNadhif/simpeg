<?php
// =============================================================
// FILE: pages/ref-diklat/download-template-diklat.php
// =============================================================

require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. Header Kolom (A-G)
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'Nama Diklat',
    'C' => 'Penyelenggara',
    'D' => 'Tempat',
    'E' => 'Angkatan',
    'F' => 'Tahun',
    'G' => 'Tgl diklat (yyyy-mm-dd)' // <-- KOLOM BARU
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
$sheet->setCellValue('B2', 'Diklat Pim IV');
$sheet->setCellValue('C2', 'BPSDM Provinsi');
$sheet->setCellValue('D2', 'Bandung');
$sheet->setCellValue('E2', 'XIX');
$sheet->setCellValue('F2', '2023');
$sheet->setCellValue('G2', '2023-10-11'); // Contoh Tanggal Registrasi

// Format Kolom Tahun & Tanggal jadi Text
$sheet->getStyle('F:G')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

// 4. Output
$filename = 'template_diklat.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>