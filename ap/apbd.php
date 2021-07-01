<?
session_start();
require_once "../config.php";
require_once "../rb.php";
require_once "../funcs.php";
if(!R::testConnection()){
    R::setup('mysql:host='.$db_mysql["host"].';dbname='.$db_mysql["db_name"].'', $db_mysql["login"], $db_mysql["password"]);
};