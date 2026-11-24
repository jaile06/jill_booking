<?php
use XoopsModules\Tadtools\Utility;

$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu, false, $interface_icon));
$xoopsTpl->assign('jill_book_adm', $jill_book_adm);
$xoTheme->addStylesheet('modules/jill_booking/css/module.css');
require_once XOOPS_ROOT_PATH . '/footer.php';
