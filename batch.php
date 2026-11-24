<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Tools;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Utility;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';
$xoopsOption['template_main'] = "jill_booking_batch.tpl";
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$jb_sn = Request::getInt('jb_sn');
$jbt_sn = Request::getInt('jbt_sn');
$jbi_sn = Request::getInt('jbi_sn');

switch ($op) {
/*---判斷動作請貼在下方---*/

//新增資料
    case "insert_jill_booking":
        $sn = bath_insert();
        header("location: {$_SERVER['PHP_SELF']}?op=list_jill_booking&jb_sn={$sn['jb_sn']}&jbi_sn={$sn['jbi_sn']}");
        exit;

    case "insert_jill_booking_date":
        $jbi_sn = bath_insert_date($jb_sn);
        header("location:index.php?jbi_sn=$jbi_sn");
        exit;

    case "list_jill_booking":
        list_jill_booking($jb_sn, $jbi_sn);
        break;

    default:
        jill_booking_form($jbi_sn);
        break;

        /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("toolbar", Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('jill_book_adm', $jill_book_adm);
$xoTheme->addStylesheet('modules/jill_booking/css/module.css');
include_once XOOPS_ROOT_PATH . '/footer.php';

/*-----------功能函數區--------------*/

//jill_booking編輯表單
function jill_booking_form($jbi_sn = "")
{
    global $xoopsDB, $xoopsTpl, $xoopsModuleConfig;
    //場地設定
    $item_opt = get_jill_booking_time_options($jbi_sn);

    if (!empty($jbi_sn)) {
        //場地資訊
        $itemArr = get_jill_booking_item($jbi_sn, 1);
        if (!$itemArr) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
        }
        $xoopsTpl->assign('itemArr', $itemArr);

        //預設值設定
        //設定 jb_booking_content 欄位的預設值
        $xoopsTpl->assign('jb_booking_content', "");

        //設定 jb_start_date 欄位的預設值
        $jb_start_date = (strtotime($itemArr['jbi_start']) <= strtotime(date("Y-m-d"))) ? date("Y-m-d") : $itemArr['jbi_start'];
        $xoopsTpl->assign('jb_start_date', $jb_start_date);
        //設定 jb_end_date 欄位的預設值
        $xoopsTpl->assign('jb_end_date', $jb_start_date);
        //設定可預約之週數及日期
        $max_bookingweek = $xoopsModuleConfig['max_bookingweek'];

        //$show_range      = date("Y-m-d", strtotime("+$max_bookingweek week"));
        if (empty($max_bookingweek)) {
            //場地預約結束日期
            $end = ($itemArr['jbi_end'] == '0000-00-00') ? 0 : strtotime($itemArr['jbi_end']);
            $max_date = ($end == 0) ? '' : $itemArr['jbi_end'];
        } else {
            $endtime = strtotime("+$max_bookingweek week");
            if ($itemArr['jbi_end'] == '0000-00-00') {
                $end = $endtime;
            } else {
                $end = (strtotime($itemArr['jbi_end']) >= $endtime) ? $endtime : strtotime($itemArr['jbi_end']);
            }
            $max_date = date("Y-m-d", $end);
        }
        //die($max_date);
        $xoopsTpl->assign('max_date', $max_date);

        //時段資訊
        $timeArr = Tools::get_bookingtime_jbisn($jbi_sn);
        $xoopsTpl->assign('timeArr', $timeArr);
        //die(var_export($timeArr));
        $weektime = array();
        foreach ($timeArr as $t => $time) {
            for ($w = 0; $w < 7; $w++) {
                $jbt_sn = $time['jbt_sn'];
                $jbt_week = strval($time['jbt_week']);
                $weektime[$t][$w]['jbt_week'] = (strpos($jbt_week, strval($w)) !== false) ? "<input type='checkbox' name='jb_week[$jbt_sn][]'  value='$w' >" : "<span style='color:#D44950'><i class='fa fa-times'></i></span>";
            }
        }
        //die(var_export($weektime));
        //套用formValidator驗證機制
        $formValidator = new FormValidator("#myForm", true);
        $formValidator_code = $formValidator->render();

        //加入Token安全機制
        include_once XOOPS_ROOT_PATH . "/class/xoopsformloader.php";
        $token = new \XoopsFormHiddenToken();
        $token_form = $token->render();
        $xoopsTpl->assign("token_form", $token_form);
        $xoopsTpl->assign('formValidator_code', $formValidator_code);
        $xoopsTpl->assign('weektime', $weektime);
        $xoopsTpl->assign('next_op', "insert_jill_booking");
    }
    $xoopsTpl->assign('item_opt', $item_opt);
    $xoopsTpl->assign('now_op', 'jill_booking_form');
    $xoopsTpl->assign('action', $_SERVER["PHP_SELF"]);
}
//批次寫入第一步：主檔與星期
function bath_insert()
{
    Utility::xoops_security_check();
    if (empty($_SESSION['can_booking'])) {
        Tools::deny(_MD_JILLBOOKIN_NO_PERM);
    }
    $jbi_sn = Request::getInt('jbi_sn', 0, 'POST');
    $item = get_jill_booking_item($jbi_sn, 1);
    $start = Request::getString('jb_start_date', '', 'POST');
    $end = Request::getString('jb_end_date', '', 'POST');

    // 只收本場地開放的時段與星期
    $weeks = [];
    foreach (Request::getArray('jb_week', [], 'POST') as $jbt_sn => $jb_week) {
        $slot = Tools::slot($jbt_sn);
        if ($slot && $slot['jbi_sn'] == $jbi_sn) {
            foreach (array_intersect((array) $jb_week, explode(',', (string) $slot['jbt_week'])) as $week) {
                $weeks[] = [(int) $jbt_sn, (int) $week];
            }
        }
    }
    if (!$item || !$weeks || !Tools::is_date($start) || !Tools::is_date($end)) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
    }
    [$min, $max] = Tools::item_range($item);
    // ponytail: 場地不限迄日時批次最多一年，避免一次展開過多日期
    $max = $max === '' ? date('Y-m-d', strtotime('+1 year')) : $max;
    $start = max($start, $min);
    $end = min($end, $max);
    if ($start > $end) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
    }

    $content = trim(Request::getText('jb_booking_content', '', 'POST'));
    $jb_sn = insert_jill_booking($content === '' ? _MD_JILLBOOKIN_SMNAME2 : $content, $start, $end);
    foreach ($weeks as [$jbt_sn, $week]) {
        insert_jill_booking_week($jb_sn, $jbt_sn, $week);
    }
    return ['jb_sn' => $jb_sn, 'jbi_sn' => $jbi_sn];
}

