<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking_item;
use XoopsModules\Jill_booking\Tools;

$GLOBALS['xoopsOption']['template_main'] = 'jill_booking_adm_approval.tpl';
require_once __DIR__ . '/header.php';
$op = Request::getString('op');
$jbi_sn = Request::getInt('jbi_sn');

switch ($op) {
    case "save_jbi_approval":
        Tools::xoops_security_check();
        Jill_booking_item::save_approval($jbi_sn);
        header("location: main.php");
        exit;

    default:
        Jill_booking_item::approval_form($jbi_sn);
        break;
}

$xoopsTpl->assign("jill_book_adm", $jill_book_adm);
require_once __DIR__ . '/footer.php';
