<?php
use XoopsModules\Jill_booking\Update;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
if (!class_exists('XoopsModules\Jill_booking\Update')) {
    require dirname(__DIR__) . '/preloads/autoloader.php';
}
function xoops_module_update_jill_booking($module, $old_version)
{
    try {
        Update::run();
    } catch (\Exception $e) {
        // TadTools 的訊息結尾帶檔案路徑，畫面上不顯示
        $module->setErrors(htmlspecialchars(preg_replace('/ in \S.* on line \d+$/', '', $e->getMessage()), ENT_QUOTES, 'UTF-8'));
        return false;
    }
    return true;
}
