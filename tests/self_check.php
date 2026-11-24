<?php
// 執行：php tests/self_check.php（不需 XOOPS 與資料庫）
if (PHP_SAPI !== 'cli') {
    exit;
}
require dirname(__DIR__) . '/class/Tools.php';
use XoopsModules\Jill_booking\Tools;

$fail = 0;
function check($name, $actual, $expected)
{
    global $fail;
    if ($actual !== $expected) {
        $fail++;
        echo "FAIL $name: " . var_export($actual, true) . "\n";
    }
}

foreach (['2026-12-01' => true, '2026-02-30' => false, '2026-1-1' => false, '' => false, "2026-12-01' OR 1" => false, 'x2026-12-01' => false] as $d => $ok) {
    check("is_date $d", Tools::is_date($d), $ok);
}

$xoopsModuleConfig = ['max_bookingweek' => 4];
$day = fn($n) => date('Y-m-d', strtotime("$n day"));
$slot = ['jbt_week' => '0,1,2,3,4,5,6', 'jbi_enable' => '1', 'jbi_start' => $day(-10), 'jbi_end' => '0000-00-00'];
check('今天', Tools::bookable($slot, $day(0)), true);
check('昨天', Tools::bookable($slot, $day(-1)), false);
check('第四週最後一天', Tools::bookable($slot, $day(28)), true);
check('超過四週', Tools::bookable($slot, $day(29)), false);
check('助手不限週數', Tools::bookable($slot, $day(29), false), true);
check('助手仍擋過去', Tools::bookable($slot, $day(-1), false), false);
check('停用場地', Tools::bookable(['jbi_enable' => '0'] + $slot, $day(1)), false);
check('迄日當天', Tools::bookable(['jbi_end' => $day(3)] + $slot, $day(3)), true);
check('迄日之後', Tools::bookable(['jbi_end' => $day(3)] + $slot, $day(4)), false);
check('啟用日之前', Tools::bookable(['jbi_start' => $day(5)] + $slot, $day(4)), false);
check('未開放星期', Tools::bookable(['jbt_week' => ''] + $slot, $day(1)), false);
check('不存在的時段', Tools::bookable(false, $day(1)), false);
check('IN 佔位', Tools::marks([1, 2, 3]), '?,?,?');
check('跳脫', Tools::esc('<b>"\''), '&lt;b&gt;&quot;&#039;');

check('期間展開', Tools::dates_in('2026-12-01', '2026-12-15', ['2', '4']), ['2026-12-01', '2026-12-03', '2026-12-08', '2026-12-10', '2026-12-15']);
check('同日展開', Tools::dates_in('2026-12-03', '2026-12-03', ['4']), ['2026-12-03']);
check('跨月展開', Tools::dates_in('2026-12-31', '2027-01-01', ['4', '5']), ['2026-12-31', '2027-01-01']);
foreach (range(0, 6) as $i) {
    define("_MD_JILLBOOKIN_W$i", "W$i");
}
check('週曆起點', array_column(Tools::weekArr('2026-12-03'), 'd')[0], '2026-11-29');
check('週曆星期日', array_column(Tools::weekArr('2026-11-29'), 'd'), ['2026-11-29', '2026-11-30', '2026-12-01', '2026-12-02', '2026-12-03', '2026-12-04', '2026-12-05']);

require dirname(__DIR__) . '/class/Jill_booking_date.php';
$row = fn($sn, $status) => ['jb_sn' => $sn, 'jb_status' => $status];
check('代表：無', XoopsModules\Jill_booking\Jill_booking_date::main([]), null);
check('代表：待審取最前', XoopsModules\Jill_booking\Jill_booking_date::main([$row(1, '0'), $row(2, '0')])['jb_sn'], 1);
check('代表：已核准優先', XoopsModules\Jill_booking\Jill_booking_date::main([$row(1, '0'), $row(2, '1')])['jb_sn'], 2);

echo $fail ? "$fail 項失敗\n" : "全部通過\n";
exit($fail ? 1 : 0);
