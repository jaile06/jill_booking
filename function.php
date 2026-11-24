<?php
use XoopsModules\Jill_booking\Tools;
use XoopsModules\Tadtools\Utility;
xoops_loadLanguage('main', 'tadtools');

/********************* 資料庫函數 *********************/
//以流水號取得某筆jill_booking_item資料
function get_jill_booking_item($jbi_sn = "", $jbi_enable = "")
{
    global $xoopsDB;
    if (empty($jbi_sn)) {
        return;
    }

    $where = ($jbi_enable == "1") ? " where `jbi_sn` = '{$jbi_sn}' and `jbi_enable`='{$jbi_enable}' and ((NOW() between `jbi_start` and `jbi_end`) or  (TO_DAYS(NOW()) - TO_DAYS(`jbi_start`) >=0 and `jbi_end`='0000-00-00')) " : " where `jbi_sn` = '{$jbi_sn}'";
    $sql = "select * from `" . $xoopsDB->prefix("jill_booking_item") . "` $where ";
    $result = $xoopsDB->query($sql) or Utility::web_error($sql);
    $data = $xoopsDB->fetchArray($result);
    return $data;
}

//取得場地選項(列出有時段設定者)
function get_jill_booking_time_options($def_jbi_sn = "", $approval = "")
{
    global $xoopsDB;
    // $where_approval = (empty($approval)) ? "" : " && (`jbi_approval` LIKE '%,{$approval}' || `jbi_approval` LIKE '{$approval},%' || `jbi_approval`='{$approval}')";
    $sql = 'SELECT `jbi_sn`,`jbi_title`,`jbi_approval` FROM `' . $xoopsDB->prefix('jill_booking_item') . '` WHERE `jbi_enable`=1 AND ((NOW() BETWEEN `jbi_start` AND `jbi_end`) OR (TO_DAYS(NOW()) - TO_DAYS(`jbi_start`) >=0 AND `jbi_end`="0000-00-00")) ORDER BY `jbi_sort`';
    $result = Utility::query($sql);

    $opt = "";
    while ($all = $xoopsDB->fetchArray($result)) {
        //以下會產生這些變數： $jbt_sn , $jbi_sn , $jbi_title
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        $jbi_approval_arr = explode(',', $jbi_approval);
        if (!empty($approval) and !in_array($approval, $jbi_approval_arr)) {
            continue;
        }

        $is_approval = (empty($jbi_approval)) ? "" : _MD_IS_APPROVAL;
        $selected = ($jbi_sn == $def_jbi_sn) ? "selected" : "";
        $opt .= "<option value='$jbi_sn' $selected>$jbi_title{$is_approval}</option>";
    }

    return $opt;
}

//以流水號取得某筆jill_booking資料
function get_jill_booking($jb_sn = "")
{
    global $xoopsDB;
    if (empty($jb_sn)) {
        return;
    }

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_booking') . '` WHERE `jb_sn` = ?';
    $result = Utility::query($sql, 'i', [$jb_sn]);
    $data = $xoopsDB->fetchArray($result);
    return $data;
}

//以流水號取得某筆jill_booking_time資料
function get_jill_booking_time($jbt_sn = "")
{
    global $xoopsDB;
    if (empty($jbt_sn)) {
        return;
    }

    $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbt_sn` = ?';
    $result = Utility::query($sql, 'i', [$jbt_sn]);
    $data = $xoopsDB->fetchArray($result);
    return $data;
}
//新增資料到jill_booking中
function insert_jill_booking($content, $start_date, $end_date)
{
    global $xoopsDB, $xoopsUser;
    $sql = 'INSERT INTO `' . $xoopsDB->prefix('jill_booking') . '` (`jb_uid`, `jb_booking_time`, `jb_booking_content`, `jb_start_date`, `jb_end_date`) VALUES (?, ?, ?, ?, ?)';
    Utility::query($sql, 'issss', [$xoopsUser->uid(), date('Y-m-d H:i:s', xoops_getUserTimestamp(time())), $content, $start_date, $end_date]);
    return $xoopsDB->getInsertId();
}

//新增資料到jill_booking_week中
function insert_jill_booking_week($jb_sn, $jbt_sn, $week)
{
    global $xoopsDB;
    $sql = 'INSERT IGNORE INTO `' . $xoopsDB->prefix('jill_booking_week') . '` (`jb_sn`, `jb_week`, `jbt_sn`) VALUES (?, ?, ?)';
    Utility::query($sql, 'iii', [$jb_sn, $week, $jbt_sn]);
}

