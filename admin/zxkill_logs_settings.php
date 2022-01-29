<?php

use Bitrix\Main\Config\Option;
use ZxKill\Logs\Log;

$MODULE_ID = 'zxkill.logs';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
global $APPLICATION;

\Bitrix\Main\Loader::includeModule($MODULE_ID);
$methodsAlert = Log::getAlertMethods();
$methodsStorage = Log::getStorageMethods();
//сохраняем
if (check_bitrix_sessid() && isset($_POST['update'])) {
    Option::set($MODULE_ID, 'LOG_DIR', $_POST['LOG_DIR']);
    Option::set($MODULE_ID, 'LOG_FILE_EXTENSION', $_POST['LOG_FILE_EXTENSION']);
    Option::set($MODULE_ID, 'DATE_FORMAT', $_POST['DATE_FORMAT']);

    Option::set($MODULE_ID, 'USE_DELIMITER', $_POST['USE_DELIMITER']);
    Option::set($MODULE_ID, 'USE_BACKTRACE', $_POST['USE_BACKTRACE']);
    Option::set($MODULE_ID, 'DEV', $_POST['DEV']);

    Option::set($MODULE_ID, 'CEVENT_TYPE', $_POST['CEVENT_TYPE']);
    Option::set($MODULE_ID, 'CEVENT_MESSAGE', $_POST['CEVENT_MESSAGE']);
    Option::set($MODULE_ID, 'DEFAULT_EMAIL', $_POST['DEFAULT_EMAIL']);
    Option::set($MODULE_ID, 'ROTATE_DAY', $_POST['ROTATE_DAY']);
    Option::set($MODULE_ID, 'TELEGRAM_API_KEY', $_POST['TELEGRAM_API_KEY']);
    Option::set($MODULE_ID, 'TELEGRAM_CHAT_ID', $_POST['TELEGRAM_CHAT_ID']);
    Option::set($MODULE_ID, 'HANDLER_EVENT', $_POST['HANDLER_EVENT']);
    Option::set($MODULE_ID, 'STORAGE', $_POST['STORAGE']);
    Option::set($MODULE_ID, 'DOMAIN_FOR_ALERT', $_POST['DOMAIN_FOR_ALERT']);

    if (!isset($_POST['USE_DELIMITER'])) {
        Option::set($MODULE_ID, 'USE_DELIMITER', 0);
    }
    if (!isset($_POST['USE_BACKTRACE'])) {
        Option::set($MODULE_ID, 'USE_BACKTRACE', 0);
    }
    if (!isset($_POST['DEV'])) {
        Option::set($MODULE_ID, 'DEV', 0);
    }
    LocalRedirect($APPLICATION->GetCurPage());
}

