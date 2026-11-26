<?php
$modversion = [];

//---模組基本資訊---//
$modversion['name'] = _MI_JILLBOOKIN_NAME;
$modversion['version'] = '4.0.0-Stable';
$modversion['description'] = _MI_JILLBOOKIN_DESC;
$modversion['author'] = _MI_JILLBOOKIN_AUTHOR;
$modversion['credits'] = _MI_JILLBOOKIN_CREDITS;
$modversion['help'] = 'page=help';
$modversion['license'] = 'GNU GPL 2.0';
$modversion['image'] = "images/logo.png";
$modversion['dirname'] = basename(__DIR__);

//---模組狀態資訊---//
$modversion['release_date'] = '2026/11/24'; // 核心 ModuleAdmin::renderAbout() 只拆 '/'
$modversion['module_website_url'] = '';
$modversion['module_website_name'] = _MI_JILLBOOKIN_AUTHOR_WEB;
$modversion['module_status'] = 'release';
$modversion['author_website_url'] = '';
$modversion['author_website_name'] = _MI_JILLBOOKIN_AUTHOR_WEB;
$modversion['min_php'] = '8.3';
$modversion['min_xoops'] = '2.5.11';

//---paypal資訊---//
$modversion['paypal'] = [];
$modversion['paypal']['business'] = 'tnjaile@gmail.com';
$modversion['paypal']['item_name'] = 'Donation :' . _MI_JILLBOOKIN_AUTHOR;
$modversion['paypal']['amount'] = 0;
$modversion['paypal']['currency_code'] = 'USD';

//---安裝設定---//
$modversion['onUpdate'] = "include/onUpdate.php";

//---啟動後台管理界面選單---//
$modversion['system_menu'] = 1;

//---資料表架構---//
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";
$modversion['tables'] = [
    'jill_booking',
    'jill_booking_date',
    'jill_booking_item',
    'jill_booking_time',
    'jill_booking_week',
];

//---管理介面設定---//
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/main.php";
$modversion['adminmenu'] = "admin/menu.php";

//---使用者主選單設定---//
$modversion['hasMain'] = 1;
$modversion['sub'] = [
    ['name' => _MI_JILLBOOKIN_SMNAME2, 'url' => 'batch.php'],
    ['name' => _MI_JILLBOOKIN_SMNAME3, 'url' => 'list.php'],
    ['name' => _MI_JILLBOOKIN_SMNAME4, 'url' => 'listapproval.php'],
];

$templates = [
    'jill_booking_adm_main.tpl',
    'jill_booking_adm_time.tpl',
    'jill_booking_adm_approval.tpl',
    'jill_booking_index.tpl',
    'jill_booking_batch.tpl',
    'jill_booking_list.tpl',
    'jill_booking_listapproval.tpl',
    'booking_helper_adm_main.tpl',
];

foreach ($templates as $file) {
    $modversion['templates'][] = [
        'file' => $file,
        'description' => $file,
    ];
}

$modversion['blocks'] = [
    [
        'file' => 'jb_b_today_list.php',
        'name' => _MI_JB_BNAME1,
        'description' => _MI_JB_BDESC1,
        'show_func' => 'jb_b_today_list',
        'template' => 'jb_b_today_list.tpl',
        'edit_func' => 'jb_b_today_list_edit',
        'options' => 'accordion',
    ],
    [
        'file' => 'jb_b_week_list.php',
        'name' => _MI_JB_BNAME2,
        'description' => _MI_JB_BDESC2,
        'show_func' => 'jb_b_week_list',
        'template' => 'jb_b_week_list.tpl',
        'edit_func' => 'jb_b_week_list_edit',
        'options' => 'accordion',
    ],
];

$modversion['config'] = [
    [
        'name' => 'booking_group',
        'title' => '_MI_JILLBOOKIN_BOOKING_GROUP',
        'description' => '_MI_JILLBOOKIN_BOOKING_GROUP_DESC',
        'formtype' => 'group_multi',
        'valuetype' => 'array',
        'default' => '1',
    ],
    [
        'name' => 'max_bookingweek',
        'title' => '_MI_JILLBOOKIN_MAX_BOOKINGWEEK',
        'description' => '_MI_JILLBOOKIN_MAX_BOOKINGWEEK_DESC',
        'formtype' => 'textbox',
        'valuetype' => 'int',
        'default' => '4',
    ],
    [
        'name' => 'can_send_mail',
        'title' => '_MI_JILLBOOKIN_CAN_SEND_MAIL',
        'description' => '_MI_JILLBOOKIN_CAN_SEND_MAIL_DESC',
        'formtype' => 'yesno',
        'valuetype' => 'int',
        'default' => 1,
    ],
];
