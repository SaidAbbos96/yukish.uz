<?
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Tashkent');

define('API_KEY', '1613035375:AAGV6QXpJxTWvDm8FON-fM04GukkgJSgsns');
$Manager = "745068183";
$company_name = "bot yoki tizim nomi";
$logging = true;
$local_db_sqlite = [
    "status" => true
];
// monitoring
$monitoring = [
    "status" => true,
    "work_start" => 6,
    "work_end" => 1,
    "url" => "test",
];
// require_once "rb.php";
// R::setup('sqlite:/datas/dbfile.db');
