<?php
namespace XoopsModules\Jill_booking;

use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\Utility;

// 預約明細、候補、審核
// token 由入口檔驗證
class Jill_booking_date
{
    // 新增明細；順位、同一人不得重複的檢查與寫入用同一個敘述，避免同時預約撞號
    public static function store($jb_sn, $slot, $date, $approved = null)
    {
        global $xoopsDB, $xoopsUser;
        $table = $xoopsDB->prefix('jill_booking_date');
        $status = ($approved ?? empty($slot['jbi_approval'])) ? '1' : '0';
        Utility::query("INSERT INTO `$table` (`jb_sn`, `jb_date`, `jbt_sn`, `jb_waiting`, `jb_status`, `approver`, `pass_date`)
            SELECT ?, ?, ?, COALESCE(MAX(`jb_waiting`), 0) + 1, ?, 0, NULL FROM `$table` WHERE `jb_date`=? AND `jbt_sn`=?
            HAVING COALESCE(MAX(`jb_sn` IN (SELECT `jb_sn` FROM `" . $xoopsDB->prefix('jill_booking') . '` WHERE `jb_uid`=?)), 0)=0',
            'isissii', [$jb_sn, $date, $slot['jbt_sn'], $status, $date, $slot['jbt_sn'], $xoopsUser->uid()]);
    }

    // 一段期間的預約：[時段][日期] => 依順位排列的預約
    public static function by_range(array $jbt_sns, $from, $to)
    {
        global $xoopsDB;
        $data = [];
        if (!$jbt_sns) {
            return $data;
        }
        $result = Utility::query('SELECT a.`jbt_sn`, a.`jb_date`, a.`jb_waiting`, a.`jb_status`, b.`jb_sn`, b.`jb_uid`, b.`jb_booking_content`
            FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS a
            JOIN `' . $xoopsDB->prefix('jill_booking') . '` AS b ON a.`jb_sn`=b.`jb_sn`
            WHERE a.`jbt_sn` IN (' . Tools::marks($jbt_sns) . ') AND a.`jb_date` BETWEEN ? AND ? ORDER BY a.`jb_waiting`',
            str_repeat('i', count($jbt_sns)) . 'ss', [...$jbt_sns, $from, $to]);
        while ($row = $xoopsDB->fetchArray($result)) {
            $data[$row['jbt_sn']][$row['jb_date']][] = $row;
        }
        return $data;
    }

    // 該格代表的預約：先取已核准的最前順位，沒有就取最前順位
    public static function main(array $list)
    {
        foreach ($list as $row) {
            if ($row['jb_status'] == '1') {
                return $row;
            }
        }
        return $list[0] ?? null;
    }

    // 超過一人時的清單圖示（qtip）
    public static function users_icon(array $list)
    {
        if (count($list) < 2) {
            return '';
        }
        $users = _MD_JILLBOOKIN_JB_UID . '<ol>';
        foreach ($list as $row) {
            [$icon, $alt] = $row['jb_status'] == '1'
                ? ['<i class="fa-solid fa-check jb-text-success mx-1" aria-hidden="true"></i>', _MD_PASS]
                : ['<i class="fa-regular fa-clock jb-text-muted mx-1" aria-hidden="true"></i>', _MD_APPROVING];
            $users .= '<li>' . \XoopsUser::getUnameFromId($row['jb_uid'], 1)
                . "{$icon}({$alt}) "
                . Tools::esc($row['jb_booking_content']) . '</li>';
        }
        // qtip 會把屬性值當 HTML：內容先跳脫，再整段跳脫放進屬性
        return " <button type='button' class='btn btn-link jb_users p-1' qtip-content='" . Tools::esc($users . '</ol>') . "' aria-label='" . _MD_JILLBOOKIN_JB_UID . "' title='" . _MD_JILLBOOKIN_JB_UID . "'><i class='fa-solid fa-users' aria-hidden='true'></i></button>";
    }

