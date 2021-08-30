<?php

namespace classes;
require_once('Logger.php');

use classes\Logger;
use ErrorException;

class ConsignmentService
{
    private const AGENT_COLUMN = 'Агент';
    private $isFirstLine = TRUE;
    private $settings;

    public function __construct()
    {
        $path = getcwd();
        $isSettingsFileExist = file_exists($path . '/settings.php');

        if ($isSettingsFileExist === FALSE) {
            throw new \ErrorException('Файл settings.php отсутствует.');
        }

        $settings = include('settings.php');
        $requiredFields = ['destinationFolder', 'inputEncoding', 'outputEncoding', 'outputFileSuffix'];

        foreach ($settings as $key => $setting) {
            $existingKey = array_keys($setting);
            foreach ($requiredFields as $requiredField) {
                $isFieldExists = in_array($requiredField, $existingKey) && $setting[$requiredField] !== '';
                if ($isFieldExists === FALSE) {
                    Logger::errorLog("В массиве настроек отсутствует значение $requiredField.");
                    unset($settings[$key]);
                    break;
                }
            }
        }
        $this->settings = $settings;
    }

    private function arrEncoding(array $arr, string $inputEncoding, string $outputEncoding): array
    {
        $result = [];

        foreach ($arr as $value) {
            $result[] = iconv($inputEncoding, $outputEncoding, $value);
        }
        return $result;
    }

    /**
     * @param string $file ;
     * @return bool;
     */
    private static function     checkDate(string $file): bool
    {
        $change = date('d-m-Y', filemtime($file));
        $now = date('d-m-Y');
        return $change !== $now;
    }

    public function goFormatCsv()
    {
        foreach ($this->settings as $setting) {
            $filePath = $setting['sourceFolder'] . '/';

            if (is_dir($filePath) === FALSE) {
                throw new \ErrorException("папка $filePath не существует");
            }

            $resultPath = $setting['destinationFolder'] . '/';
            $inputEncoding = $setting['inputEncoding'];
            $outputEncoding = $setting['outputEncoding'];
            $outputFileSuffix = $setting['outputFileSuffix'];

            foreach (glob($filePath . '*-report-daily.csv') as $file) {

                if (self::checkDate($file) === TRUE) {
                    continue;
                }

                if (is_dir($resultPath) === FALSE) {
                    mkdir($resultPath, 0777, TRUE);
                }

                $csv = fopen($file, 'rb');
                $csvResultPath = $resultPath . $outputFileSuffix . basename($file);
                $csvResult = fopen($csvResultPath, 'w+');
                $weekKey = '';
                $dayKey = '';
                $yearKey = '';
                $productNameKey = '';
                $saleKey = '';

                while (($line = fgetcsv($csv, 0, ";")) !== FALSE) {
                    $line = $this->arrEncoding($line, $inputEncoding, $outputEncoding);

                    if ($this->isFirstLine === TRUE) {
                        $weekKey = array_search('MONTH', $line);
                        $dayKey = array_search('DAY', $line);
                        $yearKey = array_search('YEAR', $line);
                        $productNameKey = array_search('PRODUCT_NAME', $line);
                        $saleKey = array_search('SALE_TYPE', $line);
                        $line[$weekKey] = 'WEEK';
                        $this->isFirstLine = FALSE;
                    } else {
                        $checkStr = iconv($inputEncoding, 'UTF-8', $line[$productNameKey]);
                        $isAgentExists = mb_stristr($checkStr, self::AGENT_COLUMN, 0, 'UTF-8');
                        $isSaleTypeExists = trim($line[$saleKey]) !== '';

                        if ($isAgentExists === FALSE || $isSaleTypeExists === FALSE) {
                            continue;
                        }
                        $line[$weekKey] = date('W', strtotime($line[$yearKey] . '-' . $line[$weekKey] . '-' . $line[$dayKey]));
                    }
                    fputcsv($csvResult, $line, ';');
                }
                $this->isFirstLine = TRUE;
                fclose($csv);
                fclose($csvResult);
                Logger::log(basename($file) . ' completed');
                echo basename($file) . " completed \n";
            }
        }
    }
}