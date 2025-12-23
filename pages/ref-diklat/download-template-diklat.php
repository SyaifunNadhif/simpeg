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

// Header Kolom (A-H)
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'Nama Diklat',
    'C' => 'Penyelenggara',
    'D' => 'Tempat',
    'E' => 'Biaya (Rp)',      // <--- POSISI BARU (Sebelah Tempat)
    'F' => 'Angkatan (opsional)',
    'G' => 'Tahun (tahun)',
    'H' => 'Tgl Awal Diklat (dd-mm-yyyy)'
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
$sheet->setCellValue('B2', 'Diklat Pim IV');
$sheet->setCellValue('C2', 'BPSDM');
$sheet->setCellValue('D2', 'Bandung');
$sheet->setCellValue('E2', '5000000'); // Contoh Biaya
$sheet->setCellValue('F2', 'XIX');
$sheet->setCellValue('G2', '2023');
$sheet->setCellValue('H2', '20-11-2023');

// Format Text
$sheet->getStyle('G:H')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

$filename = 'template_diklat.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>