    // 掛上預約者清單的 qtip（週曆與本週區塊共用）；滑鼠與鍵盤焦點都會顯示
    public static function qtip()
    {
        global $xoTheme;
        if (!$xoTheme) {
            return;
        }
        Utility::get_jquery(true);
        $xoTheme->addStylesheet('modules/tadtools/jquery.qtip/jquery.qtip.css');
        $xoTheme->addScript('modules/tadtools/jquery.qtip/jquery.qtip.js');
        $xoTheme->addScript('', null, "
            $(document).ready(function(){
                $('.jb_users').qtip({
                    content: {text: function(){ return $(this).attr('qtip-content'); }},
                    show: {event: 'mouseenter focus'},
                    hide: {event: 'mouseleave blur'},
                    style: {classes: 'qtip-blue qtip-rounded qtip-shadow'},
                    position: {my: 'top left', at: 'top right'}
                });
            });
        ");
    }

    // 取消預約：場地審核者可取消該場地任一筆，其他人只能取消自己的；過去的一律不可取消
    public static function destroy($jbt_sn, $date, $jb_sn = 0)
    {
        global $xoopsDB, $xoopsUser;
        $slot = Tools::slot($jbt_sn);
        if (!$xoopsUser || !$slot || !Tools::is_date($date) || $date < date('Y-m-d')) {
            return false;
        }
        $table = $xoopsDB->prefix('jill_booking_date');
        $and = '';
        $types = 'is';
        $params = [(int) $jbt_sn, $date];
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
        $result = Utility::query("SELECT a.`jb_sn`, a.`jb_waiting` FROM `$table` AS a JOIN `" . $xoopsDB->prefix('jill_booking') . "` AS b ON a.`jb_sn`=b.`jb_sn`
            WHERE a.`jbt_sn`=? AND a.`jb_date`=? $and ORDER BY (a.`jb_status`='1') DESC, a.`jb_waiting` LIMIT 1", $types, $params);
        $row = $xoopsDB->fetchRow($result);
        if (!$row) {
            return false;
        }
        [$jb_sn, $waiting] = $row;

        Utility::query("DELETE FROM `$table` WHERE `jb_sn`=? AND `jb_date`=? AND `jbt_sn`=?", 'isi', [$jb_sn, $date, $jbt_sn]);
        Utility::query("UPDATE `$table` SET `jb_waiting`=`jb_waiting`-1 WHERE `jb_date`=? AND `jbt_sn`=? AND `jb_waiting`>?", 'sii', [$date, $jbt_sn, $waiting]);

        // 明細全部刪完才刪主檔
        if (!self::count($jb_sn)) {
            Jill_booking::destroy($jb_sn);
        }
        return true;
    }

    // 一張預約單是否還有今天以後的明細
    public static function is_active($jb_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT MAX(`jb_date`) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jb_sn`=?', 'i', [(int) $jb_sn]);
        return $xoopsDB->fetchRow($result)[0] >= date('Y-m-d');
    }

    // 一張預約單的明細數
    public static function count($jb_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT COUNT(*) FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jb_sn`=?', 'i', [(int) $jb_sn]);
        return (int) $xoopsDB->fetchRow($result)[0];
    }

    // 個人預約清單
    public static function index_mine($jbi_sn = 0)
    {
        global $xoopsDB, $xoopsTpl, $xoopsUser;
        if (!$xoopsUser) {
            return;
        }
        $and = $jbi_sn ? ' AND c.`jbi_sn`=' . (int) $jbi_sn : '';
        $sql = 'SELECT a.`jb_sn`, a.`jb_date`, a.`jbt_sn`, a.`jb_waiting`, a.`jb_status`, b.`jb_uid`, b.`jb_booking_content`, c.`jbi_sn`, c.`jbt_title`, d.`jbi_title`
            FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS a
            JOIN `' . $xoopsDB->prefix('jill_booking') . '` AS b ON a.`jb_sn`=b.`jb_sn`
            JOIN `' . $xoopsDB->prefix('jill_booking_time') . '` AS c ON a.`jbt_sn`=c.`jbt_sn`
            JOIN `' . $xoopsDB->prefix('jill_booking_item') . '` AS d ON c.`jbi_sn`=d.`jbi_sn`
            WHERE b.`jb_uid`=' . (int) $xoopsUser->uid() . "$and ORDER BY a.`jb_status` DESC, a.`jb_date` DESC, a.`jb_waiting`, a.`jbt_sn`";
        $PageBar = Utility::getPageBar($sql, 20, 10);
        $result = Utility::query($PageBar['sql']);

        $all_content = [];
        while ($data = $xoopsDB->fetchArray($result)) {
            $data['primary'] = "{$data['jbi_sn']}_{$data['jb_date']}_{$data['jbt_sn']}_{$data['jb_sn']}";
            $data['fun'] = $data['jb_date'] >= date('Y-m-d') ? 1 : '';
            $data['jb_date'] .= _MD_JWEEK . Tools::week_name(date('w', strtotime($data['jb_date'])));
            $data['jb_uid'] = \XoopsUser::getUnameFromId($data['jb_uid'], 1);
            foreach (['jb_booking_content', 'jbt_title', 'jbi_title'] as $key) {
                $data[$key] = Tools::esc($data[$key]);
            }
            $all_content[] = $data;
        }

        $sweet_alert = new SweetAlert();
        $sweet_alert->render('delete_jill_booking_func', "{$_SERVER['PHP_SELF']}?op=delete_jill_booking&XOOPS_TOKEN_REQUEST=" . Tools::token() . '&primary=', 'primary');
        $xoopsTpl->assign('item_opt', Jill_booking_item::options($jbi_sn));
        $xoopsTpl->assign('bar', $PageBar['bar']);
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('all_content', $all_content);
        $xoopsTpl->assign('now_op', 'jill_booking_list');
    }

    // 待審清單，只列未核准的（免審核場地與預約助手的預約一建立就已核准）
    public static function index_approval($jbi_sn = 0)
    {
        global $xoopsDB, $xoopsTpl, $xoopsUser;
        if ($jbi_sn && Jill_booking_item::get($jbi_sn, true) && Tools::is_approver($jbi_sn)) {
            $sql = 'SELECT a.`jb_sn`, a.`jbt_sn`, a.`jb_date`, a.`jb_waiting`, a.`jb_status`, b.`jb_uid`, b.`jb_booking_content`, c.`jbi_sn`, c.`jbt_title`
                FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS a
                JOIN `' . $xoopsDB->prefix('jill_booking') . '` AS b ON a.`jb_sn`=b.`jb_sn`
                JOIN `' . $xoopsDB->prefix('jill_booking_time') . '` AS c ON a.`jbt_sn`=c.`jbt_sn`
                WHERE c.`jbi_sn`=' . (int) $jbi_sn . " AND a.`jb_status`='0' AND a.`pass_date` IS NULL AND a.`jb_date`>='" . date('Y-m-d') . "'
                ORDER BY a.`jb_date`, a.`jbt_sn`, b.`jb_booking_time`";
            $PageBar = Utility::getPageBar($sql, 20, 10);
            $result = Utility::query($PageBar['sql']);
            $all_content = [];
            while ($data = $xoopsDB->fetchArray($result)) {
                $data['jb_week'] = date('w', strtotime($data['jb_date']));
                $data['jb_uid'] = \XoopsUser::getUnameFromId($data['jb_uid'], 1);
                $data['jb_booking_content'] = Tools::esc($data['jb_booking_content']);
                $data['jbt_title'] = Tools::esc($data['jbt_title']);
                $data['had_pass'] = self::had_pass($data['jbt_sn'], $data['jb_date']);
                $all_content[] = $data;
            }
            $xoopsTpl->assign('bar', $PageBar['bar']);
            $xoopsTpl->assign('all_content', $all_content);
        }

        $sweet_alert = new SweetAlert();
        $sweet_alert->render('delete_jill_booking_func', "{$_SERVER['PHP_SELF']}?op=delete_booking&XOOPS_TOKEN_REQUEST=" . Tools::token() . '&jb_info=', 'jb_info');
        $xoopsTpl->assign('token', Tools::token());
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        // 模組管理員可審核所有場地
        $xoopsTpl->assign('item_opt', Jill_booking_item::options($jbi_sn, $xoopsUser->isAdmin() ? 0 : $xoopsUser->uid()));
        $xoopsTpl->assign('now_op', 'jill_booking_approvallist');
    }

    // 審核通過並通知，回傳 '1' 或 '0'
    public static function approve($jb_sn, $date, $jbt_sn)
    {
        global $xoopsDB, $xoopsUser;
        // 場地由時段查出，不信任請求帶來的 jbi_sn
        $slot = Tools::slot($jbt_sn);
        if (!$slot || !Tools::is_date($date) || !Tools::is_approver($slot['jbi_sn'])) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM, true);
        }
        $table = $xoopsDB->prefix('jill_booking_date');
        $result = Utility::query("SELECT b.`jb_uid` FROM `$table` AS a JOIN `" . $xoopsDB->prefix('jill_booking') . '` AS b ON a.`jb_sn`=b.`jb_sn`
            WHERE a.`jb_sn`=? AND a.`jb_date`=? AND a.`jbt_sn`=?', 'isi', [$jb_sn, $date, $jbt_sn]);
        $row = $xoopsDB->fetchRow($result);
        if (!$row) {
            return '0';
        }
        $now = date('Y-m-d', xoops_getUserTimestamp(time()));
        Utility::query("UPDATE `$table` SET `jb_status`='1', `approver`=?, `pass_date`=? WHERE `jb_sn`=? AND `jb_date`=? AND `jbt_sn`=?", 'isisi', [$xoopsUser->uid(), $now, $jb_sn, $date, $jbt_sn]);

        // 通知預約者與所有審核者
        $name = \XoopsUser::getUnameFromId($row[0], 1);
        $title = sprintf(_MD_JILLBOOKIN_MAIL_TITLE, $name, $date, $slot['jbi_title']);
        $content = Utility::html5(sprintf(_MD_JILLBOOKIN_MAIL_CONTENT, $name, $date, Tools::esc($slot['jbi_title']), Tools::week_name(date('w', strtotime($date))), Tools::esc($slot['jbt_title'])));
        $member_handler = xoops_getHandler('member');
        foreach (array_unique(array_filter([$row[0], ...explode(',', (string) $slot['jbi_approval'])])) as $uid) {
            $user = $member_handler->getUser((int) $uid);
            if (is_object($user)) {
                Tools::send_now($user->getVar('email', 'n'), $title, $content);
            }
        }
        return '1';
    }

    // 該時段已核給誰；預約助手等直接核准的沒有通過日期
    private static function had_pass($jbt_sn, $date)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT a.`pass_date`, b.`jb_uid` FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS a
            JOIN `' . $xoopsDB->prefix('jill_booking') . "` AS b ON b.`jb_sn`=a.`jb_sn`
            WHERE a.`jb_status`='1' AND a.`jb_date`=? AND a.`jbt_sn`=? ORDER BY a.`jb_waiting` LIMIT 1", 'si', [$date, $jbt_sn]);
        $row = $xoopsDB->fetchRow($result);
        return $row ? $row[0] . _MD_JILLBOOKIN_HADPASS . \XoopsUser::getUnameFromId($row[1], 1) : '';
    }
}
