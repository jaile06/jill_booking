<?php
namespace XoopsModules\Jill_booking;

use Xmf\Request;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Jeditable;
use XoopsModules\Tadtools\Utility;

// 預約主檔（含 week 子表）
// token 由入口檔驗證
class Jill_booking
{
    // 取得單一主檔，找不到回傳 false
    public static function get($jb_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT * FROM `' . $xoopsDB->prefix('jill_booking') . '` WHERE `jb_sn`=?', 'i', [(int) $jb_sn]);
        return $xoopsDB->fetchArray($result);
    }

    // 寫入一筆預約：$rows 為 [[時段, 日期], ...]；$approved 為 null 時依場地是否需審核決定
    public static function store($content, $start, $end, array $rows, $approved = null)
    {
        global $xoopsDB, $xoopsUser;
        Utility::query('INSERT INTO `' . $xoopsDB->prefix('jill_booking') . '` (`jb_uid`, `jb_booking_time`, `jb_booking_content`, `jb_start_date`, `jb_end_date`) VALUES (?, ?, ?, ?, ?)',
            'issss', [$xoopsUser->uid(), date('Y-m-d H:i:s', xoops_getUserTimestamp(time())), $content, $start, $end]);
        $jb_sn = $xoopsDB->getInsertId();
        try {
            foreach ($rows as [$slot, $date]) {
                Utility::query('INSERT IGNORE INTO `' . $xoopsDB->prefix('jill_booking_week') . '` (`jb_sn`, `jb_week`, `jbt_sn`) VALUES (?, ?, ?)',
                    'iii', [$jb_sn, (int) date('w', strtotime($date)), $slot['jbt_sn']]);
                Jill_booking_date::store($jb_sn, $slot, $date, $approved);
            }
        } catch (\Throwable $e) {
            // MyISAM 無交易，自行回滾；回滾失敗會再丟例外
            Utility::query('DELETE FROM `' . $xoopsDB->prefix('jill_booking_date') . '` WHERE `jb_sn`=?', 'i', [$jb_sn]);
            self::destroy($jb_sn);
            throw $e;
        }
        return $jb_sn;
    }

    // 刪除主檔與 week
    public static function destroy($jb_sn)
    {
        global $xoopsDB;
        foreach (['jill_booking_week', 'jill_booking'] as $table) {
            Utility::query('DELETE FROM `' . $xoopsDB->prefix($table) . '` WHERE `jb_sn`=?', 'i', [$jb_sn]);
        }
    }

