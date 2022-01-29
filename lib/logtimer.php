<?php

namespace ZxKill\Logs;

class LogTimer
{
    protected $timerCode = '';
    protected $timeStart = 0;
    protected $timeEnd = 0;
    protected $execTime = 0;
    protected $die = false;
    protected $startPoint = null;
    protected $endPoint = null;

    public function __construct($code = 'nameless')
    {
        $code = trim($code);
        $this->timerCode = $code;
        $this->timeStart = microtime(true);
    }

    public function setStartPoint($fl)
    {
        $this->startPoint = $fl;
    }

    public function setEndPoint($fl)
    {
        $this->endPoint = $fl;
    }

    public function isDie(): bool
    {
        return $this->die;
    }

    public function stop(): LogTimer
    {
        $this->timeEnd = microtime(true);
        $this->execTime = $this->timeEnd - $this->timeStart;
        $this->die = true;

        return $this;
    }

    public function getTimerData(): array
    {
        $start = \DateTime::createFromFormat('U.u', number_format($this->timeStart, 6, '.', ''))
            ->setTimezone(new \DateTimeZone(date('T')));
        $start = $start->format(Settings::getInstance()->DATE_FORMAT());
        $stop = \DateTime::createFromFormat('U.u', number_format($this->timeEnd, 6, '.', ''))
            ->setTimezone(new \DateTimeZone(date('T')));
        $stop = $stop->format(Settings::getInstance()->DATE_FORMAT());
        $exec = \DateTime::createFromFormat('U.u', number_format($this->execTime, 6, '.', ''));
        $exec = $exec->format('H:i:s.u');
        $data = [
            'CODE' => $this->timerCode,
            'START_TIME' => $start,
            'STOP_TIME' => $stop,
            'EXEC_TIME' => number_format($this->getExecTime(), 9),
            'EXEC_TIME_HUMAN' => $exec,
        ];

        if (!empty($this->startPoint) || !empty($this->endPoint)) {
            $data['START_POINT'] = $this->startPoint;
            $data['STOP_POINT'] = $this->endPoint;
        }

        return $data;
    }

    public function getTimeStart(): int
    {
        return $this->timeStart;
    }

    public function getTimeEnd(): int
    {
        return $this->timeEnd;
    }

    public function getExecTime(): float
    {
        return $this->execTime;
    }

    public function getTimerCode(): string
    {
        return $this->timerCode;
    }
}
