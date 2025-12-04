<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header Kolom (12 Kolom)
$headers = [
    'ID Pegawai (Wajib)', // A
    'ID Sekolah (Opsional)', // B
    'Jenjang',            // C
    'Nama Sekolah',       // D
    'Lokasi',             // E
    'Jurusan',            // F
    'No Ijazah',          // G
    'Tgl Ijazah (dd-mm-yyyy)', // H
    'Kepala Sekolah',     // I
    'Status',             // J
    'Th Masuk',           // K
    'Th Lulus'            // L
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Styling
$styleArray = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:L1')->applyFromArray($styleArray);

// Contoh Data
$sheet->setCellValue('A2', '101-001');
$sheet->setCellValue('B2', '');
$sheet->setCellValue('C2', 'S1');
$sheet->setCellValue('D2', 'Universitas Diponegoro');
$sheet->setCellValue('E2', 'Semarang');
$sheet->setCellValue('F2', 'Manajemen');
$sheet->setCellValue('G2', 'IJZ-001/2015');
$sheet->setCellValue('H2', date('d-m-Y')); // Contoh: 25-10-2020
$sheet->setCellValue('I2', 'Prof. Dr. Rektor');
$sheet->setCellValue('J2', 'Lulus');
$sheet->setCellValue('K2', '2011');
$sheet->setCellValue('L2', '2015');

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Import_Pendidikan.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>