<?php
/*********************************************************
 * DIR     : pages/ref-keluarga/template-ortu.php
 * MODULE  : SIMPEG — Template Impor Ortu (CSV)
 * VERSION : v1.0
 * DATE    : 2025-09-06
 *********************************************************/
if (session_id()==='') session_start();

$filename = 'template_import_ortu.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$out = fopen('php://output', 'w');

$header = array('id_peg','nik','nama','tmp_lhr','tgl_lhr','pendidikan','id_pekerjaan','pekerjaan','status_hub');
fputcsv($out, $header);

// contoh baris
fputcsv($out, array('135-050','1234567890123456','AYAH CONTOH','Semarang','1970-01-01','SMA','001','Wiraswasta','Ayah'));
fputcsv($out, array('135-050','','IBU CONTOH','Semarang','1972-02-02','SMA','','Ibu Rumah Tangga','Ibu'));
fclose($out);
exit;
