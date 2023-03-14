<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(-1);
ini_set('display_errors', 'On');

class TestController extends Controller
{
    public function fetch()
    {
        if ($contracts = $this->contracts->get_contracts()) {
            // var_dump($contracts);
            foreach ($contracts as $contract) {
                // var_dump($contract->order_id);
                Onec::sendRequest($contract->order_id);
            }
        }
        exit;
    }
}