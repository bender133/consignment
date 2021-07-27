<?php


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
            while (($line = fgetcsv($csv, 4000, ";")) !== FALSE) {


                if ($i === 0) {
                    $weekKey = array_search('MONTH', $line);
                    $dayKey = array_search('DAY', $line);
                    $yearKey = array_search('YEAR', $line);
                    $productNameKey = array_search('PRODUCT_NAME', $line);
                    $saleKey = array_search('SALE_TYPE', $line);

                    $line[$weekKey] = 'WEEK';
                    $i++;
                    continue;
                }
                if (!mb_stripos($line[$productNameKey], 'Агент', 0, $inputEncoding)) {
                    $i++;
                    continue;
                }

                if (!trim($line[$saleKey])) {
                    $i++;
                    continue;
                }
                var_dump($line);
                echo '<hr>';
                exit();

                $line[$weekKey] = date('W', strtotime($line[$yearKey] . '-' . $line[$weekKey] . '-' . $line[$dayKey]));

            }

        }


        fclose($csv);
        fclose($csvResult);
    }


}











