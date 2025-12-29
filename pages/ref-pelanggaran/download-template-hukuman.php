<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header Kolom (A-H)
$headers = [
    'ID Pegawai (Wajib)', // A
    'Jenis Hukuman',      // B
    'Pejabat SK',         // C
    'Jabatan Pejabat SK', // D (NEW)
    'No SK',              // E
    'Tgl SK (dd-mm-yyyy)',// F
    'Nama Dokumen',       // G (NEW)
    'Keterangan'          // H
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Styling Header Merah
$styleArray = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']], // Merah
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:H1')->applyFromArray($styleArray);

// Contoh Data Dummy
$sheet->setCellValue('A2', '101-001');
$sheet->setCellValue('B2', 'Teguran Lisan');
$sheet->setCellValue('C2', 'Budi Santoso');
$sheet->setCellValue('D2', 'Direktur Utama');
$sheet->setCellValue('E2', 'SK/001/HK/2024');
$sheet->setCellValue('F2', date('d-m-Y'));
$sheet->setCellValue('G2', 'sk_teguran_001.pdf');
$sheet->setCellValue('H2', 'Terlambat 5 hari');

// Output Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Import_Hukuman.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>