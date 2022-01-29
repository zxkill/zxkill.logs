<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menuList = [
    [
        'parent_menu' => 'global_menu_services',
        'sort' => 10,
		'text' => 'Логирование',
		'title' => 'Логирование',
		'url' => 'zxkill_logs_settings.php',
		'icon' => 'sys_menu_icon',
        'items' => []
    ]
];

return isset($menuList) ? $menuList : array();
