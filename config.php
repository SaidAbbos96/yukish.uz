<?
// header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Tashkent');
$datasign = date('Y-m-d H:i:s');
define('API_KEY','token');
$Manager = "1298348668";
$admins_group = "-508191181";
$channel = "-1001548178522";
$company_name = "bot yoki tizim nomi";
// $logging = true; 
$logging = false; 
$reword = false;
$local_db_sqlite = [
    "status" => false,
    "db_src" => "datas/local.db",
];
$db_mysql = [
    "status" => true,
    "host" => "localhost",
    "login" => "logindb",
    "db_name" => "dbname",
    "password" => "password",
];
// monitoring
$monitoring = [
    "status" => false,
    "work_start" => 6,
    "work_end" => 1,
    "url" => "test",
];

$langs_menu_text = "Assalom alaykum <b>YukIsh.uz</b> tizimiga hush kelibsiz !\n<code>Davom etish uchun, dastur tilini tanlang</code> 👇";
