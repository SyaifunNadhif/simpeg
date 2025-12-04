<?php
// =============================================================
// FILE: pages/ref-anak/download-template-anak.php
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

// Header Kolom (A-K)
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'NIK Anak',
    'C' => 'Nama Anak',
    'D' => 'Tempat Lahir',
    'E' => 'Tgl Lahir (dd-mm-yyyy)',
    'F' => 'Pendidikan',
    'G' => 'ID Pekerjaan',
    'H' => 'Pekerjaan',
    'I' => 'Status Hub (Kandung/Tiri)',
    'J' => 'Anak Ke-',
    'K' => 'No BPJS Anak'
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

// Contoh Data
$sheet->setCellValue('A2', 'P001');
$sheet->setCellValue('B2', '3374050505050001');
$sheet->setCellValue('C2', 'Budi Junior');
$sheet->setCellValue('D2', 'Semarang');
$sheet->setCellValue('E2', '01-01-2015');
$sheet->setCellValue('F2', 'SD');
$sheet->setCellValue('G2', '1');
$sheet->setCellValue('H2', 'Belum Bekerja');
$sheet->setCellValue('I2', 'Anak Kandung');
$sheet->setCellValue('J2', '1');
$sheet->setCellValue('K2', '00012345678');

// Format Text
$sheet->getStyle('B:B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); // NIK
$sheet->getStyle('E:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); // Tgl Lahir
$sheet->getStyle('K:K')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); // BPJS

$filename = 'template_anak.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>