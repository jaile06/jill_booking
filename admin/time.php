<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking_time;
use XoopsModules\Jill_booking\Tools;
use XoopsModules\Tadtools\Utility;

$GLOBALS['xoopsOption']['template_main'] = 'jill_booking_adm_time.tpl';
require_once __DIR__ . '/header.php';
$xoopsLogger->activated = false;
/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$jbt_sn = Request::getInt('jbt_sn');
$jbi_sn = Request::getInt('jbi_sn');
$to_jbi_sn = Request::getInt('to_jbi_sn');

switch ($op) {
    case "insert_jill_booking_time":
        Utility::xoops_security_check();
        Jill_booking_time::store($jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "delete_jill_booking_time":
        Tools::xoops_security_check();
        Jill_booking_time::destroy($jbt_sn, $jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "copy_time":
        Tools::xoops_security_check();
        Jill_booking_time::copy($jbi_sn, $to_jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$to_jbi_sn");
        exit;

    case "import_time":
        Tools::xoops_security_check();
        Jill_booking_time::import($jbi_sn, Request::getString('type'));
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "change_enable":
        Tools::xoops_security_check(true);
        die(Jill_booking_time::toggle($jbt_sn, Request::getString('week', '', 'POST')));

    case "save_jbt_title":
        Tools::xoops_security_check(true);
        die(Jill_booking_time::save_title($jbt_sn));

    default:
        Jill_booking_time::index($jbi_sn);
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("jill_book_adm", $jill_book_adm);
require_once __DIR__ . '/footer.php';
