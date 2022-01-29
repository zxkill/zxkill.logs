<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use ZxKill\Logs\Settings;

//Loc::loadMessages(__FILE__);

if (class_exists('zxkill_logs')) {
    return;
}

class zxkill_logs extends CModule
{
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public $moduleSettings = null;
    protected $errors = [];
    private $MODULE_PATH;

    public function __construct()
    {
        $this->MODULE_ID = 'zxkill.logs';
        $this->MODULE_NAME = 'Логирование';
        $this->MODULE_DESCRIPTION = 'Логирование и уведомления об ошибках';
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = 'Никитин Алексей';
        $this->PARTNER_URI = 'https://zxkill.ru';

        $this->MODULE_PATH = $this->getModulePath();

        $arModuleVersion = [];
        include $this->MODULE_PATH . '/install/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    /**
     * Return path module
     *
     * @return string
     */
    protected function getModulePath(): string
    {
        $modulePath = explode('/', __FILE__);
        $modulePath = array_slice($modulePath, 0, array_search($this->MODULE_ID, $modulePath) + 1);

        return join('/', $modulePath);
    }

    public function doInstall(): bool
    {
        global $APPLICATION;

        $settingsInclude = $this->includeSettings();

        if ($settingsInclude) {
            $createDir = $this->createDirectory();
            $this->checkPermission($createDir);
            $this->createSendEvent();
        } else {
            $errorMgs = 'Не удалось получить файл конфигурации модуля. Пожалуйста, убедитесь, что файл logger.config.php присутствует в папке модуля.';

            if (file_exists(__DIR__ . '/../logger.config.example.php')) {
                $errorMgs .= '<br>Найден файл примера конфига. Нужно изменить название данного файла (<b>' . realpath(__DIR__ . '/../logger.config.example.php') . '</b>) на <b>logger.config.php</b> и изменить нужные настройки';
            } else {
                $errorMgs .= '<br>Ошибка';
            }

            $this->errors[] = $errorMgs;
        }

        if (!empty($this->errors)) {
            $APPLICATION->ThrowException(implode('<br>', $this->errors));
            return false;
        } else {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->installFiles();
        }
        return true;
    }

    public function doUninstall()
    {
        $this->unInstallFiles();
        $this->removeSendEvent();
        ModuleManager::unregisterModule($this->MODULE_ID);
    }

    public function createDirectory()
    {
        $result = false;
        $defDirName = $this->moduleSettings['LOG_DIR'];

        if (empty($defDirName)) {
            $this->errors[] = 'Не установлена основная директория. Проверьте настройки файла logger.config.php';
        } else {
            $dirPath = $_SERVER['DOCUMENT_ROOT'] . $defDirName;

            if (!file_exists($dirPath)) {
                $mkdir = mkdir($dirPath, BX_DIR_PERMISSIONS, true);

                if (!$mkdir) {
                    $this->errors[] = 'Ошибка создания основной директории для логов ' . $dirPath;
                }
            }

            chmod($dirPath, 0777);
            $result = $dirPath;
            $this->checkPermission($dirPath);
        }

        return $result;
    }

    public function includeSettings(): bool
    {
        $return = false;
        $settingsFilePath = __DIR__ . '/../logger.config.php';
        $settingsFile = include_once(realpath($settingsFilePath));

        if ($settingsFile && is_array($settingsFile)) {
            $this->moduleSettings = $settingsFile;
            $return = true;
        } else {
            $this->errors[] = 'Проблема с получением настроек из файла logger.config.php';
        }

        return $return;
    }

    public function checkPermission($file): bool
    {
        if (is_writable($file)) {
            return true;
        } else {
            $this->errors[] = 'Проблема с правами директории ' . $file;
            return false;
        }
    }

    public function createSendEvent(): bool
    {
        global $APPLICATION;

        $result = false;
        $objCEventType = new \CEventType();

        $filterCEventType = ['TYPE_ID' => $this->moduleSettings['CEVENT_TYPE']];
        $objResultCEventType = $objCEventType->GetList($filterCEventType);

        if ($eventType = $objResultCEventType->Fetch()) {
            $createType = $eventType['ID'];
        } else {
            $createType = $objCEventType->Add([
                'LID' => 'ru',
                'EVENT_NAME' => $this->moduleSettings['CEVENT_TYPE'],
                'NAME' => 'zxkill.logs',
            ]);
        }

        if ($createType) {
            $objCEventMessage = new \CEventMessage();
            $objResultCEventMessage
                = $objCEventMessage->GetList(
                $by = 'id',
                $order = 'desc',
                ['TYPE_ID' => $this->moduleSettings['CEVENT_TYPE']]
            );

            if ($eventMessage = $objResultCEventMessage->Fetch()) {
                $createMessage = true;
            } else {
                $arActiveSitesIDs = [];
                $rsSite = \CSite::GetList($by = "sort", $order = "desc", ['ACTIVE' => 'Y']);

                while ($site = $rsSite->Fetch()) {
                    $arActiveSitesIDs[] = $site['ID'];
                }

                $createMessage = $objCEventMessage->Add([
                    'ACTIVE' => 'Y',
                    'EVENT_NAME' => $this->moduleSettings['CEVENT_TYPE'],
                    'LID' => $arActiveSitesIDs,
                    'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                    'EMAIL_TO' => '#EMAIL_TO#',
                    'BCC' => '#BCC#',
                    'BODY_TYPE' => 'html',
                    'SUBJECT' => '#SUBJECT#',
                    'MESSAGE' => '#MESSAGE#',
                ]);
            }

            if ($createMessage) {
                $result = true;
            }
        }

        $appErrors = $APPLICATION->LAST_ERROR;

        if (!empty($appErrors)) {
            $this->errors[] = 'Создание почтового события: ' . SITE_ID .  $appErrors->msg;
        }

        return $result;
    }

    public function removeSendEvent()
    {
        if ($this->includeSettings()) {
            $objCEventMessage = new \CEventMessage();
            $objResultCEventMessage
                = $objCEventMessage->GetList(
                $by = 'id',
                $order = 'desc',
                ['TYPE_ID' => $this->moduleSettings['CEVENT_TYPE']]
            );

            while ($eventMessage = $objResultCEventMessage->Fetch()) {
                \CEventMessage::Delete($eventMessage['ID']);
            }

            $objCEventType = new \CEventType();
            $filterCEventType = ['TYPE_ID' => $this->moduleSettings['CEVENT_TYPE']];
            $objResultCEventType = $objCEventType->GetList($filterCEventType);
            if ($eventType = $objResultCEventType->Fetch()) {
                $objCEventType->delete($eventType['ID']);
            }
        }
    }

    /**
     * Copy files module
     *
     * @return bool
     */
    public function installFiles(): bool
    {
        CopyDirFiles(
            $this->MODULE_PATH . '/install/admin',
            getenv('DOCUMENT_ROOT') . '/bitrix/admin',
            true,
            true
        );
        return true;
    }

    /**
     * Remove files module
     *
     * @return bool
     */
    public function unInstallFiles(): bool
    {
        DeleteDirFiles($this->MODULE_PATH . '/install/admin', getenv('DOCUMENT_ROOT') . '/bitrix/admin');
        return true;
    }
}