$aTabs = array(
    array("DIV" => "edit1", "TAB" => 'Настройки', "TITLE" => 'Настройки'),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

<? if (!empty($arErrors)): ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title">Ошибка</div>
            <? foreach ($arErrors as $error): ?>
                <?= $error ?><br/>
            <? endforeach ?>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
<? endif ?>
    <form method="post" action="<? echo $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
        <?= bitrix_sessid_post();?>
    <? $tabControl->Begin(); ?>

<? $tabControl->BeginNextTab(); ?>
    <tr>
        <td>
            <table class="adm-detail-content-table edit-table" id="edit1_edit_table">
                <tbody>
                <tr class="heading">
                    <td colspan="2"><b>Основные настройки</b></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Основная директория для логов:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="LOG_DIR"
                            id="LOG_DIR"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'LOG_DIR','/local/var/logs/')?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Расширение файлов логов:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="LOG_FILE_EXTENSION"
                            id="LOG_FILE_EXTENSION"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'LOG_FILE_EXTENSION', '.log')?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Формат времени:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="DATE_FORMAT"
                            id="DATE_FORMAT"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'DATE_FORMAT', 'Y-m-d H:i:s')?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="USE_DELIMITER">
                            Нужно ли добавлять разделить в файл логов:
                        </label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input id="USE_DELIMITER" class="subscribe-modal" type="checkbox"
                            name="USE_DELIMITER" value="1"
                            <?if(Option::get($MODULE_ID, 'USE_DELIMITER', 1)):?>checked<?endif;?>>
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="USE_BACKTRACE">
                            Нужно ли добавлять в файл лога информацию о месте вызова:
                        </label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input id="USE_BACKTRACE" class="subscribe-modal" type="checkbox"
                           name="USE_BACKTRACE" value="1"
                           <?if(Option::get($MODULE_ID, 'USE_BACKTRACE', 1)):?>checked<?endif;?>>
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="DEV">
                        Нужно ли записывать данные при вызове метода debug():
                        </label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input id="DEV" class="subscribe-modal" type="checkbox"
                           name="DEV" value="1"
                           <?if(Option::get($MODULE_ID, 'DEV', 1)):?>checked<?endif;?>>
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Сколько дней хранить логи:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="ROTATE_DAY"
                            id="ROTATE_DAY"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'ROTATE_DAY', 5)?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Получать уведомления на:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <select name="HANDLER_EVENT">
                            <?php foreach($methodsAlert as $method) { ?>
                                <option
                                        value="<?= $method['CODE'] ?>"
                                        <?=(Option::get($MODULE_ID, 'HANDLER_EVENT', 'email') == $method['CODE']) ? 'selected': ''?>
                                >
                                    <?= $method['NAME'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Хранить логи в:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <select name="STORAGE">
                            <?php foreach($methodsStorage as $method) { ?>
                                <option
                                        value="<?= $method['CODE'] ?>"
                                        <?=(Option::get($MODULE_ID, 'STORAGE', 'file') == $method['STORAGE']) ? 'selected': ''?>
                                >
                                    <?= $method['NAME'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr class="heading">
                    <td colspan="2"><b>Почта</b></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Почтовое событие:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="CEVENT_TYPE"
                            id="CEVENT_TYPE"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'CEVENT_TYPE', 'ZXKILL_LOGS_ALERT')?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Почтовый шаблон:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="CEVENT_MESSAGE"
                            id="CEVENT_MESSAGE"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'CEVENT_MESSAGE', 'ZXKILL_LOGS_FATAL_TEMPLATE')?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Email для оповещений (можно указать несколько, через запятую):
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="DEFAULT_EMAIL"
                            id="DEFAULT_EMAIL"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'DEFAULT_EMAIL')?>">
                    </td>
                </tr>
                <tr class="heading">
                    <td colspan="2"><b>Телеграм</b></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Телеграм, API key:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="TELEGRAM_API_KEY"
                            id="TELEGRAM_API_KEY"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'TELEGRAM_API_KEY')?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Телеграм, chat ID:
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input
                            type="text"
                            name="TELEGRAM_CHAT_ID"
                            id="TELEGRAM_CHAT_ID"
                            size="50"
                            value="<?= Option::get($MODULE_ID, 'TELEGRAM_CHAT_ID')?>">
                    </td>
                </tr>
                <tr class="heading">
                    <td colspan="2"><b>Домены</b></td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        Список доменов, на которых можно отправлять <br/>уведомления(каждый домен с новой строки):
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <textarea name="DOMAIN_FOR_ALERT"
                            style="width: 100%; box-sizing: border-box"
                            id="DOMAIN_FOR_ALERT"
                            cols="50"
                            rows="6"><?= Option::get($MODULE_ID, 'DOMAIN_FOR_ALERT')?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
                <p><input class="adm-btn" type="submit" name="update" value="Сохранить"></p>

        </td>
    </tr>

<? $tabControl->Buttons(); ?>
<? $tabControl->End(); ?>
    </form>
<? echo BeginNote(); ?>
    <span class="required">*</span> Поля обязательные для заполнения
<? echo EndNote(); ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>