//新增一筆預約明細；$approved 為 null 時依場地是否需審核決定
function insert_jill_booking_date($jb_sn, $slot, $jb_date, $approved = null)
{
    global $xoopsDB;
    $table = $xoopsDB->prefix('jill_booking_date');
    $result = Utility::query("SELECT 1 FROM `$table` WHERE `jb_sn`=? AND `jb_date`=? AND `jbt_sn`=?", 'isi', [$jb_sn, $jb_date, $slot['jbt_sn']]);
    if ($xoopsDB->fetchRow($result)) {
        return;
    }
    $status = ($approved ?? empty($slot['jbi_approval'])) ? '1' : '0';
    $jb_waiting = jb_waiting_max_sort($jb_date, $slot['jbt_sn']);
    // pass_date 的 0000-00-00 由階段 7 的升級改為 NULL
    $sql = "INSERT INTO `$table` (`jb_sn`, `jb_date`, `jbt_sn`, `jb_waiting`, `jb_status`, `approver`, `pass_date`) VALUES (?, ?, ?, ?, ?, 0, '0000-00-00')";
    Utility::query($sql, 'isiis', [$jb_sn, $jb_date, $slot['jbt_sn'], $jb_waiting, $status]);
}

//自動取得jill_booking候補值
function jb_waiting_max_sort($jb_date = "", $jbt_sn = "")
{
    global $xoopsDB;
    $sql = 'SELECT MAX(`jb_waiting`) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jb_date`=? AND `jbt_sn`=?';
    $result = Utility::query($sql, 'si', [$jb_date, $jbt_sn]);

    list($sort) = $xoopsDB->fetchRow($result);
    return (int) $sort + 1;
}

//取消預約：場地審核者可取消該場地任一筆，其他人只能取消自己的
function delete_booking($jbt_sn, $jb_date, $jb_sn = 0)
{
    global $xoopsDB, $xoopsUser;
    $slot = Tools::slot($jbt_sn);
    if (!$xoopsUser || !$slot || !Tools::is_date($jb_date)) {
        return false;
    }
    $date_table = $xoopsDB->prefix('jill_booking_date');
    $and = '';
    $types = 'is';
    $params = [(int) $jbt_sn, $jb_date];
    if ($jb_sn) {
        $and .= ' AND a.`jb_sn`=?';
        $types .= 'i';
        $params[] = (int) $jb_sn;
    }
    if (!Tools::is_approver($slot['jbi_sn'])) {
        if (empty($_SESSION['can_booking'])) {
            return false;
        }
        $and .= ' AND b.`jb_uid`=?';
        $types .= 'i';
        $params[] = $xoopsUser->uid();
    }
    $sql = "SELECT a.`jb_sn`, a.`jb_waiting` FROM `$date_table` AS a JOIN `" . $xoopsDB->prefix('jill_booking') . "` AS b ON a.`jb_sn`=b.`jb_sn`
        WHERE a.`jbt_sn`=? AND a.`jb_date`=? $and ORDER BY (a.`jb_status`='1') DESC, a.`jb_waiting` LIMIT 1";
    $result = Utility::query($sql, $types, $params);
    $row = $xoopsDB->fetchRow($result);
    if (!$row) {
        return false;
    }
    list($jb_sn, $waiting) = $row;

    Utility::query("DELETE FROM `$date_table` WHERE `jb_sn`=? AND `jb_date`=? AND `jbt_sn`=?", 'isi', [$jb_sn, $jb_date, $jbt_sn]);
    Utility::query("UPDATE `$date_table` SET `jb_waiting`=`jb_waiting`-1 WHERE `jb_date`=? AND `jbt_sn`=? AND `jb_waiting`>?", 'sii', [$jb_date, $jbt_sn, $waiting]);

    // 明細全部刪完才刪主檔
    $result = Utility::query("SELECT COUNT(*) FROM `$date_table` WHERE `jb_sn`=?", 'i', [$jb_sn]);
    list($left) = $xoopsDB->fetchRow($result);
    if (!$left) {
        foreach (['jill_booking_week', 'jill_booking'] as $table) {
            Utility::query('DELETE FROM `' . $xoopsDB->prefix($table) . '` WHERE `jb_sn`=?', 'i', [$jb_sn]);
        }
    }
    return true;
}
/********************* 自訂函數 *********************/
//取得批次預約日期
function getdateArr($seed_weekday = "", $start_date = "", $end_date = "")
{
    list($y, $m, $d) = explode("-", $start_date);
    $now = 0;
    $end = strtotime($end_date);
    $i = 0;
    $date_arr = array();
    while ($now < $end) {
        $now = mktime(0, 0, 0, $m, $d + $i, $y);
        if (strpos($seed_weekday, strval(date('w', $now))) !== false) {
            $date_arr[] = date("Y-m-d", $now);
        }
        $i++;
    }
    return $date_arr;
}
