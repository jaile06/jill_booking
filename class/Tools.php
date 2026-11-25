<?php
namespace XoopsModules\Jill_booking;

use XoopsModules\Tadtools\Utility;

class Tools
{
    // 輸出跳脫
    public static function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    // 嚴格的 Y-m-d 檢查
    public static function is_date($date)
    {
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', (string) $date, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    // 同一頁共用一個 token（供 AJAX 與連結）
    public static function token()
    {
        static $token;
        return $token ??= $GLOBALS['xoopsSecurity']->createToken();
    }

    // ponytail: 不清除 token，同頁多次 AJAX 才能重用；到期由核心回收
    public static function xoops_security_check($ajax = false)
    {
        if (!$GLOBALS['xoopsSecurity']->check(false)) {
            self::deny(_MD_JILLBOOKIN_TOKEN_FAIL, $ajax);
        }
    }

    // 拒絕請求並結束
    public static function deny($msg, $ajax = false, $url = '')
    {
        if ($ajax) {
            http_response_code(403);
            die(self::esc($msg));
        }
        redirect_header($url ?: $_SERVER['PHP_SELF'], 3, $msg);
        exit;
    }

    // 每次請求重算權限，不快取，撤權才會立即生效
    public static function get_session()
    {
        global $xoopsUser;
        $_SESSION['jill_book_adm'] = $xoopsUser ? $xoopsUser->isAdmin() : false;
        $_SESSION['can_booking'] = self::can_booking();
        $_SESSION['Isapproval'] = self::is_any_approver();
    }

    // 是否具有預約權限
    public static function can_booking()
    {
        global $xoopsUser, $xoopsModuleConfig;
        if (!$xoopsUser) {
            return false;
        }
        return $xoopsUser->isAdmin() || (bool) array_intersect($xoopsUser->groups(), (array) ($xoopsModuleConfig['booking_group'] ?? []));
    }

    // 是否為任一啟用場地的審核者（模組管理員視同）
    public static function is_any_approver()
    {
        global $xoopsDB, $xoopsUser;
        if (!$xoopsUser) {
            return false;
        }
        if ($xoopsUser->isAdmin()) {
            return true;
        }
        $result = Utility::query('SELECT `jbi_approval` FROM `' . $xoopsDB->prefix('jill_booking_item') . '` WHERE `jbi_enable`=?', 's', ['1']);
        while ($row = $xoopsDB->fetchRow($result)) {
            if (in_array((string) $xoopsUser->uid(), explode(',', (string) $row[0]), true)) {
                return true;
            }
        }
        return false;
    }

    // 是否為該場地的審核者（模組管理員視同）
    public static function is_approver($jbi_sn)
    {
        global $xoopsDB, $xoopsUser;
        if (!$xoopsUser) {
            return false;
        }
        if ($xoopsUser->isAdmin()) {
            return true;
        }
        $result = Utility::query('SELECT `jbi_approval` FROM `' . $xoopsDB->prefix('jill_booking_item') . '` WHERE `jbi_sn`=?', 'i', [(int) $jbi_sn]);
        $row = $xoopsDB->fetchRow($result);
        return $row && in_array((string) $xoopsUser->uid(), explode(',', (string) $row[0]), true);
    }

    // 時段及其場地
    public static function slot($jbt_sn)
    {
        global $xoopsDB;
        $result = Utility::query('SELECT t.*, i.`jbi_title`, i.`jbi_start`, i.`jbi_end`, i.`jbi_enable`, i.`jbi_approval`
            FROM `' . $xoopsDB->prefix('jill_booking_time') . '` AS t
            JOIN `' . $xoopsDB->prefix('jill_booking_item') . '` AS i ON t.`jbi_sn`=i.`jbi_sn`
            WHERE t.`jbt_sn`=?', 'i', [(int) $jbt_sn]);
        return $xoopsDB->fetchArray($result);
    }

    // 場地可預約的起訖日，迄日空字串代表不限
    public static function item_range($item)
    {
        global $xoopsModuleConfig;
        $end = (string) $item['jbi_end'];
        if (!empty($xoopsModuleConfig['max_bookingweek'])) {
            $limit = date('Y-m-d', strtotime('+' . (int) $xoopsModuleConfig['max_bookingweek'] . ' week'));
            $end = $end === '' ? $limit : min($end, $limit);
        }
        return [max(date('Y-m-d'), (string) $item['jbi_start']), $end];
    }

    // 該日該時段可否預約；預約助手不限場地啟用期間與週數（$check_range = false）
    public static function bookable($slot, $date, $check_range = true)
    {
        if (!$slot || !self::is_date($date) || $date < date('Y-m-d')) {
            return false;
        }
        if (!in_array(date('w', strtotime($date)), explode(',', (string) $slot['jbt_week']), true)) {
            return false;
        }
        if (!$check_range) {
            return true;
        }
        [$start, $end] = self::item_range($slot);
        return $slot['jbi_enable'] == '1' && $date >= $start && ($end === '' || $date <= $end);
    }

    // IN (?,?) 的佔位字串
    public static function marks($arr)
    {
        return implode(',', array_fill(0, count($arr), '?'));
    }

    // 星期名稱，0 為星期日
    public static function week_name($w)
    {
        return [_MD_JILLBOOKIN_W0, _MD_JILLBOOKIN_W1, _MD_JILLBOOKIN_W2, _MD_JILLBOOKIN_W3, _MD_JILLBOOKIN_W4, _MD_JILLBOOKIN_W5, _MD_JILLBOOKIN_W6][(int) $w];
    }

    // 指定日期所在的一週，由星期日開始
    public static function weekArr($date = '')
    {
        $date = $date ?: date('Y-m-d');
        $sunday = strtotime("$date -" . date('w', strtotime($date)) . ' days');
        $week = [];
        for ($i = 0; $i <= 6; $i++) {
            $day = strtotime("+$i days", $sunday);
            // y、md 供樣板顯示，避免 Smarty 3 的 date_format 呼叫已棄用的 strftime()
            $week[$i] = ['d' => date('Y-m-d', $day), 'y' => date('Y', $day), 'md' => date('m-d', $day), 'w' => self::week_name($i)];
        }
        return $week;
    }

    // 期間內符合星期的日期（含頭尾）
    public static function dates_in($start, $end, array $weeks)
    {
        $dates = [];
        // 9999-12-31 的隔天變成五位數年份，字串比較會失準，以長度擋下
        for ($date = $start; $date <= $end && strlen($date) === 10; $date = date('Y-m-d', strtotime("$date +1 day"))) {
            if (in_array(date('w', strtotime($date)), $weeks, true)) {
                $dates[] = $date;
            }
        }
        return $dates;
    }

    // 立即寄出；偏好設定關閉寄信時回傳 false
    public static function send_now($email, $title, $content)
    {
        global $xoopsModuleConfig;
        if (empty($xoopsModuleConfig['can_send_mail'])) {
            return false;
        }
        $xoopsMailer = xoops_getMailer();
        $xoopsMailer->multimailer->ContentType = 'text/html';
        $xoopsMailer->addHeaders('MIME-Version: 1.0');
        return $xoopsMailer->sendMail($email, $title, $content, []);
    }
}
