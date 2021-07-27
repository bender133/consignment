<?php

function arrEncoding($arr, $inputEncoding, $outputEncoding)
{
    $result = [];
    foreach ($arr as $value) {
        $result[] = iconv($inputEncoding, $outputEncoding, $value);
    }
    return $result;
}

$sett = [
    0 => [
        'sourceFolder' => '/sourceFolder/',
        'destinationFolder' => '/destinationFolder/',
        'inputEncoding' => 'Windows-1251',
        'outputEncoding' => 'Windows-1251',
        'outputFileSuffix' => 'ыфф_',]
];

foreach ($sett as $setting) {


    $filePath = $_SERVER['DOCUMENT_ROOT'] . $setting['sourceFolder'];
    $resultPath = $_SERVER['DOCUMENT_ROOT'] . $setting['destinationFolder'];
    $inputEncoding = $setting['inputEncoding'];
    $outputEncoding = $setting['outputEncoding'];
    $outputFileSuffix = $setting['outputFileSuffix'];

    foreach (glob($filePath . '*-report-daily.csv') as $file) {

        $change = date('d-m-Y', filemtime($file));
        $now = date('d-m-Y');

        if ($change === $now) {
            $csv = fopen($file, 'rb') or die(new Exception("ошибка открытия файла $file"));
            $csvResultPath = $resultPath . $outputFileSuffix . basename($file);
            $csvResult = fopen($csvResultPath, 'w+') or die(new Exception("ошибка открытия файла $csvResultPath"));
            $weekKey = '';
            $dayKey = '';
            $yearKey = '';
            $productNameKey = '';
            $saleKey = '';
            $i = 0;

            while (($line = fgetcsv($csv, 4000, ";")) !== false) {

                $res = arrEncoding($line, $inputEncoding, $outputEncoding);
                if ($i === 0) {
                    $weekKey = array_search('MONTH', $res);
                    $dayKey = array_search('DAY', $res);
                    $yearKey = array_search('YEAR', $res);
                    $productNameKey = array_search('PRODUCT_NAME', $res);
                    $saleKey = array_search('SALE_TYPE', $res);

                    $res[$weekKey] = 'WEEK';

                } else {
                    $checkStr = iconv($inputEncoding, 'UTF-8', $res[$productNameKey]);

                    if (!mb_strpos($checkStr, 'Агент', 0, $inputEncoding)) {

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
            if ($i == 30) {
                exit('Скрипт отработал');
            }
            fclose($csv);
            fclose($csvResult);

        }

    }

}