    // 修改預約理由：限本人或模組管理員，回傳顯示用內容
    public static function update_content($jb_sn)
    {
        global $xoopsDB, $xoopsUser;
        $booking = self::get($jb_sn);
        if (!$xoopsUser || !$booking || ($booking['jb_uid'] != $xoopsUser->uid() && !$xoopsUser->isAdmin())) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM, true);
        }
        // jeditable 以 innerHTML 回填，送回的是跳脫後文字，存檔前還原
        $value = htmlspecialchars_decode(Request::getText('value', '', 'POST'), ENT_QUOTES);
        Utility::query('UPDATE `' . $xoopsDB->prefix('jill_booking') . '` SET `jb_booking_content`=? WHERE `jb_sn`=?', 'si', [$value, $jb_sn]);
        return Tools::esc($value);
    }

    /********************* 週曆與單筆預約 *********************/

    // 單筆預約，回傳該格的 HTML
    public static function store_single($jbt_sn, $jb_date)
    {
        global $xoopsUser;
        if (empty($_SESSION['can_booking'])) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM, true);
        }
        $slot = Tools::slot($jbt_sn);
        if (!Tools::bookable($slot, $jb_date)) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA, true);
        }
        $jb_sn = self::store(_MD_INDIVIDUAL_BOOKING, $jb_date, $jb_date, [[$slot, $jb_date]]);

        $jeditable = new Jeditable();
        self::editable($jeditable, $jb_sn);
        $booking = [
            'jb_sn' => $jb_sn, 'jb_uid' => $xoopsUser->uid(), 'jb_booking_content' => _MD_INDIVIDUAL_BOOKING,
            'jb_status' => empty($slot['jbi_approval']) ? '1' : '0',
        ];
        return $jeditable->render('force') . self::cell_own($booking, 0, 0, $slot, $jb_date, '');
    }

    // 週曆
    public static function calendar($jbi_sn, $getdate = '')
    {
        global $xoopsTpl, $xoopsUser, $xoTheme;
        $item = $jbi_sn ? Jill_booking_item::get($jbi_sn, true) : false;
        if ($item) {
            $myts = \MyTextSanitizer::getInstance();
            $uid = $xoopsUser ? $xoopsUser->uid() : 0;
            $is_approver = Tools::is_approver($jbi_sn);
            [$start, $end] = Tools::item_range($item);
            $timeArr = Jill_booking_time::get_all($jbi_sn);
            $weekArr = Tools::weekArr($getdate);
            $booked = Jill_booking_date::by_range(array_column($timeArr, 'jbt_sn'), $weekArr[0]['d'], $weekArr[6]['d']);

            $jeditable = new Jeditable();
            $bookingArr = [];
            foreach ($timeArr as $t => $time) {
                $timeArr[$t]['jbt_title'] = Tools::esc($time['jbt_title']);
                foreach ($weekArr as $wk => $week) {
                    $date = $week['d'];
                    $list = $booked[$time['jbt_sn']][$date] ?? [];
                    $main = Jill_booking_date::main($list);
                    $cell = ['color' => 'transparent', 'content' => '', 'status' => []];

                    if ($date < $start) {
                        //過去預約
                        if ($main) {
                            $cell = ['color' => '#959595', 'content' => self::who($main)] + $cell;
                        }
                    } elseif (($end === '' || $date <= $end) && in_array((string) $wk, explode(',', (string) $time['jbt_week']), true)) {
                        //可預約期間
                        if (!$main) {
                            if ($_SESSION['can_booking']) {
                                $cell['status'] = ['func' => 'single_insert_booking', 'time' => $time['jbt_sn'], 'weekinfo' => $date, 'jbi_sn' => $jbi_sn];
                            } else {
                                $cell = ['color' => '#EA4335', 'content' => _MD_JILLBOOKIN_NO] + $cell;
                            }
                        } else {
                            $users = Jill_booking_date::users_icon($list);
                            $slot = $time + ['jbi_sn' => $jbi_sn];
                            if ($uid == $main['jb_uid'] && $_SESSION['can_booking']) {
                                self::editable($jeditable, $main['jb_sn']);
                                $content = self::cell_own($main, $t, $wk, $slot, $date, $users);
                            } else {
                                $head = $is_approver ? self::delete_icon($main, $t, $wk, $slot, $date) : self::who($main);
                                $content = $head . $users . "<div class='show_reason'>" . Tools::esc($main['jb_booking_content']) . '</div>';
                            }
                            $cell = ['color' => '#000000', 'content' => $content] + $cell;
                        }
                    }
                    $bookingArr[$t][$wk] = $cell;
                }
            }
            $jeditable->render();
            $item['jbi_title'] = Tools::esc($item['jbi_title']);
            $item['jbi_desc'] = $item['jbi_desc'] === '' ? '' : $myts->displayTarea($item['jbi_desc'], 1, 1, 1, 1, 0);
            $xoopsTpl->assign('timeArr', $timeArr);
            $xoopsTpl->assign('bookingArr', $bookingArr);
            $xoopsTpl->assign('itemArr', $item);
            $xoopsTpl->assign('weekArr', $weekArr);
        }

        $xoopsTpl->assign('jquery', Utility::get_jquery(true));
        $xoTheme->addStylesheet('modules/tadtools/jquery.qtip/jquery.qtip.css');
        $xoTheme->addScript('modules/tadtools/jquery.qtip/jquery.qtip.js');
        $xoopsTpl->assign('token', Tools::token());
        $xoopsTpl->assign('jbi_sn', $item ? $jbi_sn : '');
        $xoopsTpl->assign('item_opt', Jill_booking_item::options($jbi_sn));
        $xoopsTpl->assign('now_op', 'booking_table');
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    }

    // 待審或預約者姓名
    public static function who($booking)
    {
        $name = \XoopsUser::getUnameFromId($booking['jb_uid'], 1);
        return $booking['jb_status'] == '1' ? $name : _MD_APPROVING . ":{$name}";
    }

    private static function cell_own($booking, $t, $wk, $slot, $date, $users)
    {
        return self::delete_icon($booking, $t, $wk, $slot, $date) . $users
            . "<div id='jb_booking_content{$booking['jb_sn']}' class='edit show_reason'>" . Tools::esc($booking['jb_booking_content']) . '</div>';
    }

    private static function delete_icon($booking, $t, $wk, $slot, $date)
    {
        $name = $booking['jb_status'] == '1' ? \XoopsUser::getUnameFromId($booking['jb_uid'], 1) : _MD_APPROVING;
        return "{$name}<a href=\"javascript:delete_booking({$t} , {$wk} , {$slot['jbt_sn']},'{$date}',{$slot['jbi_sn']});\" style='color:#D44950;' ><i class='fa fa-times' ></i></a>";
    }

    private static function editable($jeditable, $jb_sn)
    {
        $jeditable->setTextCol("#jb_booking_content{$jb_sn}", 'index.php', '100px', '1.5em',
            "{'jb_sn':'" . (int) $jb_sn . "', 'op' : 'save_jb_booking_content', 'XOOPS_TOKEN_REQUEST' : '" . Tools::token() . "'}", 'Edit');
    }

    /********************* 批次預約 *********************/

    // 批次表單
    public static function batch_form($jbi_sn)
    {
        global $xoopsTpl;
        if ($jbi_sn) {
            $item = Jill_booking_item::get($jbi_sn, true);
            if (!$item) {
                Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
            }
            [$start, $end] = Tools::item_range($item);
            $item['jbi_title'] = Tools::esc($item['jbi_title']);

            $weektime = [];
            $timeArr = Jill_booking_time::get_all($jbi_sn);
            foreach ($timeArr as $t => $time) {
                $timeArr[$t]['jbt_title'] = Tools::esc($time['jbt_title']);
                foreach (Jill_booking_time::WEEKS as $w) {
                    $weektime[$t][$w]['jbt_week'] = in_array($w, explode(',', (string) $time['jbt_week']), true)
                        ? "<input type='checkbox' name='jb_week[{$time['jbt_sn']}][]' value='$w'>"
                        : "<span style='color:#D44950'><i class='fa fa-times'></i></span>";
                }
            }
            $formValidator = new FormValidator('#myForm', true);
            $xoopsTpl->assign('formValidator_code', $formValidator->render());
            Utility::token_form();
            $xoopsTpl->assign('itemArr', $item);
            $xoopsTpl->assign('jb_start_date', $start);
            $xoopsTpl->assign('jb_end_date', $start);
            $xoopsTpl->assign('max_date', $end);
            $xoopsTpl->assign('timeArr', $timeArr);
            $xoopsTpl->assign('weektime', $weektime);
            $xoopsTpl->assign('next_op', 'insert_jill_booking');
        }
        $xoopsTpl->assign('item_opt', Jill_booking_item::options($jbi_sn));
        $xoopsTpl->assign('now_op', 'jill_booking_form');
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    }

    // 第一步：驗證後暫存在 session，不寫資料庫；回傳暫存鍵
    public static function batch_prepare()
    {
        if (empty($_SESSION['can_booking'])) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM);
        }
        $jbi_sn = Request::getInt('jbi_sn', 0, 'POST');
        $item = Jill_booking_item::get($jbi_sn, true);
        $start = Request::getString('jb_start_date', '', 'POST');
        $end = Request::getString('jb_end_date', '', 'POST');

        // 只收本場地開放的時段與星期
        $weeks = [];
        foreach (Request::getArray('jb_week', [], 'POST') as $jbt_sn => $jb_week) {
            $slot = Tools::slot($jbt_sn);
            if ($slot && $slot['jbi_sn'] == $jbi_sn) {
                $open = array_intersect((array) $jb_week, explode(',', (string) $slot['jbt_week']));
                if ($open) {
                    $weeks[(int) $jbt_sn] = array_values($open);
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
        $key = bin2hex(random_bytes(8));
        $_SESSION['jill_booking_batch'][$key] = [
            'jbi_sn' => $jbi_sn, 'start' => $start, 'end' => $end, 'weeks' => $weeks,
            'content' => $content === '' ? _MD_JILLBOOKIN_SMNAME2 : $content,
        ];
        // 只留最近幾份，供多分頁使用
        $_SESSION['jill_booking_batch'] = array_slice($_SESSION['jill_booking_batch'], -5, null, true);
        return $key;
    }

    // 第一步與第二步之間的預覽
    public static function batch_preview($key)
    {
        global $xoopsTpl;
        $batch = self::batch_get($key);
        $item = Jill_booking_item::get($batch['jbi_sn'], true);
        if (!$item) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
        }
        $booked = Jill_booking_date::by_range(array_keys($batch['weeks']), $batch['start'], $batch['end']);
        $maxwaiting = 0;
        foreach ($booked as $dates) {
            foreach ($dates as $list) {
                $maxwaiting = max($maxwaiting, ...array_map('intval', array_column($list, 'jb_waiting')));
            }
        }

        $check = "<span style='color:#D44950'><i class='fa fa-check'></i></span>";
        $dateweek = [];
        foreach (self::batch_rows($batch) as [$slot, $date]) {
            $list = array_column($booked[$slot['jbt_sn']][$date] ?? [], null, 'jb_waiting');
            $cells = [];
            for ($j = 1; $j <= max(1, $maxwaiting); $j++) {
                // 已有人的順位顯示姓名，下一個可排的順位打勾
                $cells[] = ['name' => isset($list[$j]) ? \XoopsUser::getUnameFromId($list[$j]['jb_uid'], 1) : ($j === count($list) + 1 ? $check : '')];
            }
            $dateweek[] = [
                'jb_date' => $date, 'week' => date('w', strtotime($date)), 'jbt_sn' => $slot['jbt_sn'],
                'jbt_title' => Tools::esc($slot['jbt_title']), 'waitingArr' => $cells, 'jb_exit' => $list ? 1 : 0,
            ];
        }
        usort($dateweek, fn($a, $b) => $a['jb_date'] <=> $b['jb_date']);

        Utility::token_form();
        $xoopsTpl->assign('jbi_title', Tools::esc($item['jbi_title']));
        $xoopsTpl->assign('batch_key', $key);
        $xoopsTpl->assign('maxwaiting', $maxwaiting);
        $xoopsTpl->assign('dateweek', $dateweek);
        $xoopsTpl->assign('now_op', 'list_jill_booking');
        $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
        $xoopsTpl->assign('next_op', 'insert_jill_booking_date');
    }

    // 第二步：寫入勾選的日期，回傳場地編號
    public static function batch_store($key)
    {
        $batch = self::batch_get($key);
        $chosen = Request::getArray('jb_date', [], 'POST');
        $rows = [];
        foreach (self::batch_rows($batch) as [$slot, $date]) {
            if (in_array($date, (array) ($chosen[$slot['jbt_sn']] ?? []), true) && Tools::bookable($slot, $date)) {
                $rows[] = [$slot, $date];
            }
        }
        if (!$rows) {
            Tools::deny(_MD_JILLBOOKIN_BAD_DATA);
        }
        self::store($batch['content'], $batch['start'], $batch['end'], $rows);
        unset($_SESSION['jill_booking_batch'][$key]);
        return $batch['jbi_sn'];
    }

    private static function batch_get($key)
    {
        if (empty($_SESSION['can_booking']) || empty($_SESSION['jill_booking_batch'][$key])) {
            Tools::deny(_MD_JILLBOOKIN_NO_PERM);
        }
        return $_SESSION['jill_booking_batch'][$key];
    }

    // 展開成 [[時段, 日期], ...]
    private static function batch_rows($batch)
    {
        $rows = [];
        foreach ($batch['weeks'] as $jbt_sn => $weeks) {
            $slot = Tools::slot($jbt_sn);
            if ($slot) {
                foreach (Tools::dates_in($batch['start'], $batch['end'], $weeks) as $date) {
                    $rows[] = [$slot, $date];
                }
            }
        }
        return $rows;
    }
}
