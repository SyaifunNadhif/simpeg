<?php
// =============================================================
// FILE: pages/ref-ortu/download-template-ortu.php
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

// Header Kolom (A-I)
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'NIK',
    'C' => 'Nama Orang Tua',
    'D' => 'Tempat Lahir',
    'E' => 'Tgl Lahir (dd-mm-yyyy)',
    'F' => 'Pendidikan',
    'G' => 'ID Pekerjaan',
    'H' => 'Pekerjaan',
    'I' => 'Status Hubungan'
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

// Contoh Data (Ayah & Ibu)
// Baris 2: Ayah
$sheet->setCellValue('A2', 'P001');
$sheet->setCellValue('B2', '3374010101550001');
$sheet->setCellValue('C2', 'H. Ahmad Dahlan');
$sheet->setCellValue('D2', 'Yogyakarta');
$sheet->setCellValue('E2', '01-01-1950');
$sheet->setCellValue('F2', 'S1');
$sheet->setCellValue('G2', 'K01');
$sheet->setCellValue('H2', 'Pensiunan');
$sheet->setCellValue('I2', 'Ayah');

// Baris 3: Ibu
$sheet->setCellValue('A3', 'P001');
$sheet->setCellValue('B3', '3374020202600002');
$sheet->setCellValue('C3', 'Hj. Siti Walidah');
$sheet->setCellValue('D3', 'Yogyakarta');
$sheet->setCellValue('E3', '15-05-1955');
$sheet->setCellValue('F3', 'SMA');
$sheet->setCellValue('G3', 'K02');
$sheet->setCellValue('H3', 'Ibu Rumah Tangga');
$sheet->setCellValue('I3', 'Ibu');

// Format Teks
$sheet->getStyle('B:B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
$sheet->getStyle('E:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

$filename = 'template_orangtua.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>