//取得本人的批次主檔，不是本人的就拒絕
function get_own_booking($jb_sn)
{
    global $xoopsUser;
    $booking = get_jill_booking($jb_sn);
    if (!$xoopsUser || empty($_SESSION['can_booking']) || !$booking || $booking['jb_uid'] != $xoopsUser->uid()) {
        Tools::deny(_MD_JILLBOOKIN_NO_PERM);
    }
    return $booking;
}

//批次寫入第二步：明細
function bath_insert_date($jb_sn)
{
    Utility::xoops_security_check();
    $booking = get_own_booking($jb_sn);
    $weekArr = get_booking_weekArr($jb_sn);
    $jbi_sn = 0;
    foreach (Request::getArray('jb_date', [], 'POST') as $jbt_sn => $dates) {
        $slot = Tools::slot($jbt_sn);
        foreach ((array) $dates as $jb_date) {
            if (!Tools::bookable($slot, $jb_date) || $jb_date < $booking['jb_start_date'] || $jb_date > $booking['jb_end_date']) {
                continue;
            }
            // 只收第一步選過的時段與星期
            foreach ($weekArr['rows'] as $row) {
                if ($row['jbt_sn'] == $jbt_sn && $row['jb_week'] == date('w', strtotime($jb_date))) {
                    insert_jill_booking_date($jb_sn, $slot, $jb_date);
                    $jbi_sn = (int) $slot['jbi_sn'];
                }
            }
        }
    }
    if (!$jbi_sn) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
    }
    return $jbi_sn;
}

