<?php

class CustomLogger implements \Psr\Log\LoggerInterface {

    private static $instance;

    private $folder;
    private array $handles = [];

    private function __construct() {
        $this->folder = dirname(__FILE__)."/_log";

        if(!is_dir($this->folder)) {
            mkdir($this->folder);
        }
    }

    private function getHandle($type)  {
        $date = date("Y-m-d");
        $fileName = sprintf("%s/%s-%s.log", $this->folder, $date, $type);

        if(!isset($this->handles[$type])) {
            $this->handles[$type] = fopen($fileName, "a");
        }

        return $this->handles[$type];
    }

    public static function getInstance(): static {
        if(self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function writeLogInternal(string $type, string $message, mixed $data = null) {
        $handle = $this->getHandle($type);

        $logLine = array_filter([
            date("Y-m-d H:i:s"),
            mb_strtoupper($type),
            self::getRequestId(),
            $message,
            $data ? json_encode($data) : null,
        ]);

        fwrite($handle, implode(" | ", $logLine).PHP_EOL);
    }

    private function writeLog(string $type, string $message, mixed $data = null) {
        $this->writeLogInternal($type, $message, $data);
        $this->writeLogInternal("stream", $message, ["level" => $type]);
    }

    public function info( \Stringable|string $message, array $context = [] ): void  {
        $this->writeLog("info", $message, $context);
    }

    public function debug( \Stringable|string $message, array $context = [] ): void  {
        $this->writeLog("debug", $message, $context);
    }

    public function warning( \Stringable|string $message, array $context = [] ): void  {
        $this->writeLog("warning", $message, $context);
    }

    public function error( \Stringable|string $message, array $context = [] ): void  {
        $this->writeLog("error", $message, $context);
    }

    public function emergency( \Stringable|string $message, array $context = [] ): void {
        $this->writeLog("emergency", $message, $context);
    }

    public function alert( \Stringable|string $message, array $context = [] ): void {
        $this->writeLog("alert", $message, $context);
    }

    public function critical( \Stringable|string $message, array $context = [] ): void {
        $this->writeLog("critical", $message, $context);
    }

    public function notice( \Stringable|string $message, array $context = [] ): void {
        $this->writeLog("notice", $message, $context);
    }

    public function log( $level, \Stringable|string $message, array $context = [] ): void {
        $this->writeLog($level, $message, $context);
    }

    private static function getRequestId(): string
    {
        static $requestId = null;

        if($requestId === null) {
            $requestId = sprintf("X-REQ-%s", bin2hex(random_bytes(20)));
        }

        return mb_substr($requestId, 0, 30);
    }
}