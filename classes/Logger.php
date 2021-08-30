<?php

namespace classes;

class Logger
{
    /**
     * @param string $message
     */
    public static function log(string $message)
    {
        $logRoot = getcwd() . '/logs';
        if (is_dir($logRoot) === FALSE) {
            mkdir($logRoot, 0777, TRUE);
        }

        $f = fopen($logRoot . '/log.txt', 'a');
        $message = "[" . date('d-m-Y H:i:s') . "]  " . $message . PHP_EOL;
        fwrite($f, $message);
        fclose($f);
    }

    /**
     * @param string $message
     */
    public static function errorLog(string $message)
    {
        $logRoot = getcwd() . '/logs';

        if (is_dir($logRoot) === FALSE) {
            mkdir($logRoot, 0777, TRUE);
        }

        $errorText = fopen($logRoot . '/error.txt', 'a');
        $errStr = "[" . date('d-m-Y H:i:s') . "]" . "$message \n";
        fwrite($errorText, $errStr);
        fclose($errorText);
    }
}