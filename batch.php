<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking;
use XoopsModules\Jill_booking\Tools;

require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = "jill_booking_batch.tpl";
require_once XOOPS_ROOT_PATH . '/header.php';

$op = Request::getString('op');
$jbi_sn = Request::getInt('jbi_sn');
$key = Request::getString('key');

switch ($op) {
    //第一步：暫存
    case "insert_jill_booking":
        Tools::xoops_security_check();
        $key = Jill_booking::batch_prepare();
        header("location: {$_SERVER['PHP_SELF']}?op=list_jill_booking&key={$key}");
        exit;

    //第二步：寫入
    case "insert_jill_booking_date":
        Tools::xoops_security_check();
        $jbi_sn = Jill_booking::batch_store($key);
        header("location: index.php?jbi_sn=$jbi_sn");
        exit;

    case "list_jill_booking":
        Jill_booking::batch_preview($key);
        break;

    default:
        Jill_booking::batch_form($jbi_sn);
        break;
}

require_once __DIR__ . '/footer.php';
