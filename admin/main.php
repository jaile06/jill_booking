<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking_item;
use XoopsModules\Jill_booking\Tools;

$GLOBALS['xoopsOption']['template_main'] = 'jill_booking_adm_main.tpl';
require_once __DIR__ . '/header.php';
/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$jbi_sn = Request::getInt('jbi_sn');

switch ($op) {
    case "insert_jill_booking_item":
        Tools::xoops_security_check();
        $jbi_sn = Jill_booking_item::store();
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "update_jill_booking_item":
        Tools::xoops_security_check();
        Jill_booking_item::update($jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "jill_booking_item_form":
        Jill_booking_item::create($jbi_sn);
        break;

    case "delete_jill_booking_item":
        Tools::xoops_security_check();
        Jill_booking_item::destroy($jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    case "update_jill_booking_item_sort":
        $xoopsLogger->activated = false;
        Tools::xoops_security_check(true);
        die(Jill_booking_item::sort());

    default:
        if ($jbi_sn) {
            Jill_booking_item::show($jbi_sn);
        } else {
            Jill_booking_item::index();
        }
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("jill_book_adm", $jill_book_adm);
require_once __DIR__ . '/footer.php';
