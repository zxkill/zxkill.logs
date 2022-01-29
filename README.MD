Описание
-------------------------


Пример использования
-------------------------

```
CModule::IncludeModule('zxkill.logs');
$logger = new \ZxKill\Logs\Log('import_script');

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
    "zxkill/zxkill.logs": "dev-master"
  },
}

```