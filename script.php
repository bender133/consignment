<?php

error_reporting(E_ALL);

function arrEncoding(array $arr, string $inputEncoding, string $outputEncoding): array
{
    $result = [];
    foreach ($arr as $value) {
        $result[] = iconv($inputEncoding, $outputEncoding, $value);
    }
    return $result;
}

function myErrorHandler($errno, $msg, $file, $line)
{
    if (!is_dir('errorLogs')) {
        mkdir('errorLogs', 0777, true);
    }

    $fileName = $_SERVER['DOCUMENT_ROOT'] . '/' . 'errorLogs/' . 'error.txt';
    $errorText = fopen($fileName, 'a');
    $errStr = "[" . date('d-m-Y H:i:s') . "]" . "Ошибка №-$errno, message: $msg, in file:  $file, line: $line\n";
    fwrite($errorText, $errStr);
    fclose($errorText);
}

set_error_handler('myErrorHandler', E_ALL);


$sett = [
    0 => [
        'sourceFolder' => '111',
        'destinationFolder' => 'destinationFolder',
        'inputEncoding' => 'Windows-1251',
        'outputEncoding' => 'Windows-1251',
        'outputFileSuffix' => 'ыффuj_',],
    1 => [
        'sourceFolder' => 'sourceFolder',
        'destinationFolder' => 'destinationFolder',
        'inputEncoding' => 'Windows-1251',
        'outputEncoding' => 'Windows-1251',
        'outputFileSuffix' => 'ыффuj_',]
];

foreach ($sett as $setting) {


    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $setting['sourceFolder'] . '/';
    if (!is_dir($filePath)) {
        trigger_error("папка $filePath не существует", $error_level = E_USER_ERROR);
        continue;
    }

    $resultPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $setting['destinationFolder'] . '/';
    $inputEncoding = $setting['inputEncoding'];
    $outputEncoding = $setting['outputEncoding'];
    $outputFileSuffix = $setting['outputFileSuffix'];

    foreach (glob($filePath . '*-report-daily.csv') as $file) {

        $change = date('d-m-Y', filemtime($file));
        $now = date('d-m-Y');

        if ($change === $now) {

            if (!is_dir($resultPath)) {
                mkdir($resultPath, 0777, true);
            }

            $csv = fopen($file, 'rb');
            $csvResultPath = $resultPath . $outputFileSuffix . basename($file);
            $csvResult = fopen($csvResultPath, 'w+');
            $weekKey = '';
            $dayKey = '';
            $yearKey = '';
            $productNameKey = '';
            $saleKey = '';
            $i = 0;

            while (($line = fgetcsv($csv, 4000, ";")) !== false) {

                $res = arrEncoding($line, $inputEncoding, $outputEncoding);

                //чтоб не читал весь файл
                if ($i == 30) {
                    fclose($csv);
                    fclose($csvResult);
                    exit('Скрипт отработал');
                }

                if ($i === 0) {
                    $weekKey = array_search('MONTH', $res);
                    $dayKey = array_search('DAY', $res);
                    $yearKey = array_search('YEAR', $res);
                    $productNameKey = array_search('PRODUCT_NAME', $res);
                    $saleKey = array_search('SALE_TYPE', $res);

                    $res[$weekKey] = 'WEEK';

                } else {
                    $checkStr = iconv($inputEncoding, 'UTF-8', $res[$productNameKey]);

                    if (mb_strpos($checkStr, 'Агент', 0, $inputEncoding) === false) {
                        $i++;
                        continue;
                    }

                    if (empty(trim($res[$saleKey]))) {
                        $i++;
                        continue;
                    }
                    $res[$weekKey] = date('W', strtotime($res[$yearKey] . '-' . $res[$weekKey] . '-' . $res[$dayKey]));
                }

                fputcsv($csvResult, $res, ';');
                $i++;

            }

            fclose($csv);
            fclose($csvResult);

        }

    }

}











