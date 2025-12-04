<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- UPDATE HEADER (Total 18 Kolom) ---
$headers = [
    'ID Pegawai (Wajib)', // A
    'NIP',                // B
    'Nama Lengkap',       // C
    'Tempat Lahir',       // D
    'Tgl Lahir (YYYY-MM-DD)', // E
    'Agama',              // F
    'Jenis Kelamin',      // G
    'Gol Darah',          // H
    'Status Nikah',       // I
    'Status Kepegawaian', // J
    'Alamat',             // K
    'No Telp',            // L
    'Email',              // M
    'Nama File Foto',     // N
    'TMT Kerja (YYYY-MM-DD)',   // O (BARU)
    'Tgl Pensiun (YYYY-MM-DD)', // P (BARU)
    'No BPJS TK',         // Q (Geser)
    'No BPJS Kes'         // R (Geser)
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Styling Header (A1 sampai R1)
$styleArray = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];
$sheet->getStyle('A1:R1')->applyFromArray($styleArray);

// Contoh Data Dummy
$sheet->setCellValue('A2', '101-001');
$sheet->setCellValue('B2', '199001012015011001');
$sheet->setCellValue('C2', 'Budi Santoso');
$sheet->setCellValue('D2', 'Jakarta');
$sheet->setCellValue('E2', '1990-01-01');
$sheet->setCellValue('F2', 'Islam');
$sheet->setCellValue('G2', 'Laki-laki');
$sheet->setCellValue('H2', 'O');
$sheet->setCellValue('I2', 'Menikah');
$sheet->setCellValue('J2', 'Pegawai Tetap');
$sheet->setCellValue('K2', 'Jl. Merdeka No. 1');
$sheet->setCellValue('L2', '081234567890');
$sheet->setCellValue('M2', 'budi@mail.com');
$sheet->setCellValue('N2', 'budi.jpg');
$sheet->setCellValue('O2', '2015-01-01'); // TMT
$sheet->setCellValue('P2', '2045-01-01'); // Pensiun
$sheet->setCellValue('Q2', '12345678901');
$sheet->setCellValue('R2', '00012345678');

// Output File
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Import_Pegawai_V2.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>