<?php
use XoopsModules\Jill_booking\Tools;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
if (!class_exists('XoopsModules\Jill_booking\Tools')) {
    require XOOPS_ROOT_PATH . '/modules/jill_booking/preloads/autoloader.php';
}

Tools::get_session();
$jill_book_adm = $_SESSION['jill_book_adm'];

$interface_menu[_MD_JILLBOOKIN_SMNAME1] = "index.php";
$interface_icon[_MD_JILLBOOKIN_SMNAME1] = "fa-calendar-plus";

if ($_SESSION['Isapproval']) {
    $interface_menu[_MD_JILLBOOKIN_SMNAME4] = "listapproval.php";
    $interface_icon[_MD_JILLBOOKIN_SMNAME4] = "fa-check-square";
}
if ($_SESSION['can_booking']) {
    $interface_menu[_MD_JILLBOOKIN_SMNAME2] = "batch.php";
    $interface_icon[_MD_JILLBOOKIN_SMNAME2] = "fa-address-book";

    $interface_menu[_MD_JILLBOOKIN_SMNAME3] = "list.php";
    $interface_icon[_MD_JILLBOOKIN_SMNAME3] = "fa-id-card";
}
