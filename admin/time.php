<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Tools;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Jeditable;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;
/*-----------引入檔案區--------------*/
$xoopsOption['template_main'] = 'jill_booking_adm_time.tpl';
include_once "header.php";
include_once "../function.php";
header('HTTP/1.1 200 OK');
$xoopsLogger->activated = false;
/*-----------執行動作判斷區----------*/
$op = Request::getString('op');
$jb_sn = Request::getInt('jb_sn');
$jbt_sn = Request::getInt('jbt_sn');
$jbi_sn = Request::getInt('jbi_sn');
$to_jbi_sn = Request::getInt('to_jbi_sn');

switch ($op) {
/*---判斷動作請貼在下方---*/

//新增資料
    case "insert_jill_booking_time":
        Tools::check_token();
        $jbi_sn = insert_jill_booking_time($jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "delete_jill_booking_time":
        Tools::check_token();
        if (get_booking_times($jbt_sn)) {
            Tools::deny(_MD_JILLBOOKIN_HAS_BOOKING);
        }
        delete_jill_booking_time($jbt_sn);
        header("location: {$_SERVER['PHP_SELF']}?jbi_sn=$jbi_sn");
        exit;

    case "copy_time":
        Tools::check_token();
        copy_time($jbi_sn, $to_jbi_sn);
        header("location: {$_SERVER['PHP_SELF']}?op=list_jill_booking_time&jbi_sn={$to_jbi_sn}");
        exit;

    case "import_time":
        Tools::check_token();
        import_time($jbi_sn, Request::getString('type'));
        header("location: {$_SERVER['PHP_SELF']}?op=list_jill_booking_time&jbi_sn={$jbi_sn}");
        exit;

    case "change_enable":
        Tools::check_token(true);
        die(change_enable($jbt_sn, Request::getString('week', '', 'POST')));

    case "save_jbt_title":
        Tools::check_token(true);
        die(Tools::esc(save_jbt_title($jbt_sn)));

    default:
        list_jill_booking_time($jbi_sn);
        break;

        /*---判斷動作請貼在上方---*/
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign("jill_book_adm", $jill_book_adm);
include_once 'footer.php';

/*-----------功能函數區--------------*/

//自動取得jill_booking_time的最新排序
function jill_booking_time_max_sort($jbi_sn = "")
{
    global $xoopsDB;
    $sql = 'SELECT MAX(`jbt_sort`) FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbi_sn`=?';
    $result = Utility::query($sql, 'i', [$jbi_sn]);
    list($sort) = $xoopsDB->fetchRow($result);
    return ++$sort;
}

//新增資料到jill_booking_time中
function insert_jill_booking_time($jbi_sn)
{
    global $xoopsDB;

    $week = array_intersect(['0', '1', '2', '3', '4', '5', '6'], Request::getArray('jbt_week', [], 'POST'));
    $jbt_title = trim(Request::getText('jbt_title', '', 'POST'));
    if (!$week || $jbt_title === '' || !get_jill_booking_item($jbi_sn)) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
    }

    $jbt_sort = jill_booking_time_max_sort($jbi_sn);
    $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_booking_time') . '` (`jbi_sn`, `jbt_title`, `jbt_sort`, `jbt_week`) VALUES (?, ?, ?, ?)';
    Utility::query($sql, 'isis', [$jbi_sn, $jbt_title, $jbt_sort, implode(',', $week)]);

    return $jbi_sn;
}

//刪除jill_booking_time某筆資料資料
function delete_jill_booking_time($jbt_sn = "")
{
    global $xoopsDB;
    if (empty($jbt_sn)) {
        return;
    }

    $sql = 'DELETE FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbt_sn` = ?';
    Utility::query($sql, 'i', [$jbt_sn]);
}

//列出所有jill_booking_time資料
function list_jill_booking_time($jbi_sn = "")
{
    global $xoopsDB, $xoopsTpl;
    if (empty($jbi_sn)) {
        return;
    }

    $item = get_jill_booking_item($jbi_sn);

    $jeditable = new Jeditable();

    $myts = \MyTextSanitizer::getInstance();
    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbi_sn`=? ORDER BY `jbt_sort`';
    $result = Utility::query($sql, 'i', [$jbi_sn]);
    $all_content = array();
    $i = 0;
    while ($all = $xoopsDB->fetchArray($result)) {
        //以下會產生這些變數： $jbt_sn , $jbi_sn , $jbt_title , $jbt_sort
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        //過濾讀出的變數值
        $jbt_title = $myts->htmlSpecialChars($jbt_title);

        $jeditable->setTextCol("#jbt_title_{$jbt_sn}", 'time.php', '100%', '11pt', "{'jbt_sn':$jbt_sn,'op' : 'save_jbt_title', 'XOOPS_TOKEN_REQUEST' : '" . Tools::token() . "'}", _TAD_EDIT . _MA_JILLBOOKIN_JBT_TITLE);

        $all_content[$i]['jbi_sn'] = $jbi_sn;
        $all_content[$i]['jbt_sn'] = $jbt_sn;
        $all_content[$i]['jbt_title_link'] = "<a href='{$_SERVER['PHP_SELF']}?jbt_sn={$jbt_sn}'>{$jbt_title}</a>";
        $all_content[$i]['jbt_title'] = $jbt_title;
        $all_content[$i]['jbt_sort'] = $jbt_sort;
        $all_content[$i]['jbt_week'] = strval($jbt_week);
        $booking_times = get_booking_times($jbt_sn);
        $all_content[$i]['booking_times'] = empty($booking_times) ? "" : sprintf(_MA_JILLBOOKIN_BOOKING_TIME, $booking_times);
        $w_arr = explode(',', $jbt_week);

        for ($j = 0; $j <= 7; $j++) {
            $name = "w{$j}";
            if ($jbt_week == null) {
                $pic = "no.gif";
            } else {
                $pic = in_array($j, $w_arr) ? "yes.gif" : "no.gif";
            }

            $all_content[$i][$name] = "<img src='../images/{$pic}' id='{$jbt_sn}_{$j}' onClick=\"change_enable($jbt_sn,$j);\" style='cursor: pointer;'>";
        }
        ++$i;
    }

    //die(var_export($all_content));
    //刪除確認的JS

    $xoopsTpl->assign('token', Tools::token());
    $xoopsTpl->assign('item', $item);
    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('all_content', $all_content);
    $xoopsTpl->assign('now_op', 'list_jill_booking_time');
    $xoopsTpl->assign('jbi_sn', $jbi_sn);

    $sweet_alert = new SweetAlert();
    $sweet_alert->render('delete_jill_booking_time_func', "{$_SERVER['PHP_SELF']}?op=delete_jill_booking_time&XOOPS_TOKEN_REQUEST=" . Tools::token() . "&jbi_sn=$jbi_sn&jbt_sn=", "jbt_sn");

    //套用formValidator驗證機制
    $formValidator = new FormValidator("#myForm", true);
    $formValidator_code = $formValidator->render();
    $xoopsTpl->assign('formValidator_code', $formValidator_code);

    $jeditable_set = $jeditable->render();
    $xoopsTpl->assign('jeditable_set', $jeditable_set);

    //找出現有場地
    $i = 0;
    $place_time = array();
    $sql = 'SELECT a.*, COUNT(b.jbt_sn) AS counter FROM `' . $xoopsDB->prefix('jill_booking_item') . '` AS a JOIN `' . $xoopsDB->prefix('jill_booking_time') . '` AS b ON a.jbi_sn=b.jbi_sn WHERE a.jbi_enable=1 GROUP BY b.jbi_sn';
    $result = Utility::query($sql);
    while ($data = $xoopsDB->fetchArray($result)) {
        $data['jbi_link'] = sprintf(_MA_JILLBOOKIN_IMPORT_PLACE, $data['jbi_title'], $data['counter']);
        $place_time[$i] = $data;
        $i++;
    }
    $xoopsTpl->assign('place_time', $place_time);
    // Utility::get_jquery(true);
}

//取得該節被預約的次數
function get_booking_times($jbt_sn = "")
{
    global $xoopsDB;

    $sql = 'SELECT COUNT(*) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jbt_sn`=?';
    $result = Utility::query($sql, 'i', [$jbt_sn]);
    list($all) = $xoopsDB->fetchRow($result);
    return $all;
}

//複製時間區段
function copy_time($jbi_sn = "", $to_jbi_sn = "")
{
    global $xoopsDB;

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbi_sn` =?';
    $result = Utility::query($sql, 'i', [$jbi_sn]);

    $rows = [];
    while ($all = $xoopsDB->fetchArray($result)) {
        $rows[] = $all;
    }
    if (!$rows || !get_jill_booking_item($to_jbi_sn)) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
    }
    $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_booking_time') . '` (`jbi_sn`, `jbt_title`, `jbt_sort`, `jbt_week`) VALUES (?, ?, ?, ?)';
    foreach ($rows as $row) {
        Utility::query($sql, 'isis', [$to_jbi_sn, $row['jbt_title'], $row['jbt_sort'], $row['jbt_week']]);
    }
}

//從範本快速匯入時段設定
function import_time($jbi_sn = "", $type = "")
{
    global $xoopsDB;
    if ($type == '18') {
        for ($i = 1; $i <= 8; $i++) {
            $jbt_title = sprintf(_MA_JILLBOOKIN_N_TIME, $i);
            $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_booking_time') . '` (`jbi_sn`, `jbt_title`, `jbt_sort`, `jbt_week`) VALUES (?, ?, ?, ?)';
            Utility::query($sql, 'isis', [$jbi_sn, $jbt_title, $i, '1,2,3,4,5']);
        }
    } elseif ($type == 'apm') {
        $apm_arr[1] = _MA_JILLBOOKIN_AM;
        $apm_arr[2] = _MA_JILLBOOKIN_PM;
        for ($i = 1; $i <= 2; $i++) {
            $jbt_title = $apm_arr[$i];
            $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_booking_time') . '` (`jbi_sn`, `jbt_title`, `jbt_sort`, `jbt_week`) VALUES (?, ?, ?, ?)';
            Utility::query($sql, 'isis', [$jbi_sn, $jbt_title, $i, '1,2,3,4,5']);
        }
    }
}

//改變啟用狀態
function change_enable($jbt_sn, $week)
{
    global $xoopsDB;
    $time = get_jill_booking_time($jbt_sn);
    if (!$time || !in_array($week, ['0', '1', '2', '3', '4', '5', '6'], true)) {
        Tools::deny(_MD_JILLBOOKIN_BAD_DATA, true);
    }
    $week_arr = array_filter(explode(',', (string) $time['jbt_week']), 'strlen');
    if (in_array($week, $week_arr, true)) {
        $week_arr = array_diff($week_arr, [$week]);
        $new_pic = "../images/no.gif";
    } else {
        $week_arr[] = $week;
        sort($week_arr);
        $new_pic = "../images/yes.gif";
    }

    $sql = 'UPDATE `' . $xoopsDB->prefix('jill_booking_time') . '` SET `jbt_week`=? WHERE `jbt_sn`=?';
    Utility::query($sql, 'si', [implode(',', $week_arr), $jbt_sn]);

    return $new_pic;
}

function save_jbt_title($jbt_sn)
{
    global $xoopsDB;

    // jeditable 以 innerHTML 回填，送回的是跳脫後文字，存檔前還原
    $jbt_title = htmlspecialchars_decode(Request::getText('value', '', 'POST'), ENT_QUOTES);
    $sql = 'UPDATE `' . $xoopsDB->prefix('jill_booking_time') . '` SET `jbt_title` = ? WHERE `jbt_sn` = ?';
    Utility::query($sql, 'si', [$jbt_title, $jbt_sn]);
    return $jbt_title;
}
