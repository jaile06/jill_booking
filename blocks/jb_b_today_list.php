<?php
use XoopsModules\Jill_booking\Jill_booking;
if (!class_exists('XoopsModules\Tadtools\Utility')) {
    require XOOPS_ROOT_PATH . '/modules/tadtools/preloads/autoloader.php';
}
if (!class_exists('XoopsModules\Jill_booking\Jill_booking')) {
    require XOOPS_ROOT_PATH . '/modules/jill_booking/preloads/autoloader.php';
}

function jb_b_today_list($options)
{
    xoops_loadLanguage('main', 'jill_booking');
    return Jill_booking::block_tabs($options[0], Jill_booking::block_today());
}

function jb_b_today_list_edit($options)
{
    return Jill_booking::block_edit($options);
}
