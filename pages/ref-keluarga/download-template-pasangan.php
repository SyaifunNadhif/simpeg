<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- HEADER KOLOM ---
$headers = [
    'ID Pegawai',    // A
    'NIK',           // B
    'Nama Pasangan', // C
    'Tempat Lahir',  // D
    'Tgl Lahir (YYYY-MM-DD)', // E
    'Pendidikan',    // F
    'ID Pekerjaan',  // G
    'Pekerjaan',     // H
    'Status Hub (Suami/Istri)', // I
    'No HP',         // J
    'BPJS Pasangan'  // K
];

// Set Header
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Style Header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

// Contoh Data (Baris 2) - Opsional
$sheet->setCellValue('A2', '101-001');
$sheet->setCellValue('B2', '3301012012900001');
$sheet->setCellValue('C2', 'SITI AMINAH');
$sheet->setCellValue('D2', 'Semarang');
$sheet->setCellValue('E2', '1990-05-20');
$sheet->setCellValue('F2', 'S1');
$sheet->setCellValue('G2', '1');
$sheet->setCellValue('H2', 'Wiraswasta');
$sheet->setCellValue('I2', 'Istri');
$sheet->setCellValue('J2', '08123456789');
$sheet->setCellValue('K2', '00012345678');

// Nama File Output
$filename = "Template_Import_Pasangan.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>