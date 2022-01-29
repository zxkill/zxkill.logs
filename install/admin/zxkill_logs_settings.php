<?php
$bitrixpath = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/zxkill.logs/admin/zxkill_logs_settings.php";
$localpath = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/zxkill.logs/admin/zxkill_logs_settings.php";

if (file_exists($bitrixpath)) {
    require($bitrixpath);
} else if (file_exists($localpath)) {
    require($localpath);
}
