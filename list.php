<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking_date;
use XoopsModules\Jill_booking\Tools;

require_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = "jill_booking_list.tpl";
require_once XOOPS_ROOT_PATH . '/header.php';

$op = Request::getString('op');
$primary = Request::getString('primary');
$jbi_sn = Request::getInt('jbi_sn');

switch ($op) {
    case "delete_jill_booking":
        Tools::xoops_security_check();
        //jbi_sn, jb_date, jbt_sn, jb_sn
        $arr = array_pad(explode("_", $primary), 4, '');
        if (!(int) $arr[3] || !Jill_booking_date::destroy((int) $arr[2], $arr[1], (int) $arr[3])) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM);
        }
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    default:
        Jill_booking_date::index_mine($jbi_sn);
        break;
}

require_once __DIR__ . '/footer.php';
