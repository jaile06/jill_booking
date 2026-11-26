<?php
namespace XoopsModules\Jill_booking;

use XoopsModules\Tadtools\Utility;

class Update
{
    // 查詢用索引：資料表 => [索引名, 欄位]
    const INDEXES = [
        'jill_booking' => ['jb_uid', '`jb_uid`'],
        'jill_booking_date' => ['slot', '`jbt_sn`, `jb_date`'],
        'jill_booking_time' => ['jbi_sn', '`jbi_sn`'],
    ];

    // 已移除的舊檔，覆蓋式升級時會留在站上
    const OLD_FILES = [
        'interface_menu.php',
        'function.php',
        'class/Helper.php',
        'templates/sub_jill_booking_item_form.tpl',
        'templates/sub_list_jill_booking_item.tpl',
        'templates/sub_show_one_jill_booking_item.tpl',
        'images/admin/management.png',
        'images/admin/xoopsmicrobutton.gif',
        'images/blank_logo.png',
        'images/down.png',
        'images/edit.cur',
        'images/icons/icon_more.gif',
        'images/icons/off.png',
        'images/left.png',
        'images/right.png',
        'images/up.png',
    ];

    // 依現況逐項升級，可重複執行；失敗丟例外
    public static function run()
    {
        foreach (self::OLD_FILES as $file) {
            $path = dirname(__DIR__) . '/' . $file;
            if (is_file($path)) {
                @unlink($path); // 沒有寫入權限時留著也不影響執行
            }
        }

        // 舊資料有 0000-00-00，嚴格模式下連 ALTER 都會失敗，升級期間暫時放寬
        $mode = Utility::query('SELECT @@SESSION.sql_mode')->fetch_row()[0];
        Utility::query("SET SESSION sql_mode=''");
        try {
            self::old_columns();
            self::null_date('jill_booking_item', 'jbi_end', '停用日期');
            self::null_date('jill_booking_date', 'pass_date', '通過日期');
            foreach (self::INDEXES as $table => [$name, $columns]) {
                self::add_index($table, $name, $columns);
            }
        } finally {
            Utility::query('SET SESSION sql_mode=?', 's', [$mode]);
        }
    }

    // 3.0 以前的欄位修正
    private static function old_columns()
    {
        global $xoopsDB;
        $date = $xoopsDB->prefix('jill_booking_date');
        $item = $xoopsDB->prefix('jill_booking_item');

        if (stripos(self::column('jill_booking_date', 'jbt_sn')['Extra'], 'auto_increment') !== false) {
            Utility::query("ALTER TABLE `$date` MODIFY `jbt_sn` mediumint(8) unsigned NOT NULL COMMENT '時段編號'");
        }
        if (stripos(self::column('jill_booking_item', 'jbi_approval')['Type'], 'enum') === 0) {
            Utility::query("ALTER TABLE `$item` MODIFY `jbi_approval` varchar(255) NOT NULL COMMENT '審核人員'");
        }
        if (!self::column('jill_booking_date', 'approver')) {
            Utility::query("ALTER TABLE `$date` ADD `approver` mediumint(8) unsigned NOT NULL default 0 COMMENT '審核者'");
        }
        if (!self::column('jill_booking_date', 'pass_date')) {
            Utility::query("ALTER TABLE `$date` ADD `pass_date` date DEFAULT NULL COMMENT '通過日期'");
            // 過去的、已核准的，以及與已核准者同時段的候補，視為已處理
            $now = date('Y-m-d');
            Utility::query("UPDATE `$date` AS a LEFT JOIN `$date` AS b ON a.`jb_date`=b.`jb_date` AND a.`jbt_sn`=b.`jbt_sn` AND b.`jb_status`='1'
                SET a.`pass_date`=? WHERE a.`jb_date`<? OR b.`jb_sn` IS NOT NULL", 'ss', [$now, $now]);
        }
    }

    // 日期欄位改為可 NULL，0000-00-00 轉成 NULL
    private static function null_date($table, $column, $comment)
    {
        global $xoopsDB;
        $name = $xoopsDB->prefix($table);
        if (self::column($table, $column)['Null'] === 'NO') {
            Utility::query("ALTER TABLE `$name` MODIFY `$column` date DEFAULT NULL COMMENT '$comment'");
        }
        Utility::query("UPDATE `$name` SET `$column`=NULL WHERE `$column`='0000-00-00'");
    }

    private static function add_index($table, $index, $columns)
    {
        global $xoopsDB;
        $name = $xoopsDB->prefix($table);
        if (!Utility::query("SHOW INDEX FROM `$name` WHERE `Key_name`='$index'")->num_rows) {
            Utility::query("ALTER TABLE `$name` ADD INDEX `$index` ($columns)");
        }
    }

    private static function column($table, $column)
    {
        global $xoopsDB;
        return Utility::query('SHOW COLUMNS FROM `' . $xoopsDB->prefix($table) . "` WHERE `Field`='$column'")->fetch_assoc();
    }

    // 孤兒與重複順位的筆數（只報告不刪除），沒有問題的項目不列
    public static function orphans()
    {
        global $xoopsDB;
        foreach (['main' => '', 'date' => '_date', 'week' => '_week', 'time' => '_time', 'item' => '_item'] as $key => $suffix) {
            $t[$key] = '`' . $xoopsDB->prefix('jill_booking' . $suffix) . '`';
        }
        $lost = fn($a, $b, $key) => "FROM {$t[$a]} AS a LEFT JOIN {$t[$b]} AS b ON a.`$key`=b.`$key` WHERE b.`$key` IS NULL";
        $checks = [
            _MA_JILLBOOKIN_ORPHAN_DATE => $lost('date', 'main', 'jb_sn'),
            _MA_JILLBOOKIN_ORPHAN_MAIN => $lost('main', 'date', 'jb_sn'),
            _MA_JILLBOOKIN_ORPHAN_WEEK => $lost('week', 'main', 'jb_sn'),
            _MA_JILLBOOKIN_ORPHAN_SLOT => $lost('date', 'time', 'jbt_sn'),
            _MA_JILLBOOKIN_ORPHAN_TIME => $lost('time', 'item', 'jbi_sn'),
            _MA_JILLBOOKIN_DUP_WAITING => "FROM (SELECT 1 FROM {$t['date']} GROUP BY `jbt_sn`, `jb_date`, `jb_waiting` HAVING COUNT(*)>1) AS a",
        ];
        $report = [];
        foreach ($checks as $label => $from) {
            $count = (int) Utility::query("SELECT COUNT(*) $from")->fetch_row()[0];
            if ($count) {
                $report[$label] = $count;
            }
        }
        return $report;
    }
}
