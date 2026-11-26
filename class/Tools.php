<?php
namespace XoopsModules\Jill_booking;

use Xmf\Request;
use XoopsModules\Tadtools\Utility;

class Tools
{
    // 輸出跳脫
    public static function esc($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    // 富文字 HTML 白名單淨化（防 Stored XSS，同時保留安全排版）
    public static function purify_html($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }
        if (!class_exists('\DOMDocument')) {
            return self::esc($html);
        }

        $prev = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
            if (!$dom->loadHTML($wrapped, LIBXML_NONET)) {
                return self::esc($html);
            }
            $body = $dom->getElementsByTagName('body')->item(0);
            if (!$body) {
                return '';
            }
            self::sanitize_dom_node($body);

            $out = '';
            foreach ($body->childNodes as $child) {
                $out .= $dom->saveHTML($child);
            }
            return $out;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
    }

    private static function sanitize_dom_node(\DOMNode $node)
    {
        static $drop_tags = [
            'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet',
            'svg', 'math', 'template', 'noscript', 'head', 'title', 'meta', 'link', 'base',
            'form', 'input', 'button', 'select', 'textarea', 'option',
        ];
        static $allow_tags = [
            'p', 'br', 'hr', 'div', 'span', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins',
            'sub', 'sup', 'small', 'mark', 'code', 'pre', 'blockquote', 'h1', 'h2', 'h3', 'h4',
            'h5', 'h6', 'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'a', 'img', 'table', 'caption',
            'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'colgroup', 'col', 'figure', 'figcaption',
        ];
        static $global_attrs = ['class', 'title', 'dir', 'lang', 'style'];
        static $tag_attrs = [
            'a' => ['href', 'target', 'rel'],
            'img' => ['src', 'alt', 'width', 'height'],
            'td' => ['colspan', 'rowspan', 'scope', 'headers'],
            'th' => ['colspan', 'rowspan', 'scope', 'headers'],
            'col' => ['span'],
            'colgroup' => ['span'],
            'ol' => ['start', 'type'],
        ];

        $parent = $node;
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $parent->removeChild($child);
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, $drop_tags, true)) {
                $parent->removeChild($child);
                continue;
            }
            self::sanitize_dom_node($child);
            if (!in_array($tag, $allow_tags, true)) {
                foreach (iterator_to_array($child->childNodes) as $grand) {
                    $parent->insertBefore($grand, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            $allowed = array_merge($global_attrs, $tag_attrs[$tag] ?? []);
            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->nodeName);
                if (!in_array($name, $allowed, true)) {
                    $child->removeAttribute($attr->nodeName);
                    continue;
                }
                if ($name === 'href' || $name === 'src') {
                    $url = self::clean_url($attr->nodeValue, $name === 'src');
                    if ($url === null) {
                        $child->removeAttribute($attr->nodeName);
                    } else {
                        $child->setAttribute($name, $url);
                    }
                } elseif ($name === 'style') {
                    $css = self::clean_style($attr->nodeValue);
                    if ($css === '') {
                        $child->removeAttribute($attr->nodeName);
                    } else {
                        $child->setAttribute('style', $css);
                    }
                } elseif ($name === 'target') {
                    $target = strtolower(trim($attr->nodeValue));
                    if (!in_array($target, ['_blank', '_self'], true)) {
                        $child->removeAttribute($attr->nodeName);
                    } else {
                        $child->setAttribute('target', $target);
                    }
                }
            }
            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    private static function clean_url($value, $is_img)
    {
        $url = trim((string) preg_replace('/[\x00-\x20\x7F]+/', '', (string) $value));
        if ($url === '' || strpos($url, '//') === 0) {
            return null;
        }
        $scheme_pos = strpos($url, ':');
        $slash_pos = strpos($url, '/');
        if ($scheme_pos === false || ($slash_pos !== false && $slash_pos < $scheme_pos)) {
            if ($is_img && ($url[0] === '#' || $url[0] === '?')) {
                return null;
            }
            return $url;
        }
        $scheme = strtolower(substr($url, 0, $scheme_pos));
        $allowed = $is_img ? ['http', 'https'] : ['http', 'https', 'mailto', 'tel'];
        return in_array($scheme, $allowed, true) ? $url : null;
    }

    private static function clean_style($style)
    {
        static $allowed_props = [
            'color', 'background-color', 'text-align', 'vertical-align', 'width', 'height',
            'min-width', 'max-width', 'border', 'border-collapse', 'border-spacing',
            'padding', 'margin', 'font-weight', 'font-style', 'text-decoration',
            'list-style-type', 'float',
        ];
        $safe = [];
        foreach (explode(';', (string) $style) as $decl) {
            $parts = explode(':', $decl, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $prop = strtolower(trim($parts[0]));
            $val = trim($parts[1]);
            if (!in_array($prop, $allowed_props, true)) {
                continue;
            }
            if (preg_match('/^(?:[#a-zA-Z0-9\s.,%+-]+|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\))$/', $val)) {
                $safe[] = "{$prop}: {$val}";
            }
        }
        return implode('; ', $safe);
    }

    // 嚴格的 Y-m-d 檢查
    public static function is_date($date)
    {
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', (string) $date, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    // 同一頁共用一個 token
    public static function token()
    {
        static $token;
        return $token ??= $GLOBALS['xoopsSecurity']->createToken();
    }

    // 寫入 op 一律經此：只收 POST 與 POST 內的 token
    // ponytail: AJAX 不清除 token，同頁多次 AJAX 才能重用，到期由核心回收；其餘用過即清除
    public static function xoops_security_check($ajax = false)
    {
        // 空 token 要先擋：2.7.4 核心收到空字串會改讀網址
        $token = Request::getString('XOOPS_TOKEN_REQUEST', '', 'POST');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $token === '' || !$GLOBALS['xoopsSecurity']->check(!$ajax, $token)) {
            self::deny(_MD_JILLBOOKIN_TOKEN_FAIL, $ajax);
        }
    }

    // 產生 JS 函式 $func：確認後以 POST 送到目前頁面；$vars 為函式參數，$data 為固定欄位
    // TadTools 的 SweetAlert 只能導向網址（GET），所以只用它的資源
    public static function confirm_post($func, $op, array $vars, array $data = [])
    {
        global $xoTheme;
        xoops_loadLanguage('main', 'tadtools');
        Utility::get_jquery();
        $xoTheme->addStylesheet('modules/tadtools/sweet-alert/sweet-alert.css');
        $xoTheme->addScript('modules/tadtools/sweet-alert/sweet-alert.js');

        $js = fn($value) => json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        $args = implode(', ', $vars);
        $fixed = $js(['op' => $op, 'XOOPS_TOKEN_REQUEST' => self::token()] + $data);
        $xoTheme->addScript('', null, "
            function {$func}({$args}) {
                swal({
                    title: {$js(_TAD_DEL_CONFIRM_TITLE)},
                    text: {$js(_TAD_DEL_CONFIRM_TEXT)},
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DD6B55',
                    confirmButtonText: {$js(_TAD_DEL_CONFIRM_BTN)},
                    closeOnConfirm: false,
                    allowOutsideClick: true
                }, function() {
                    var form = $('<form method=\"post\">').attr('action', {$js($_SERVER['PHP_SELF'])});
                    $.each($.extend({$fixed}, {{$args}}), function(name, value) {
                        $('<input type=\"hidden\">').attr({name: name, value: value}).appendTo(form);
                    });
                    form.appendTo('body')[0].submit();
                });
            }
        ");
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
        return $slot['jbi_enable'] == '1' && (string) $slot['jbi_start'] <= date('Y-m-d') && $date >= $start && ($end === '' || $date <= $end);
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
