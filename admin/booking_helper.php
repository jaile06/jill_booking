<?php
use Xmf\Request;
use XoopsModules\Jill_booking\Tools;
use XoopsModules\Tadtools\My97DatePicker;
use XoopsModules\Tadtools\Utility;
$GLOBALS['xoopsOption']['template_main'] = "booking_helper_adm_main.tpl";
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/function.php';
header('HTTP/1.1 200 OK');
$xoopsLogger->activated = false;
/*-----------執行動作判斷區----------*/
$op = Request::getString('op');

switch ($op) {
    case 'get_times_of_item':
        // 全部時段 array
        // jbt_sn, jbi_sn, jbt_title, jbt_week, opened
        $data = getTimesByItem(Request::getInt('sn'), Request::getString('date'));
        // 當日已預約時段  array
        // jbt_sn, jb_uid, jb_booking_content, name
        $used = getUsedTimes(array_column($data, 'jbt_sn'), Request::getString('date'));
        $used_sn = array_column($used, 'jbt_sn');

        // 轉換全部時段之資料陣列
        // 附加是否可預約標記，以及預約者姓名、預約事由
        // jbi_sn: 1
        // jbt_sn: 1
        // jbt_title: "第 1 節"
        // jbt_week: "2,3,4,5,6"
        // opened: true
        // can_order: false
        // event: "個人預約"
        // order_user: "管理員"
        $data = array_map(function ($orig) use ($used, $used_sn) {
            $key = array_search($orig['jbt_sn'], $used_sn);
            if ($key !== false) {
                // 已被預約，則設為不可預約且附加預約者姓名與事由
                $orig['can_order'] = false;
                $orig['order_user'] = $used[$key]['name'];
                $orig['event'] = Tools::esc($used[$key]['jb_booking_content']);
            } else {
                // 未被預約，則是否可預約設為是否開放之值
                $orig['can_order'] = $orig['opened'];
            }

            return $orig;
        }, $data);
        echo getJSONResponse($data);
        die();

    case 'create_orders':
        Tools::check_token(true);
        $data = json_decode(file_get_contents("php://input"), true);
        $error = createOrders(is_array($data) ? $data : []);
        die(getJSONResponse(['result' => $error === '', 'msg' => $error]));

    default:
        show_content();
        break;
}

$xoopsTpl->assign("jill_book_adm", $jill_book_adm);
require_once __DIR__ . '/footer.php';

/*-----------function區--------------*/

//顯示預設頁面內容
function show_content()
{
    // 加入日期選擇器
    // https://campus-xoops.tn.edu.tw/modules/tad_book3/page.php?tbdsn=874
    // http://my97.net/demo/index.htm
    $cal = new My97DatePicker();
    $cal->render();

    global $xoopsTpl;

    $items = getAllItems();

    $xoopsTpl->assign('items', $items);
    $xoopsTpl->assign('token', Tools::token());
}

// 進行預約，成功回傳空字串，否則回傳錯誤訊息
function createOrders($data)
{
    $jb_date = trim((string) ($data['jb_date'] ?? ''));
    $slots = [];
    foreach ((array) ($data['values'] ?? []) as $jbt_sn) {
        $slot = Tools::slot((int) $jbt_sn);
        if (!Tools::bookable($slot, $jb_date, false)) {
            return _MD_JILLBOOKIN_BAD_DATA;
        }
        $slots[(int) $jbt_sn] = $slot;
    }
    if (!$slots) {
        return _MD_JILLBOOKIN_BAD_DATA;
    }
    if (getUsedTimes(array_keys($slots), $jb_date)) {
        return _MD_JILLBOOKIN_BOOKED;
    }

    $event = trim((string) ($data['event'] ?? ''));
    $jb_sn = insert_jill_booking($event === '' ? _MD_INDIVIDUAL_BOOKING : $event, $jb_date, $jb_date);
    foreach ($slots as $jbt_sn => $slot) {
        insert_jill_booking_week($jb_sn, $jbt_sn, date('w', strtotime($jb_date)));
        insert_jill_booking_date($jb_sn, $slot, $jb_date, true);
    }
    return '';
}

// 取得所有場地
function getAllItems()
{
    global $xoopsDB;

    $sql = 'SELECT `jbi_sn`, `jbi_title` FROM `' . $xoopsDB->prefix('jill_booking_item') . '`';
    $result = Utility::query($sql);

    $data = [];
    while ($item = $xoopsDB->fetchArray($result)) {
        $data[] = $item;
    }
    ;

    return $data;
}

// 取得某場地可用之時段
function getTimesByItem($jbi_sn, $date)
{
    global $xoopsDB;

    $sql = 'SELECT `jbt_sn`, `jbi_sn`, `jbt_title`, `jbt_week` FROM `' . $xoopsDB->prefix('jill_booking_time') . '` WHERE `jbi_sn` = ? ORDER BY `jbt_sort` ASC';
    $result = Utility::query($sql, 'i', [$jbi_sn]);

    $data = [];
    $w = date('w', strtotime($date)); // 0-6，日一二....六
    while ($item = $xoopsDB->fetchArray($result)) {
        // 附加該時段是否開放之資訊
        $item['opened'] = in_array($w, explode(',', $item['jbt_week']));
        $data[] = $item;
    }
    ;

    return $data;
}

// 取得已預約之時段資料陣列
// jbt_sn, jb_uid, jb_booking_content, name
function getUsedTimes(array $all_sn, $date)
{
    global $xoopsDB;
    if (!$all_sn) {
        return [];
    }

    $sql = 'SELECT `jbt_sn`, `jb_uid`, `jb_booking_content` FROM `' . $xoopsDB->prefix('jill_booking_date') . '` AS t_date LEFT JOIN `' . $xoopsDB->prefix('jill_booking') . '` AS booking ON t_date.`jb_sn` = booking.`jb_sn` WHERE `jb_date` = ? AND `jbt_sn` IN (' . Tools::marks($all_sn) . ')';
    $result = Utility::query($sql, 's' . str_repeat('i', count($all_sn)), [$date, ...$all_sn]);

    // 已預約之時段資料陣列，
    // jbt_sn, jb_uid, jb_booking_content, name
    $data = [];
    while ($item = $xoopsDB->fetchArray($result)) {
        // 附加預約者姓名
        $item['name'] = XoopsUser::getUnameFromId($item['jb_uid'], 1);
        $data[] = $item;
    }
    ;

    return $data;
}

// 處理欲回傳之 json
function getJSONResponse($data)
{
    header('Content-Type:application/json;charset=utf-8');

    return json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
