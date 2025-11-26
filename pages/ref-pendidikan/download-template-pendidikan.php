<?php
// =============================================================
// FILE: pages/ref-pendidikan/download-template-pendidikan.php
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

// 2. Header Kolom (A-L)
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'ID Sekolah (Opsional)',
    'C' => 'Jenjang',
    'D' => 'Nama Sekolah/Kampus',
    'E' => 'Lokasi',
    'F' => 'Jurusan',
    'G' => 'Th Masuk',
    'H' => 'Th Lulus',
    'I' => 'No Ijazah',
    'J' => 'Tgl Ijazah',
    'K' => 'Kepala Sekolah/Rektor',
    'L' => 'Status'
];

foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '1', $text);
    $sheet->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 3. Contoh Data
$sheet->setCellValue('A2', 'P001');
$sheet->setCellValue('B2', '');
$sheet->setCellValue('C2', 'S1');
$sheet->setCellValue('D2', 'Universitas Indonesia');
$sheet->setCellValue('E2', 'Depok');
$sheet->setCellValue('F2', 'Teknik Informatika');
$sheet->setCellValue('G2', '2010');
$sheet->setCellValue('H2', '2014');
$sheet->setCellValue('I2', 'UI-2014-001');
$sheet->setCellValue('J2', '15-08-2014'); // Format dd-mm-yyyy
$sheet->setCellValue('K2', 'Prof. Dr. Budi');
$sheet->setCellValue('L2', 'Lulus');

// Format Text untuk Tahun & Tanggal
$sheet->getStyle('G:J')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

// 4. Output
$filename = 'template_pendidikan.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>