<?php
// PHP_SELF 會帶入網址尾端的任意內容，改用實際檔案路徑（本模組與 TadTools 都會用到）
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
require_once dirname(dirname(__DIR__)) . '/mainfile.php';
require_once __DIR__ . '/interface.php';
