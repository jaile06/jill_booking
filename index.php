<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking;
use XoopsModules\Jill_booking\Jill_booking_date;
use XoopsModules\Jill_booking\Tools;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = "jill_booking_index.tpl";
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$jb_sn = Request::getInt('jb_sn');
$jbt_sn = Request::getInt('jbt_sn');
$jbi_sn = Request::getInt('jbi_sn');
$jb_date = Request::getString('jb_date');
$getdate = Request::getString('getdate');

switch ($op) {
    case "save_jb_booking_content":
        $xoopsLogger->activated = false;
        Tools::xoops_security_check(true);
        die(Jill_booking::update_content($jb_sn));

    case "single_insert_booking":
        $xoopsLogger->activated = false;
        Tools::xoops_security_check(true);
        die(Jill_booking::store_single($jbt_sn, $jb_date));

    case "delete_booking":
        Tools::xoops_security_check();
        if (!Jill_booking_date::destroy($jbt_sn, $jb_date)) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM);
        }
        header("location: {$_SERVER['PHP_SELF']}?op=booking_table&jbi_sn={$jbi_sn}&getdate={$jb_date}");
        exit;

    default:
        Jill_booking::calendar($jbi_sn, Tools::is_date($getdate) ? $getdate : '');
        break;
}

/*-----------秀出結果區--------------*/
require_once __DIR__ . '/footer.php';
