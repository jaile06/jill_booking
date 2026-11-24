<?php
use XoopsModules\Jill_booking\Jill_booking;
use XoopsModules\Jill_booking\Tools;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
if (!class_exists('XoopsModules\Jill_booking\Jill_booking')) {
    require XOOPS_ROOT_PATH . '/modules/jill_booking/preloads/autoloader.php';
}

//區塊主函式 (jb_b_week_list)
function jb_b_week_list($options)
{
    global $xoTheme;
    xoops_loadLanguage('main', 'jill_booking');
    if ($xoTheme) {
        $xoTheme->addStylesheet('modules/tadtools/jquery.qtip/jquery.qtip.css');
        $xoTheme->addScript('modules/tadtools/jquery.qtip/jquery.qtip.js');
    }
    $weekArr = Tools::weekArr();
    return ['weekArr' => $weekArr] + Jill_booking::block_tabs($options[0], Jill_booking::block_week($weekArr));
}

//區塊編輯函式 (jb_b_week_list_edit)
function jb_b_week_list_edit($options)
{
    return Jill_booking::block_edit($options);
}
