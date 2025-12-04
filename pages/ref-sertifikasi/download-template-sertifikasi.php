<?php
// =============================================================
// FILE: pages/ref-sertifikasi/download-template-sertifikasi.php
// =============================================================

require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header Kolom
$headers = ['A'=>'ID Pegawai', 'B'=>'Nama Sertifikasi', 'C'=>'Penyelenggara', 'D'=>'Tgl Sertifikat', 'E'=>'Tgl Expired', 'F'=>'No Sertifikat'];

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

// Data Contoh
$sheet->setCellValue('A2', 'P001');
$sheet->setCellValue('B2', 'Ahli K3 Umum');
$sheet->setCellValue('C2', 'Kemnaker RI');
$sheet->setCellValue('D2', '10-01-2023');
$sheet->setCellValue('E2', '10-01-2026');
$sheet->setCellValue('F2', 'SERT-K3-001');

// Format Text
$sheet->getStyle('D:E')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template_sertifikasi.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>