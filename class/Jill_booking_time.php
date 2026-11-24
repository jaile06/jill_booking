<?php
namespace XoopsModules\Jill_booking;

use Xmf\Request;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Jeditable;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;

// 場地的時段
// token 由入口檔驗證
class Jill_booking_time
{
    const WEEKS = ['0', '1', '2', '3', '4', '5', '6'];

    // 某場地的時段設定畫面
    public static function index($jbi_sn)
    {
        global $xoopsTpl;
        if (empty($jbi_sn)) {
            return;
        }
        $item = Jill_booking_item::get($jbi_sn);
        if (!$item) {
            Tools::deny(_MD_JILLBOOKIN_NO_ITEM, false, 'main.php');
        }
        $item['jbi_title'] = Tools::esc($item['jbi_title']);

        $jeditable = new Jeditable();
        $all_content = [];
        foreach (self::get_all($jbi_sn) as $data) {
            $jbt_sn = $data['jbt_sn'];
            $jeditable->setTextCol("#jbt_title_{$jbt_sn}", 'time.php', '100%', '11pt', "{'jbt_sn':$jbt_sn,'op' : 'save_jbt_title', 'XOOPS_TOKEN_REQUEST' : '" . Tools::token() . "'}", _TAD_EDIT . _MA_JILLBOOKIN_JBT_TITLE);
            $data['jbt_title'] = Tools::esc($data['jbt_title']);
            $times = self::booked_count($jbt_sn);
            $data['booking_times'] = $times ? sprintf(_MA_JILLBOOKIN_BOOKING_TIME, $times) : '';
            foreach (self::WEEKS as $w) {
                $data['open'][$w] = in_array($w, explode(',', (string) $data['jbt_week']), true);
            }
            $all_content[] = $data;
        }

        // 星期一到星期日
        $weeks = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $w) {
            $weeks[$w] = constant("_MA_JILLBOOKIN_W$w");
        }
        $xoopsTpl->assign('weeks', $weeks);
        $xoopsTpl->assign('token', Tools::token());
        $xoopsTpl->assign('item', $item);
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('all_content', $all_content);
        $xoopsTpl->assign('now_op', 'list_jill_booking_time');
        $xoopsTpl->assign('jbi_sn', $jbi_sn);
        $xoopsTpl->assign('place_time', self::places());

