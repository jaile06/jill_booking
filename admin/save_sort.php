<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Tools;
use XoopsModules\Tadtools\Utility;
include "../../../mainfile.php";
include '../../../include/cp_header.php';
header('HTTP/1.1 200 OK');
$xoopsLogger->activated = false;

xoops_loadLanguage('main', 'jill_booking');
Tools::check_token(true);

$sort = 1;
foreach (Request::getArray('tr', [], 'POST') as $jbt_sn) {
    $sql = 'UPDATE `' . $xoopsDB->prefix('jill_booking_time') . '` SET `jbt_sort`=? WHERE `jbt_sn`=?';
    Utility::query($sql, 'ii', [$sort, $jbt_sn]) or die(_MA_UPDATE_FAILED . ' (' . date('Y-m-d H:i:s') . ')' . $sql);
    $sort++;
}

echo _MA_UPDATE_COMPLETED . " (" . date("Y-m-d H:i:s") . ")";
