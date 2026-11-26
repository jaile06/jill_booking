<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Jill_booking;
use XoopsModules\Jill_booking\Tools;

$GLOBALS['xoopsOption']['template_main'] = "booking_helper_adm_main.tpl";
require_once __DIR__ . '/header.php';
/*-----------執行動作判斷區----------*/
$op = Request::getString('op');

switch ($op) {
    case 'get_times_of_item':
        $xoopsLogger->activated = false;
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(Jill_booking::helper_times(Request::getInt('sn'), Request::getString('date')), JSON_UNESCAPED_UNICODE));

    case 'create_orders':
        $xoopsLogger->activated = false;
        Tools::xoops_security_check(true);
        $error = Jill_booking::helper_store(Request::getString('jb_date', '', 'POST'), Request::getArray('values', [], 'POST'), Request::getText('event', '', 'POST'));
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(['result' => $error === '', 'msg' => $error], JSON_UNESCAPED_UNICODE));

    default:
        Jill_booking::helper_form();
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("jill_book_adm", $jill_book_adm);
require_once __DIR__ . '/footer.php';
