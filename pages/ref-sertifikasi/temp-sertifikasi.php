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

// 1. Buat Spreadsheet Baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. Buat Header Kolom (Baris 1)
$headers = [
    'A' => 'ID Pegawai',
    'B' => 'Nama Sertifikasi',
    'C' => 'Penyelenggara',
    'D' => 'Tgl Sertifikat',
    'E' => 'Tgl Expired',
    'F' => 'No Sertifikat'
];

foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '1', $text);
    
    // Styling Header (Tebal, Biru, Tengah, Border)
    $sheet->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    
    // Lebar Kolom Otomatis
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 3. Isi Data Contoh (Baris 2)
$sheet->setCellValue('A2', 'P001');
$sheet->setCellValue('B2', 'Ahli K3 Umum');
$sheet->setCellValue('C2', 'Kemnaker RI');
$sheet->setCellValue('D2', '10-01-2023'); // Format teks dd-mm-yyyy
$sheet->setCellValue('E2', '10-01-2026');
$sheet->setCellValue('F2', 'SERT-K3-001');

// Paksa format teks untuk kolom Tanggal (D & E) agar tidak berubah jadi angka
$sheet->getStyle('D2:E100')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

// 4. Output Download
$filename = 'template_sertifikasi.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>