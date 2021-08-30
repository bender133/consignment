<?php

use classes\ConsignmentService;
use classes\Logger;

require_once ('classes/ConsignmentService.php');
require_once ('classes/Logger.php');

error_reporting(E_ALL);

try {
    $consignmentService = new ConsignmentService();
    $consignmentService->goFormatCsv();
} catch (Exception $e) {
    Logger::errorLog($e->getMessage());
}