//列出所有批次預約表單
function list_jill_booking($jb_sn = "", $jbi_sn = "")
{
    global $xoopsDB, $xoopsTpl;
    $DBV1 = get_own_booking($jb_sn);
    $DBV2 = get_booking_weekArr($jb_sn);
    //可啟用場地資訊
    $itemArr = get_jill_booking_item($jbi_sn, 1);
    if (!$itemArr || !$DBV2['rows']) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
    }
    $xoopsTpl->assign("jbi_title", $itemArr['jbi_title']);
    $xoopsTpl->assign("jbi_sn", $jbi_sn);

    $i = 0;
    $dateweek = [];
    $maxwaiting = get_maxwaiting_byrange($DBV1['jb_start_date'], $DBV1['jb_end_date'], $DBV2['jbt_sn']);

    $xoopsTpl->assign("maxwaiting", $maxwaiting);

    foreach ($DBV2['rows'] as $jb_weekArr) {
        //die($jb_weekArr['jb_week']);
        //$jb_weekArr['jbt_sn'];
        if (isset($jb_weekArr['jb_week']) && isset($jb_weekArr['jbt_sn'])) {
            $dateArr = getdateArr($jb_weekArr['jb_week'], $DBV1['jb_start_date'], $DBV1['jb_end_date']);
            $timeArr = get_jill_booking_time($jb_weekArr['jbt_sn']);
            //die(var_dump($jb_weekArr));
            if (is_array($dateArr) && !empty($dateArr)) {
                foreach ($dateArr as $date) {
                    $dateweek[$i]['jb_date'] = $date;
                    $dateweek[$i]['week'] = date('w', strtotime($date));
                    $dateweek[$i]['jbt_title'] = $timeArr['jbt_title'];
                    $dateweek[$i]['jbt_sn'] = $jb_weekArr['jbt_sn'];
                    if (!empty($maxwaiting)) {
                        $waitingArr = get_jbwaiting($jb_weekArr['jbt_sn'], $date);
                        for ($j = 0; $j < $maxwaiting; $j++) {
                            $ok = "<span style='color:#D44950'><i class='fa fa-check'></i></span>";
                            $jb_exit = 0;
                            if (!empty($waitingArr)) {
                                foreach ($waitingArr as $key => $w) {
                                    if ($w['jb_waiting'] == $j + 1) {
                                        $ok = $w['name'];
                                    }
                                    $jb_exit = 1;
                                }
                                $dateweek[$i]['waitingArr'][$j]['name'] = $ok;
                                $dateweek[$i]['jb_exit'] = $jb_exit;
                            } else {
                                $dateweek[$i]['waitingArr'][0]['name'] = "<span style='color:#D44950'><i class='fa fa-check'></i></span>";
                                for ($j = 1; $j < $maxwaiting; $j++) {
                                    $dateweek[$i]['waitingArr'][$j]['name'] = "";
                                }
                                $dateweek[$i]['jb_exit'] = 0;

                            }
                        }
                    } else {
                        $dateweek[$i]['waitingArr'][0]['name'] = "<span style='color:#D44950'><i class='fa fa-check'></i></span>";
                        $dateweek[$i]['jb_exit'] = 0;
                    }

                    ++$i;
                }
            }
        }

    }
    if ($dateweek) {
        $jb_date = [];
        foreach ($dateweek as $key => $value) {
            $jb_date[$key] = strtotime($value['jb_date']);
        }
        array_multisort($jb_date, $dateweek);
    }

    Utility::token_form();
    $xoopsTpl->assign("jb_sn", $jb_sn);
    $xoopsTpl->assign("dateweek", $dateweek);
    $xoopsTpl->assign('now_op', 'list_jill_booking');
    $xoopsTpl->assign('action', $_SERVER["PHP_SELF"]);
    $xoopsTpl->assign('next_op', "insert_jill_booking_date");
}
//依日期範圍及節次取得jill_booking_date的資訊
function get_jbwaiting($jbt_sn = "", $jb_date = "")
{
    global $xoopsDB;
    $sql = 'SELECT a.jb_waiting, b.jb_uid FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS a
        JOIN `' . $xoopsDB->prefix('jill_booking') . '` AS b ON a.jb_sn=b.jb_sn
        WHERE a.jbt_sn=? AND a.jb_date=? ORDER BY a.jb_waiting';
    $result = Utility::query($sql, 'is', [$jbt_sn, $jb_date]);

    $data = array();
    $i = 0;
    while (list($jb_waiting, $jb_uid) = $xoopsDB->fetchRow($result)) {
        $data[$i]['jb_waiting'] = $jb_waiting;
        $data[$i]['name'] = XoopsUser::getUnameFromId($jb_uid, 1);
        ++$i;
    }
    return $data;
}
//依日期範圍取得jill_booking候補值
function get_maxwaiting_byrange($jb_start_date, $jb_end_date, array $jbt_sns)
{
    global $xoopsDB;
    $sql = 'SELECT MAX(`jb_waiting`) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jbt_sn` IN(' . Tools::marks($jbt_sns) . ') AND `jb_date` BETWEEN ? AND ?';
    $result = Utility::query($sql, str_repeat('i', count($jbt_sns)) . 'ss', [...$jbt_sns, $jb_start_date, $jb_end_date]);

    list($max) = $xoopsDB->fetchRow($result);
    return (int) $max;
}

//以流水號取得某筆jill_booking_week資料
function get_booking_weekArr($jb_sn)
{
    global $xoopsDB;
    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_booking_week') . '` WHERE `jb_sn` =?';
    $result = Utility::query($sql, 'i', [$jb_sn]);

    $data = ['rows' => [], 'jbt_sn' => []];
    while ($all = $xoopsDB->fetchArray($result)) {
        $data['rows'][] = $all;
        $data['jbt_sn'][$all['jbt_sn']] = (int) $all['jbt_sn'];
    }
    $data['jbt_sn'] = array_values($data['jbt_sn']);
    return $data;
}
