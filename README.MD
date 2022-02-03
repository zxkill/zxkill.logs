Модуль логирования 1С-Битрикс
-------------------------
Модуль предоставляет возможность удобного ведения логов на проекте, с поддержкой ротации файлов логов.

После установки модуля, не забудьте произвести настройку в административной части Сервисы->Логирование, укажите email для получения оповещений и список доменов, на которых вы хотите получать оповещения.

Разные уровни логов (PSR-3). Таймер, для замера времени выполнения кода. Возможность отправки логов на почту или telegram.

Возможность интеграции модуля в битрикс, для перехвата Exception генерируемых CMS 1С-Битрикс. Для этого необходимо изменить настройки в файле bitrix/.settings.php, пример директивы 'exception_handling'
```
'exception_handling' =>
  array (
    'value' =>
    array (
      'debug' => false,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => array (
          'settings' =>
          array (
            'file' => '/var/log/php/exceptions.log',
            'log_size' => 1000000,
            'email' => 'zxkill@gmail.com'
          ),
          'class_name' => 'ZxKill\Logs\Exception\BaseExceptionHandlerLog',
          'extension' => '',
          'required_file' => '../local/modules/zxkill.logs/lib/exception/baseexceptionhandlerlog.php',
      ),
    ),
    'readonly' => false,
  ),
```

логи будут отправляться на указанный тут email, только если не указаны данные в настройках модуля.

Примеры использования модуля
-------------------------

```
use BitrixMainLoader;

Loader::includeModule('zxkill.logs');

$logger = new \ZxKill\Logs\Log('import_script'); //в параметре указывается наименование лога

$logger->debug('debug message', [0 => 'context']);
$logger->info('log message', [0 => 'context']);
$logger->log('log message', [0 => 'context']);
$logger->error('error message', [0 => 'context']);
$logger->fatal('fatal message', [0 => 'context']); //данный метод так же выполняет отправку уведомления на почту

// запуск таймера. необходимо передать код таймера
$logger->startTimer('timer_1');
sleep(1);
// остановка таймера. необходимо передать код таймера.
$logger->stopTimer('timer_1');

//если необходимо выполнить отправку на почту любых логов
$logger->sendAlert();
```

Установка через composer
-------------------------
Пример вашего composer.json
```
{
  "extra": {
    "installer-paths": {
      "local/modules/{$name}/": ["type:bitrix-module"]
    }
  },
  "require": {
    "zxkill/zxkill.logs": "dev-main"
  },
  "config": {
    "allow-plugins": {
      "composer/installers": true
    }
  }
}
```