        $sweet_alert = new SweetAlert();
        $sweet_alert->render('delete_jill_booking_time_func', "{$_SERVER['PHP_SELF']}?op=delete_jill_booking_time&XOOPS_TOKEN_REQUEST=" . Tools::token() . "&jbi_sn=$jbi_sn&jbt_sn=", 'jbt_sn');
        $formValidator = new FormValidator('#myForm', true);
        $xoopsTpl->assign('formValidator_code', $formValidator->render());
        // 沒有可編輯欄位時 Jeditable 會讀到未定義變數
        $xoopsTpl->assign('jeditable_set', $jeditable->cols ? $jeditable->render() : '');
    }

    // 某場地的所有時段
    public static function get_all($jbi_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT * FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbi_sn`=? ORDER BY `jbt_sort`', 'i', [(int) $jbi_sn]);
        $data = [];
        while ($row = $xoopsDB->fetchArray($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 取得單一時段，找不到回傳 false
    public static function get($jbt_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT * FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbt_sn`=?', 'i', [(int) $jbt_sn]);
        return $xoopsDB->fetchArray($result);
    }

    // 由表單新增
    public static function store($jbi_sn)
    {
        global $xoopsDB;
        $week = array_intersect(self::WEEKS, Request::getArray('jbt_week', [], 'POST'));
        $title = trim(Request::getText('jbt_title', '', 'POST'));
        if (!$week || $title === '' || !Jill_booking_item::get($jbi_sn)) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA, false, "time.php?jbi_sn=$jbi_sn");
        }
        $result = Utility::query('SELECT MAX(`jbt_sort`) FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbi_sn`=?', 'i', [$jbi_sn]);
        list($sort) = $xoopsDB->fetchRow($result);
        self::insert($jbi_sn, $title, (int) $sort + 1, implode(',', $week));
    }

    // 刪除；有預約時拒絕
    public static function destroy($jbt_sn, $jbi_sn)
    {
        global $xoopsDB;
        if (self::booked_count($jbt_sn)) {
            Tools::deny(_MD_JILLBOOKIN_HAS_BOOKING, false, "time.php?jbi_sn=$jbi_sn");
        }
        Utility::query('DELETE FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbt_sn`=?', 'i', [$jbt_sn]);
    }

    // 複製另一場地的時段
    public static function copy($jbi_sn, $to_jbi_sn)
    {
        $rows = self::get_all($jbi_sn);
        if (!$rows || !Jill_booking_item::get($to_jbi_sn)) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA, false, "time.php?jbi_sn=$to_jbi_sn");
        }
        foreach ($rows as $row) {
            self::insert($to_jbi_sn, $row['jbt_title'], $row['jbt_sort'], $row['jbt_week']);
        }
    }

    // 從範本匯入
    public static function import($jbi_sn, $type)
    {
        $titles = [];
        if ($type === '18') {
            for ($i = 1; $i <= 8; $i++) {
                $titles[$i] = sprintf(_MA_JILLBOOKIN_N_TIME, $i);
            }
        } elseif ($type === 'apm') {
            $titles = [1 => _MA_JILLBOOKIN_AM, 2 => _MA_JILLBOOKIN_PM];
        }
        if (!$titles || !Jill_booking_item::get($jbi_sn)) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA, false, "time.php?jbi_sn=$jbi_sn");
        }
        foreach ($titles as $sort => $title) {
            self::insert($jbi_sn, $title, $sort, '1,2,3,4,5');
        }
    }

    // 切換某星期是否開放，回傳新圖示
    public static function toggle($jbt_sn, $week)
    {
        global $xoopsDB;
        $time = self::get($jbt_sn);
        if (!$time || !in_array($week, self::WEEKS, true)) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA, true);
        }
        $weeks = array_filter(explode(',', (string) $time['jbt_week']), 'strlen');
        $open = !in_array($week, $weeks, true);
        $weeks = $open ? [...$weeks, $week] : array_diff($weeks, [$week]);
        sort($weeks);
        Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking_time') . '` SET `jbt_week`=? WHERE `jbt_sn`=?', 'si', [implode(',', $weeks), $jbt_sn]);
        return $open ? '../images/yes.gif' : '../images/no.gif';
    }

    // 行內編輯標題，回傳顯示用內容
    public static function save_title($jbt_sn)
    {
        global $xoopsDB;
        // jeditable 以 innerHTML 回填，送回的是跳脫後文字，存檔前還原
        $title = htmlspecialchars_decode(Request::getText('value', '', 'POST'), ENT_QUOTES);
        Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking_time') . '` SET `jbt_title`=? WHERE `jbt_sn`=?', 'si', [$title, $jbt_sn]);
        return Tools::esc($title);
    }

    // 拖曳排序，回傳訊息
    public static function sort()
    {
        global $xoopsDB;
        $sort = 1;
        foreach (Request::getArray('tr', [], 'POST') as $jbt_sn) {
            Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking_time') . '` SET `jbt_sort`=? WHERE `jbt_sn`=?', 'ii', [$sort++, (int) $jbt_sn]);
        }
        return _MA_UPDATE_COMPLETED . ' (' . date('Y-m-d H:i:s') . ')';
    }

    // 被預約次數
    public static function booked_count($jbt_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT COUNT(*) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jbt_sn`=?', 'i', [(int) $jbt_sn]);
        list($count) = $xoopsDB->fetchRow($result);
        return (int) $count;
    }

    // 可供複製時段的場地
    private static function places()
    {
        global $xoopsDB;
        $result = Utility::query('SELECT a.`jbi_sn`, a.`jbi_title`, COUNT(*) AS counter FROM `' . $xoopsDB->prefix('jill_booking_item') . '` AS a
            JOIN `' . $xoopsDB->prefix('jill_booking_time') . "` AS b ON a.`jbi_sn`=b.`jbi_sn` WHERE a.`jbi_enable`='1' GROUP BY a.`jbi_sn`, a.`jbi_title`");
        $data = [];
        while ($row = $xoopsDB->fetchArray($result)) {
            $row['jbi_link'] = sprintf(_MA_JILLBOOKIN_IMPORT_PLACE, Tools::esc($row['jbi_title']), $row['counter']);
            $data[] = $row;
        }
        return $data;
    }

    private static function insert($jbi_sn, $title, $sort, $week)
    {
        global $xoopsDB;
        Utility::query('INSERT INTO `' . $xoopsDB->prefix('jill_booking_time') . '` (`jbi_sn`, `jbt_title`, `jbt_sort`, `jbt_week`) VALUES (?, ?, ?, ?)', 'isis', [$jbi_sn, $title, $sort, $week]);
    }
}
