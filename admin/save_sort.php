<?php
use XoopsModules\Jill_booking\Jill_booking_time;
use XoopsModules\Jill_booking\Tools;

require_once dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php';
$xoopsLogger->activated = false;
xoops_loadLanguage('main', 'jill_booking');

Tools::xoops_security_check(true);
die(Jill_booking_time::sort());
