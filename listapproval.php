<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking_date;
use XoopsModules\Jill_booking\Tools;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = "jill_booking_listapproval.tpl";
require_once XOOPS_ROOT_PATH . '/header.php';
if (empty($_SESSION['Isapproval'])) {
    redirect_header(XOOPS_URL, 3, _MD_JILLBOOKIN_NOAPPROVAL);
}

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$jb_info = Request::getString('jb_info');
$jb_sn = Request::getInt('jb_sn');
$jbt_sn = Request::getInt('jbt_sn');
$jbi_sn = Request::getInt('jbi_sn');

switch ($op) {
    case "update_jb_status":
        $xoopsLogger->activated = false;
        Tools::xoops_security_check(true);
        die(Jill_booking_date::approve($jb_sn, Request::getString('jb_date'), $jbt_sn));

    case "delete_booking":
        Tools::xoops_security_check();
        //jb_sn, jbt_sn, jb_date, jbi_sn
        $arr = array_pad(explode("_", $jb_info), 4, '');
        $slot = Tools::slot((int) $arr[1]);
        if (!(int) $arr[0] || !$slot || !Tools::is_approver($slot['jbi_sn']) || !Jill_booking_date::destroy((int) $arr[1], $arr[2], (int) $arr[0])) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM);
        }
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn={$slot['jbi_sn']}");
        exit;

    default:
        Jill_booking_date::index_approval($jbi_sn);
        break;
}

/*-----------秀出結果區--------------*/
require_once __DIR__ . '/footer.php';
