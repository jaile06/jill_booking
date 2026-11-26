<?php
namespace XoopsModules\Jill_booking;

use Xmf\Request;
use XoopsModules\Tadtools\CkEditor;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\My97DatePicker;
use XoopsModules\Tadtools\Tmt;
use XoopsModules\Tadtools\Utility;

// token 由入口檔驗證
class Jill_booking_item
{
    // 啟用中的條件；停用日當天仍可用
    const ENABLED = "`jbi_enable`='1' AND `jbi_start`<=CURDATE() AND (`jbi_end`>=CURDATE() OR `jbi_end` IS NULL)";

    public static function index()
    {
        global $xoopsDB, $xoopsTpl;

        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('jill_booking_item') . '` ORDER BY `jbi_sort`';
        $PageBar = Utility::getPageBar($sql, 20, 10);
        $result = Utility::query($PageBar['sql']);

        $all_content = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $all_content[] = self::format($data);
        }

        self::delete_js();
        Utility::get_jquery(true);
        $xoopsTpl->assign('token', Tools::token());
        $xoopsTpl->assign('bar', $PageBar['bar']);
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('all_content', $all_content);
        $xoopsTpl->assign('orphans', Update::orphans());
        $xoopsTpl->assign('now_op', 'list_jill_booking_item');
    }

    // 取得單一場地，找不到回傳 false
    public static function get($jbi_sn, $only_enabled = false)
    {
        global $xoopsDB;
        $and = $only_enabled ? ' AND ' . self::ENABLED : '';
        $result = Utility::query('SELECT * FROM `' . $xoopsDB->prefix('jill_booking_item') . "` WHERE `jbi_sn`=?$and", 'i', [(int) $jbi_sn]);
        return $xoopsDB->fetchArray($result);
    }

    // 所有場地（名稱已跳脫）
    public static function get_all($only_enabled = false)
    {
        global $xoopsDB;
        $where = $only_enabled ? ' WHERE ' . self::ENABLED : '';
        $result = Utility::query('SELECT `jbi_sn`, `jbi_title` FROM `' . $xoopsDB->prefix('jill_booking_item') . "`$where ORDER BY `jbi_sort`");
        $data = [];
        while ($row = $xoopsDB->fetchArray($result)) {
            $row['jbi_title'] = Tools::esc($row['jbi_title']);
            $data[] = $row;
        }
        return $data;
    }

    // 場地下拉選項；$approver_uid 有值時只列該員審核的場地
    public static function options($def_jbi_sn = 0, $approver_uid = 0)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT `jbi_sn`, `jbi_title`, `jbi_approval` FROM `' . $xoopsDB->prefix('jill_booking_item') . '` WHERE ' . self::ENABLED . ' ORDER BY `jbi_sort`');
        $opt = '';
        while ($data = $xoopsDB->fetchArray($result)) {
            if ($approver_uid && !in_array((string) $approver_uid, explode(',', (string) $data['jbi_approval']), true)) {
                continue;
            }
            $selected = $data['jbi_sn'] == $def_jbi_sn ? 'selected' : '';
            $note = empty($data['jbi_approval']) ? '' : _MD_IS_APPROVAL;
            $opt .= "<option value='{$data['jbi_sn']}' $selected>" . Tools::esc($data['jbi_title']) . "{$note}</option>";
        }
        return $opt;
    }

    public static function show($jbi_sn)
    {
        global $xoopsTpl;
        $data = self::get_or_deny($jbi_sn);
        foreach (self::format($data) as $key => $value) {
            $xoopsTpl->assign($key, $value);
        }
        self::delete_js();
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('now_op', 'show_one_jill_booking_item');
    }

    // 編輯表單
    public static function create($jbi_sn = 0)
    {
        global $xoopsTpl;
        $myts = \MyTextSanitizer::getInstance();
        $data = $jbi_sn ? self::get_or_deny($jbi_sn) : [
            'jbi_sn' => 0, 'jbi_title' => '', 'jbi_desc' => '', 'jbi_start' => date('Y-m-d'), 'jbi_end' => '', 'jbi_enable' => '1',
        ];

        $Editor = new CkEditor('jill_booking', 'jbi_desc', $data['jbi_desc'] === '' ? '' : Tools::purify_html($myts->displayTarea($data['jbi_desc'], 1, 1, 1, 1, 0)));
        $Editor->setToolbarSet('mySimple');
        $xoopsTpl->assign('Editor_code', $Editor->render());

        foreach (['jbi_sn', 'jbi_title', 'jbi_start', 'jbi_end', 'jbi_enable'] as $key) {
            $xoopsTpl->assign($key, Tools::esc($data[$key]));
        }

        My97DatePicker::render();
        $formValidator = new FormValidator('#myForm', true);
        $xoopsTpl->assign('formValidator_code', $formValidator->render());
        $xoopsTpl->assign('token', Tools::token());
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('now_op', 'jill_booking_item_form');
        $xoopsTpl->assign('next_op', $jbi_sn ? 'update_jill_booking_item' : 'insert_jill_booking_item');
    }

    public static function store()
    {
        global $xoopsDB;
        $data = self::form_data();
        $table = $xoopsDB->prefix('jill_booking_item');
        $result = Utility::query("SELECT MAX(`jbi_sort`) FROM `$table`");
        list($sort) = $xoopsDB->fetchRow($result);
        Utility::query("INSERT INTO `$table` (`jbi_title`, `jbi_desc`, `jbi_start`, `jbi_end`, `jbi_enable`, `jbi_sort`, `jbi_approval`) VALUES (?, ?, ?, ?, ?, ?, '')", 'sssssi', [...$data, (int) $sort + 1]);
        return $xoopsDB->getInsertId();
    }

    // 更新；排序只由拖曳改變
    public static function update($jbi_sn)
    {
        global $xoopsDB;
        $data = self::form_data();
        self::get_or_deny($jbi_sn);
        Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking_item') . '` SET `jbi_title`=?, `jbi_desc`=?, `jbi_start`=?, `jbi_end`=?, `jbi_enable`=? WHERE `jbi_sn`=?', 'sssssi', [...$data, $jbi_sn]);
    }

    // 刪除場地及其時段；有預約時拒絕
    public static function destroy($jbi_sn)
    {
        global $xoopsDB;
        if (self::has_booking($jbi_sn)) {
            Tools::deny(_MD_JILLBOOKIN_HAS_BOOKING);
        }
        foreach (['jill_booking_time', 'jill_booking_item'] as $table) {
            Utility::query('DELETE FROM `' . $xoopsDB->prefix($table) . '` WHERE `jbi_sn`=?', 'i', [$jbi_sn]);
        }
    }

    // 拖曳排序，回傳訊息
    public static function sort()
    {
        global $xoopsDB;
        $sort = 1;
        foreach (Request::getArray('tr', [], 'POST') as $jbi_sn) {
            Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking_item') . '` SET `jbi_sort`=? WHERE `jbi_sn`=?', 'ii', [$sort++, (int) $jbi_sn]);
        }
        return _TAD_SORTED . ' (' . date('Y-m-d H:i:s') . ')';
    }

    public static function approval_form($jbi_sn)
    {
        global $xoopsDB, $xoopsTpl;
        $item = self::get_or_deny($jbi_sn, 'main.php');
        $approvers = explode(',', (string) $item['jbi_approval']);

        $from_arr = $to_arr = [];
        $result = Utility::query('SELECT `uid`, `name`, `uname` FROM `' . $xoopsDB->prefix('users') . '` WHERE `level`>0 ORDER BY `uname`');
        while (list($uid, $name, $uname) = $xoopsDB->fetchRow($result)) {
            $label = Tools::esc("{$name} ({$uname})");
            if (in_array((string) $uid, $approvers, true)) {
                $to_arr[$uid] = $label;
            } else {
                $from_arr[$uid] = $label;
            }
        }

        $xoopsTpl->assign('token', Tools::token());
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('jbi_title', sprintf(_MA_JILLBOOKIN_APPROVAL, Tools::esc($item['jbi_title'])));
        $xoopsTpl->assign('now_op', 'jbi_approval_form');
        $xoopsTpl->assign('tmt_box', Tmt::render('jbi_approval', $from_arr, $to_arr, ['op' => 'save_jbi_approval', 'jbi_sn' => $jbi_sn]));
    }

    public static function save_approval($jbi_sn)
    {
        global $xoopsDB;
        self::get_or_deny($jbi_sn, 'main.php');
        $uids = array_filter(array_map('intval', explode(',', Request::getString('jbi_approval', '', 'POST'))));
        Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking_item') . '` SET `jbi_approval`=? WHERE `jbi_sn`=?', 'si', [implode(',', array_unique($uids)), $jbi_sn]);
    }

    public static function has_booking($jbi_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT COUNT(*) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS a
            JOIN `' . $xoopsDB->prefix('jill_booking_time') . '` AS b ON a.`jbt_sn`=b.`jbt_sn` WHERE b.`jbi_sn`=?', 'i', [(int) $jbi_sn]);
        $row = $xoopsDB->fetchRow($result);
        return !empty($row[0]);
    }

    // 取得場地，找不到就轉址
    private static function get_or_deny($jbi_sn, $url = '')
    {
        $item = self::get($jbi_sn);
        if (!$item) {
            Tools::deny(_MD_JILLBOOKIN_NO_ITEM, false, $url);
        }
        return $item;
    }

    // 驗證表單：標題、說明、啟用日、停用日、是否可借
    private static function form_data()
    {
        $title = trim(Request::getText('jbi_title', '', 'POST'));
        $start = Request::getString('jbi_start', '', 'POST');
        $end = Request::getString('jbi_end', '', 'POST');
        $end = $end === '' ? null : $end; // NULL 代表不限
        if ($title === '' || !Tools::is_date($start) || ($end !== null && (!Tools::is_date($end) || $end < $start))) {
            Tools::deny(_MD_JILLBOOKIN_BAD_ITEM);
        }
        $enable = Request::getInt('jbi_enable', 0, 'POST') === 1 ? '1' : '0';
        return [$title, Tools::purify_html(Request::getText('jbi_desc', '', 'POST')), $start, $end, $enable];
    }

    // 轉成顯示用內容
    private static function format($data)
    {
        $myts = \MyTextSanitizer::getInstance();
        $no = '<span class="jb-text-muted"><i class="fa-solid fa-xmark me-1" aria-hidden="true"></i>' . _NO . '</span>';
        $yes = '<span class="jb-text-success fw-medium"><i class="fa-solid fa-check me-1" aria-hidden="true"></i>' . _YES . '</span>';
        $names = [];
        foreach (array_filter(explode(',', (string) $data['jbi_approval'])) as $uid) {
            $names[] = \XoopsUser::getUnameFromId($uid, 1);
        }
        $data['jbi_enable'] = $data['jbi_enable'] == '1' ? $yes : $no;
        $data['jbi_approval'] = $names ? implode(',', $names) : $no;
        $data['jbi_title'] = Tools::esc($data['jbi_title']);
        $data['jbi_desc'] = $data['jbi_desc'] === '' ? '' : Tools::purify_html($myts->displayTarea($data['jbi_desc'], 1, 1, 1, 1, 0));
        return $data;
    }

    private static function delete_js()
    {
        Tools::confirm_post('delete_jill_booking_item_func', 'delete_jill_booking_item', ['jbi_sn']);
    }
}
