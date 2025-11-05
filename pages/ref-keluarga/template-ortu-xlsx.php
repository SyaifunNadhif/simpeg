<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/template-ortu-xlsx.php
 * MODULE  : SIMPEG — Template Impor Ortu (Excel)
 * VERSION : v1.0 (PHP 5.6)
 * DATE    : 2025-09-06
 *********************************************************/
if (session_id()==='') session_start();

$path = __DIR__ . '/ref-keluarga/template_import_ortu.xlsx';
if (!file_exists($path)){
  header('Content-Type: text/plain; charset=UTF-8');
  echo "File template tidak ditemukan. Silakan letakkan di assets/templates/template_import_ortu.xlsx";
  exit;
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename=\"template_import_ortu.xlsx\"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
